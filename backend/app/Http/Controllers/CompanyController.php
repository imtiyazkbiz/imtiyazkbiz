<?php

namespace App\Http\Controllers;

use App\Http\Traits\CommonTrait;
use App\Http\Traits\CompanyTrait;
use App\Models\CompanyCoursesModel;
use App\Models\CompanyDocumentModel;
use App\Models\CompanyModel;
use App\Models\CompanyTypeModel;
use App\Models\CourseFolderModel;
use App\Models\CourseModel;
use App\Models\EmployeeCompanyLocationsModel;
use App\Models\EmployeeCoursesModel;
use App\Models\EmployeeModel;
use App\Models\MinLocationSignupModel;
use App\Models\PaymentModel;
use App\Models\PricePlanModel;
use App\Models\PromoCodeModel;
use App\Models\PromoCodeReportModel;
use App\Models\UserOnboarding;
use Auth;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Intervention\Image\Facades\Image;
use Validator;

class CompanyController extends Controller {
    use CompanyTrait, CommonTrait;

    private $secret_key;
    private $base_url;
    private $profile_id;
    private $profile_key;
    private $status;
    private $sucess;
    private $fail;
    private $food_manager_course;


    /**
     * Construct function
     */
    public function __construct() {
        $this->secret_key = env('STRIPE_SECRET_KEY');
        $this->base_url = env('MARCHANTE_SOLUTION_URL');
        $this->profile_id = env('MARCHANTE_SOLUTION_PROFILE_ID');
        $this->profile_key = env('MARCHANTE_SOLUTION_PROFILE_KEY');
        $this->status = config('constant.status');
        $this->sucess = config('constant.success');
        $this->fail = config('constant.fail');
        $this->food_manager_course = config('constant.food_manager_course');
    }


    public function register(Request $request) {


        Validator::extend('base64image', function($attribute, $value, $parameters, $validator) {
            try {
                $image = Image::make($value);
                $size = strlen(base64_decode($value));
                $size_kb = $size / 1024;

                return $size_kb <= $parameters[0];
            } catch (Exception $e) {
                return FALSE;
            }
        });


        $validator = Validator::make($request->all(), [
            'company_name' => 'required',
            'first_name' => 'required',
            'last_name' => 'required',
            'image' => 'base64image:3072',
        ], [
            'image.base64image' => 'Image is too big! Image max size should be 2 MB.',
        ]);

        if ($validator->fails()) {
            $errors = json_decode($validator->errors());
            foreach ($errors as $error) {
                $message[] = $error[0];
            }
            $message = implode(", ", $message);

            return response()->json(['message' => $message], 422);
        }

        try {
            DB::beginTransaction();
            if (!empty($request->company_email)) {
                if (CommonTrait::isValidEmail($request->company_email) == FALSE) {

                    return response()->json(['message' => "Email is not valid, Enter correct email."], 422);
                }
                $isEmailExist = EmployeeModel::where('email', $request->company_email)->orWhere('user_name', $request->company_email)->first();
                if ($isEmailExist != NULL) {

                    return response()->json(['message' => "Email already exists, Try another."], 422);
                }
            }
            if (!empty($request->company_phone)) {
                if (CommonTrait::isValidPhone($request->company_phone) == FALSE) {

                    return response()->json(['message' => "Phone number is not correct,  Phone number should be 10 digit."], 422);
                }
            }

            if ($request->special_courses) {
                $special_course_id = [];
                $special_course_users = [];
                foreach ($request->special_courses as $specialCourse) {
                    if ($specialCourse['users'] != 0) {
                        array_push($special_course_id, $specialCourse['id']);
                        array_push($special_course_users, $specialCourse['users']);
                    }
                }
            }

            // $paymentId = 0;
            // $isPaymentResponse = array();
            $payment_request = $request->payment;
            if (!empty($payment_request)) {
                $paymentResponse = PaymentModel::stripeCustomer($request->company_name, $request->company_email, $this->secret_key, $payment_request);
                if ($paymentResponse['status'] == 'error') {

                    return response()->json([
                        'status' => 'error',
                        'message' => $paymentResponse['message'],
                    ], 422);
                }
            }

            $logoname = "";
            if (!empty($request->image)) {
                $path = 'images/';
                $logoname = CommonTrait::storeBaseEncodeImage($path, $request->image);
            }
            $username = $request->username;
            $first_name = $request->first_name;
            $last_name = $request->last_name;
            $email = $request->company_email;
            $password = $request->company_password;
            $name = $request->company_name;
            $payment = $request->payment;
            $card_info = $request->card_info;
            $this->companyZip($request->company_zip, $request->company_city, $request->company_state);
            $company = new CompanyModel();
            $company->name = $request->company_name;
            $company->admin = $request->company_admin;
            $company->location_num = $request->company_location_num;
            $company->employee_num = $request->company_employee_num;
            $company->type = $request->company_type;
            if ($request->parent_id) {
                $company->parent_id = $request->parent_id;
            }
            $company->price_plan = $request->price_plan;
            $company->status = empty($request->status) ? 0 : $request->status;
            $company->sms_status = empty($request->sms_status) ? 0 : $request->sms_status;
            $company->pay_employee_status = empty($request->company_pay_by_employee_status) ? 0 : $request->company_pay_by_employee_status;
            $company->pay_employee_discount = $request->company_pay_by_employee_discount;
            $company->website = $request->website;
            $company->address_1 = $request->company_address_1;
            if (!empty($request->company_address_2)) {
                $company->address_2 = $request->company_address_2;
            }
            $company->phone = $request->company_contact;
            $company->email = $request->company_email;
            $company->city = $request->company_city;
            $company->state = $request->company_state;
            $company->company_zip = $request->company_zip;
            $company->document_status = $request->document_status;
            $company->logo = $logoname;
            $company->notes = $request->notes;
            $company->save();
            $company_id = $company->id;
            $role = 2;
            $employee = NULL;
            $user_type = "admin";
            $phone = preg_replace('~\D~', '', $request->company_phone);
            $user_id = $this->companyAdmin($username, $first_name, $last_name, $password, $role, $employee, $user_type, $phone);
            CompanyModel::where('id', $company_id)->update(['employee_id' => $user_id]);
            $data = new EmployeeCompanyLocationsModel();
            $data->employee_id = $user_id;
            TestingDebuggingController::logging('$request', $request->all());
            if ($request->parent_id) {
                TestingDebuggingController::logging('1');
                $data->company_id = $request->parent_id;
                $data->location_id = $company_id;
            } else {
                TestingDebuggingController::logging('2');
                $data->company_id = $company_id;
            }
            $data->save();

            TestingDebuggingController::logging('$data', $data);

            if ($request->document_text) {
                $company_employee = EmployeeCompanyLocationsModel::select('employee_id')->where('company_id', $company_id)->groupby('employee_id')->get();
                $employees = [];
                foreach ($company_employee as $employee) {
                    array_push($employees, $employee->employee_id);
                }
                EmployeeModel::whereIn('id', $employees)->update(['is_onboarding_signed' => 0]);
                DB::table('tbl_company_documents')->where('company_id', $company_id)->delete();
                foreach ($request->document_text as $document) {
                    DB::table('tbl_company_documents')->insert([
                        'company_id' => $company_id,
                        'document' => $document['text'],
                        'created_at' => Carbon::now('UTC'),
                    ]);
                }
            }


            if (!empty($request->course_ids)) {

                CourseModel::assignCoursetoCompany($request->course_ids, $company_id);
            }
            if (!empty($special_course_id)) {

                CourseModel::assignCoursetoCompany($special_course_id, $company_id);
            }


            //     if ($paymentId != 0 &&  !empty($isPaymentResponse)) {
            if (!empty($payment_request)) {
                // Send Email to Support
                $courses = CourseModel::whereIn('id', $request->course_ids)->get();
                $courses1 = CourseModel::whereIn('id', $special_course_id)->get();
                $course_names = array();
                //  $non_dis_course_names = array();
                $i = 0;
                $reminder = 0;
                if ($courses->count() > 0) {
                    foreach ($courses as $course) {
                        $course_names[$i]['name'] = $course->name;
                        $i++;
                    }
                }
                if ($courses1->count() > 0) {
                    foreach ($courses1 as $course) {
                        $course_names[$i]['name'] = $course->name;
                        $i++;
                    }
                }

                if ($request->promo_code) {
                    $promoCode = PromoCodeModel::where('name', $request->promo_code)->first();
                    $promoReport = new PromoCodeReportModel();
                    $promoReport->user_id = $user_id;
                    $promoReport->promocode_id = $promoCode->id;
                    if ($payment_request['payment_type'] == 'monthly') {
                        $promoReport->total_amount = $request->course_cost_monthly;
                    } else {
                        $promoReport->total_amount = $request->course_cost_yearly;
                    }
                    $promoReport->amount_paid = $payment_request['transaction_amount'];
                    $promoReport->created_at = Carbon::now("UTC");
                    $promoReport->save();
                }

                $newsignupdata = array(
                    'date' => Carbon::now('UTC')->format("m-d-Y"),
                    'interface' => "Company",
                    'first_name' => $request->first_name,
                    'last_name' => $request->last_name,
                    'name' => $request->company_name,
                    'address' => $request->company_address_1 . ', ' . $request->company_city . ', ' . $request->company_state . ', ' . $request->company_zip,
                    'phone_no' => $request->company_phone,
                    'street_address' => $request->company_address_1,
                    'zip_code' => $request->company_zip,
                    'email' => $request->company_email,
                    'location_count' => $request->company_location_num,
                    'user_count' => $request->company_employee_num,
                    'courses' => $course_names,
                    'plan_detail' => $payment_request['payment_type'],
                    // 'credit_card' => $payment_request['card_number'],
                    // 'expiry'=> $payment_request['card_exp_date'],
                    'fee' => $payment_request['transaction_amount'],
                    'promo_code' => $request->promo_code ? $request->promo_code : '',
                    'course_cost' => $payment_request['payment_type'] == "monthly" ? $request->course_cost_monthly : $request->course_cost_yearly,
                    'discounted_cost' => $request->discounted_cost ? $request->discounted_cost : '',
                );
                $supportEmail = config('mail.support');
                Mail::send('new_signup', $newsignupdata, function($message) use ($supportEmail) {
                    $message->to($supportEmail)->subject('New Company Signup - ' . env('SITE_NAME') . '!');
                    $message->from(env('MAIL_FROM_ADDRESS'), env('MAIL_FROM_NAME'));
                });
                CommonTrait::emailLog("New Company Sign Up", $supportEmail, 0);

            }

            DB::commit();

            return response()->json($company, 200);

        } catch (Exception $ex) {
            DB::rollback();

            return response()->json(['message' => $ex->getMessage], 422);
        }
    }

    public function registerMinLocation(Request $request) {

        Validator::extend('base64image', function($attribute, $value, $parameters, $validator) {
            try {
                $image = Image::make($value);
                $size = strlen(base64_decode($value));
                $size_kb = $size / 1024;

                return $size_kb <= $parameters[0];
            } catch (Exception $e) {
                return FALSE;
            }
        });


        $validator = Validator::make($request->all(), [
            'company_name' => 'required',
            'first_name' => 'required',
            'last_name' => 'required',
            'image' => 'base64image:3072',
        ], [
            'image.base64image' => 'Image is too big! Image max size should be 2 MB.',
        ]);

        if ($validator->fails()) {
            $errors = json_decode($validator->errors());
            foreach ($errors as $error) {
                $message[] = $error[0];
            }
            $message = implode(", ", $message);

            return response()->json(['message' => $message], 422);
        }

        try {
            if (!empty($request->company_email)) {
                if (CommonTrait::isValidEmail($request->company_email) == FALSE) {

                    return response()->json(['message' => "Email is not valid, Enter correct email."], 422);
                }
                $isEmailExist = EmployeeModel::where('email', $request->company_email)->orWhere('user_name', $request->company_email)->first();
                if ($isEmailExist != NULL) {

                    return response()->json(['message' => "Email already exists, Try another."], 422);
                }
            }
            if (!empty($request->company_phone)) {
                if (CommonTrait::isValidPhone($request->company_phone) == FALSE) {

                    return response()->json(['message' => "Phone number is not correct,  Phone number should be 10 digit."], 422);
                }
            }


            $paymentId = 0;
            $isPaymentResponse = array();
            $payment_request = $request->payment;
            if (!empty($payment_request)) {
                $invoice = CommonTrait::generateUniqueId();
                $payment_request['invoice_number'] = $invoice;
                $payment_request['interface'] = "company_signup";
                $paymentResponse = PaymentModel::storeCardofUserForPayment($this->base_url, $this->profile_id, $this->profile_key, $payment_request);
                if ($paymentResponse['status'] == 'error') {

                    return response()->json([
                        'status' => 'error',
                        'message' => $paymentResponse['data'],
                    ], 422);
                }

                $courseData = array(
                    'course_ids' => $request->course_ids,
                    'number_of_locations' => isset($request->company_location_num) ? $request->company_location_num : 0,
                    'number_of_users' => isset($request->company_employee_num) ? $request->company_employee_num : 0,
                );
                $paymentId = PaymentModel::insertGetId([
                    'user_id' => 0,
                    'email' => $request->company_email,
                    'event_type' => '1-3 Location Signup',
                    'transaction_id' => $paymentResponse['data']['transaction_id'],
                    'error_code' => $paymentResponse['data']['error_code'],
                    'payment_type' => ($payment_request['payment_type'] == 'monthly') ? 1 : (($payment_request['payment_type'] == 'yearly') ? 2 : 3),
                    'payment_status' => isset($paymentResponse['data']['avs_result']) ? $paymentResponse['data']['avs_result'] : 'F',
                    'auth_response_text' => $paymentResponse['data']['auth_response_text'],
                    'amount' => $payment_request['transaction_amount'],
                    'courses' => json_encode($courseData),
                    'invoice_number' => $invoice,
                    'response' => json_encode($paymentResponse),
                    'created_at' => Carbon::now('UTC'),
                ]);


                if ($paymentResponse['data']['error_code'] != '000') {

                    return response()->json(['message' => $paymentResponse['data']['auth_response_text']], 422);
                }

                if (isset($paymentResponse['data']['avs_result']) && $paymentResponse['data']['avs_result'] == 'N') {

                    return response()->json(['message' => $paymentResponse['data']['auth_response_text']], 422);
                }

                $isPaymentResponse = $paymentResponse;
            }


            $logoname = "";
            if (!empty($request->image)) {
                $path = 'images/';
                $logoname = CommonTrait::storeBaseEncodeImage($path, $request->image);
            }
            $username = $request->username;
            $first_name = $request->first_name;
            $last_name = $request->last_name;
            $email = $request->company_email;
            $password = $request->company_password;
            $name = $request->company_name;
            $payment = $request->payment;
            $card_info = $request->card_info;
            $this->companyZip($request->company_zip, $request->company_city, $request->company_state);
            $company = new CompanyModel();
            $company->name = $request->company_name;
            $company->admin = $request->company_admin;
            $company->location_num = $request->company_location_num;
            $company->employee_num = $request->company_employee_num;
            $company->type = $request->company_type;
            if ($request->parent_id) {
                $company->parent_id = $request->parent_id;
            }
            $company->price_plan = $request->price_plan;
            $company->status = empty($request->status) ? 0 : $request->status;
            $company->sms_status = empty($request->sms_status) ? 0 : $request->sms_status;
            $company->pay_employee_status = empty($request->company_pay_by_employee_status) ? 0 : $request->company_pay_by_employee_status;
            $company->pay_employee_discount = $request->company_pay_by_employee_discount;
            $company->website = $request->website;
            $company->address_1 = $request->company_address_1;
            if (!empty($request->company_address_2)) {
                $company->address_2 = $request->company_address_2;
            }
            $company->phone = $request->company_phone;
            $company->email = $request->company_email;
            $company->city = $request->company_city;
            $company->state = $request->company_state;
            $company->company_zip = $request->company_zip;
            $company->logo = $logoname;
            $company->save();
            $company_id = $company->id;
            $role = 2;
            $employee = NULL;
            $user_type = "admin";
            $phone = preg_replace('~\D~', '', $request->company_phone);
            $user_id = $this->companyAdmin($username, $first_name, $last_name, $password, $role, $employee, $user_type, $phone);
            CompanyModel::where('id', $company_id)->update(['employee_id' => $user_id]);
            $data = new EmployeeCompanyLocationsModel();
            $data->employee_id = $user_id;
            if ($request->parent_id) {
                $data->company_id = $request->parent_id;
                $data->location_id = $company_id;
            } else {
                $data->company_id = $company_id;
            }
            $data->save();

            if ($request->document_text) {
                $company_employee = EmployeeCompanyLocationsModel::select('employee_id')->where('company_id', $company_id)->groupby('employee_id')->get();
                $employees = [];
                foreach ($company_employee as $employee) {
                    array_push($employees, $employee->employee_id);
                }
                EmployeeModel::whereIn('id', $employees)->update(['is_onboarding_signed' => 0]);
                DB::table('tbl_company_documents')->where('company_id', $company_id)->delete();
                foreach ($request->document_text as $document) {
                    DB::table('tbl_company_documents')->insert([
                        'company_id' => $company_id,
                        'document' => $document['text'],
                        'created_at' => Carbon::now('UTC'),
                    ]);
                }
            }


            if (!empty($request->course_ids)) {

                CourseModel::assignCoursetoCompany($request->course_ids, $company_id);
            }


            if ($paymentId != 0 && !empty($isPaymentResponse)) {
                if (!empty($payment_request)) {
                    $saveDetail = new MinLocationSignupModel();
                    $saveDetail->company_id = $company_id;
                    $saveDetail->course_ids = json_encode($request->course_ids);
                    $saveDetail->is_food_manager = $request->is_foodManager;
                    $saveDetail->no_of_manager = $request->no_of_managers;
                    $saveDetail->is_sexual_harassment = $request->is_sexualHarassment;
                    $saveDetail->is_text_on = $request->sms_notification;
                    $saveDetail->created_at = Carbon::now('UTC');
                    $saveDetail->save();

                    // Send Email to Support
                    $courses = CourseModel::whereIn('id', $request->course_ids)->get();
                    $course_names = array();
                    //  $non_dis_course_names = array();
                    $i = 0;
                    $reminder = 0;
                    if ($courses->count() > 0) {
                        foreach ($courses as $course) {
                            $course_names[$i]['name'] = $course->name;
                            $i++;
                        }
                    }

                    $newsignupdata = array(
                        'date' => Carbon::now('UTC')->format("m-d-Y"),
                        'interface' => "Company",
                        'first_name' => $request->first_name,
                        'last_name' => $request->last_name,
                        'name' => $request->company_name,
                        'address' => $request->company_address_1 . ', ' . $request->company_city . ', ' . $request->company_state . ', ' . $request->company_zip,
                        'phone_no' => $request->company_phone,
                        'street_address' => $request->company_address_1,
                        'zip_code' => $request->company_zip,
                        'email' => $request->company_email,
                        'location_count' => $request->company_location_num,
                        'user_count' => $request->company_employee_num,
                        'courses' => $course_names,
                        'plan_detail' => $payment_request['payment_type'],
                        'credit_card' => $payment_request['card_number'],
                        'expiry' => $payment_request['card_exp_date'],
                        'fee' => $payment_request['transaction_amount'],

                    );
                    $supportEmail = config('mail.support');
                    Mail::send('new_signup', $newsignupdata, function($message) use ($supportEmail) {
                        $message->to($supportEmail)->subject('New 1-3 Location Signup - ' . env('SITE_NAME') . '!');
                        $message->from(env('MAIL_FROM_ADDRESS'), env('MAIL_FROM_NAME'));
                    });
                    CommonTrait::emailLog("New Company Sign Up", $supportEmail, 0);

                }
                // email sended to support

                //Send payement receipt email to user
                if ($request->company_email) {
                    $reciptdata = array(
                        'type' => 'Company',
                        'first_name' => $request->first_name,
                        'last_name' => $request->last_name,
                        'courses' => $course_names,
                        'plan_detail' => $payment_request['payment_type'],
                        'credit_card' => $payment_request['card_number'],
                        'expiry' => $payment_request['card_exp_date'],
                        'fee' => $payment_request['transaction_amount'],
                    );

                    $userEmail = $request->company_email;
                    Mail::send('payment_receipt', $reciptdata, function($message) use ($userEmail) {
                        $message->to($userEmail)->subject('Payment Receipt - ' . env('SITE_NAME') . '!');
                        $message->from(env('MAIL_FROM_ADDRESS'), env('MAIL_FROM_NAME'));
                    });
                    CommonTrait::emailLog("New Company Sign Up - Payment Receipt", $userEmail, 0);
                }


                //Sent payement receipt email to user
                PaymentModel::where('id', $paymentId)->update([
                    'user_id' => $user_id,
                ]);
                $isUserCard = DB::table('tbl_user_card_store')->where('user_id', $user_id)->first();
                if ($isUserCard == NULL) {
                    DB::table('tbl_user_card_store')->insert([
                        'user_id' => $user_id,
                        'card_id' => $paymentResponse['data']['card_id'],
                        'created_at' => Carbon::now('UTC'),
                    ]);
                } else {
                    DB::table('tbl_user_card_store')->where('id', $isUserCard->id)->update([
                        'card_id' => $paymentResponse['data']['card_id'],
                        'updated_at' => Carbon::now('UTC'),
                    ]);
                }
                if ($payment_request['payment_type'] == 'monthly' || $payment_request['payment_type'] == 'yearly') {
                    DB::table('tbl_payment_cron_scheduling')->insert([
                        'user_id' => $user_id,
                        'type' => 'company',
                        'recurring' => ($payment_request['payment_type'] == 'monthly') ? 1 : 2,
                        'processing_date' => Carbon::now('UTC')->format('Y-m-d'),
                        'next_processing_date' => $payment_request['payment_type'] == 'monthly' ? date('Y-m-d', strtotime(Carbon::now('UTC')->format('Y-m-d') . ' + 30 days')) : date('Y-m-d', strtotime(Carbon::now('UTC')->format('Y-m-d') . ' + 365 days')),
                        'processed' => 0,
                        'transaction_id' => $paymentResponse['data']['transaction_id'],
                        'recurring_pmt_num' => 1,
                        'recurring_pmt_count' => $payment_request['payment_type'] == 'monthly' ? 36 : 3,
                        'comments' => 'User sign up as company',
                        'status' => 1,
                        'created_at' => Carbon::now('UTC'),
                    ]);
                }
            }


            return response()->json($company, 200);

        } catch (Exception $ex) {

            return response()->json(['message' => $ex->getMessage], 422);
        }
    }

    public function welcomeEmail(Request $request) {
        $password = $request->password;
        $ids = $request->ids;
        foreach ($ids as $id) {
            $company_data = CompanyModel::find($id['id']);
            $user_id = $company_data->employee_id;
            $employee_data = EmployeeModel::where('id', $user_id)->get();
            if (!empty($company_data->email)) {
                $email = $company_data->email;
                $data = array(
                    'first_name' => $employee_data[0]->first_name,
                    'last_name' => $employee_data[0]->last_name,
                    'company_name' => $company_data->name,
                    'email' => $company_data->email,
                    'access_code' => $password,
                );
                Mail::send('welcome_company', $data, function($message) use ($email) {
                    $message->to($email)->subject('Your ' . env('SITE_NAME') . ' profile is now active!');
                    $message->from(env('MAIL_FROM_ADDRESS'), env('MAIL_FROM_NAME'));
                });
                CommonTrait::emailLog("Welcome company", $email, $user_id);
            }
        }

        return response()->json(['Email' => "Email sent successfully!"], 200);
    }

    public function update(Request $request, $id) {

        Validator::extend('base64image', function($attribute, $value, $parameters, $validator) {
            try {
                $image = Image::make($value);
                $size = strlen(base64_decode($value));
                $size_kb = $size / 1024;

                return $size_kb <= $parameters[0];
            } catch (Exception $e) {
                return FALSE;
            }
        });

        $validator = Validator::make($request->all(), [
            'company_name' => 'required',
            'image' => 'base64image:3072',
        ], [
            'image.base64image' => 'Image is too big! Image max size should be 2 MB.',
        ]);
        if ($validator->fails()) {
            $errors = json_decode($validator->errors());
            foreach ($errors as $error) {
                $message[] = $error[0];
            }
            $message = implode(", ", $message);

            return response()->json(['message' => $message], 422);
        }
        try {
            $name = "";
            if (!empty($request->image)) {
                $image = $request->image;
                $name = time() . '.' . explode('/', explode(':', substr($image, 0, strpos($image, ';'))) [1]) [1];
                Image::make($request->image)->save(public_path('images/') . $name);
            }
            $company = CompanyModel::find($id);
            if (empty($company)) {

                return response()->json(['message' => 'Invalid id no company found.!'], 422);
            }

            $company_email = $company->company_email;
            $this->companyZip($request->company_zip, $request->company_city, $request->comany_state);

            if ($name == "") {
                CompanyModel::where('id', $id)->update([
                    'name' => $request->company_name,
                    'admin' => $request->company_admin,
                    'location_num' => $request->company_location_num,
                    'employee_num' => $request->company_employee_num,
                    'phone' => $request->company_contact,
                    'address_1' => $request->company_address_1,
                    'address_2' => $request->company_address_2,
                    'company_zip' => $request->company_zip,
                    'type' => $request->company_type,
                    'parent_id' => $request->parent_id,
                    'price_plan' => $request->company_price_plan,
                    'website' => $request->company_website,
                    'status' => $request->company_status,
                    'sms_status' => $request->company_smsstatus,
                    'pay_employee_status' => $request->company_pay_by_employee_status,
                    'pay_employee_discount' => $request->company_pay_by_employee_discount,
                    'secondary_course_status' => $request->secondary_course_status,
                    'document_status' => $request->document_status,
                    'notes' => $request->notes,
                ]);
            } else {
                CompanyModel::where('id', $id)->update([
                    'name' => $request->company_name,
                    'admin' => $request->company_admin,
                    'location_num' => $request->company_location_num,
                    'employee_num' => $request->company_employee_num,
                    'phone' => $request->company_contact,
                    'address_1' => $request->company_address_1,
                    'address_2' => $request->company_address_2,
                    'company_zip' => $request->company_zip,
                    'type' => $request->company_type,
                    'parent_id' => $request->parent_id,
                    'price_plan' => $request->company_price_plan,
                    'website' => $request->company_website,
                    'status' => $request->company_status,
                    'sms_status' => $request->company_smsstatus,
                    'logo' => $name,
                    'pay_employee_status' => $request->company_pay_by_employee_status,
                    'pay_employee_discount' => $request->company_pay_by_employee_discount,
                    'secondary_course_status' => $request->secondary_course_status,
                    'document_status' => $request->document_status,
                    'notes' => $request->notes,
                ]);
            }

            if ($request->year) {
                $ifAlreadyExist = DB::table('tbl_company_food_manager')->where('company_id', $id)->where('year', $request->year)->get();
                if (count($ifAlreadyExist) > 0) {
                    DB::table('tbl_company_food_manager')->where('company_id', $id)->where('year', $request->year)->update(['fm_certificate_count' => $request->company_fm_certificate_count]);
                } else {
                    DB::table('tbl_company_food_manager')->insert([
                        'company_id' => $id,
                        'year' => $request->year,
                        'fm_certificate_count' => $request->company_fm_certificate_count,
                        'created_at' => Carbon::now('UTC'),
                    ]);
                }
            }

            if ($request->parent_id) {
                //$employeeCompanyLocations = EmployeeCompanyLocationsModel::where('location_id', $id)->get();
                /*if (!empty($employeeCompanyLocations)) {
                    foreach ($employeeCompanyLocations as $employeeCompanyLocation) {
                        $employeeCompanyLocation->company_id = $request->parent_id;
                        $employeeCompanyLocation->save();
                    }
                }*/

                $employeeCompanyLocations1 = EmployeeCompanyLocationsModel::where('company_id', $id)->where('location_id', 0)->get();
                foreach ($employeeCompanyLocations1 as $employeeCompanyLocation1) {
                    $employeeCompanyLocation1->company_id = $request->parent_id;
                    $employeeCompanyLocation1->location_id = $id;
                    $employeeCompanyLocation1->save();
                }
            }

            if (!empty($request->course_ids)) {
                $getCourses = CompanyCoursesModel::whereNotIn('course_id', $request->course_ids)->where(['company_id' => $id])->get();


                DB::table('tbl_company_courses')->where(['company_id' => $id])->delete();

                CourseModel::assignCoursetoCompany($request->course_ids, $id);
                foreach ($getCourses as $courses) {
                    $getempData = EmployeeCoursesModel::leftjoin('tbl_employee_company_locations', 'tbl_employee_company_locations.employee_id', '=', 'tbl_employee_courses.employee_id')->where('tbl_employee_courses.course_id', $courses->course_id)->where('tbl_employee_courses.employee_course_status', '!=', '1')->where('tbl_employee_company_locations.company_id', $id)->delete();
                }
            }

            if (!empty($request->folder_ids)) {
                DB::table('tbl_company_coursefolders')->where(['company_id' => $id])->delete();
                CourseModel::assignCourseFoldertoCompany($request->folder_ids, $id);
            }

            return response()->json(['Company' => 'update successfully..!'], 200);


        } catch (Exception $th) {

            return response()->json(['message' => $th->getMessage()], 422);
        }
    }

    public function getData(Request $request) {
        $email = $request->email;
        $user = Auth::user();
        EmployeeModel::where('id', $user->id)->update(['last_sign_in' => date('Y-m-d')]);
        $companyId = CompanyModel::getCompantIdByEmployeeId($user->id);
        $data = CompanyModel::where('id', $companyId)->get();
        foreach ($data as $data1) {
            if ($data1->parent_id == "0") {
                $data['level'] = "parent";
            }
        }

        $data['admin_id'] = $user->id;

        return response()->json($data, 200);
    }

    public function getManagerData(Request $request) {
        try {
            $user = Auth::user();
            EmployeeModel::where('id', $user->id)->update(['last_sign_in' => date('Y-m-d')]);

            return response()->json([$user], 200);
        } catch (Exception $th) {

            return response()->json(['message' => $th->getMessage()], 422);
        }
    }

    public function employeeUpdate(Request $request, $id) {
        if (!empty($request->employee_email)) {
            EmployeeModel::where('id', $id)->update([
                'first_name' => $request->employee_first_name,
                'last_name' => $request->employee_last_name,
                'email' => $request->employee_email,
                'phone_num' => $request->employee_phone_num,
                'job_title' => $request->employee_job_title,
                'type' => $request->employee_type,
                'location_id' => $request->employee_location_id,
            ]);
        } else {
            EmployeeModel::where('id', $id)->update([
                'first_name' => $request->employee_first_name,
                'last_name' => $request->employee_last_name,
                'phone_num' => $request->employee_phone_num,
                'job_title' => $request->employee_job_title,
                'type' => $request->employee_type,
                'location_id' => $request->employee_location_id,
            ]);
        }

        return response()->json(['Employee' => 'Update Successfully..!'], 200);
    }

    public function get($id) {
        try {
            $getUser = Auth::User();
            if ($getUser->role_id == 3) {
                $managerLocation = EmployeeCompanyLocationsModel::where('employee_id', $id)->select('location_id')->get()->toArray();
                $locationIds = array_column($managerLocation, 'location_id');

                $company = CompanyModel::with('location')->whereIn('id', $locationIds)->get()->toArray();
                if (empty($company)) {

                    return response()->json(['Company' => 'Invalid id no company found.!'], 422);
                }
                foreach ($company as $key => $value) {
                    $company[$key]['employee_id'] = $getUser->id;
                }
            } else if ($getUser->role_id == 2) {
                $company = CompanyModel::with('location')->where('id', $id)->get()->toArray();
            } else {

                $company = CompanyModel::with('location')->where('id', $id)->get()->toArray();
            }
            $company[0]['food_manager_year'] = DB::table('tbl_company_food_manager')->select('year')->where('company_id', $id)->where('year', date('Y'))->first();

            $company[0]['food_manager_total_count'] = DB::table('tbl_company_food_manager')->select('fm_certificate_count')->where('company_id', $id)->where('year', date('Y'))->first();
            $company[0]['company_documents'] = DB::table('tbl_company_documents')->where('company_id', $id)->get();
            $company[0]['admin'] = CompanyModel::getCompnayAdmin($company[0]['id'], $company[0]['parent_id']);
            $company[0]['manager'] = CompanyModel::getCompnayManager($company[0]['id'], $company[0]['parent_id']);
            $company[0]['courses'] = CourseModel::leftJoin('tbl_company_courses', 'tbl_company_courses.course_id', '=', 'tbl_course.id')->where('tbl_company_courses.company_id', $id)->get();
            $company[0]['folders'] = CourseFolderModel::leftJoin('tbl_company_coursefolders', 'tbl_company_coursefolders.folder_id', '=', 'tbl_course_folder.id')->where('tbl_company_coursefolders.company_id', $id)->get();

            return response()->json($company, 200);
        } catch (Exception $th) {

            return response()->json($th->getMessage(), 422);
        }
    }

    public function companyEmployee($id) {
        try {
            $employee_data = EmployeeModel::where('id', $id)->first();
            $is_companies = EmployeeCompanyLocationsModel::where('tbl_employee_company_locations.employee_id', $id)->get();
            $companys = array();
            if ($is_companies->count() > 0) {
                foreach ($is_companies as $key => $value) {
                    if ($value->location_id == 0) {
                        $company = CompanyModel::where('id', $value->company_id)->first();
                    } else {
                        $company = CompanyModel::where('id', $value->location_id)->first();
                    }
                    $companys[$key] = $company;
                }
            }
            $employee_data->company = $companys;

            return response()->json([$employee_data], 200);

        } catch (Exception $th) {

            return response()->json(['message' => $th->getMessage()], 422);
        }
    }

    public function companyCourse(Request $request) {
        $user = Auth::user();
        $company_id = $request->company_id;
        if ($user->role_id == 3) {
            $getParentCompany = EmployeeCompanyLocationsModel::where('employee_id', $id)->select('company_id')->first();
            $company_id = $getParentCompany->company_id;
        }
        $course_id = $request->course_id;
        $course_info = CourseModel::find($course_id);
        $course_name = $course_info['course_name'];
        $companyCourse = new CompanyCoursesModel;
        $companyCourse->company_id = $company_id;
        $companyCourse->course_id = $course_id;
        $companyCourse->save();

        return response()->json(['Course' => 'Course Buy Successfully.!'], 200);
    }

    public function companyCourseFolders($id) {
        if ($id == 0) {
            $data = array();
            $data['coursefolders'] = CourseFolderModel::all();

            return response()->json([$data], 200);
        }

        $user = Auth::user();
        if ($user->role_id == 2 || $user->role_id == 3 || $id == NULL) {
            $company = EmployeeCompanyLocationsModel::where('tbl_employee_company_locations.employee_id', $user->id)->rightJoin('tbl_company', 'tbl_company.id', '=', 'tbl_employee_company_locations.company_id')->select('company_id')->first();

            $id = $company->company_id;

        }

        $company = CompanyModel::find($id);
        if (empty($company)) {
            return response()->json(['Company' => 'Invalid id no company found.!'], 422);
        }

        if ($company->parent_id == 0) {
            $id = $company->id;
        } else {
            $id = $company->parent_id;
        }
        $data = CompanyModel::with('courses', 'coursefolders')->where('id', $id)->get();

        return response()->json($data, 200);
    }

    public function getLogo() {
        try {
            $user = Auth::user();
            $user_id = $user->id;
            $id = 0;
            $getData = EmployeeCompanyLocationsModel::where('employee_id', $user_id)->select('company_id', 'location_id')->get();
            if ($getData->count() > 1) {
                $id = $getData[0]->company_id;
            } else {
                if (($getData->count() > 0)) {
                    if ($getData[0]->location_id) {
                        $id = $getData[0]->location_id;
                        $isLogo = CompanyModel::where('id', $id)->first();

                        if ($isLogo->logo == "" || $isLogo->logo == NULL) {
                            $id = $getData[0]->company_id;
                        } else {
                            $id = $getData[0]->location_id;
                        }
                    } else {
                        $id = $getData[0]->company_id;
                    }
                }
            }
            $company = CompanyModel::find($id);
            if (empty($company)) {
                return response()->json(['Company' => 'Invalid id no company found.!'], 422);
            }
            $company_data = CompanyModel::where('id', $id)->get();

            return response()->json($company_data, 200);
        } catch (Exception $th) {
            return response()->json($th->getMessage(), 422);
        }
    }

    public function courses(Request $request) {
        try {
            $user = Auth::user();
            if ($user->role_id == 1) {
                $company_id = $request->company_id;
                $search = $request->search;
                $courseStatus = $request->company_course_status;
                $data = $this->getCompanyCourses($company_id, $search, $courseStatus);
            } else {
                $id = $request->company_id;
                if ($user->role_id == 3) {
                    $getParentCompany = EmployeeCompanyLocationsModel::where('employee_id', $id)->select('company_id', 'location_id')->orderBy('location_id', 'asc')->first();
                    $id = $getParentCompany->company_id;
                    if ($getParentCompany->location_id != 0) {
                        $id = $getParentCompany->location_id;
                    }
                }
                $search = $request->search;
                $courseStatus = $request->company_course_status;
                $company = CompanyModel::find($id);
                if (empty($company)) {

                    return response()->json(['Company' => 'Invalid id no company found.!'], 422);
                }
                $company_id = CompanyModel::getParentIdOfLocationByUserId($user->id);
                $data = $this->getCompanyCourses($company_id, $search, $courseStatus);
            }

            return response()->json($data, 200);
        } catch (Exception $th) {

            return response()->json($th->getMessage(), 422);
        }
    }

    public function assignCourse(Request $request) {
        $course_id = $request->course_id;
        $companiess = $request->companies;
        $course = CourseModel::find($course_id);
        if (is_array($companiess)) {
            $companies = $companiess;
        } else {
            $companies = array($companiess);
        }
        if (empty($course_id)) {
            return response()->json(['message' => 'Please select the course'], 422);
        } else {
            foreach ($companies as $company) {
                $companycourse = CompanyCoursesModel::where([
                    [
                        'course_id',
                        $course_id,
                    ],
                    [
                        'company_id',
                        $company,
                    ],
                ])->get();
                $count = count($companycourse);
                if ($count == 0) {
                    $companyCourse = new CompanyCoursesModel;
                    $companyCourse->company_id = $company;
                    $companyCourse->course_id = $course_id;
                    $companyCourse->save();
                }
            }

            return response()->json(['message' => 'Course Assigned Successfully'], 200);
        }
    }

    public function companyCourses($id) {
        if ($id == 0) {
            $data = array();
            $data['courses'] = CourseModel::all();

            return response()->json([$data], 200);
        }

        $user = Auth::user();
        if ($user->role_id == 2 || $user->role_id == 3 || $id == NULL) {
            $company = EmployeeCompanyLocationsModel::where('tbl_employee_company_locations.employee_id', $user->id)->rightJoin('tbl_company', 'tbl_company.id', '=', 'tbl_employee_company_locations.company_id')->select('company_id')->first();

            $id = $company->company_id;

        }

        $company = CompanyModel::find($id);
        if (empty($company)) {
            return response()->json(['Company' => 'Invalid id no company found.!'], 422);
        }

        if ($company->parent_id == 0) {
            $id = $company->id;
        } else {
            $id = $company->parent_id;
        }
        $data = CompanyModel::with('courses')->where('id', $id)->get();

        return response()->json($data, 200);
    }

    public function CourseFolder(Request $request) {
        try {
            $user = Auth::user();
            if ($user->role_id == 1) {
                $company_id = $request->company_id;
                $search = $request->search;
                $courseStatus = $request->company_course_status;
                $data = $this->getCompanyCourseFolders($company_id, $search, $courseStatus);
            } else {
                $id = $request->company_id;
                if ($user->role_id == 3) {
                    $getParentCompany = EmployeeCompanyLocationsModel::where('employee_id', $id)->select('company_id', 'location_id')->orderBy('location_id', 'asc')->first();
                    $id = $getParentCompany->company_id;
                    if ($getParentCompany->location_id != 0) {
                        $id = $getParentCompany->location_id;
                    }
                }
                $search = $request->search;
                $courseStatus = $request->company_course_status;
                $company = CompanyModel::find($id);
                if (empty($company)) {

                    return response()->json(['Company' => 'Invalid id no company found.!'], 422);
                }
                $company_id = CompanyModel::getParentIdOfLocationByUserId($user->id);

                $data = $this->getCompanyCourseFolders($company_id, $search, $courseStatus);
            }

            return response()->json($data, 200);
        } catch (Exception $th) {

            return response()->json($th->getMessage(), 422);
        }
    }

    public function companyAllCourses($id) {
        if ($id == 0) {
            $user = Auth::user();
            $user_id = $user->id;
            $employeeData = EmployeeCompanyLocationsModel::where('employee_id', $user_id)->first();
            $id = $employeeData->company_id;
        }
        $company = CompanyModel::where('id', $id)->first();
        if ($company == NULL) {
            return response()->json(['Company' => 'Invalid id no company found.!'], 422);
        }
        if ($company->parent_id == 0) {
            $company_id = $company->id;
        } else {
            $company_id = $company->parent_id;
        }
        $data = $company;
        $data['courses'] = CourseModel::leftJoin('tbl_company_courses', 'tbl_company_courses.course_id', '=', 'tbl_course.id')->where('tbl_company_courses.company_id', $company_id)->where('tbl_course.status', '1')->orderby('tbl_course.name', 'asc')->get();

        return response()->json([$data], 200);
    }

    public function stats($id) {
        $user = Auth::user();
        $data = [];
        $where_data_manager = [];
        if ($user->role_id == 3) {

            $where_data_manager[] = [
                'tbl_employee.role_id',
                '!=',
                '2',
            ];
        }
        $companies = CompanyModel::getCompaniesByAdminUser($user->id);
        $companyIds = [];
        if ($companies['isParent'] != 0) {
            if (is_array($companies['isParent'])) {
                $companyIds = $companies['isParent'];
            } else {
                $companyIds = array(
                    $companies['isParent'],
                );
            }
            $where_dataa = "tbl_employee_company_locations.company_id";
            $where_data_location = "parent_id";
        } else {
            $companyIds = $companies['isLocations'];
            $where_dataa = "tbl_employee_company_locations.location_id";
            $where_data_location = "id";
        }
        $getParentCompany = EmployeeCompanyLocationsModel::where('employee_id', $user->id)->first();

        $location = CompanyModel::whereIn($where_data_location, $companyIds)->where('status', 1)->count();

        // Company Admin Dashboard Filters
        $company = CompanyModel::find($id);
        $whereData = [];

        $employeeWhereClause = 'tbl_employee_company_locations.location_id';
        $employeeCompanyId1 = [$id];

        $employeeCompanyId = $employeeLocationId = 0;

        $employeeLocationId = $id;
        $employeeCompanyId = $company->parent_id;
        $companyParentId1 = $company->parent_id;
        if ($company->parent_id == 0) {
            $employeeWhereClause = 'tbl_employee_company_locations.company_id';
            $employeeCompanyId = $id;
            $employeeLocationId = 0;
            $companyParentId1 = $company->id;
        }

        $employee = EmployeeModel::join('tbl_employee_company_locations', 'tbl_employee.id', '=', 'tbl_employee_company_locations.employee_id')->where('status', '1')->where('tbl_employee_company_locations.company_id', $employeeCompanyId)->where('tbl_employee_company_locations.location_id', $employeeLocationId)->where($where_data_manager)->count();

        $companyParentId = "";
        if ($getParentCompany != NULL) {
            $companyParentId = $getParentCompany->company_id;
        }
        $courses_ids = CompanyCoursesModel::select('course_id')->where('company_id', $companyParentId1)->where('company_course_status', 1)->get();
        $ids = [];
        foreach ($courses_ids as $id) {
            $ids[] = $id->course_id;
        }
        $courses = CourseModel::where('status', 1)->whereIn('id', $ids)->count();


        $data['location'] = $location;
        $data['employee'] = $employee;
        $data['courses'] = $courses;

        return response()->json($data, 200);
    }

    public function allCompaniesBACKUP(Request $request) {
        try {
            $requestData = $request->all();
            $columnName = [
                0 => 'tbl_company.name',
                1 => 'locations_count',
                2 => 'employees_count',
                3 => 'courses_count',
            ];
            $startFrom = "";
            $limit = "";
            if (isset($requestData['page']) && isset($requestData['per_page'])) {
                $startFrom = ($requestData['page'] == 0) ? ($requestData['page'] * $requestData['per_page']) : ($requestData['page'] - 1) * $requestData['per_page'];
                $limit = $requestData['per_page'];
            }

            $orderBy = "";
            $orderColumn = "";
            if (isset($requestData['order']) && isset($requestData['column'])) {
                $orderBy = $request->order;
                $orderColumn = ($request->column < 4) ? $columnName[$request->column] : 'tbl_company.name';
            }


            $where_data = [];
            if (!empty($request->search)) {
                $search = $request->search;
                $search = explode(" ", $search);
                foreach ($search as $key => $name) {
                    if ($request->search != '') {
                        $where_data[] = [
                            'name',
                            'like',
                            '%' . $request->search . '%',
                        ];
                    }
                }
            }

            if (!empty($request->company_status)) {
                $status = $request->company_status;
                if ($status == "Active") {
                    $status = 1;
                }
                if ($status == "Inactive") {
                    $status = 0;
                }
                array_push($where_data, [
                    'status',
                    $status,
                ]);
            }
            $data = array();
            $total = 0;
            switch ($request->company_type) {
                case 'parent':
                    $companies = CompanyModel::getParentCompany($where_data, $this->food_manager_course);
                    $total = $companies->count();
                    if ($orderColumn != '' && $orderBy != '') {
                        $companies->orderBy($orderColumn, $orderBy);
                    } else {
                        $companies->orderBy('tbl_company.name', 'asc');
                    }
                    if ($limit != '') {
                        $companies->skip($startFrom);
                        $companies->take($limit);
                    }
                    $data['companies'] = $companies->get();
                    $data['total'] = $total;
                    break;
                case 'child':

                    $companies = CompanyModel::getChildCompany($where_data);
                    $total = $companies->count();
                    if ($orderColumn != '' && $orderBy != '') {
                        $companies->orderBy($orderColumn, $orderBy);
                    }
                    if ($limit != '') {
                        $companies->skip($startFrom);
                        $companies->take($limit);
                    }
                    $data['companies'] = $companies->get();
                    $data['total'] = $total;
                    break;
                default:
                    $companies = CompanyModel::getParentChildCompany($where_data);
                    $countChildCompanies = $companies->count();
                    if ($orderColumn != '' && $orderBy != '') {
                        $companies->orderBy($orderColumn, $orderBy);
                    }
                    if ($limit != '') {
                        $companies->skip($startFrom);
                        $companies->take($limit);
                    }
                    $childData = $companies->get()->toArray();
                    $data['companies'] = $childData;
                    $data['total'] = $countChildCompanies;
                    break;
            }

            return response()->json($data, 200);
        } catch (Exception $th) {

            return response()->json(['message' => $th->getMessage()], 422);
        }
    }

    public function allCompanies(Request $request) {
        try {
            $requestData = $request->all();
            $columnName = [
                0 => 'tbl_company.name',
                1 => 'locations_count',
                2 => 'employees_count',
                3 => 'courses_count',
            ];
            $startFrom = "";
            $limit = "";
            if (isset($requestData['page']) && isset($requestData['per_page'])) {
                $startFrom = ($requestData['page'] == 0) ? ($requestData['page'] * $requestData['per_page']) : ($requestData['page'] - 1) * $requestData['per_page'];
                $limit = $requestData['per_page'];
            }

            $orderBy = "";
            $orderColumn = "";
            if (isset($requestData['order']) && isset($requestData['column'])) {
                $orderBy = $request->order;
                $orderColumn = ($request->column < 4) ? $columnName[$request->column] : 'tbl_company.name';
            }


            $where_data = [];
            if (!empty($request->search)) {
                $search = $request->search;
                $search = explode(" ", $search);
                foreach ($search as $key => $name) {
                    if ($request->search != '') {
                        $where_data[] = [
                            'name',
                            'like',
                            '%' . $request->search . '%',
                        ];
                    }
                }
            }

            if (!empty($request->company_status)) {
                $status = $request->company_status;
                if ($status == "Active") {
                    $status = 1;
                }
                if ($status == "Inactive") {
                    $status = 0;
                }
                array_push($where_data, [
                    'status',
                    $status,
                ]);
            }
            $data = array();
            $total = 0;
            switch ($request->company_type) {
                case 'parent':
                    $companies = CompanyModel::getParentCompany($where_data);
                    $total = $companies->count();
                    if ($orderColumn != '' && $orderBy != '') {
                        $companies->orderBy($orderColumn, $orderBy);
                    } else {
                        $companies->orderBy('tbl_company.name', 'asc');
                    }
                    if ($limit != '') {
                        $companies->skip($startFrom);
                        $companies->take($limit);
                    }
                    $data['companies'] = self::swapTotalEmployeesCount($companies->get(), $request);
                    $data['total'] = $total;
                    break;
                case 'child':

                    $companies = CompanyModel::getChildCompany($where_data);
                    $total = $companies->count();
                    if ($orderColumn != '' && $orderBy != '') {
                        $companies->orderBy($orderColumn, $orderBy);
                    }
                    if ($limit != '') {
                        $companies->skip($startFrom);
                        $companies->take($limit);
                    }
                    $data['companies'] = self::swapTotalEmployeesCount($companies->get(), $request);
                    $data['total'] = $total;
                    break;
                default:
                    $companies = CompanyModel::getParentChildCompany($where_data);
                    $countChildCompanies = $companies->count();
                    if ($orderColumn != '' && $orderBy != '') {
                        $companies->orderBy($orderColumn, $orderBy);
                    }
                    if ($limit != '') {
                        $companies->skip($startFrom);
                        $companies->take($limit);
                    }
                    $childData = $companies->get();
                    $data['companies'] = $data['companies'] = self::swapTotalEmployeesCount($childData, $request);
                    $data['total'] = $countChildCompanies;
                    break;
            }

            return response()->json($data, 200);
        } catch (Exception $th) {

            return response()->json(['message' => $th->getMessage()], 422);
        }
    }

    /**
     * To swap the total_employees_count with employee_num
     */
    public static function swapTotalEmployeesCount($companies, $request) {
        foreach ($companies as $key => $company) {
            $companies[$key]['total_employees_count'] = $company->employee_num;
            $companies[$key]['employees_count'] = self::getParentAndChildCompanyEmployeesCount($company->id);
        }

        return $companies;
    }

    /**
     * To get the total count of employee of a specific company
     *
     * @param $company_id
     *
     * @return int
     */
    public static function getParentAndChildCompanyEmployeesCount($company_id) {
        $parentCompanyData = $childCompanyData = $parentCompanyEmployees = $childCompanyEmployees = [];

        $locations = CompanyModel::where('id', $company_id)->first();

        if ($locations->parent_id == 0) {
            $parentCompanyData = CompanyModel::where('tbl_company.id', $locations->id)->select('tbl_company.*')->get();
            $childCompanyData = CompanyModel::where('tbl_company.parent_id', $locations->id)->where('tbl_company.status', 1)->select('tbl_company.*')->get();
        } else {
            $childCompanyData = CompanyModel::where('tbl_company.id', $locations->id)->select('tbl_company.*')->get();
        }

        if (!empty($parentCompanyData)) {
            foreach ($parentCompanyData as $key => $value) {
                $parentCompanyEmployees[] = EmployeeCompanyLocationsModel::leftjoin('tbl_employee', 'tbl_employee.id', '=', 'tbl_employee_company_locations.employee_id')->where('tbl_employee_company_locations.company_id', $value->id)->where('tbl_employee.status', 1)->where('tbl_employee_company_locations.location_id', 0)->count();
            }
        }

        if (!empty($childCompanyData)) {
            foreach ($childCompanyData as $key => $value) {
                $childCompanyEmployees[] = LocationController::getUserCountOfCompany($value->id);
            }
        }

        return array_sum($parentCompanyEmployees) + array_sum($childCompanyEmployees);
    }

    public function changeStatus(Request $request, $id) {
        $status = $request->status;
        $company = CompanyModel::find($id);
        if (empty($company)) {
            return response()->json(['Company' => 'Invalid id no company found.!'], 422);
        }
        CompanyModel::where('id', $id)->update(["status" => $status]);

        return response()->json(['Status' => 'Status Change Successfully.!'], 200);
    }

    public function all() {
        $data = CompanyModel::where('status', 1)->orderBy('name', 'asc')->get();
        if (empty($data)) {
            return response()->json(['Company' => 'No company found.!'], 422);
        }

        return response()->json($data, 200);
    }

    public function allLocations(Request $request) {
        $status = $request->location_status;
        $company_id = $request->company_id;
        $data = [];
        $where_data = [];
        if (!empty($request->search)) {
            $search = $request->search;
            $search = explode(" ", $search);
            foreach ($search as $key => $name) {
                $where_data[] = [
                    'name',
                    'like',
                    '%' . $name . '%',
                ];
            }
        }
        if (!empty($request->company_id)) {
            array_push($where_data, [
                'id',
                $company_id,
            ]);
        }
        if (!empty($status)) {
            if ($status == "Inactive") {
                $status = 0;
            } else if ($status == "Active") {
                $status = 1;
            }
            array_push($where_data, [
                'status',
                $status,
            ]);
        }

        $locations = CompanyModel::where($where_data)->orderBy('name', 'ASC')->get();
        foreach ($locations as $location) {
            if ($location->parent_id == "0") {
                $location['level'] = "parent";
                $location['childcount'] = CompanyModel::where('parent_id', $location->id)->count();
                $location['childlocations'] = CompanyModel::where('parent_id', $location->id)->get();
            } else {
                $location['level'] = "child";
            }
        }
        $data['locations'] = $locations;

        return response()->json($data, 200);
    }

    public function getCompnayName($id = NULL) {
        try {

            $user = Auth::user();
            if ($user->role_id == 3 || $id == NULL) {

                $comapnys = EmployeeCompanyLocationsModel::where('tbl_employee_company_locations.employee_id', $user->id)->select('location_id', 'company_id')->get()->toArray();
                $locationsId = array_column($comapnys, 'location_id');
                $companiesId = array_column($comapnys, 'company_id');

                if (in_array(0, $locationsId)) {

                    $company = CompanyModel::whereIn('id', $companiesId)->get();

                } else {
                    $company = CompanyModel::whereIn('id', $locationsId)->get();

                }
                $total = count($company);
                $data['company'] = $company;

            } else {
                $company = CompanyModel::where('id', $id)->get();
                $total = count($company);
                $data['company'] = $company;
            }


            return response()->json($data, 200);
        } catch (Exception $th) {

            return response()->json($th->getMessage(), 422);
        }
    }

    public function adminStats() {
        $data = [];
        $companies = CompanyModel::where('status', 1)->count();
        $courses = CourseModel::where('status', 1)->count();
        $employees = EmployeeModel::where('status', 1)->count();
        $data['companies'] = $companies;
        $data['courses'] = $courses;
        $data['employees'] = $employees;

        return response()->json($data, 200);
    }

    public function certificates(Request $request) {
        $company_id = $request->company_id;
        $user = Auth::user();
        if ($user->role_id == 3) {
            $company = EmployeeCompanyLocationsModel::where('tbl_employee_company_locations.employee_id', $user->id)->rightJoin('tbl_company', 'tbl_company.id', '=', 'tbl_employee_company_locations.company_id')->select('company_id')->first();
            $company_id = $company->company_id;
        }


        $status = $request->status;
        $company = CompanyModel::find($company_id);
        if (empty($company)) {
            return response()->json(['Company' => 'Invalid id no company found.!'], 422);
        }
        $data = $this->allCertificates($company_id, $status);

        return response()->json($data, 200);
    }

    public function updatePasswordSuperAdmin(Request $request) {
        $id = $request->id;
        $password = $request->password;
        EmployeeModel::where('id', $id)->update(['password' => md5($password)]);

        return response()->json(['Password' => 'Update Successfully..!'], 200);
    }

    public function courseStatus(Request $request) {
        $course_id = $request->course_id;
        $course_check = CourseModel::where([
            [
                'id',
                $course_id,
            ],
            [
                'status',
                1,
            ],
        ])->count();
        $company_id = $request->company_id;
        $status = $request->status;
        if ($course_check === 1) {
            CompanyCoursesModel::where([
                [
                    'course_id',
                    $course_id,
                ],
                [
                    'company_id',
                    $company_id,
                ],
            ])->update(['company_course_status' => $status]);
            EmployeeCoursesModel::where('course_id', $course_id)->update(['course_status' => $status]);
        } else {
            return response()->json(['Status' => env('SITE_NAME') . ' stop giving this course..!'], 422);
        }
    }

    public function companyDropdownData() {
        $companytype = CompanyTypeModel::get();
        $parentCompanies = CompanyModel::orderby('name', 'asc')->get();
        $pricePlan = PricePlanModel::get();
        $data['companytype'] = $companytype;
        $data['parentcompanies'] = $parentCompanies;
        $data['priceplan'] = $pricePlan;

        return response()->json($data, 200);
    }


    public function companyDropdown() {
        $data = CompanyModel::orderBy('name', 'asc')->get();
        if (empty($data)) {
            return response()->json(['Company' => 'No company found.!'], 422);
        }

        return response()->json($data, 200);
    }

    public function childCompanyDropdown(Request $request) {
        $data = CompanyModel::orderBy('name', 'asc')->where('parent_id', $request->id)->orWhere('id', $request->id)->get();
        if (empty($data)) {
            return response()->json(['Company' => 'No company found.!'], 422);
        }

        return response()->json($data, 200);
    }

    public function parentCompanyDropdown() {
        $data = CompanyModel::orderBy('name', 'asc')->where('parent_id', '0')->get();
        if (empty($data)) {
            return response()->json(['Company' => 'No company found.!'], 422);
        }

        return response()->json($data, 200);
    }

    public function users(Request $request) {
        //$request->report_type => active_user/all_user
        $validator = Validator::make($request->all(), [
            'report_type' => 'required',
        ]);

        if ($validator->fails()) {
            $errors = json_decode($validator->errors());
            foreach ($errors as $error) {
                $message[] = $error[0];
            }
            $message = implode(",", $message);

            return response()->json(['user' => $message], 422);
        }
        if ($request->company_id) {
            $company_id = array($request->company_id);
        } else {
            $user = Auth::user();
            $companies = CompanyModel::getCompaniesByAdminUser($user->id);
            $company_id = [];
            if ($companies['isParent'] != 0) {
                if (is_array($companies['isParent'])) {
                    $company_id = $companies['isParent'];
                } else {
                    $company_id = array(
                        $companies['isParent'],
                    );
                }


            } else {
                $company_id = $companies['isLocations'];

            }
        }
        try {
            $result = CompanyModel::getUsers($company_id, $request->report_type);

            return response()->json($result, 200);
        } catch (Exception $ex) {

            return response()->json(['message' => $ex->getMessage()], 422);
        }
    }

    public function nonAssignCourse($companyId) {
        try {
            $isCourse_ssign = CompanyCoursesModel::where('company_id', $companyId)->select('course_id')->get()->toArray();
            $course_assign = array_column($isCourse_ssign, 'course_id');
            $data = CourseModel::where('tbl_course.status', '=', 1)->whereNotIn('tbl_course.id', $course_assign)->orWhere('tbl_course.company_specific', $companyId)->select('tbl_course.id', 'tbl_course.name')->orderBy('tbl_course.name', 'asc')->groupBy('tbl_course.id')->get();

            return response()->json($data, 200);
        } catch (Exception $ex) {

            return response()->json(['message' => $ex->getMessage()], 422);
        }
    }

    public function deleteCompanies(Request $request) {

        try {
            DB::beginTransaction();
            $companies = $request->companies;
            foreach ($companies as $company) {
                $company = CompanyModel::where('id', $company)->first();
                CompanyCoursesModel::where('company_id', $company->id)->delete();
                if ($company->parent_id == "0") {
                    $employees = EmployeeCompanyLocationsModel::leftJoin('tbl_employee', 'tbl_employee.id', '=', 'tbl_employee_company_locations.employee_id')->where('company_id', $company->id)->get();
                    foreach ($employees as $employee) {
                        EmployeeCoursesModel::where('employee_id', $employee->employee_id)->delete();
                        EmployeeCompanyLocationsModel::where('employee_id', $employee->employee_id)->delete();
                        //EmployeeModel::where('id' ,$employee->employee_id)->delete();
                        $updated = EmployeeModel::where('id', $employee->employee_id)->update(['status' => '0']);
                        if ($updated) {
                            $employee = EmployeeModel::where('id', $employee->employee_id)->first();
                            $infoDataArray = [
                                'action' => $company->name . " company is deleted by super admin.",
                                'user_id' => $employee->id,
                                'user_name' => $employee->full_name . ' got inactivated.',
                                'date' => Carbon::now('UTC'),
                                'ip' => $_SERVER['REMOTE_ADDR'],
                            ];
                            CommonTrait::logInactiveEmployeeInfo($infoDataArray);

                        }

                    }
                    CompanyModel::where('id', $company->id)->orWhere('parent_id', $company->id)->delete();
                } else {
                    $employees = EmployeeCompanyLocationsModel::leftJoin('tbl_employee', 'tbl_employee.id', '=', 'tbl_employee_company_locations.employee_id')->where('location_id', $company->id)->get();
                    foreach ($employees as $employee) {
                        EmployeeCoursesModel::leftJoin('tbl_employee', 'tbl_employee.id', '=', 'tbl_employee_courses.employee_id')->where('employee_id', $employee->employee_id)->where('tbl_employee.role_id', '=', '4')->delete();
                        EmployeeCompanyLocationsModel::leftJoin('tbl_employee', 'tbl_employee.id', '=', 'tbl_employee_company_locations.employee_id')->where('employee_id', $employee->employee_id)->delete();
                        // EmployeeModel::where('id' ,$employee->employee_id)->where('tbl_employee.role_id', '=', '4')->delete();
                        $updated = EmployeeModel::where('id', $employee->employee_id)->update(['status' => '0']);
                        if ($updated) {
                            $employee = EmployeeModel::where('id', $employee->employee_id)->first();

                            $infoDataArray = [
                                'action' => $company->name . " location is deleted by super admin.",
                                'user_id' => $employee->id,
                                'user_name' => $employee->full_name . ' got deactivated.',
                                'date' => Carbon::now('UTC'),
                                'ip' => $_SERVER['REMOTE_ADDR'],
                            ];

                            CommonTrait::logInactiveEmployeeInfo($infoDataArray);

                        }
                    }
                    CompanyModel::where('id', $company->id)->delete();
                }

                CourseModel::where('company_specific', $company->id)->update(['status' => '0']);
            }
            DB::commit();
        } catch (Exception $ex) {
            DB::rollback();

            return response()->json(['message' => $ex->getMessage()], 422);
        }
    }

    public function userOnboardingSign(Request $request) {
        try {
            $user = Auth::user();
            $getCompany = EmployeeCompanyLocationsModel::where('employee_id', $user->id)->first();
            $documents = [];
            $existDocu = [];
            $employee_data = UserOnboarding::where('employee_id', $user->id)->get();
            if ($employee_data) {
                foreach ($employee_data as $data) {
                    array_push($existDocu, $data->document_id);
                }
            }
            foreach ($request->documents as $document) {
                if (in_array($document['id'], $existDocu)) {
                    UserOnboarding::where('employee_id', $user->id)->where('document_id', $document['id'])->update([
                        'first_name' => $request->first_name,
                        'last_name' => $request->last_name,
                        'date' => Carbon::now('UTC'),
                        'ip' => $_SERVER['REMOTE_ADDR'],
                        'signature' => $request->signature,
                        'sign_status' => 1,
                        'updated_at' => Carbon::now('UTC'),
                    ]);
                } else {
                    DB::table('tbl_user_onboarding')->insert([
                        'document_id' => $document['id'],
                        'employee_id' => $user->id,
                        'company_id' => $getCompany->company_id,
                        'ip' => $_SERVER['REMOTE_ADDR'],
                        'date' => Carbon::now('UTC'),
                        'first_name' => $request->first_name,
                        'last_name' => $request->last_name,
                        'signature' => $request->signature,
                        'sign_status' => 1,
                        'created_at' => Carbon::now('UTC'),
                    ]);
                }
            }

            return response()->json(['message' => 'Onboarding done.'], 200);
        } catch (Exception $ex) {
            return response()->json(['message' => $ex->getMessage()], 422);
        }
    }

    public function userOnboardingSave(Request $request) {
        try {
            $document = $request->data;
            if (isset($document['id'])) {
                CompanyDocumentModel::where('id', $document['id'])->where('company_id', $request->company_id)->update([
                    'document' => $document['text'],
                    'available_for' => implode(",", $document['availableFor']),
                ]);
                UserOnboarding::where('document_id', $document['id'])->update(['sign_status' => 0]);
            } else {
                DB::table('tbl_company_documents')->insert([
                    'company_id' => $request->company_id,
                    'document' => $document['text'],
                    'available_for' => implode(",", $document['availableFor']),
                    'created_at' => Carbon::now('UTC'),
                ]);
            }

            return response()->json(['message' => 'Added Successfully.'], 200);
        } catch (Exception $ex) {
            return response()->json(['message' => $ex->getMessage()], 422);
        }
    }

    public function useronboardingDocumentDelete(Request $request) {
        try {
            $document = $request->data;
            CompanyDocumentModel::where('id', $document['id'])->where('company_id', $request->company_id)->delete();

            return response()->json(['message' => 'Deleted Successfully.'], 200);
        } catch (Exception $ex) {
            return response()->json(['message' => $ex->getMessage()], 422);
        }

    }

    public function userOnboardingDocuments($id = NULL) {
        try {
            $user = Auth::user();
            if ($user->role_id == 1) {
                $company_id = $id;
            } else {
                $getCompany = EmployeeCompanyLocationsModel::where('employee_id', $user->id)->first();
                $company_id = $getCompany->company_id;
            }
            $company_onoboarding_documents = CompanyDocumentModel::select('tbl_company_documents.id', 'tbl_company_documents.document', 'tbl_company_documents.available_for')->whereNotIn('id', UserOnboarding::select('tbl_user_onboarding.document_id')->where('employee_id', '=', $user->id)->where('sign_status', '=', 1)->get()->toArray())->whereRaw("FIND_IN_SET(" . $user->role_id . ", tbl_company_documents.available_for)")->where('tbl_company_documents.company_id', '=', $company_id);
            $data = $company_onoboarding_documents->get();

            return response()->json($data, 200);
        } catch (Exception $ex) {
            return response()->json(['message' => $ex->getMessage()], 422);
        }
    }

    public function userOnboardingPdfDocuments($id) {
        try {
            $data = CompanyDocumentModel::where('id', $id)->get();

            return response()->json($data, 200);
        } catch (Exception $ex) {
            return response()->json(['message' => $ex->getMessage()], 422);
        }
    }

    public function userOnboardingReport(Request $request) {
        try {
            $where_data = [];
            $user = Auth::user();
            if (!empty($request->search)) {
                $search = $request->search;
                $search = explode(" ", $search);
                foreach ($search as $key => $name) {
                    $where_data[] = [
                        'tbl_user_onboarding.first_name',
                        'like',
                        '%' . $name . '%',
                    ];
                }
            }
            $where = [];
            $companyIds = [];
            if ($request->company_id != '' && $request->company_id != 0) {
                $companyId = $request->company_id;
                $companies = CompanyModel::where('id', $companyId)->first();

                $companyIds = $request->company_id;
                if ($companies->parent_id != 0) {
                    $where = 'tbl_employee_company_locations.location_id';
                } else {
                    $where = 'tbl_employee_company_locations.company_id';
                }
            } else {
                if ($user->role_id != 1) {
                    $companies = CompanyModel::getCompaniesByAdminUser($user->id);
                    if ($companies['isParent'] != 0) {

                        $companyIds = $companies['isParent'];
                        $where = 'tbl_employee_company_locations.company_id';

                    } else {
                        $companyIds = $companies['isLocations'];
                        $where = 'tbl_employee_company_locations.location_id';

                    }
                }
            }
            $requestData = $request->all();
            $startFrom = "";
            $limit = "";
            if (isset($requestData['page']) && isset($requestData['per_page'])) {
                $startFrom = ($requestData['page'] == 0) ? ($requestData['page'] * $requestData['per_page']) : ($requestData['page'] - 1) * $requestData['per_page'];
                $limit = $requestData['per_page'];
            }

            $data = DB::table('tbl_user_onboarding')->select('tbl_company.name as location', 'tbl_user_onboarding.*')->leftjoin('tbl_employee', 'tbl_employee.id', '=', 'tbl_user_onboarding.employee_id')->leftjoin('tbl_employee_company_locations', 'tbl_employee_company_locations.employee_id', '=', 'tbl_user_onboarding.employee_id')->leftJoin('tbl_company', 'tbl_company.id', '=', DB::Raw('(CASE WHEN `tbl_employee_company_locations`.location_id = 0 then `tbl_employee_company_locations`.company_id ELSE `tbl_employee_company_locations`.location_id END)'))->where($where, $companyIds)->where($where_data);
            $data->groupBy('tbl_user_onboarding.employee_id', 'tbl_user_onboarding.created_at');
            $data->orderby('tbl_user_onboarding.first_name', 'asc');

            $records = array();
            $total = 0;
            if ($user->role_id == 1) {
                if (!empty($request->search) || !empty($request->company_id)) {
                    $records = $data->get();

                    if (!empty($records->toArray())) {
                        $total = count($records->toArray());
                    }

                    if ($limit != '' && $request->has('isExcelDownload') === FALSE) {
                        $data->skip($startFrom);
                        $data->take($limit);
                    }

                    $records = $data->get();
                }
            } else {
                $records = $data->get();

                if (!empty($records->toArray())) {
                    $total = count($records->toArray());
                }

                if ($limit != '' && $request->has('isExcelDownload') === FALSE) {
                    $data->skip($startFrom);
                    $data->take($limit);
                }

                $records = $data->get();
            }

            return response()->json([
                'report' => $records,
                'total' => $total,
            ], 200);
        } catch (Exception $ex) {
            return response()->json(['message' => $ex->getMessage()], 422);
        }
    }
}
