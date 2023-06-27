<?php

namespace App\Http\Controllers;

use App\Helpers\Helper;
use App\Http\Traits\CommonTrait;
use App\Http\Traits\CompanyTrait;
use App\Http\Traits\CourseTrait;
use App\Models\CompanyCoursesModel;
use App\Models\CompanyModel;
use App\Models\CourseCertificatesModel;
use App\Models\CourseFolderAssignModel;
use App\Models\CourseFolderModel;
use App\Models\CourseModel;
use App\Models\CourseResourceModel;
use App\Models\EmployeeCertificateModel;
use App\Models\EmployeeCompanyLocationsModel;
use App\Models\EmployeeCourseAttemptsModel;
use App\Models\EmployeeCourseFolderModel;
use App\Models\EmployeeCourseHistoryModel;
use App\Models\EmployeeCoursesModel;
use App\Models\EmployeeDocumentModel;
use App\Models\EmployeeModel;
use App\Models\PayByEmployeeHistoryModel;
use App\Models\PaymentModel;
use App\Models\PromoCodeModel;
use App\Models\PromoCodeReportModel;
use App\Models\TourVideo;
use App\Models\VideoModel;
use Auth;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use Validator;

class EmployeeController extends Controller {
    use CompanyTrait, CommonTrait, CourseTrait;

    private $secret_key;
    private $base_url;
    private $profile_id;
    private $profile_key;
    private $status;
    private $sucess;
    private $fail;


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
    }


    public function register(Request $request) {
        $validator = Validator::make($request->all(), [
            'employee_first_name' => 'required',
            'employee_last_name' => 'required',
            'user_type' => 'required',
        ]);
        if ($validator->fails()) {
            $errors = json_decode($validator->errors());
            foreach ($errors as $error) {
                $message[] = $error[0];
            }
            $message = implode("<br/>", $message);

            return response()->json(['message' => $message], 422);
        }
        try {

            DB::beginTransaction();
            if (!empty($request->employee_email)) {
                if (CommonTrait::isValidEmail($request->employee_email) == FALSE) {

                    return response()->json(['message' => "Email is not valid, Enter correct email."], 422);
                }
                $isEmailExist = EmployeeModel::where('email', $request->employee_email)->orWhere('user_name', $request->employee_email)->first();
                if ($isEmailExist != NULL) {

                    return response()->json(['message' => "Email already exists, Try another."], 422);
                }
            }
            if (!empty($request->employee_phone_num)) {
                if (CommonTrait::isValidPhone($request->employee_phone_num) == FALSE) {

                    return response()->json(['message' => "Phone number is not correct,  Phone number should be 10 digit."], 422);
                }
            }

            $paymentId = 0;
            $isPaymentResponse = array();
            $payment_request = $request->payment;
            if (!empty($payment_request)) {
                $invoice = CommonTrait::generateUniqueId();
                $payment_request['invoice_number'] = $invoice;
                //$paymentResponse = PaymentModel::storeCardofUserForPayment($this->base_url, $this->profile_id, $this->profile_key, $payment_request);
                $paymentResponse = PaymentModel:: stripePayment($this->secret_key, $payment_request, $request);
                if ($paymentResponse['status'] == 'error') {

                    return response()->json([
                        'status' => 'error',
                        'message' => $paymentResponse['message'],
                    ], 422);
                }
                $courseData = array(
                    'course_ids' => $request->courses,
                    'number_of_locations' => isset($request->company_location_num) ? $request->company_location_num : 0,
                    'number_of_users' => isset($request->company_employee_num) ? $request->company_employee_num : 0,
                );
                $paymentId = PaymentModel::insertGetId([
                    'user_id' => 0,
                    'email' => $request->employee_email,
                    'event_type' => 'Individual Sign UP',
                    'transaction_id' => $paymentResponse['data']['balance_transaction'],
                    'error_code' => "000",
                    'payment_type' => ($payment_request['payment_type'] == 'monthly') ? 1 : 2,
                    'payment_status' => "Y",
                    'auth_response_text' => "Individual Signup",
                    'amount' => $payment_request['transaction_amount'],
                    'courses' => json_encode($courseData),
                    'invoice_number' => $invoice,
                    'response' => json_encode($paymentResponse),
                    'created_at' => Carbon::now('UTC'),
                ]);
                // if ($paymentResponse['data']['error_code'] != '000') {

                //     return response()->json(['message' => $paymentResponse['data']['auth_response_text']], 422);
                // }
                // if (isset($paymentResponse['data']['avs_result']) && $paymentResponse['data']['avs_result'] == 'N') {
                //     return response()->json(['message' => $paymentResponse['data']['auth_response_text']], 422);
                // }
                $isPaymentResponse = $paymentResponse;
            }

            if (!empty($request->user_type)) {
                if ($request->user_type == "location_manager" || $request->user_type == "manager") {
                    $role = 3;
                } else if ($request->user_type == 'admin') {
                    $role = 2;
                } else {
                    $role = 4;
                }
            } else {

                $role = 4;
                $request->user_type = "employee";
            }
            $token = 'Bearer' . sha1(time());
            $employee = new EmployeeModel;
            if ($request->password) {
                $access_code = $request->password;
            } else {
                $access_code = rand(100000, 999999);
            }
            $employee->role_id = $role;
            $employee->first_name = $request->employee_first_name;
            $employee->last_name = $request->employee_last_name;
            $employee->full_name = $request->employee_first_name . ' ' . $request->employee_last_name;
            if ($request->employee_job_title_id) {
                $employee->job_title_id = $request->employee_job_title_id;
            }
            $employee->type = $request->user_type;
            if (!empty($request->employee_email)) {
                $employee->email = $request->employee_email;
            }
            $employee->phone_num = preg_replace('~\D~', '', $request->employee_phone_num);
            $employee->access_code = $access_code;
            $employee->password = md5($access_code);
            $employee->api_token = $token;
            if ($request->employee_progress) {
                if ($request->employee_progress === TRUE || $request->employee_progress === 1) {
                    $employee->progress_status = 1;
                } else {
                    $employee->progress_status = 0;
                }
            }

            $all_data = $request->all();
            if (isset($all_data['employee_status'])) {
                $employee->status = $all_data['employee_status'];
            }

            $employee->city = $request->employee_city;
            $employee->state = $request->employee_state;
            //$employee->status = $request->employee_zipcode;
            //$employee->company_id=$request->employee_company_id;
            //$employee->location_id=$request->employee_location_id;
            $employee->address = $request->address;
            if ($request->dob) {
                $employee->dob = $request->dob;
            }
            if ($request->social_security) {
                $employee->social_security = $request->social_security;
            }
            if ($request->employee_zipcode) {
                $employee->zipcode = $request->employee_zipcode;
            }
            $employee->added_on = date('Y-m-d');
            if (!empty($request->employee_card_number)) {
                $employee->card_number = $request->employee_card_number;
                $employee->cvv_code = $request->employee_cvv_code;
                $employee->card_expiration_date = $request->employee_card_expiration_date;
            }
            $employee->save();
            $userAuthToken = $employee->createToken('MyApp')->accessToken;
            $employee->api_token = $userAuthToken;
            $employee_id = $employee->id;
            if (!empty($request->employee_username)) {
                EmployeeModel::where('id', $employee_id)->update(['user_name' => $request->employee_username]);
            } else if (!empty($request->employee_email)) {
                EmployeeModel::where('id', $employee_id)->update(['user_name' => $request->employee_email]);
            } else if (!empty($request->employee_phone_num)) {
                $phone_num = preg_replace('~\D~', '', $request->employee_phone_num);
                $username = preg_replace('~\D~', '', $request->employee_phone_num);
                $isUsername = EmployeeModel::Where('user_name', $phone_num)->first();
                if ($isUsername != NULL) {
                    $username = $request->employee_first_name . '_' . $employee_id;
                }
                EmployeeModel::where('id', $employee_id)->update(['user_name' => $username]);
            } else {
                EmployeeModel::where('id', $employee_id)->update(['user_name' => $request->employee_first_name . '_' . $employee_id]);
            }
            $username = '';
            if ($request->employee_email) {
                $username = $request->employee_email;
            } else if ($request->employee_phone_num) {
                $username = (int)$request->employee_phone_num;
            } else {
                $username = $request->employee_first_name . '_' . $employee_id;
            }

            $company_id = 0;
            $location_id = 0;
            $courseCompany = 0;
            $companyName = '';
            if (Auth::check() && $request->user_type != 'individual') {
                if (Auth::user()->role_id != 1 && empty($request->employee_location_id)) {
                    $isCompany = CompanyModel::where('employee_id', Auth::user()->id)->first();
                    if ($isCompany->parent_id == 0) {
                        $company_id = $isCompany->id;
                        $courseCompany = $isCompany->id;
                        if ($request->employee_location_id != $isCompany->id) {
                            $location_id = $request->employee_location_id;
                            $courseCompany = $request->employee_location_id;
                        }
                    } else {
                        $company_id = $isCompany->parent_id;
                        $location_id = $request->employee_location_id;
                        $courseCompany = $request->employee_location_id;
                    }
                    $data = new EmployeeCompanyLocationsModel();
                    $data->employee_id = $employee_id;
                    $data->company_id = $company_id;
                    $data->location_id = $location_id;
                    $data->save();

                } else {
                    if (is_array($request->employee_location_id)) {
                        $location_ids = $request->employee_location_id;
                    } else {
                        $location_ids = array($request->employee_location_id);
                    }
                    foreach ($location_ids as $locations_id) {
                        $isCompany = CompanyModel::where('id', $locations_id)->first();
                        if ($isCompany->parent_id == 0) {
                            $company_id = $isCompany->id;
                            $courseCompany = $isCompany->id;
                            if ($locations_id != $isCompany->id) {
                                $location_id = $locations_id;
                                $courseCompany = $locations_id;
                            }
                        } else {
                            $company_id = $isCompany->parent_id;
                            $location_id = $locations_id;
                            $courseCompany = $locations_id;
                        }

                        $data = new EmployeeCompanyLocationsModel();
                        $data->employee_id = $employee_id;
                        $data->company_id = $company_id;
                        $data->location_id = $location_id;
                        $data->save();

                    }
                }

                if ($isCompany == NULL) {
                    return response()->json(['message' => 'Assigned location is required, Please assign a location.'], 422);
                }

                $companyName = ucfirst($isCompany->name);
            }

            //$this->companyAdmin($username,$access_code,$role,$employee_id);
            //below assign the location courses
            //$this->assignEmployeeCourses($location_id, $employee_id, $company_id);
            //below assign the selected course
            if ($request->employee_course) {
                $employee_type = $request->user_type;
                if (!$request->user_type) {
                    $employee_type = "employee";
                }

                $this->assigenEmployeeCourse($employee_id, $courseCompany, $request->employee_course, $employee_type);
            }


            if ($request->courses) {
                $employee_type = $request->user_type;
                if (!$request->user_type) {
                    $employee_type = "employee";
                }
                $this->assignCourse($employee_id, $request->courses, $employee_type);
            }

            if ($paymentId != 0 && !empty($isPaymentResponse)) {

                // Send email to support
                $courses = CourseModel::whereIn('id', $request->courses)->get();
                $course_names = array();
                $i = 0;
                $reminder = 0;
                if ($courses->count() > 0) {
                    foreach ($courses as $course) {
                        $course_names[$i]['name'] = $course->name;
                        $i++;
                    }
                }
                if ($request->promo_code) {
                    $promoCode = PromoCodeModel::where('name', $request->promo_code)->first();
                    $promoReport = new PromoCodeReportModel();
                    $promoReport->user_id = $employee_id;
                    $promoReport->promocode_id = $promoCode->id;
                    $promoReport->total_amount = $request->course_cost;
                    $promoReport->amount_paid = $payment_request['transaction_amount'];
                    $promoReport->created_at = Carbon::now("UTC");
                    $promoReport->save();
                }
                $newsignupdata = array(
                    'date' => Carbon::now('UTC')->format("m-d-Y"),
                    'interface' => "Individual",
                    'first_name' => $request->employee_first_name,
                    'last_name' => $request->employee_last_name,
                    'address' => $request->employee_address . ', ' . $request->employee_city . ', ' . $request->employee_state . ', ' . $request->employee_zipcode,
                    'phone_no' => $request->employee_phone_num,
                    'email' => $request->employee_email,
                    'courses' => $course_names,
                    'plan_detail' => $payment_request['payment_type'],
                    'credit_card' => str_replace('"', '', json_encode($paymentResponse['data']['payment_method_details']['card']['last4'])),
                    'expiry' => json_encode($paymentResponse['data']['payment_method_details']['card']['exp_month']) . '/' . json_encode($paymentResponse['data']['payment_method_details']['card']['exp_year']),
                    'fee' => $payment_request['transaction_amount'],
                    'promo_code' => $request->promo_code ? $request->promo_code : '',
                    'course_cost' => $request->course_cost ? $request->course_cost : '',
                    'discounted_cost' => $request->discounted_cost ? $request->discounted_cost : '',
                );

                $supportEmail = config('mail.support');
                Mail::send('new_signup', $newsignupdata, function($message) use ($supportEmail) {
                    $message->to($supportEmail)->subject('New Individual Signup - ' . env('SITE_NAME') . '!');
                    $message->from(env('MAIL_FROM_ADDRESS'), env('MAIL_FROM_NAME'));
                });
                CommonTrait::emailLog("New Individual Sign Up", $supportEmail, 0);
                // email sended to support
                //Send payement receipt email to user
                if ($request->employee_email) {
                    $reciptdata = array(
                        'type' => 'Individual',
                        'first_name' => $request->employee_first_name,
                        'last_name' => $request->employee_last_name,
                        'courses' => $course_names,
                        'plan_detail' => $payment_request['payment_type'],
                        'credit_card' => str_replace('"', '', json_encode($paymentResponse['data']['payment_method_details']['card']['last4'])),
                        'expiry' => json_encode($paymentResponse['data']['payment_method_details']['card']['exp_month']) . '/' . json_encode($paymentResponse['data']['payment_method_details']['card']['exp_year']),
                        'fee' => $payment_request['transaction_amount'],
                    );

                    $userEmail = $request->employee_email;
                    Mail::send('payment_receipt', $reciptdata, function($message) use ($userEmail) {
                        $message->to($userEmail)->subject('Payment Receipt - ' . env('SITE_NAME') . '!');
                        $message->from(env('MAIL_FROM_ADDRESS'), env('MAIL_FROM_NAME'));
                    });
                    CommonTrait::emailLog("New Individual Sign Up - Payment Receipt", $userEmail, 0);
                }
                //Sent payement receipt email to user
                PaymentModel::where('id', $paymentId)->update([
                    'user_id' => $employee_id,
                ]);
                $isUserCard = DB::table('tbl_user_card_store')->where('user_id', $employee_id)->first();
                if ($isUserCard == NULL) {
                    DB::table('tbl_user_card_store')->insert([
                        'user_id' => $employee_id,
                        'card_id' => json_encode($paymentResponse['data']['source']['id']),
                        'created_at' => Carbon::now('UTC'),
                    ]);
                } else {
                    DB::table('tbl_user_card_store')->where('id', $isUserCard->id)->update([
                        'card_id' => json_encode($paymentResponse['data']['source']['id']),
                        'updated_at' => Carbon::now('UTC'),
                    ]);
                }
                // DB::table('tbl_payment_cron_scheduling')->insert([
                //     'user_id' => $employee_id,
                //     'type' => 'company',
                //     'recurring' =>($payment_request['payment_type'] == 'monthly')?1:2,
                //     'processing_date' =>  Carbon::now('UTC')->format('Y-m-d'),
                //     'processed' => 0,
                //     'comments' => 'User sign up as company',
                //     'status' => 1,
                //     'created_at' => Carbon::now('UTC')
                // ]);
            }

            if ($employee->email) {
                $company_data = array();
                if ($request->employee_location_id != '') {
                    $companyId = (int)$request->employee_location_id;
                    $company_data = CompanyModel::find($companyId);
                } else {
                    $companyId = (int)$request->employee_company_id;
                    $company_data = CompanyModel::find($companyId);
                }
                $company_name = '';
                if ($company_data != NULL) {
                    $company_name = $company_data->name;
                }
                $email = $employee->email;
                $data = array(
                    'first_name' => $employee->first_name,
                    'last_name' => $employee->last_name,
                    'company_name' => $company_name,
                    'email' => $employee->email,
                    'access_code' => $employee->access_code,
                    'user_type' => 'employee',
                    'user_name' => $username,
                    'userId' => Helper::maskUserId($employee->id),
                );
                if ($request->user_type == 'individual') {
                    $content = 'You are assigned to following course(s):';
                    CommonTrait::sendWelcomeEmailToUser($data, $email, $employee_id, 'welcome_employee_indivisual');
                } else {
                    $content = 'Your employer, ' . ucwords($company_name) . ', has assigned you the following course(s):';
                    CommonTrait::sendWelcomeEmailToUser($data, $email, $employee_id, 'welcome_employee');
                }
                if (!empty($request->courses)) {
                    $course_array = array();
                    if (is_array($request->courses)) {
                        $course_array = $request->courses;
                    } else {
                        array_push($course_array, $request->courses);
                    }
                    $courses = CourseModel::whereIn('id', $course_array)->get();
                    if ($courses->count() > 0) {
                        $course_names = array();
                        foreach ($courses as $course_name) {
                            $name = $course_name->name;
                            array_push($course_names, $name);
                        }
                        $data = array(
                            'courses' => $course_names,
                            'content' => $content,
                            'first_name' => $employee->first_name,
                            'last_name' => $employee->last_name,
                            'userId' => Helper::maskUserId($employee->id),
                        );
                        CommonTrait::sendEmailToAssginCourseEmployee($data, $email, $employee->id);
                    }
                }
            }
            if (Auth::check() && $request->user_type != 'individual') {
                if (Auth::user()->role_id != 1 && Auth::user()->role_id != 5) {
                    $isCompany = EmployeeCompanyLocationsModel::select('tbl_company.id as company_id', 'tbl_company.name as company_name', 'tbl_company.employee_num as allowed_users')->leftjoin('tbl_company', 'tbl_company.id', '=', 'tbl_employee_company_locations.company_id')->where('tbl_employee_company_locations.employee_id', Auth::user()->id)->first();
                    $company_id = $isCompany->company_id;
                    $company_name = $isCompany->company_name;
                    $companyAllowedUsers = $isCompany->allowed_users;
                    $getActiveCompanyUsersCount = EmployeeCompanyLocationsModel::select(DB::raw('count(Distinct(employee_id)) as user_count'))->leftjoin('tbl_employee', 'tbl_employee.id', '=', 'tbl_employee_company_locations.employee_id')->where('tbl_employee_company_locations.company_id', $company_id)->where('tbl_employee.status', 1)->first();

                    if ($getActiveCompanyUsersCount->user_count > $companyAllowedUsers) {
                        $data = array(
                            "type" => "employee",
                            "location_name" => $request->employee_first_name . ' ' . $request->employee_last_name,
                            "company_name" => $company_name,
                            "status" => "added",
                            "maxUserCount" => $companyAllowedUsers,
                            "currentUserCount" => $getActiveCompanyUsersCount->user_count,
                            "employeeDetails" => [
                                'firstName' => $employee->first_name,
                                'lastName' => $employee->last_name,
                                'email' => $employee->username,
                            ],
                        );

                        $email = config('mail.support');
                        Mail::send('charge_company', $data, function($message) use ($email) {
                            $message->to($email)->subject(env('SITE_NAME') . ' - Employee Added');
                            $message->from(env('MAIL_FROM_ADDRESS'), env('MAIL_FROM_NAME'));
                        });
                        CommonTrait::emailLog("Employee Added", $email, 0);
                    }
                }
            }

            $employee->message = "User created successfully.";
            DB::commit();

            return response()->json($employee, 200);

        } catch (Exception $ex) {
            DB::rollback();

            return response()->json(['message' => $ex->getMessage()], 422);
        }
    }

    public function update(Request $request, $id) {

        $employee = EmployeeModel::find($id);


        if (empty($employee)) {
            return response()->json(['employee' => 'Invalid id no employee found.!'], 422);
        }

        $all_data = $request->all();
        if ($request->employee_user_name) {
            $ifusernameexist = EmployeeModel::where('user_name', $request->employee_user_name)->where('id', '!=', $id)->first();
            if ($ifusernameexist) {
                return response()->json(['employee' => 'User name already exist'], 422);
            }
        }
        if (isset($all_data['employee_status'])) {
            if ($id != Auth::user()->id) {
                EmployeeModel::where('id', $id)->update(['status' => $all_data['employee_status']]);
                if ($all_data['employee_status'] == 0) {
                    $employeeData = EmployeeModel::where('id', $id)->first();
                    $infoDataArray = [
                        'activity' => 'Activity done by ' . Auth::user()->id,
                        'role' => 'Role ' . Auth::user()->role_id,
                        'user_id' => $id,
                        'user_name' => $employeeData->full_name . ' got deactivated.',
                        'date' => Carbon::now('UTC'),
                        'ip' => $_SERVER['REMOTE_ADDR'],
                    ];
                    CommonTrait::logInactiveEmployeeInfo($infoDataArray);
                }
            }
        }

        if (!empty($request->employee_location_id)) {
            EmployeeModel::where('id', $id)->update([
                'first_name' => $request->employee_first_name,
                'last_name' => $request->employee_last_name,
                'full_name' => $request->employee_first_name . ' ' . $request->employee_last_name,
                'email' => $request->employee_email,
                'phone_num' => $request->employee_phone_num,
                'job_title_id' => $request->employee_job_title,
            ]);
        } else {
            EmployeeModel::where('id', $id)->update([
                'first_name' => $request->employee_first_name,
                'last_name' => $request->employee_last_name,
                'full_name' => $request->employee_first_name . ' ' . $request->employee_last_name,
                'email' => $request->employee_email,
                'phone_num' => $request->employee_phone_num,
                'job_title_id' => $request->employee_job_title,
            ]);
        }
        if (!empty($request->employee_email)) {
            EmployeeModel::where('id', $id)->update(['email' => $request->employee_email]);
        }
        if (!empty($request->employee_user_name)) {
            EmployeeModel::where('id', $id)->update(['user_name' => $request->employee_user_name]);
        }
        if (!empty($request->employee_city)) {
            EmployeeModel::where('id', $id)->update(['city' => $request->employee_city]);
        }
        if (!empty($request->employee_state)) {
            EmployeeModel::where('id', $id)->update(['state' => $request->employee_state]);
        }
        if (!empty($request->employee_dob)) {
            EmployeeModel::where('id', $id)->update(['dob' => $request->employee_dob]);
        }
        if (!empty($request->employee_soical_security)) {
            EmployeeModel::where('id', $id)->update(['social_security' => $request->employee_soical_security]);
        }
        if (!empty($request->employee_address)) {
            EmployeeModel::where('id', $id)->update(['address' => $request->employee_address]);
        }
        if (!empty($request->employee_zipcode)) {
            EmployeeModel::where('id', $id)->update(['zipcode' => $request->employee_zipcode]);
        }
        if (!empty($request->employee_type)) {
            switch ($request->employee_type) {
                case 'admin':
                    $user_role_id = 2;
                    break;
                case 'location_manager':
                    $user_role_id = 3;
                    break;
                case 'employee':
                    $user_role_id = 4;
                    break;
                case 'individual':
                    $user_role_id = 4;
                    break;
                default:
                    $user_role_id = 4;
                    break;
            }
            EmployeeModel::where('id', $id)->update([
                'type' => $request->employee_type,
                'role_id' => $user_role_id,
            ]);
        }
        if ($request->employee_access_code != '') {
            EmployeeModel::where('id', $id)->update([
                'access_code' => $request->employee_access_code,
                'password' => md5($request->employee_access_code),
            ]);
        }

        if (isset($all_data['employee_progress'])) {
            EmployeeModel::where('id', $id)->update(['progress_status' => $all_data['employee_progress']]);
        }
        if ($request->employee_location_id) {

            if (is_array($request->employee_location_id)) {
                $locations = $request->employee_location_id;
            } else {
                $locations = array($request->employee_location_id);
            }

            $getCurrentCompany = EmployeeCompanyLocationsModel::where('employee_id', $id)->first();
            EmployeeCompanyLocationsModel::where('employee_id', $id)->delete();
            foreach ($locations as $locations_id) {
                $isCompany = CompanyModel::where('id', $locations_id)->first();

                if ($isCompany->parent_id == 0) {
                    $company_id = $locations_id;
                    $location_id = 0;
                } else {
                    $company_id = $isCompany->parent_id;
                    $location_id = $locations_id;
                }

                $update = new EmployeeCompanyLocationsModel();
                $update->location_id = $location_id;
                $update->company_id = $company_id;
                $update->employee_id = $id;
                $update->save();
                if ($getCurrentCompany->company_id != $company_id) {
                    EmployeeCoursesModel::where('employee_id', $id)->where('employee_course_status', '!=', '1')->delete();
                }
            }
        }

        return response()->json(['Employee' => 'Updated successfully..!'], 200);
    }

    public function deleteEmployee(Request $request) {
        $employee_id = $request->employee_id;
        EmployeeCoursesModel::where('employee_id', $employee_id)->delete();
        EmployeeCertificateModel::where('employee_id', $employee_id)->delete();
        EmployeeModel::where('id', $employee_id)->delete();
        EmployeeCompanyLocationsModel::where('employee_id', $employee_id)->delete();

        return response()->json(['Employee' => 'Deleted Successfully..!'], 200);
    }

    public function updateStatus(Request $request, $id) {
        $employee = EmployeeModel::find($id);
        if (empty($employee)) {
            return response()->json(['Employee' => 'Invalid id no employee found.!'], 422);
        }

        $employeeCompanyData = CompanyModel::select('tbl_company.name as company_name', 'tbl_company.id as company_id', 'tbl_company.employee_num as allowed_employees')->leftjoin('tbl_employee_company_locations', 'tbl_employee_company_locations.company_id', '=', 'tbl_company.id')->where('tbl_employee_company_locations.employee_id', $id)->first();
        if ($employeeCompanyData) {
            $employeeCompanyId = $employeeCompanyData->company_id;
            $employeeCompany = $employeeCompanyData->company_name;
            $companyAllowedUser = $employeeCompanyData->allowed_employees;
            $employeeCompanyData = CompanyModel::select('tbl_company.name as company_name', 'tbl_company.id as company_id', 'tbl_company.employee_num as allowed_employees')->leftjoin('tbl_employee_company_locations', 'tbl_employee_company_locations.company_id', '=', 'tbl_company.id')->where('tbl_employee_company_locations.employee_id', $id)->first();
            $getActiveCompanyUsersCount1 = EmployeeCompanyLocationsModel::select(DB::raw('count(Distinct(employee_id)) as user_count'))->leftjoin('tbl_employee', 'tbl_employee_company_locations.employee_id', '=', 'tbl_employee.id')->where('tbl_employee.status', 1)->where('company_id', $employeeCompanyId)->first();
            //$getActiveCompanyUsersCount=$getActiveCompanyUsersCount1['user_count'];
            $employeeData = EmployeeModel::where('id', $id)->first();
            $employeeName = $employeeData->full_name;


            // if(($request->status== 1 || $request->status==true) && ($getActiveCompanyUsersCount1->user_count > $companyAllowedUser)){
            //   $data=array(
            //       "type" => "employee",
            //       "location_name" =>$employeeName,
            //       "company_name" => $employeeCompany,
            //       "status" => "activated"
            //     );
            //     $email = config('mail.support');
            //     Mail::send('charge_company', $data, function ($message) use ($email) {
            //         $message->to($email)->subject(env('SITE_NAME').' - Employee Activated');
            //         $message->from(env('MAIL_FROM_ADDRESS'), env('MAIL_FROM_NAME'));
            //     });
            //     CommonTrait::emailLog("Employee Activated", $email, 0);
            // }
        }
        EmployeeModel::where('id', $id)->update([
            'status' => $request->status,
            'inactive_since' => NULL,
        ]);
        if ($request->status == 0) {
            $employee = EmployeeModel::where('id', $id)->first();

            $infoDataArray = [
                'activity' => 'Activity done by ' . Auth::user()->id,
                'role' => 'Role ' . Auth::user()->role_id,
                'user_id' => $employee->id,
                'user_name' => $employee->full_name . ' got deactivated.',
                'date' => Carbon::now('UTC'),
                'ip' => $_SERVER['REMOTE_ADDR'],
            ];

            CommonTrait::logInactiveEmployeeInfo($infoDataArray);
            EmployeeModel::where('id', $id)->update(['inactive_since' => date('Y-m-d')]);
        }

        return response()->json(['Status' => 'Update Successfully..!'], 200);
    }

    public function resetCode($id) {
        $employee = EmployeeModel::find($id);
        if (empty($employee)) {
            return response()->json(['Employee' => 'Invalid id no employee found.!'], 422);
        }
        $access_code = rand(100000, 999999);
        EmployeeModel::where('id', $id)->update([
            'access_code' => $access_code,
            'password' => md5($access_code),
        ]);
        $user_name = $employee->first_name . '_' . $employee->id;

        return response()->json(['Access Code' => 'New Access Code is ' . $access_code], 200);
    }

    public function get($id) {
        try {
            $employee = EmployeeModel::where('tbl_employee.id', $id)->first();
            if ($employee == NULL) {

                return response()->json(['message' => 'User is not valid.'], 422);
            }
            $employeesData = EmployeeCompanyLocationsModel::where('employee_id', $id)->get();
            $companies = array();
            $parentCompany = 0;
            if ($employeesData->count() > 0) {
                $employee->companyList = CompanyModel::where('id', $employeesData[0]->company_id)->orWhere('parent_id', $employeesData[0]->company_id)->get();
                foreach ($employeesData as $key => $value) {
                    $company = array();
                    if ($value->location_id == 0) {
                        $company = CompanyModel::where('id', $value->company_id)->first();
                    } else {
                        $company = CompanyModel::where('id', $value->location_id)->first();
                    }
                    $companies[$key] = $company;
                }
            }
            $employee->company = $companies;

            return response()->json([$employee], 200);

        } catch (Exception $th) {

            return response()->json(['message' => $th->getMessage()], 422);
        }
    }

    public function userData(Request $request) {
        $user_name = $request->user_name;
        EmployeeModel::where('user_name', $user_name)->orWhere('email', $user_name)->update(['last_sign_in' => date('Y-m-d')]);
        $employee = EmployeeModel::where('user_name', $user_name)->orWhere('email', $user_name)->get();
        $company_id = $employee[0]->company_id;
        $company = CompanyModel::where('id', $company_id)->get();
        $employee = json_decode($employee);
        $employee['company_info'] = $company;

        return response()->json($employee, 200);
    }

    public function bulkUser(Request $request) {
        $validator = Validator::make($request->all(), [
            'password' => 'required',
            'company_id' => 'required',
            "employees" => "required|array",
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
            $data = $request->employees;
            $company_id = $request->company_id;
            $location_id = 0;
            $checkChildCompany = CompanyModel::where('id', $company_id)->select('parent_id', 'name')->first();
            $compnayName = $checkChildCompany->name;
            if ($checkChildCompany != NULL && $checkChildCompany->parent_id > 0) {
                $location_id = $company_id;
                $company_id = $checkChildCompany->parent_id;
            }
            $insertData = array();
            $allUsernames = array();
            $errors = array();
            $sucessCount = 0;
            foreach ($data as $key => $employee) {
                if ($employee['employee_first_name'] == '') {

                    return response()->json(['message' => "Employee first name is required."], 422);
                }
                if ($employee['employee_last_name'] == '') {

                    return response()->json(['message' => "Employee last name is required."], 422);
                }
                $jobTitleId = "";
                if (isset($employee['employee_job_title'])) {
                    $isJobTitle = DB::table('tbl_job_title')->where('name', $employee['employee_job_title'])->first();
                    if ($isJobTitle == NULL) {

                        $errors[] = "<li>Job title " . $employee['employee_job_title'] . " is not correct for " . $employee['employee_first_name'] . "</li>";
                        continue;
                        //  return response()->json(['message' => "Job title ".$employee['employee_job_title']." is not correct for " . $employee['employee_first_name'].", Please fill correct job title."], 422);
                    } else {
                        $jobTitleId = $isJobTitle->id;
                    }
                }
                if (isset($employee['user_name'])) {
                    $isEmailExist = EmployeeModel::where('user_name', $employee['user_name'])->first();
                    if ($isEmailExist) {
                        $errors[] = "<li>Username " . $employee['user_name'] . " already exists for " . $employee['employee_first_name'] . " " . $employee['employee_last_name'] . "</li>";
                        continue;
                        //  return response()->json(['message' => "Username ".$employee['user_name']." already exists for " . $employee['employee_first_name'] ." " . $employee['employee_last_name']. ", Please try another."], 422);
                    } else {
                        $username = $employee['user_name'];
                    }
                } else if (isset($employee['employee_email']) && $employee['employee_email'] != '') {
                    $isEmailExist = EmployeeModel::where('email', $employee['employee_email'])->first();
                    if ($isEmailExist) {
                        $errors[] = "<li>Email " . $employee['employee_email'] . " already exists for " . $employee['employee_first_name'] . " " . $employee['employee_last_name'] . "</li>";
                        continue;
                        // return response()->json(['message' => "Email ".$employee['employee_email']." already exists for " . $employee['employee_first_name'] ." " . $employee['employee_last_name']. ", Please try another."], 422);
                    } else {
                        $username = $employee['employee_email'];
                    }
                } else if (isset($employee['phonenum']) && $employee['phonenum'] != '') {
                    $isPhone = EmployeeModel::where('phone_num', $employee['phonenum'])->first();
                    if ($isPhone) {
                        $errors[] = "<li>Phone number exists already for " . $employee['employee_first_name'] . "</li>";
                        continue;
                        // return response()->json(['message' => "Phone number already exists for " . $employee['employee_first_name'].", Please try another."], 422);
                    } else {
                        $username = $employee['phonenum'];
                    }
                } else {

                    $first_name = str_replace(" ", "_", $employee['employee_first_name']);
                    $username = $first_name . '_' . rand(100000, 999999);
                }
                if (!isset($employee['employee_first_name']) && $employee['employee_first_name'] != '') {

                    return response()->json(['message' => "Employee first name is required."], 422);
                }
                $userRoleId = 4;
                if (isset($employee['usertype']) && $employee['usertype'] != '') {
                    if (strtolower($employee['usertype']) == 'manager') {
                        $userRoleId = 3;
                    } else if (strtolower($employee['usertype']) == 'employee') {
                        $userRoleId = 4;
                    } else if (strtolower($employee['usertype']) == 'admin') {
                        $userRoleId = 2;
                    } else {
                        $errors[] = "<li>User type is not valid for " . $employee['employee_first_name'] . "</li>";
                        continue;
                        //  return response()->json(['message' => "User type is not valid for ".$employee['employee_first_name']."."], 422);
                    }
                } else {

                    return response()->json(['message' => "User type is required."], 422);
                }
                $usertype = "";
                if (strtolower($employee['usertype']) == 'manager') {
                    $usertype = "location_manager";
                } else if (strtolower($employee['usertype']) == 'employee') {
                    $usertype = "employee";
                } else if (strtolower($employee['usertype']) == 'admin') {
                    $usertype = "admin";
                }

                $insertData[$key]['role_id'] = $userRoleId;
                $insertData[$key]['first_name'] = $employee['employee_first_name'];
                $insertData[$key]['last_name'] = $employee['employee_last_name'];
                $insertData[$key]['full_name'] = $employee['employee_first_name'] . ' ' . $employee['employee_last_name'];
                $insertData[$key]['user_name'] = $username;
                $insertData[$key]['job_title_id'] = $jobTitleId;
                $insertData[$key]['type'] = $usertype;
                $insertData[$key]['email'] = $employee['employee_email'];
                $insertData[$key]['phone_num'] = $employee['phonenum'];
                $insertData[$key]['access_code'] = $request->password;
                $insertData[$key]['password'] = md5($request->password);
                $insertData[$key]['address'] = $employee['address'];
                $insertData[$key]['city'] = $employee['city'];
                $insertData[$key]['state'] = $employee['state'];
                $insertData[$key]['zipcode'] = $employee['zipcode'];
                $insertData[$key]['added_on'] = date('Y-m-d');
                $insertData[$key]['created_by'] = Auth::User()->id;
                $insertData[$key]['created_at'] = Carbon::now('UTC');
                $allUsernames[] = $username;
                $sucessCount++;

            }

            $insertEmployees = EmployeeModel::insert($insertData);
            $currentEmployeeRecords = EmployeeModel::whereIn('user_name', $allUsernames)->get();
            $result = array();
            $companyEmployee = array();
            $allUserIds = array();
            // assign to company
            foreach ($currentEmployeeRecords as $key => $value) {
                $userType = "";
                if ($value->type == 'location_manager') {
                    $userType = "Manager";
                } else if ($value->type == 'employee') {
                    $userType = "Employee";
                } else if ($value->type == 'admin') {
                    $userType = "Admin";
                }
                $companyEmployee[$key]['employee_id'] = $value->id;
                $companyEmployee[$key]['company_id'] = $company_id;
                $companyEmployee[$key]['location_id'] = $location_id;
                $companyEmployee[$key]['created_at'] = Carbon::now('UTC');
                if (!empty($request->selected_courses)) {
                    $this->assigenEmployeeCourse($value->id, $company_id, $request->selected_courses, $userType);
                }
            }

            $insertCompanyEmployees = EmployeeCompanyLocationsModel::insert($companyEmployee);

            //send emails
            foreach ($currentEmployeeRecords as $key => $value) {
                $userType = "";
                if ($value->type == 'location_manager') {
                    $userType = "Manager";
                } else if ($value->type == 'employee') {
                    $userType = "Employee";
                } else if ($value->type == 'admin') {
                    $userType = "Admin";
                }

                $result[$key]['First Name'] = $value->first_name;
                $result[$key]['Last Name'] = $value->last_name;
                $result[$key]['User Type'] = $userType;
                $result[$key]['Username'] = $value->user_name;
                $result[$key]['Password'] = $request->password;
                $result[$key]['Email'] = $value->email;
                $result[$key]['Phone Number'] = $value->phone_num;
                if ($value->email != '') {
                    $email = $value->email;
                    $data = array(
                        'first_name' => $value->first_name,
                        'last_name' => $value->last_name,
                        'company_name' => $compnayName,
                        'email' => $value->email,
                        'access_code' => $request->password,
                        'user_type' => 'employee',
                        'user_name' => $value->user_name,
                    );
                    Mail::send('welcome_employee', $data, function($message) use ($email) {
                        $message->to($email)->subject('Welcome to ' . env('SITE_NAME'));
                        $message->from(env('MAIL_FROM_ADDRESS'), env('MAIL_FROM_NAME'));
                    });
                    CommonTrait::emailLog("Welcome Employee Bulk", $email, $value->id);
                }
            }

            $getOrder = DB::table('tbl_company_employee_imports')->where('company_id', $company_id)->select('order_number')->orderBy('id', 'desc')->first();
            $order_number = 1;
            if ($getOrder != NULL) {
                $order_number = $getOrder->order_number + 1;
            }

            DB::table('tbl_company_employee_imports')->insert([
                'company_id' => $company_id,
                'records' => count($allUserIds),
                'data' => json_encode($allUserIds),
                'order_number' => $order_number,
                'created_at' => Carbon::now('UTC'),
            ]);

            if ($errors) {
                DB::commit();

                return response()->json([
                    'message' => implode("</br>", $errors),
                    'success_count' => $sucessCount,
                    'data' => $result,
                ], 202);
            }
            DB::commit();

            return response()->json([
                'message' => 'User inserted successfully.',
                'data' => $result,
            ], 200);
        } catch (Exception $th) {
            DB::rollback();

            return response()->json(['message' => $th->getMessage()], 422);
        }
    }

    public function employeeList(Request $request) {
        $company_id = $request->company_id;
        $employee = EmployeeModel::join('tbl_employee_company_locations', 'tbl_employee.id', '=', 'tbl_employee_company_locations.employee_id')->where('company_id', $company_id)->where('type', 'employee')->get();

        return response()->json($employee, 200);
    }

    public function managerList(Request $request) {
        $company_id = $request->company_id;
        // $employees=EmployeeCompanyLocationsModel::where('company_id',$company_id)->get();
        $locationManager = "location_manager";
        // foreach($employees as $employee){
        $managers = EmployeeModel::join('tbl_employee_company_locations', 'tbl_employee.id', '=', 'tbl_employee_company_locations.employee_id')->where('company_id', $company_id)->where('type', $locationManager)->get();
        //  }
        if (empty($managers)) {
            return response()->json(['Manager' => 'No manager found.!'], 422);
        }

        return response()->json($managers, 200);

    }

    public function becomeManager($id) {
        $role = "3";
        $employee = EmployeeModel::find($id);
        if (empty($employee)) {
            return response()->json(['Employee' => 'Invalid id no employee found.!'], 422);
        }
        EmployeeModel::where('id', $id)->update([
            'type' => 'location_manager',
            'role_id' => $role,
        ]);

        return response()->json(['Employee' => 'Employee become a Manager'], 200);
    }

    public function addManager(Request $request) {
        $access_code = rand(100000, 999999);
        $location_manager = new EmployeeModel();
        $location_manager->first_name = $request->manager_first_name;
        $location_manager->last_name = $request->manager_last_name;
        $location_manager->full_name = $request->manager_first_name . ' ' . $request->manager_last_name;
        $location_manager->email = $request->manager_email;
        $location_manager->company_id = $request->company_id;
        $location_manager->type = 'location_manager';
        $location_manager->access_code = $access_code;
        $location_manager->added_on = date('Y-m-d');
        $location_manager->save();
        $location_manager_id = $location_manager->id;
        EmployeeModel::where('id', $location_manager_id)->update(['user_name' => $request->manager_first_name . '_' . $location_manager_id]);
        $username = $request->manager_first_name . '_' . $location_manager_id;
        $role = 4;
        $employee_id = NULL;
        $this->companyAdmin($username, $access_code, $role, $employee_id);

        return response()->json($location_manager, 200);

    }

    public function courseReminderEmail(Request $request) {
        try {
            $ids = $request->ids;
            $finalMessage = [];
            foreach ($ids as $id) {
                $getCompany = EmployeeCompanyLocationsModel::where('employee_id', $id['id'])->first();
                $company = "";
                if ($getCompany != NULL) {

                    if ($getCompany->location_id == 0) {
                        $company_id = $getCompany->company_id;
                    } else {
                        $company_id = $getCompany->location_id;
                    }
                    $isCompany = CompanyModel::where('id', $company_id)->first();
                    if ($isCompany) {
                        $company = $isCompany->name;
                    }
                }


                $employee_data = EmployeeModel::find($id['id']);

                $get_course = EmployeeCoursesModel::leftJoin('tbl_course', 'tbl_course.id', '=', 'tbl_employee_courses.course_id')->where([
                    [
                        'tbl_employee_courses.employee_id',
                        $id['id'],
                    ],
                    [
                        'tbl_employee_courses.employee_course_status',
                        '!=',
                        1,
                    ],
                ])->select('tbl_employee_courses.course_id', 'tbl_course.name', 'tbl_employee_courses.employee_course_date_assigned', 'tbl_employee_courses.employee_course_due_date')->get();

                $course_names = array();
                $i = 0;
                foreach ($get_course as $key => $value) {
                    $employee_course_date_assigned = Carbon::now('UTC');
                    $employee_course_due_date = Carbon::parse($value->employee_course_due_date);
                    $dus_days = $employee_course_date_assigned->diffInDays($employee_course_due_date, FALSE);

                    if ($dus_days) {
                        $dueDaysText = '';
                        if ($dus_days < 0) {
                            $dueDaysText = abs($dus_days) . ' day ago';
                            if (abs($dus_days) > 1) {
                                $dueDaysText = abs($dus_days) . ' days ago';
                            }
                        } else {
                            $dueDaysText = $dus_days . ' day';
                            if (abs($dus_days) > 1) {
                                $dueDaysText = abs($dus_days) . ' days';
                            }
                        }
                        $course_names[$i]['dus_days'] = $dueDaysText;
                        $course_names[$i]['name'] = $value->name;
                    }
                    $i++;
                }

                // foreach($data1 as $course_name) {
                //     $name = $course_name->name;
                //     array_push($course_names, $name);
                // }
                if (!empty($employee_data->email)) {
                    $email = $employee_data->email;
                    if ($employee_data->type == 'individual') {
                        $company = 'individual';
                    }
                    $data = array(
                        'first_name' => $employee_data->first_name,
                        'last_name' => $employee_data->last_name,
                        'company_name' => $company,
                        'email' => $employee_data->email,
                        'courses' => $course_names,
                        'userId' => Helper::maskUserId($id['id']),
                    );
                    if (count($course_names) > 0) {
                        $status = 200;
                        if ((new UnsubscribeController())->wantToSendTheEmail($id['id']) === FALSE) {
                            continue;
                        }
                        Mail::send('mail', $data, function($message) use ($email) {
                            $message->to($email)->subject(env('SITE_NAME') . ' - Important Reminder - Training Course Due!');
                            $message->from(env('MAIL_FROM_ADDRESS'), env('MAIL_FROM_NAME'));
                        });
                        CommonTrait::emailLog("Course Reminder", $email, $id['id']);
                        array_push($finalMessage, 'Email sent successfully!');
                        $status = 200;
                    } else {
                        array_push($finalMessage, 'No courses due for this employee.');
                        $status = 422;
                    }
                }
            }

            return response()->json([
                'Email' => $finalMessage,
                'Status' => $status,
            ], 200);


        } catch (Exception $th) {
            return response()->json(['Email' => $th->getMessage()], 422);
        }
    }

    public function courseExpireReminderEmail(Request $request) {
        try {
            $ids = $request->ids;
            $finalMessage = [];
            foreach ($ids as $id) {
                $getCompany = EmployeeCompanyLocationsModel::where('employee_id', $id['id'])->first();
                $company = "";
                if ($getCompany != NULL) {

                    if ($getCompany->location_id == 0) {
                        $company_id = $getCompany->company_id;
                    } else {
                        $company_id = $getCompany->location_id;
                    }
                    $isCompany = CompanyModel::where('id', $company_id)->first();

                    $company = $isCompany->name;
                }

                $employee_data = EmployeeModel::find($id['id']);
                $get_course = EmployeeCoursesModel::leftJoin('tbl_course', 'tbl_course.id', '=', 'tbl_employee_courses.course_id')->where('tbl_employee_courses.employee_id', $id['id'])->where(function($query) {
                    $query->where('tbl_employee_courses.employee_course_status', 0);
                    $query->orWhere('tbl_employee_courses.employee_course_status', 3);
                })->select('tbl_employee_courses.course_id', 'tbl_course.name', 'tbl_employee_courses.employee_course_date_assigned', 'tbl_employee_courses.employee_course_due_date')->get();

                $course_names = array();
                $i = 0;
                foreach ($get_course as $key => $value) {
                    $employee_course_date_assigned = Carbon::now('UTC');
                    $employee_course_due_date = Carbon::parse($value->employee_course_due_date);
                    $dus_days = $employee_course_date_assigned->diffInDays($employee_course_due_date, FALSE);

                    // if ($dus_days >= 0) {
                    $course_names[$i]['dus_days'] = $dus_days;
                    $course_names[$i]['name'] = $value->name;
                    //  }
                    $i++;
                }
                if (!empty($employee_data->email)) {
                    $email = $employee_data->email;
                    if ($employee_data->type == 'individual') {
                        $company = 'individual';
                    }
                    $data = array(
                        'first_name' => $employee_data->first_name,
                        'last_name' => $employee_data->last_name,
                        'company_name' => $company,
                        'email' => $employee_data->email,
                        'courses' => $course_names,
                        'userId' => Helper::maskUserId($id['id']),
                    );


                    if (count($course_names) > 0) {
                        $status = 200;
                        if ((new UnsubscribeController())->wantToSendTheEmail($id['id']) === FALSE) {
                            continue;
                        }
                        Mail::send('expired_mail', $data, function($message) use ($email) {
                            $message->to($email)->subject(env('SITE_NAME') . ' - Important Reminder - Training Course Due!');
                            $message->from(env('MAIL_FROM_ADDRESS'), env('MAIL_FROM_NAME'));
                        });
                        CommonTrait::emailLog("Course Expire Reminder", $email, $id['id']);
                        array_push($finalMessage, 'Email sent successfully!');
                    } else {
                        array_push($finalMessage, 'No courses due for this employee.');
                        $status = 422;
                    }
                }
            }

            return response()->json([
                'Email' => $finalMessage,
                'Status' => $status,
            ], 200);
            //return response()->json(['Email' => "Email sent successfully!"], 200);


        } catch (Exception $th) {
            return response()->json(['Email' => $th->getMessage()], 422);
        }
    }

    public function courseExpiringReminderEmail(Request $request) {
        try {
            $today = date('Y-m-d');
            $next_date = date('Y-m-d', strtotime('+7 days'));
            $ids = $request->ids;
            foreach ($ids as $id) {
                $getCompany = EmployeeCompanyLocationsModel::where('employee_id', $id['id'])->first();
                $company = "";
                if ($getCompany != NULL) {

                    if ($getCompany->location_id == 0) {
                        $company_id = $getCompany->company_id;
                    } else {
                        $company_id = $getCompany->location_id;
                    }
                    $isCompany = CompanyModel::where('id', $company_id)->first();

                    $company = $isCompany->name;
                }

                $employee_data = EmployeeModel::find($id['id']);
                $get_course = EmployeeCoursesModel::leftJoin('tbl_course', 'tbl_course.id', '=', 'tbl_employee_courses.course_id')->where('tbl_employee_courses.employee_id', $id['id'])->where('tbl_employee_courses.employee_course_status', '!=', 1)->whereBetween('tbl_employee_courses.employee_course_due_date', [
                    $today,
                    $next_date,
                ])->select('tbl_employee_courses.course_id', 'tbl_course.name', 'tbl_employee_courses.employee_course_date_assigned', 'tbl_employee_courses.employee_course_due_date')->get();

                $course_names = array();
                $i = 0;
                foreach ($get_course as $key => $value) {
                    $employee_course_date_assigned = Carbon::now('UTC');
                    $employee_course_due_date = Carbon::parse($value->employee_course_due_date);
                    $dus_days = $employee_course_date_assigned->diffInDays($employee_course_due_date, FALSE);

                    if ($dus_days >= 0) {
                        $course_names[$i]['dus_days'] = $dus_days;
                        $course_names[$i]['name'] = $value->name;
                    }
                    $i++;
                }
                if (!empty($employee_data->email)) {
                    $email = $employee_data->email;
                    if ($employee_data->type == 'individual') {
                        $company = 'individual';
                    }
                    $data = array(
                        'first_name' => $employee_data->first_name,
                        'last_name' => $employee_data->last_name,
                        'company_name' => $company,
                        'email' => $employee_data->email,
                        'courses' => $course_names,
                        'userId' => Helper::maskUserId($id['id']),
                    );
                    $status = 200;
                    if ((new UnsubscribeController())->wantToSendTheEmail($id['id']) === FALSE) {
                        continue;
                    }
                    Mail::send('mail', $data, function($message) use ($email) {
                        $message->to($email)->subject(env('SITE_NAME') . ' - Important Reminder - Training Course Due!');
                        $message->from(env('MAIL_FROM_ADDRESS'), env('MAIL_FROM_NAME'));
                    });
                    CommonTrait::emailLog("Course Due Reminder", $email, $id['id']);
                }
            }

            return response()->json(['Email' => "Email sent successfully!"], 200);


        } catch (Exception $th) {
            return response()->json(['Email' => $th->getMessage()], 422);
        }
    }

    public function sendTutorialLink(Request $request) {
        try {
            $ids = $request->ids;
            foreach ($ids as $id) {
                $employee = EmployeeModel::where('id', $id['id'])->first();

                $email = $employee->email;
                $data = array(
                    'full_name' => $employee->full_name,
                    'tutorial_link' => $request->tutorial_link,
                    'userId' => Helper::maskUserId($id['id']),
                );
                if ($email) {
                    if ((new UnsubscribeController())->wantToSendTheEmail($id['id']) === FALSE) {
                        continue;
                    }

                    Mail::send('tutorial_link', $data, function($message) use ($email) {
                        $message->to($email)->subject(env('SITE_NAME') . ' - Tutorial Link');
                        $message->from(env('MAIL_FROM_ADDRESS'), env('MAIL_FROM_NAME'));
                    });

                    CommonTrait::emailLog("Tutorial Link", $email, $id['id']);
                }
            }

            return response()->json(['Email' => "Email sent successfully!"], 200);
        } catch (Exception $th) {
            return response()->json(['Email' => $th->getMessage()], 422);
        }
    }

    public function welcomeEmail(Request $request) {

        $ids = $request->ids;
        foreach ($ids as $id) {
            $employee = EmployeeModel::where('id', $id['id'])->first();

            $email = $employee->email;
            $data = array(
                'first_name' => $employee->first_name,
                'last_name' => $employee->last_name,
                'email' => $employee->email,
                'access_code' => $employee->access_code,
                'user_name' => $employee->user_name,
                'userId' => Helper::maskUserId($employee->id),
            );
            if ($email) {
                if ($employee->type == 'individual') {
                    $data['user_type'] = 'individual';
                    $data['userId'] = Helper::maskUserId($id['id']);
                    $content = 'You have selected to view the following course(s):';
                    CommonTrait::sendWelcomeEmailToUser($data, $email, $id['id'], 'welcome_employee_indivisual');
                } else {
                    $getCompany = EmployeeCompanyLocationsModel::where('employee_id', $employee->id)->where('company_id', '!=', 0)->first();
                    $company = "";
                    if ($getCompany != NULL) {
                        if ($getCompany->location_id == 0) {
                            $company_id = $getCompany->company_id;
                        } else {
                            $company_id = $getCompany->location_id;
                        }
                        $isCompany = CompanyModel::where('id', $company_id)->first();
                        $company = $isCompany->name;
                    }
                    $data['company_name'] = $company;
                    $data['user_type'] = 'employee';
                    $data['userId'] = Helper::maskUserId($id['id']);
                    $content = 'Your employer, ' . ucwords($company) . ', has assigned you the following course(s):';
                    CommonTrait::sendWelcomeEmailToUser($data, $email, $id['id'], 'welcome_employee');
                }
            }
        }

        return response()->json(['Email' => "Email sent successfully!"], 200);
    }

    public function welcomeIndividualEmail(Request $request) {
        $ids = $request->ids;
        foreach ($ids as $id) {
            $employee_data = EmployeeModel::find($id['id']);
            if (!empty($employee_data->email)) {
                $email = $employee_data->email;
                $data = array(
                    'first_name' => $employee_data->first_name,
                    'last_name' => $employee_data->last_name,
                    'email' => $employee_data->email,
                    'access_code' => $employee_data->access_code,
                    'user_type' => 'employee',
                    'user_name' => $employee_data->user_name,
                    'userId' => Helper::maskUserId($id['id']),
                );

                if ((new UnsubscribeController)->wantToSendTheEmail($id['id']) == FALSE) {
                    continue;
                }

                Mail::send('welcome_employee_indivisual', $data, function($message) use ($email) {
                    $message->to($email)->subject('Welcome to' . env('SITE_NAME'));
                    // from is same email we set in .env file
                    $message->from(env('MAIL_FROM_ADDRESS'), env('MAIL_FROM_NAME'));
                });
                CommonTrait::emailLog("WELCOME IDIVIDUAL EMAIL", $email, $id['id']);
            }
        }

        return response()->json(['Email' => "Email sent successfully!"], 200);
    }

    public function destroy($id) {
        $employee = EmployeeModel::find($id);
        if (empty($employee)) {
            return response()->json(['Employee' => 'Invalid id no employee found.!'], 422);
        }
        EmployeeModel::destroy($id);

        return response()->json([], 200);
    }

    public function companyEmployee(Request $request) {

        try {
            $search = $request->search;
            $user = Auth::user();
            switch ($user->role_id) {
                case $this->status['user_role']['super-admin']:
                    if (!empty($request->company_id)) {
                        $getEmployee = EmployeeCompanyLocationsModel::leftJoin('tbl_employee', 'tbl_employee.id', '=', 'tbl_employee_company_locations.employee_id')->where('tbl_employee_company_locations.company_id', $isCompany->id)->orWhere('tbl_employee_company_locations.location_id', $isCompany->id)->select('tbl_employee.*');
                    } else {
                        $getEmployee = EmployeeModel::select('tbl_employee.*');
                    }
                    break;
                case $this->status['user_role']['company-admin']:
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
                        $c = 'tbl_employee_company_locations.company_id';
                    } else {
                        $c = 'tbl_employee_company_locations.location_id';
                        $company_id = $companies['isLocations'];
                    }

                    $getEmployee = EmployeeCompanyLocationsModel::leftJoin('tbl_employee', 'tbl_employee.id', '=', 'tbl_employee_company_locations.employee_id')->whereIn($c, $company_id)->select('tbl_employee.*');


                    break;
                case $this->status['user_role']['manager']:
                    $getCompany = EmployeeCompanyLocationsModel::where([
                        'employee_id' => $user->id,
                        'location_id' => 0,
                    ])->first();
                    $getEmployee = EmployeeCompanyLocationsModel::leftJoin('tbl_employee', 'tbl_employee.id', '=', 'tbl_employee_company_locations.employee_id')->select('tbl_employee.*');
                    if ($getCompany != NULL) {


                        $getEmployee->where('tbl_employee_company_locations.company_id', $getCompany->company_id);
                    } else {

                        $getCompanies = EmployeeCompanyLocationsModel::where(['employee_id' => $user->id])->select('location_id')->groupBy('location_id')->get()->toArray();
                        $companyIds = array_column($getCompanies, 'location_id');
                        $getEmployee->whereIn('tbl_employee_company_locations.location_id', $companyIds);
                    }

                    break;
                default:
                    if (!empty($request->company_id)) {
                        $getEmployee = EmployeeCompanyLocationsModel::leftJoin('tbl_employee', 'tbl_employee.id', '=', 'tbl_employee_company_locations.employee_id')->where('tbl_employee_company_locations.company_id', $isCompany->id)->orWhere('tbl_employee_company_locations.location_id', $isCompany->id)->select('tbl_employee.*');
                    } else {
                        $getEmployee = EmployeeModel::select('tbl_employee.*');
                    }
                    break;
            }

            $getEmployee->where(function($query) use ($search) {
                $query->where('tbl_employee.first_name', 'like', '%' . $search . '%');
                $query->orWhere('tbl_employee.last_name', 'like', '%' . $search . '%');
            })->orderBy('tbl_employee.full_name');


            return response()->json($getEmployee->get(), 200);

        } catch (Exception $th) {

            return response()->json(['message' => $th->getMessage()], 422);
        }

        // $id = $request->company_id;
        // $search = $request->search;
        // $company = CompanyModel::find($id);
        // if (empty($company))
        // {
        //     return response()->json(['Company' => 'Invalid id no company found.!'], 422);
        // }
        // $search = explode(" ", $search);
        // $where_data = [];
        // foreach ($search as $key => $name)
        // {
        //     $where_data[] = ['full_name', 'like', '%' . $name . '%'];
        // }
        // $employee = EmployeeModel::where($where_data)->take(5)
        //     ->get();


    }

    public function jobTitle(Request $request) {
        $id = $request->company_id;
        $company = CompanyModel::find($id);
        if (empty($company)) {
            return response()->json(['Company' => 'Invalid id no company found.!'], 422);
        }
        $employee = EmployeeModel::select('job_title')->where('company_id', $id)->groupBy('employee_job_title')->get();

        return response()->json($employee, 200);
    }

    public function stats($id) {
        $employee = EmployeeModel::find($id);
        if (empty($employee)) {
            return response()->json(['Employee' => 'Invalid id no company found.!'], 422);
        }
        $data = [];
        //1 for pass course
        $pass = EmployeeCoursesModel::leftJoin('tbl_course', 'tbl_course.id', 'tbl_employee_courses.course_id')->where([
            [
                'employee_id',
                $id,
            ],
            [
                'employee_course_status',
                1,
            ],
            [
                'tbl_course.status',
                1,
            ],
        ])->count();
        //0 for fail
        $fail = EmployeeCoursesModel::leftJoin('tbl_course', 'tbl_course.id', 'tbl_employee_courses.course_id')->where([
            [
                'employee_id',
                $id,
            ],
            [
                'employee_course_status',
                0,
            ],
        ], [
            'tbl_course.status',
            1,
        ])->count();
        //2 for open
        $open = EmployeeCoursesModel::leftJoin('tbl_course', 'tbl_course.id', 'tbl_employee_courses.course_id')->where([
            [
                'employee_id',
                $id,
            ],
            [
                'employee_course_status',
                2,
            ],
        ], [
            'tbl_course.status',
            1,
        ])->count();
        //3 for expired
        $expired = EmployeeCoursesModel::leftJoin('tbl_course', 'tbl_course.id', 'tbl_employee_courses.course_id')->where([
            [
                'employee_id',
                $id,
            ],
            [
                'employee_course_status',
                3,
            ],
        ], [
            'tbl_course.status',
            1,
        ])->count();
        $data['open'] = $open;
        $data['pass'] = $pass;
        $data['fail'] = $fail;
        $data['expired'] = $expired;

        return response()->json($data, 200);
    }

    public function coursesBACKUP(Request $request) {
        $id = $request->employee_id;
        $data = [];
        $where_data = [];
        $employee = EmployeeModel::find($id);
        if (empty($employee)) {
            return response()->json(['Employee' => 'Invalid id no employee found.!'], 422);
        }
        if ($request->employee_status) {
            if ($request->employee_status == "open") {
                $emp_course_status = "2";
            } else if ($request->employee_status == "passed") {
                $emp_course_status = "1";
            } else if ($request->employee_status == "failed") {
                $emp_course_status = "0";
            } else if ($request->employee_status == "expired") {
                $emp_course_status = "3";
            }
            array_push($where_data, [
                'employee_course_status',
                $emp_course_status,
            ]);
        }
        if ($request->section) {
            array_push($where_data, [
                'employee_course_status',
                '<>',
                '1',
            ]);
        }
        $secondCourseStatus = 0;
        $companyStatus = EmployeeCompanyLocationsModel::select('tbl_company.id as company_id', 'secondary_course_status', 'pay_employee_status', 'pay_employee_discount')->leftjoin('tbl_company', 'tbl_company.id', '=', 'tbl_employee_company_locations.company_id')->where('tbl_employee_company_locations.employee_id', $id)->first();
        if ($companyStatus) {
            $secondCourseStatus = $companyStatus->secondary_course_status;
        }
        $courses = EmployeeCoursesModel::select('tbl_employee_courses.*', 'tbl_course.*', DB::Raw('(CASE WHEN ' . $secondCourseStatus . ' && `tbl_course`.secondary_course_name!="" then `tbl_course`.secondary_course_name  ELSE `tbl_course`.name END) as name'))->leftJoin('tbl_course', 'tbl_course.id', '=', 'tbl_employee_courses.course_id')->where([
            [
                'employee_id',
                $id,
            ],
            [
                'tbl_course.status',
                1,
            ],
        ])->where($where_data)->orderBy('tbl_course.name', 'ASC')->get();

        $isEmployeePayByEmployee = EmployeeCompanyLocationsModel::select('tbl_company.id as company_id', 'pay_employee_status', 'pay_employee_discount')->leftjoin('tbl_company', 'tbl_company.id', '=', 'tbl_employee_company_locations.company_id')->where('tbl_employee_company_locations.employee_id', $id)->first();
        for ($i = 0; $i < count($courses); $i++) {

            $course_id = $courses[$i]['course_id'];
            $paidCourseCount = PayByEmployeeHistoryModel::where([
                [
                    'employee_id',
                    $id,
                ],
                [
                    'course_id',
                    $course_id,
                ],
                [
                    'accessible',
                    1,
                ],
            ])->count();
            $coursestatus = $courses[$i]['employee_course_status'];
            if ($coursestatus == "1") {
                $employeecertificates = EmployeeCertificateModel::where([
                    [
                        'course_id',
                        $course_id,
                    ],
                    [
                        'employee_id',
                        $id,
                    ],
                ])->orderBy('id', 'desc')->limit(1)->get();
                if ($employeecertificates->count() > 0) {
                    $pdf_url = url() . '/employee/certificate_manual/';
                    foreach ($employeecertificates as $value) {
                        if ($value->manual == 1 && $value->certificate_url != '') {
                            $value->certificate_url = $pdf_url . $value->certificate_url;
                        }
                        if ($value->is_proctored_exam == 1 && $value->certificate_url != '') {
                            $value->certificate_url = $value->certificate_url;
                        }
                        $value->certificate_url;
                    }
                    $courses[$i]->employee_certiifcates = $employeecertificates;
                }
            }

            $courseduedate = $courses[$i]['employee_course_due_date'];

            $course_info = CourseModel::find($course_id);
            // $courses[$i]->name = $course_info->name;
            $courses[$i]->length = $course_info->length;
            $courses[$i]->is_2fa_required = $course_info->is_2fa_required;
            $user_attempted = EmployeeCourseAttemptsModel::where([
                [
                    'user_id',
                    $id,
                ],
                [
                    'course_id',
                    $course_id,
                ],
            ])->get();
            $user_resources = CourseResourceModel::leftjoin('tbl_resources', 'tbl_resources.id', '=', 'tbl_course_resource.resource_id')->where([
                [
                    'course_id',
                    $course_id,
                ],
            ])->get();
            $courses[$i]->course_attempts = $user_attempted;
            $courses[$i]->course_resources = $user_resources;
            $courses[$i]->course_paid_status = $paidCourseCount;
        }
        $total = EmployeeCoursesModel::where([
            [
                'employee_id',
                $id,
            ],
            [
                'course_status',
                1,
            ],
        ])->count();
        $data['courses'] = $courses;
        $data['total'] = $total;
        $data['employee'] = $employee;
        $data['company_pay_by_employee'] = $companyStatus;

        return response()->json($data, 200);
    }

    public function courses(Request $request) {
        $id = $request->employee_id;
        $data = [];
        $where_data = [];
        $employee = EmployeeModel::find($id);
        if (empty($employee)) {
            return response()->json(['Employee' => 'Invalid id no employee found.!'], 422);
        }
        if ($request->employee_status) {
            if ($request->employee_status == "open") {
                $emp_course_status = "2";
            } else if ($request->employee_status == "passed") {
                $emp_course_status = "1";
            } else if ($request->employee_status == "failed") {
                $emp_course_status = "0";
            } else if ($request->employee_status == "expired") {
                $emp_course_status = "3";
            }
            array_push($where_data, [
                'employee_course_status',
                $emp_course_status,
            ]);
        }
        if ($request->section) {
            array_push($where_data, [
                'employee_course_status',
                '<>',
                '1',
            ]);
        }
        $courses = EmployeeCoursesModel::leftJoin('tbl_course', 'tbl_course.id', '=', 'tbl_employee_courses.course_id')->where([
            [
                'employee_id',
                $id,
            ],
            [
                'tbl_course.status',
                1,
            ],
        ])->where($where_data)->orderBy('tbl_course.name', 'ASC')->get();

        $isEmployeePayByEmployee = EmployeeCompanyLocationsModel::select('tbl_company.id as company_id', 'pay_employee_status', 'pay_employee_discount')->leftjoin('tbl_company', 'tbl_company.id', '=', 'tbl_employee_company_locations.company_id')->where('tbl_employee_company_locations.employee_id', $id)->first();
        for ($i = 0; $i < count($courses); $i++) {

            $course_id = $courses[$i]['course_id'];
            $paidCourseCount = PayByEmployeeHistoryModel::where([
                [
                    'employee_id',
                    $id,
                ],
                [
                    'course_id',
                    $course_id,
                ],
                [
                    'accessible',
                    1,
                ],
            ])->count();
            $coursestatus = $courses[$i]['employee_course_status'];
            if ($coursestatus == "1") {
                $employeecertificates = EmployeeCertificateModel::where([
                    [
                        'course_id',
                        $course_id,
                    ],
                    [
                        'employee_id',
                        $id,
                    ],
                ])->orderBy('id', 'desc')->limit(1)->get();
                if ($employeecertificates->count() > 0) {

                    $pdf_url = url() . '/employee/certificate_manual/';
                    foreach ($employeecertificates as $value) {
                        if ($value->manual == 1 && $value->certificate_url != '') {
                            $value->certificate_url = $pdf_url . $value->certificate_url;
                        }
                        if ($value->is_proctored_exam == 1 && $value->certificate_url != '') {
                            $value->certificate_url = $value->certificate_url;
                        }
                        $value->certificate_url;
                    }
                    $courses[$i]->employee_certiifcates = $employeecertificates;
                }
            }

            $courseduedate = $courses[$i]['employee_course_due_date'];

            $course_info = CourseModel::find($course_id);
            $courses[$i]->name = $course_info->name;
            $courses[$i]->length = $course_info->length;
            $courses[$i]->is_2fa_required = $course_info->is_2fa_required;
            $user_attempted = EmployeeCourseAttemptsModel::where([
                [
                    'user_id',
                    $id,
                ],
                [
                    'course_id',
                    $course_id,
                ],
            ])->get();
            $user_resources = CourseResourceModel::leftjoin('tbl_resources', 'tbl_resources.id', '=', 'tbl_course_resource.resource_id')->where([
                [
                    'course_id',
                    $course_id,
                ],
            ])->get();
            $courses[$i]->course_attempts = $user_attempted;
            $courses[$i]->course_resources = $user_resources;
            $courses[$i]->course_paid_status = $paidCourseCount;
        }
        $total = EmployeeCoursesModel::where([
            [
                'employee_id',
                $id,
            ],
            [
                'course_status',
                1,
            ],
        ])->count();
        $data['courses'] = $courses;
        $data['total'] = $total;
        $data['employee'] = $employee;
        $data['company_pay_by_employee'] = $isEmployeePayByEmployee;

        return response()->json($data, 200);
    }

    public function unassignCourse(Request $request) {
        $course_id = $request->course_id;
        $employee_id = $request->employee_id;
        EmployeeCoursesModel::where([
            [
                'employee_id',
                $employee_id,
            ],
            [
                'course_id',
                $course_id,
            ],
        ])->delete();
        EmployeeCourseAttemptsModel::where([
            [
                'user_id',
                $employee_id,
            ],
            [
                'course_id',
                $course_id,
            ],
        ])->delete();

        return response()->json(['Course' => 'Un-assign Successfully..!'], 200);
    }

    public function reassignLocation(Request $request) {
        $locations_id = $request->location_id;
        $employee_ids = $request->employee_ids;

        // foreach ($employee_ids as $id)
        // {
        //     EmployeeCompanyLocationsModel::where('employee_id', $id['id'])->update(["location_id" => $location_id]);
        // }

        foreach ($employee_ids as $id) {
            EmployeeCompanyLocationsModel::where('employee_id', $id['id'])->delete();
            $isCompany = CompanyModel::where('id', $locations_id)->first();

            if ($isCompany->parent_id == 0) {
                $company_id = $locations_id;
                $location_id = 0;
            } else {
                $company_id = $isCompany->parent_id;
                $location_id = $locations_id;
            }
            $update = new EmployeeCompanyLocationsModel();
            $update->location_id = $location_id;
            $update->company_id = $company_id;
            $update->employee_id = $id['id'];
            $update->save();

        }

        return response()->json(['Location' => 'Assign Successfully..!'], 200);

    }

    public function reassignMultipleLocation(Request $request) {
        $location_ids = is_array($request->location_ids) ? $request->location_ids : [$request->location_ids];
        $employee_ids = $request->employee_ids;
        $new_location_ids = [];
        foreach ($location_ids as $location_id) {
            $isCompany = CompanyModel::where('id', $location_id)->first();
            if ($isCompany->parent_id == 0) {
                $new_location_ids[$location_id] = [
                    'location_id' => 0,
                    'company_id' => $location_id,
                ];
            } else {
                $new_location_ids[$location_id] = [
                    'location_id' => $location_id,
                    'company_id' => $isCompany->parent_id,
                ];
            }
        }

        foreach ($employee_ids as $id) {
            EmployeeCompanyLocationsModel::where('employee_id', $id)->delete();
            $batchInsertLocation = [];
            foreach ($new_location_ids as $location_id) {
                $insertLocation = new EmployeeCompanyLocationsModel();
                $insertLocation->location_id = $location_id['location_id'];
                $insertLocation->company_id = $location_id['company_id'];
                $insertLocation->employee_id = $id;
                $batchInsertLocation[] = $insertLocation->attributesToArray();
            }
            EmployeeCompanyLocationsModel::insert($batchInsertLocation);
        }

        return response()->json(['Location' => 'Assign Successfully..!'], 200);

    }

    public function tutorialVideos() {
        $data = VideoModel::orderBy('video_title', 'Asc')->get();

        return response()->json($data, 200);
    }

    public function filteredTutorialVideos(Request $request) {
        $status = $request->video_status;
        $data = [];
        $where_data = [];
        $orWhere_data = [];

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
        if (!empty($request->filter_interface)) {
            array_push($where_data, [
                'role',
                $request->filter_interface,
            ]);
        }

        $data = VideoModel::where($where_data);
        if (!empty($request->search)) {
            $search = $request->search;
            $data->where(function($query) use ($search) {
                $query->orWhere('video_title', 'like', '%' . $search . '%');
                $query->orWhere('video', 'like', '%' . $search . '%');
            });

        }
        $tutorialData = $data->orderBy('order', 'Asc')->get();

        return response()->json($tutorialData, 200);
    }

    public function updateVideoStatus(Request $request, $id) {
        $video = VideoModel::find($id);
        if (empty($video)) {
            return response()->json(['video' => 'Invalid id no video found.!'], 422);
        }
        VideoModel::where('id', $id)->update(['status' => $request->status]);

        return response()->json(['Video' => 'Status Updated Successfully.!'], 200);
    }

    public function tutorialVideo($id) {
        $video = VideoModel::find($id);
        if (empty($video)) {
            return response()->json(['video' => 'Invaild id no video found.!'], 422);
        }

        return response()->json($video, 200);
    }

    public function updateTutorialVideo(Request $request, $id) {
        $video = VideoModel::find($id);
        if (empty($video)) {
            return response()->json(['video' => 'Invalid id no video found.!'], 422);
        }
        foreach ($request->role as $role) {
            VideoModel::where('id', $id)->update([
                'video_title' => $request->video_name,
                'video_description' => $request->video_description,
                'video' => $request->video,
                'role' => $role,
            ]);
        }

        return response()->json(['Video' => 'Video Updated Successfully.!'], 200);
    }

    public function addTutorialVideo(Request $request) {
        foreach ($request->role as $role) {
            $video = new VideoModel();
            $video->role = $role;
            $video->video_title = $request->video_name;
            $video->video_description = $request->video_description;
            $video->video = $request->video;
            $video->save();
            //  $role=array_push($role);
        }


    }

    public function deleteTutorialVideo(Request $request) {
        $video_id = $request->video_id;
        if (empty($video_id)) {
            return response()->json(['video' => 'Invalid id no video found.!'], 422);
        }
        VideoModel::where('id', $video_id)->delete();

        return response()->json(['Video' => 'Video Deleted Successfully.!'], 200);
    }

    public function tutorialVideoWithRole(Request $request) {
        $videos = "";

        $videos = VideoModel::where('role', $request->role)->where('status', 1)->orderby('order', 'asc')->get();

        return response()->json($videos, 200);
    }

    public function updateTutorialVideoOrder(Request $request) {
        for ($i = 0; $i < count($request->data); $i++) {
            VideoModel::where('id', $request->data[$i])->update(['order' => $i + 1]);
        }
        $video_status = 1;
        if ($request->video_status == 'Active') {
            $video_status = 1;
        } else if ($request->video_status == 'Inactive') {
            $video_status = 0;
        } else {
            $video_status = "";
        }
        $result = DB::table('tbl_tutorial_video');
        $result->where('role', $request->filter_interface)->where('status', $video_status)->orderby('tbl_tutorial_video.order', 'asc');
        $result = $result->get();

        return response()->json($result, 200);
    }

    public function listBACKUP(Request $request) {
        $columnName = [
            0 => 'tbl_employee.first_name',
            1 => 'tbl_employee.last_name',
            2 => 'tbl_company.name',
            3 => 'tbl_employee.role_id',
            4 => 'pass_total',
        ];
        $requestData = $request->all();
        $startFrom = "";
        $limit = "";
        if (isset($requestData['page']) && isset($requestData['per_page'])) {
            $startFrom = ($request->page == 0) ? ($request->page * $request->per_page) : ($request->page - 1) * $request->per_page;
            $limit = $request->per_page;
        }
        $orderBy = '';
        $orderColumn = "";
        if (isset($requestData['order']) && isset($requestData['column'])) {
            $orderBy = $request->order;
            $orderColumn = ($request->column < 5) ? $columnName[$request->column] : 'tbl_employee.first_name';
        }
        $where_data = [];
        $orWhere_data = [];
        $where_dataa = "";
        $total = 0;
        if (!empty($request->employee_status)) {
            $status = $request->employee_status;
            if ($status == "Active") {
                $status = 1;
            }
            if ($status == "Inactive") {
                $status = 0;
            }
            array_push($where_data, [
                'tbl_employee.status',
                $status,
            ]);
        }
        $user = Auth::User();
        if ($request->role == "manager" || $request->role == "company" || $request->role == "admin") {
            $employee_id = $user->id;
            $whereRoleData = [];
            if ($request->role == "manager") {
                array_push($whereRoleData, [
                    'tbl_employee.role_id',
                    '<>',
                    2,
                ]);
            }
            if ($request->company_id != '' && $request->company_id != 0) {
                $company_id = array($request->company_id);
                $companies = CompanyModel::whereIn('id', $company_id)->first();
                if ($companies->parent_id != 0) {
                    $where_dataa = 'tbl_employee_company_locations.location_id';
                } else {
                    $where_dataa = 'tbl_employee_company_locations.company_id';
                    array_push($where_data, [
                        'tbl_employee_company_locations.location_id',
                        0,
                    ]);

                }
            } else {
                $companies = CompanyModel::getCompaniesByAdminUser($employee_id);
                if ($companies == NULL) {
                    return response()->json(['message' => 'User does not have any company.'], 422);
                }
                $company_id = [];
                if ($companies['isParent'] != 0) {
                    if (is_array($companies['isParent'])) {
                        $company_id = $companies['isParent'];
                    } else {
                        $company_id = array(
                            $companies['isParent'],
                        );
                    }
                    $where_dataa = 'tbl_employee_company_locations.company_id';
                    if ($request->company_id != '') {
                        array_push($where_data, [
                            'tbl_employee_company_locations.location_id',
                            0,
                        ]);
                    }
                } else {
                    $where_dataa = 'tbl_employee_company_locations.location_id';
                    $company_id = $companies['isLocations'];
                }
            }

            $getResult = EmployeeCompanyLocationsModel::
            leftJoin('tbl_company', 'tbl_company.id', '=', DB::Raw('(CASE WHEN `tbl_employee_company_locations`.location_id = 0 then `tbl_employee_company_locations`.company_id ELSE `tbl_employee_company_locations`.location_id END)'))->leftJoin('tbl_employee', 'tbl_employee.id', '=', 'tbl_employee_company_locations.employee_id')->leftjoin('tbl_employee_courses', 'tbl_employee_courses.employee_id', '=', 'tbl_employee_company_locations.employee_id')->where('tbl_employee.type', '!=', 'individual')->where($whereRoleData)->whereIn($where_dataa, $company_id)->where($where_data);
            if (!empty($request->search)) {
                $search = $request->search;
                $getResult->where(function($query) use ($search) {
                    $query->orWhere('tbl_employee.full_name', 'like', '%' . $search . '%');
                    $query->orWhere('tbl_employee.first_name', 'like', '%' . $search . '%');
                    $query->orWhere('tbl_employee.last_name', 'like', '%' . $search . '%');
                    $query->orWhere('tbl_employee.email', 'like', '%' . $search . '%');
                    $query->orWhere('tbl_employee.user_name', 'like', '%' . $search . '%');
                });

            }

            if ($request->filter_value) {
                $getResult->whereIn('tbl_employee.role_id', $request->filter_value);
            }
            $getResult->groupBy('tbl_employee.id')->select('tbl_employee.*', 'tbl_company.name as company_name', 'tbl_company.secondary_course_status', 'tbl_employee_company_locations.company_id', 'tbl_employee_company_locations.location_id', DB::raw('max(CASE employee_course_status WHEN 3 THEN 3 WHEN 0 THEN 3 WHEN 2 THEN 2 WHEN 1 THEN 1 ELSE 0 END) AS pass_total'));

            if (!empty($getResult->get()->toArray())) {
                $total = count($getResult->get()->toArray());
            }
            if ($orderColumn != '' && $orderBy != '') {
                $getResult->orderBy($orderColumn, $orderBy);
            }
            if ($limit != '') {
                $getResult->skip($startFrom)->take($limit);
            }

            $get = $getResult->get();


            $employees = array();
            foreach ($get as $key => $value) {

                $employees[$key]['id'] = $value->id;
                $employees[$key]['first_name'] = $value->first_name;
                $employees[$key]['last_name'] = $value->last_name;
                $employees[$key]['company_name'] = $value->company_name;
                $employees[$key]['email'] = $value->email;
                $employees[$key]['type'] = $value->type;
                $employees[$key]['status'] = $value->status;
                $employees[$key]['progress_status'] = $value->progress_status;
                $employees[$key]['company_id'] = 0;
                $employees[$key]['name'] = '';
                $employees[$key]['course_pass_count'] = EmployeeCoursesModel::leftJoin('tbl_course', 'tbl_course.id', 'tbl_employee_courses.course_id')->where('tbl_course.status', 1)->where([
                    'employee_id' => $value->id,
                    'employee_course_status' => 1,
                ])->count();
                $employees[$key]['course_fail_count'] = EmployeeCoursesModel::leftJoin('tbl_course', 'tbl_course.id', 'tbl_employee_courses.course_id')->where('tbl_course.status', 1)->where([
                    'employee_id' => $value->id,
                    'employee_course_status' => 0,
                ])->count();
                $employees[$key]['course_expired_count'] = EmployeeCoursesModel::leftJoin('tbl_course', 'tbl_course.id', 'tbl_employee_courses.course_id')->where('tbl_course.status', 1)->where([
                    'employee_id' => $value->id,
                    'employee_course_status' => 3,
                ])->count();
                $employees[$key]['course_open_count'] = EmployeeCoursesModel::leftJoin('tbl_course', 'tbl_course.id', 'tbl_employee_courses.course_id')->where('tbl_course.status', 1)->where([
                    'employee_id' => $value->id,
                    'employee_course_status' => 2,
                ])->count();


                $companyId = $value->location_id;
                if ($value->location_id == 0) {
                    $companyId = $value->company_id;
                }
                $isCompany = CompanyModel::where('id', $companyId)->first();

                if ($isCompany != NULL) {
                    $employees[$key]['company_id'] = $isCompany->id;
                    $employees[$key]['name'] = $isCompany->name;
                }
                $employeeCourses = EmployeeCoursesModel::leftJoin('tbl_course', 'tbl_course.id', 'tbl_employee_courses.course_id')->select('tbl_course.*', DB::Raw('(CASE WHEN ' . $value->secondary_course_status . ' && `tbl_course`.secondary_course_name!="" then `tbl_course`.secondary_course_name  ELSE `tbl_course`.name END) as name'), 'tbl_employee_courses.employee_course_due_date', 'tbl_employee_courses.employee_course_status', 'tbl_employee_courses.employee_course_date_assigned', 'tbl_employee_courses.employee_course_date_completed')->where('tbl_course.status', 1)->where(['employee_id' => $value->id])->get();
                foreach ($employeeCourses as $ecvalue) {
                    $isCertificate = EmployeeCertificateModel::where([
                        'course_id' => $ecvalue->id,
                        'employee_id' => $value->id,
                    ])->orderBy('id', 'desc')->first();
                    $ecvalue->cerificate_expiration_date = "";
                    $ecvalue->employee_certificate_id = "";
                    $ecvalue->is_food_certifcate = "";
                    $ecvalue->food_safe_certificate_url = "";
                    if ($isCertificate != NULL) {
                        $ecvalue->cerificate_expiration_date = $isCertificate->certificate_expiration_date;
                        $ecvalue->employee_certificate_id = $isCertificate->id;
                        $ecvalue->is_food_certifcate = $isCertificate->is_proctored_exam;
                        $ecvalue->food_safe_certificate_url = $isCertificate->certificate_url;
                    }
                }
                $employees[$key]['courses'] = $employeeCourses;

            }

            $data['employee'] = $employees;
            $data['total'] = $total;

            return response()->json($data, 200);
        } else {
            //select('tbl_employee.*' , DB::raw('max(CASE employee_course_status WHEN 3 THEN 3 WHEN 0 THEN 3 WHEN 2 THEN 2 WHEN 1 THEN 1 ELSE 0 END) AS pass_total'))
            // ->leftjoin('tbl_employee_courses','tbl_employee_courses.employee_id','=','tbl_employee.id')
            if (!empty($request->search) || !empty($request->filter_type) || !empty($request->filter_value) || !empty($request->company_id)) {
                if ($request->company_id == '') {
                    $getEmployee = EmployeeModel::select('tbl_employee.*', 'tbl_company.name as company_name', DB::raw('max(CASE employee_course_status WHEN 3 THEN 3 WHEN 0 THEN 3 WHEN 2 THEN 2 WHEN 1 THEN 1 ELSE 0 END) AS pass_total'))->leftjoin('tbl_employee_company_locations', 'tbl_employee_company_locations.employee_id', '=', 'tbl_employee.id')->leftJoin('tbl_company', 'tbl_company.id', '=', DB::Raw('(CASE WHEN `tbl_employee_company_locations`.location_id = 0 then `tbl_employee_company_locations`.company_id ELSE `tbl_employee_company_locations`.location_id END)'))->leftjoin('tbl_employee_courses', 'tbl_employee_courses.employee_id', '=', 'tbl_employee.id')->where(function($query) use ($where_data) {
                        $query->where($where_data);
                    });
                    if (!empty($request->search)) {
                        $search = $request->search;
                        $getEmployee->where(function($query) use ($search) {
                            $query->orWhere('tbl_employee.full_name', 'like', '%' . $search . '%');
                            $query->orWhere('tbl_employee.first_name', 'like', '%' . $search . '%');
                            $query->orWhere('tbl_employee.last_name', 'like', '%' . $search . '%');
                            $query->orWhere('tbl_employee.email', 'like', '%' . $search . '%');
                            $query->orWhere('tbl_employee.user_name', 'like', '%' . $search . '%');
                        });
                    }
                    if ($request->filter_type == "individual") {
                        $getEmployee->where('tbl_employee.type', 'individual');
                    } else if ($request->filter_type == "company_user") {
                        $getEmployee->where('tbl_employee.type', '!=', 'individual')->where('tbl_employee.type', '!=', 'sub-admin');
                    } else if ($request->filter_type == "sub-admin") {
                        $getEmployee->where('tbl_employee.type', 'sub-admin');
                    }
                    if ($request->user_type) {
                        $getEmployee->where('tbl_employee.role_id', $request->user_type);
                    }
                    if ($request->filter_value) {
                        $getEmployee->whereIn('tbl_employee.role_id', $request->filter_value);
                    }
                    $getEmployee->groupBy('tbl_employee.id');
                    if (!empty($getEmployee->get()->toArray())) {
                        $total = count($getEmployee->get()->toArray());
                    }
                    if ($orderColumn != '' && $orderBy != '') {
                        $getEmployee->orderBy($orderColumn, $orderBy);
                    }

                    if ($limit != '') {
                        $getEmployee->skip($startFrom);
                        $getEmployee->take($limit);
                    }

                    $get = $getEmployee->get();
                    //   $total = $getEmployee->count();


                } else {
                    $company_id = $request->company_id;
                    $company = CompanyModel::where('id', $company_id)->first();
                    $wheredata = [];
                    $locationType = "";
                    if ($request->only_parent == "true") {
                        array_push($wheredata, [
                            'tbl_employee_company_locations.location_id',
                            0,
                        ]);
                    }

                    if ($company->parent_id == 0) {
                        $locationType = 'tbl_employee_company_locations.company_id';
                    } else {
                        $locationType = 'tbl_employee_company_locations.location_id';
                    }


                    $getEmployee = EmployeeCompanyLocationsModel::
                    leftJoin('tbl_company', 'tbl_company.id', '=', DB::Raw('(CASE WHEN `tbl_employee_company_locations`.location_id = 0 then `tbl_employee_company_locations`.company_id ELSE `tbl_employee_company_locations`.location_id END)'))->leftJoin('tbl_employee', 'tbl_employee.id', '=', 'tbl_employee_company_locations.employee_id')->leftjoin('tbl_employee_courses', 'tbl_employee_courses.employee_id', '=', 'tbl_employee_company_locations.employee_id')->where($locationType, $company_id)->where($wheredata)->where('tbl_employee.type', '!=', 'individual')->where($where_data)->orWhere($orWhere_data)->groupBy('tbl_employee.id')->select('tbl_employee.*', 'tbl_company.name as company_name', 'tbl_employee_company_locations.company_id', 'tbl_employee_company_locations.location_id', DB::raw('max(CASE employee_course_status WHEN 3 THEN 3 WHEN 0 THEN 3 WHEN 2 THEN 2 WHEN 1 THEN 1 ELSE 0 END) AS pass_total'));
                    if (!empty($request->search)) {
                        $search = $request->search;
                        $getEmployee->where(function($query) use ($search) {
                            $query->orWhere('tbl_employee.full_name', 'like', '%' . $search . '%');
                            $query->orWhere('tbl_employee.first_name', 'like', '%' . $search . '%');
                            $query->orWhere('tbl_employee.last_name', 'like', '%' . $search . '%');
                            $query->orWhere('tbl_employee.email', 'like', '%' . $search . '%');
                            $query->orWhere('tbl_employee.user_name', 'like', '%' . $search . '%');
                        });
                    }
                    if ($request->filter_value) {
                        $getEmployee->whereIn('tbl_employee.role_id', $request->filter_value);
                    }
                    if (!empty($getEmployee->get()->toArray())) {
                        $total = count($getEmployee->get()->toArray());
                    }


                    if ($orderColumn != '' && $orderBy != '') {
                        $getEmployee->orderBy($orderColumn, $orderBy);
                    }
                    if ($limit != '') {
                        $getEmployee->skip($startFrom);
                        $getEmployee->take($limit);
                    }

                    $get = $getEmployee->get();

                }

                $employees = array();
                foreach ($get as $key => $value) {
                    $employees[$key]['id'] = $value->id;
                    $employees[$key]['first_name'] = $value->first_name;
                    $employees[$key]['last_name'] = $value->last_name;
                    $employees[$key]['email'] = $value->email;
                    $employees[$key]['type'] = $value->type;
                    $employees[$key]['status'] = $value->status;
                    $employees[$key]['progress_status'] = $value->progress_status;
                    $employees[$key]['company_id'] = 0;
                    $employees[$key]['name'] = 'Individual';
                    $employees[$key]['course_pass_count'] = EmployeeCoursesModel::leftJoin('tbl_course', 'tbl_course.id', 'tbl_employee_courses.course_id')->where('tbl_course.status', 1)->where([
                        'employee_id' => $value->id,
                        'employee_course_status' => 1,
                    ])->count();
                    $employees[$key]['course_fail_count'] = EmployeeCoursesModel::leftJoin('tbl_course', 'tbl_course.id', 'tbl_employee_courses.course_id')->where('tbl_course.status', 1)->where([
                        'employee_id' => $value->id,
                        'employee_course_status' => 0,
                    ])->count();
                    $employees[$key]['course_expired_count'] = EmployeeCoursesModel::leftJoin('tbl_course', 'tbl_course.id', 'tbl_employee_courses.course_id')->where('tbl_course.status', 1)->where([
                        'employee_id' => $value->id,
                        'employee_course_status' => 3,
                    ])->count();
                    $employees[$key]['course_open_count'] = EmployeeCoursesModel::leftJoin('tbl_course', 'tbl_course.id', 'tbl_employee_courses.course_id')->where('tbl_course.status', 1)->where([
                        'employee_id' => $value->id,
                        'employee_course_status' => 2,
                    ])->count();
                    $employees[$key]['food_manager_status'] = EmployeeCoursesModel::where([
                        'employee_id' => $value->id,
                        'course_id' => 1,
                    ])->pluck('employee_course_status');

                    $companyId = CompanyModel::getCompantIdByEmployeeId($value->id);
                    $isCompany = CompanyModel::where('id', $companyId)->first();
                    if ($isCompany != NULL) {
                        $employees[$key]['company_id'] = $isCompany->id;
                        $employees[$key]['name'] = $isCompany->name;
                    }
                    $employeeCourses = EmployeeCoursesModel::leftJoin('tbl_course', 'tbl_course.id', 'tbl_employee_courses.course_id')->select('tbl_course.*', 'tbl_employee_courses.employee_course_due_date', 'tbl_employee_courses.employee_course_status', 'tbl_employee_courses.employee_course_date_assigned', 'tbl_employee_courses.employee_course_date_completed')->where('tbl_course.status', 1)->where(['employee_id' => $value->id])->get();
                    foreach ($employeeCourses as $ecvalue) {
                        $isCertificate = EmployeeCertificateModel::where([
                            'course_id' => $ecvalue->id,
                            'employee_id' => $value->id,
                        ])->orderBy('id', 'desc')->first();
                        $ecvalue->cerificate_expiration_date = "";
                        $ecvalue->employee_certificate_id = "";
                        $ecvalue->is_food_certifcate = "";
                        $ecvalue->food_safe_certificate_url = "";
                        if ($isCertificate != NULL) {
                            $ecvalue->cerificate_expiration_date = $isCertificate->certificate_expiration_date;
                            $ecvalue->employee_certificate_id = $isCertificate->id;
                            $ecvalue->is_food_certifcate = $isCertificate->is_proctored_exam;
                            $ecvalue->food_safe_certificate_url = $isCertificate->certificate_url;
                        }
                    }
                    $employees[$key]['courses'] = $employeeCourses;
                }

                $data['employee'] = $employees;
                $data['total'] = $total;

                return response()->json($data, 200);
            }
        }

    }

    public function list(Request $request) {
        $columnName = [
            0 => 'tbl_employee.first_name',
            1 => 'tbl_employee.last_name',
            2 => 'tbl_company.name',
            3 => 'tbl_employee.role_id',
            4 => 'pass_total',
        ];
        $requestData = $request->all();
        $startFrom = "";
        $limit = "";
        if (isset($requestData['page']) && isset($requestData['per_page'])) {
            $startFrom = ($request->page == 0) ? ($request->page * $request->per_page) : ($request->page - 1) * $request->per_page;
            $limit = $request->per_page;
        }
        $orderBy = '';
        $orderColumn = "";
        if (isset($requestData['order']) && isset($requestData['column'])) {
            $orderBy = $request->order;
            $orderColumn = ($request->column < 5) ? $columnName[$request->column] : 'tbl_employee.first_name';
        }
        $where_data = [];
        $orWhere_data = [];
        $total = 0;
        if (!empty($request->employee_status)) {
            $status = $request->employee_status;
            if ($status == "Active") {
                $status = 1;
            }
            if ($status == "Inactive") {
                $status = 0;
            }
            array_push($where_data, [
                'tbl_employee.status',
                $status,
            ]);
        }
        $user = Auth::User();
        if ($request->role == "manager" || $request->role == "company" || $request->role == "admin") {
            $employee_id = $user->id;
            $whereRoleData = [];
            if ($request->role == "manager") {
                array_push($whereRoleData, [
                    'tbl_employee.role_id',
                    '<>',
                    2,
                ]);
            }
            if ($request->company_id != '' && $request->company_id != 0) {
                $company_id = array($request->company_id);
                $companies = CompanyModel::whereIn('id', $company_id)->first();
                if ($companies->parent_id != 0) {
                    $where_dataa = 'tbl_employee_company_locations.location_id';
                } else {
                    $where_dataa = 'tbl_employee_company_locations.company_id';
                    array_push($where_data, [
                        'tbl_employee_company_locations.location_id',
                        0,
                    ]);

                }
            } else {
                $companies = CompanyModel::getCompaniesByAdminUser($employee_id);
                if ($companies == NULL) {
                    return response()->json(['message' => 'User does not have any company.'], 422);
                }
                $company_id = [];
                if ($companies['isParent'] != 0) {
                    if (is_array($companies['isParent'])) {
                        $company_id = $companies['isParent'];
                    } else {
                        $company_id = array(
                            $companies['isParent'],
                        );
                    }
                    $where_dataa = 'tbl_employee_company_locations.company_id';
                    if ($request->company_id != '') {
                        array_push($where_data, [
                            'tbl_employee_company_locations.location_id',
                            0,
                        ]);
                    }
                } else {
                    $where_dataa = 'tbl_employee_company_locations.location_id';
                    $company_id = $companies['isLocations'];
                }
            }

            $getResult = EmployeeCompanyLocationsModel::leftJoin('tbl_company', 'tbl_company.id', '=', DB::Raw('(CASE WHEN `tbl_employee_company_locations`.location_id = 0 then `tbl_employee_company_locations`.company_id ELSE `tbl_employee_company_locations`.location_id END)'))->leftJoin('tbl_employee', 'tbl_employee.id', '=', 'tbl_employee_company_locations.employee_id')->leftjoin('tbl_employee_courses', 'tbl_employee_courses.employee_id', '=', 'tbl_employee_company_locations.employee_id')->where('tbl_employee.type', '!=', 'individual')->where($whereRoleData)->whereIn($where_dataa, $company_id)->where($where_data);
            if (!empty($request->search)) {
                $search = $request->search;
                $getResult->where(function($query) use ($search) {
                    $query->orWhere('tbl_employee.full_name', 'like', '%' . $search . '%');
                    $query->orWhere('tbl_employee.first_name', 'like', '%' . $search . '%');
                    $query->orWhere('tbl_employee.last_name', 'like', '%' . $search . '%');
                    $query->orWhere('tbl_employee.email', 'like', '%' . $search . '%');
                    $query->orWhere('tbl_employee.user_name', 'like', '%' . $search . '%');
                });

            }

            if ($request->filter_value) {
                $getResult->whereIn('tbl_employee.role_id', $request->filter_value);
            }
            $getResult->groupBy('tbl_employee.id')->select('tbl_employee.*', 'tbl_company.name as company_name', 'tbl_company.secondary_course_status', 'tbl_employee_company_locations.company_id', 'tbl_employee_company_locations.location_id', DB::raw('max(CASE employee_course_status WHEN 3 THEN 3 WHEN 0 THEN 3 WHEN 2 THEN 2 WHEN 1 THEN 1 ELSE 0 END) AS pass_total'));

            if (!empty($getResult->get()->toArray())) {
                $total = count($getResult->get()->toArray());
            }
            if ($orderColumn != '' && $orderBy != '') {
                $getResult->orderBy($orderColumn, $orderBy);
            }
            if ($limit != '') {
                $getResult->skip($startFrom)->take($limit);
            }

            $get = $getResult->get();


            $employees = array();
            foreach ($get as $key => $value) {

                $employees[$key]['id'] = $value->id;
                $employees[$key]['first_name'] = $value->first_name;
                $employees[$key]['last_name'] = $value->last_name;
                $employees[$key]['company_name'] = $value->company_name;
                $employees[$key]['email'] = $value->email;
                $employees[$key]['type'] = $value->type;
                $employees[$key]['status'] = $value->status;
                $employees[$key]['progress_status'] = $value->progress_status;
                $employees[$key]['company_id'] = 0;
                $employees[$key]['name'] = '';
                $employees[$key]['course_pass_count'] = EmployeeCoursesModel::leftJoin('tbl_course', 'tbl_course.id', 'tbl_employee_courses.course_id')->where('tbl_course.status', 1)->where([
                    'employee_id' => $value->id,
                    'employee_course_status' => 1,
                ])->count();
                $employees[$key]['course_fail_count'] = EmployeeCoursesModel::leftJoin('tbl_course', 'tbl_course.id', 'tbl_employee_courses.course_id')->where('tbl_course.status', 1)->where([
                    'employee_id' => $value->id,
                    'employee_course_status' => 0,
                ])->count();
                $employees[$key]['course_expired_count'] = EmployeeCoursesModel::leftJoin('tbl_course', 'tbl_course.id', 'tbl_employee_courses.course_id')->where('tbl_course.status', 1)->where([
                    'employee_id' => $value->id,
                    'employee_course_status' => 3,
                ])->count();
                $employees[$key]['course_open_count'] = EmployeeCoursesModel::leftJoin('tbl_course', 'tbl_course.id', 'tbl_employee_courses.course_id')->where('tbl_course.status', 1)->where([
                    'employee_id' => $value->id,
                    'employee_course_status' => 2,
                ])->count();


                $companyId = $value->location_id;
                if ($value->location_id == 0) {
                    $companyId = $value->company_id;
                }
                $isCompany = CompanyModel::where('id', $companyId)->first();

                if ($isCompany != NULL) {
                    $employees[$key]['company_id'] = $isCompany->id;
                    $employees[$key]['name'] = $isCompany->name;
                }
                $employeeCourses = EmployeeCoursesModel::leftJoin('tbl_course', 'tbl_course.id', 'tbl_employee_courses.course_id')->select('tbl_course.*', DB::Raw('(CASE WHEN ' . $value->secondary_course_status . ' && `tbl_course`.secondary_course_name!="" then `tbl_course`.secondary_course_name  ELSE `tbl_course`.name END) as name'), 'tbl_employee_courses.employee_course_due_date', 'tbl_employee_courses.employee_course_status', 'tbl_employee_courses.employee_course_date_assigned', 'tbl_employee_courses.employee_course_date_completed')->where('tbl_course.status', 1)->where(['employee_id' => $value->id])->get();
                foreach ($employeeCourses as $ecvalue) {
                    $isCertificate = EmployeeCertificateModel::where([
                        'course_id' => $ecvalue->id,
                        'employee_id' => $value->id,
                    ])->orderBy('id', 'desc')->first();
                    $ecvalue->cerificate_expiration_date = "";
                    $ecvalue->employee_certificate_id = "";
                    $ecvalue->is_food_certifcate = "";
                    $ecvalue->food_safe_certificate_url = "";
                    if ($isCertificate != NULL) {
                        $ecvalue->cerificate_expiration_date = $isCertificate->certificate_expiration_date;
                        $ecvalue->employee_certificate_id = $isCertificate->id;
                        $ecvalue->is_food_certifcate = $isCertificate->is_proctored_exam;
                        $ecvalue->food_safe_certificate_url = $isCertificate->certificate_url;
                    }
                }
                $employees[$key]['courses'] = $employeeCourses;
            }

            $data['employee'] = $employees;
            $data['total'] = $total;

            return response()->json($data, 200);
        } else {
            //select('tbl_employee.*' , DB::raw('max(CASE employee_course_status WHEN 3 THEN 3 WHEN 0 THEN 3 WHEN 2 THEN 2 WHEN 1 THEN 1 ELSE 0 END) AS pass_total'))
            // ->leftjoin('tbl_employee_courses','tbl_employee_courses.employee_id','=','tbl_employee.id')
            if (!empty($request->search) || !empty($request->filter_type) || !empty($request->filter_value) || !empty($request->company_id)) {
                if ($request->company_id == '') {
                    $getEmployee = EmployeeModel::select('tbl_employee.*', 'tbl_company.name as company_name', DB::raw('max(CASE employee_course_status WHEN 3 THEN 3 WHEN 0 THEN 3 WHEN 2 THEN 2 WHEN 1 THEN 1 ELSE 0 END) AS pass_total'))->leftjoin('tbl_employee_company_locations', 'tbl_employee_company_locations.employee_id', '=', 'tbl_employee.id')->leftJoin('tbl_company', 'tbl_company.id', '=', DB::Raw('(CASE WHEN `tbl_employee_company_locations`.location_id = 0 then `tbl_employee_company_locations`.company_id ELSE `tbl_employee_company_locations`.location_id END)'))->leftjoin('tbl_employee_courses', 'tbl_employee_courses.employee_id', '=', 'tbl_employee.id')->where(function($query) use ($where_data) {
                        $query->where($where_data);
                    });
                    if (!empty($request->search)) {
                        $search = $request->search;
                        $getEmployee->where(function($query) use ($search) {
                            $query->orWhere('tbl_employee.full_name', 'like', '%' . $search . '%');
                            $query->orWhere('tbl_employee.first_name', 'like', '%' . $search . '%');
                            $query->orWhere('tbl_employee.last_name', 'like', '%' . $search . '%');
                            $query->orWhere('tbl_employee.email', 'like', '%' . $search . '%');
                            $query->orWhere('tbl_employee.user_name', 'like', '%' . $search . '%');
                        });
                    }
                    if ($request->filter_type == "individual") {
                        $getEmployee->where('tbl_employee.type', 'individual');
                    } else if ($request->filter_type == "company_user") {
                        $getEmployee->where('tbl_employee.type', '!=', 'individual')->where('tbl_employee.type', '!=', 'sub-admin');
                    } else if ($request->filter_type == "sub-admin") {
                        $getEmployee->where('tbl_employee.type', 'sub-admin');
                    }
                    if ($request->user_type) {
                        $getEmployee->where('tbl_employee.role_id', $request->user_type);
                    }
                    if ($request->filter_value) {
                        $getEmployee->whereIn('tbl_employee.role_id', $request->filter_value);
                    }
                    $getEmployee->groupBy('tbl_employee.id');
                    if (!empty($getEmployee->get()->toArray())) {
                        $total = count($getEmployee->get()->toArray());
                    }
                    if ($orderColumn != '' && $orderBy != '') {
                        $getEmployee->orderBy($orderColumn, $orderBy);
                    }

                    if ($limit != '') {
                        $getEmployee->skip($startFrom);
                        $getEmployee->take($limit);
                    }

                    $get = $getEmployee->get();
                    //   $total = $getEmployee->count();


                } else {
                    $company_id = $request->company_id;
                    $company = CompanyModel::where('id', $company_id)->first();
                    $wheredata = [];

                    //if ($request->only_parent == "true") {
                    //    array_push($wheredata, [
                    //        'tbl_employee_company_locations.location_id',
                    //        0,
                    //    ]);
                    //}

                    $locationType = "";
                    if ($company->parent_id == 0) {
                        $locationType = 'tbl_employee_company_locations.company_id';
                        if ($request->only_parent == "true") {
                            array_push($wheredata, [
                                'tbl_employee_company_locations.location_id',
                                0,
                            ]);
                        }
                    } else {
                        $locationType = 'tbl_employee_company_locations.location_id';
                    }


                    $getEmployee = EmployeeCompanyLocationsModel::
                    leftJoin('tbl_company', 'tbl_company.id', '=', DB::Raw('(CASE WHEN `tbl_employee_company_locations`.location_id = 0 then `tbl_employee_company_locations`.company_id ELSE `tbl_employee_company_locations`.location_id END)'))->leftJoin('tbl_employee', 'tbl_employee.id', '=', 'tbl_employee_company_locations.employee_id')->leftjoin('tbl_employee_courses', 'tbl_employee_courses.employee_id', '=', 'tbl_employee_company_locations.employee_id')->where($locationType, $company_id)->where($wheredata)->where('tbl_employee.type', '!=', 'individual')->where($where_data)->orWhere($orWhere_data)->groupBy('tbl_employee.id')->select('tbl_employee.*', 'tbl_company.name as company_name', 'tbl_employee_company_locations.company_id', 'tbl_employee_company_locations.location_id', DB::raw('max(CASE employee_course_status WHEN 3 THEN 3 WHEN 0 THEN 3 WHEN 2 THEN 2 WHEN 1 THEN 1 ELSE 0 END) AS pass_total'));
                    if (!empty($request->search)) {
                        $search = $request->search;
                        $getEmployee->where(function($query) use ($search) {
                            $query->orWhere('tbl_employee.full_name', 'like', '%' . $search . '%');
                            $query->orWhere('tbl_employee.first_name', 'like', '%' . $search . '%');
                            $query->orWhere('tbl_employee.last_name', 'like', '%' . $search . '%');
                            $query->orWhere('tbl_employee.email', 'like', '%' . $search . '%');
                            $query->orWhere('tbl_employee.user_name', 'like', '%' . $search . '%');
                        });
                    }
                    if ($request->filter_value) {
                        $getEmployee->whereIn('tbl_employee.role_id', $request->filter_value);
                    }
                    if (!empty($getEmployee->get()->toArray())) {
                        $total = count($getEmployee->get()->toArray());
                    }


                    if ($orderColumn != '' && $orderBy != '') {
                        $getEmployee->orderBy($orderColumn, $orderBy);
                    }
                    if ($limit != '') {
                        $getEmployee->skip($startFrom);
                        $getEmployee->take($limit);
                    }

                    $get = $getEmployee->get();

                }

                $employees = array();
                foreach ($get as $key => $value) {
                    $employees[$key]['id'] = $value->id;
                    $employees[$key]['first_name'] = $value->first_name;
                    $employees[$key]['last_name'] = $value->last_name;
                    $employees[$key]['email'] = $value->email;
                    $employees[$key]['type'] = $value->type;
                    $employees[$key]['status'] = $value->status;
                    $employees[$key]['progress_status'] = $value->progress_status;
                    $employees[$key]['company_id'] = 0;
                    $employees[$key]['name'] = 'Individual';
                    $employees[$key]['course_pass_count'] = EmployeeCoursesModel::leftJoin('tbl_course', 'tbl_course.id', 'tbl_employee_courses.course_id')->where('tbl_course.status', 1)->where([
                        'employee_id' => $value->id,
                        'employee_course_status' => 1,
                    ])->count();
                    $employees[$key]['course_fail_count'] = EmployeeCoursesModel::leftJoin('tbl_course', 'tbl_course.id', 'tbl_employee_courses.course_id')->where('tbl_course.status', 1)->where([
                        'employee_id' => $value->id,
                        'employee_course_status' => 0,
                    ])->count();
                    $employees[$key]['course_expired_count'] = EmployeeCoursesModel::leftJoin('tbl_course', 'tbl_course.id', 'tbl_employee_courses.course_id')->where('tbl_course.status', 1)->where([
                        'employee_id' => $value->id,
                        'employee_course_status' => 3,
                    ])->count();
                    $employees[$key]['course_open_count'] = EmployeeCoursesModel::leftJoin('tbl_course', 'tbl_course.id', 'tbl_employee_courses.course_id')->where('tbl_course.status', 1)->where([
                        'employee_id' => $value->id,
                        'employee_course_status' => 2,
                    ])->count();
                    $employees[$key]['food_manager_status'] = EmployeeCoursesModel::where([
                        'employee_id' => $value->id,
                        'course_id' => 1,
                    ])->pluck('employee_course_status');

                    $companyId = CompanyModel::getCompantIdByEmployeeId($value->id);
                    $isCompany = CompanyModel::where('id', $companyId)->first();
                    if ($isCompany != NULL) {
                        $employees[$key]['company_id'] = $isCompany->id;
                        $employees[$key]['name'] = $isCompany->name;
                    }
                    $employeeCourses = EmployeeCoursesModel::leftJoin('tbl_course', 'tbl_course.id', 'tbl_employee_courses.course_id')->select('tbl_course.*', 'tbl_employee_courses.employee_course_due_date', 'tbl_employee_courses.employee_course_status', 'tbl_employee_courses.employee_course_date_assigned', 'tbl_employee_courses.employee_course_date_completed')->where('tbl_course.status', 1)->where(['employee_id' => $value->id])->get();
                    foreach ($employeeCourses as $ecvalue) {
                        $isCertificate = EmployeeCertificateModel::where([
                            'course_id' => $ecvalue->id,
                            'employee_id' => $value->id,
                        ])->orderBy('id', 'desc')->first();
                        $courseExistInCourseFolders = CourseFolderAssignModel::select('tbl_employee_coursefolders.folder_id as course_folder_id')->leftjoin('tbl_employee_coursefolders', 'tbl_employee_coursefolders.folder_id', '=', 'tbl_course_coursefolder.course_folder_id')->where('course_id', $ecvalue->id)->where('employee_id', $value->id)->groupby('tbl_employee_coursefolders.folder_id')->get();

                        if ($courseExistInCourseFolders) {
                            foreach ($courseExistInCourseFolders as $courseExistInCourseFolder) {
                                if ($courseExistInCourseFolder) {
                                    $course_folder_id = $courseExistInCourseFolder->course_folder_id;

                                    $getAllCourses = CourseFolderAssignModel::where('course_folder_id', $course_folder_id)->get();
                                    $courses = array();
                                    foreach ($getAllCourses as $course_id_test) {
                                        array_push($courses, $course_id_test->course_id);
                                    }
                                    $checkIfEmployeeAssignedCourses = EmployeeCoursesModel::whereIn('course_id', $courses)->where('employee_id', $value->id)->get();

                                    if (count($checkIfEmployeeAssignedCourses) == count($courses)) {

                                        $checkIfEmployeeCompletedCourseFolder = EmployeeCoursesModel::whereIn('course_id', $courses)->where('employee_id', $value->id)->where('employee_course_status', '!=', '1');

                                        if ($checkIfEmployeeCompletedCourseFolder->count() == 0) {

                                            $isCertificate = EmployeeCertificateModel::where([
                                                'course_id' => $course_folder_id,
                                                'employee_id' => $value->id,
                                            ])->orderBy('id', 'desc')->first();
                                        }
                                    }
                                }
                            }
                        }

                        $ecvalue->cerificate_expiration_date = "";
                        $ecvalue->employee_certificate_id = "";
                        $ecvalue->is_food_certifcate = "";
                        $ecvalue->food_safe_certificate_url = "";
                        $ecvalue->is_coursefolder = "";
                        if ($isCertificate != NULL) {
                            $ecvalue->cerificate_expiration_date = $isCertificate->certificate_expiration_date;
                            $ecvalue->employee_certificate_id = $isCertificate->id;
                            $ecvalue->is_food_certifcate = $isCertificate->is_proctored_exam;
                            $ecvalue->food_safe_certificate_url = $isCertificate->certificate_url;
                            $ecvalue->is_coursefolder = $isCertificate->is_coursefolder_certificate;
                        }
                    }
                    $employees[$key]['courses'] = $employeeCourses;
                }

                $data['employee'] = $employees;
                $data['total'] = $total;

                return response()->json($data, 200);
            }
        }

    }

    public function allList(Request $request) {
        if ($request->company_id) {
            $company_id = $request->company_id;
        }
        if ($request->role == "super-admin") {
            $employee['admin'] = EmployeeModel::join('tbl_employee_company_locations', 'tbl_employee.id', '=', 'tbl_employee_company_locations.employee_id')->where('company_id', $company_id)->where('tbl_employee.role_id', 2)->groupBy('tbl_employee_company_locations.employee_id')->get();
            $employee['manager'] = EmployeeModel::join('tbl_employee_company_locations', 'tbl_employee.id', '=', 'tbl_employee_company_locations.employee_id')->where('company_id', $company_id)->where('tbl_employee.role_id', 3)->groupBy('tbl_employee_company_locations.employee_id')->get();
        } else {
            $employee['admin'] = EmployeeModel::join('tbl_employee_company_locations', 'tbl_employee.id', '=', 'tbl_employee_company_locations.employee_id')->where('company_id', $company_id)->where('tbl_employee.role_id', 2)->groupBy('tbl_employee_company_locations.employee_id')->get();
            $employee['manager'] = EmployeeModel::join('tbl_employee_company_locations', 'tbl_employee.id', '=', 'tbl_employee_company_locations.employee_id')->where('company_id', $company_id)->where('tbl_employee.role_id', 3)->groupBy('tbl_employee_company_locations.employee_id')->get();
        }

        return response()->json($employee, 200);
    }

    public function updateCourseBACKUP(Request $request) {

        try {
            $completed_date = $request->completed_date;
            $course_status = $request->status;
            $course_id = $request->course_id;
            $employee_id = $request->employee_id;

            $certificate_expiration_date = $request->expiration_date;
            $employee = EmployeeModel::where('id', $request->employee_id)->first();
            if ($employee == NULL) {
                return response()->json(['Employee' => 'Invalid id no employee found.!'], 422);
            }
            $response = array();
            $userDueDays = CourseModel::where('id', $course_id)->select('employees_days_to_complete', 'managers_days_to_complete', 'reassignment_expiry', 'expiry_attempts')->first();
            switch ($request->type) {
                case 'date_assigned':
                    $courseexpire = EmployeeCoursesModel::leftjoin('tbl_employee', 'tbl_employee.id', '=', 'tbl_employee_courses.employee_id')->where('employee_id', $employee_id)->where('course_id', $course_id)->first();
                    $dueDays = 0;
                    if ($userDueDays != NULL) {
                        $getReassignmentExpiryStatus = $userDueDays->reassignment_expiry;
                        $getReassignmentExpiryAttempts = $userDueDays->expiry_attempts;
                        $getEmployeesDaystoComplete = $userDueDays->employees_days_to_complete;
                        $getManagersDaystoComplete = $userDueDays->managers_days_to_complete;
                    }
                    $defaultDueDate = "";
                    if ($courseexpire->role_id == 4) {
                        $defaultDueDate = Carbon::now('UTC')->addDays($getEmployeesDaystoComplete)->format('Y-m-d');
                    } else {

                        $defaultDueDate = Carbon::now('UTC')->addDays($getManagersDaystoComplete)->format('Y-m-d');
                    }
                    $dateAssigned = Carbon::parse($request->date_assigned)->format('Y-m-d');
                    $dueDate = Carbon::parse($request->due_date)->format('Y-m-d');
                    if ($dueDate < date('Y-m-d')) {
                        if ($getReassignmentExpiryStatus && $getReassignmentExpiryAttempts != NULL && $getReassignmentExpiryAttempts > 0) {
                            if ($courseexpire->reassignment_expiry_attempts != $getReassignmentExpiryAttempts && $courseexpire->reassignment_expiry_attempts < $getReassignmentExpiryAttempts) {
                                $reassignment_expiry_attempt = ($courseexpire->reassignment_expiry_attempts + 1);
                                EmployeeCoursesModel::where([
                                    'employee_id' => $employee_id,
                                    'course_id' => $course_id,
                                ])->update([
                                    'employee_course_date_assigned' => date('Y-m-d'),
                                    'employee_course_due_date' => $defaultDueDate,
                                    'employee_course_status' => 2,
                                    'reassignment_expiry_attempts' => $reassignment_expiry_attempt,
                                ]);
                            } else {
                                EmployeeCoursesModel::where([
                                    'employee_id' => $employee_id,
                                    'course_id' => $course_id,
                                ])->update([
                                    'employee_course_date_assigned' => $dateAssigned,
                                    'employee_course_due_date' => $dueDate,
                                    'employee_course_status' => 3,
                                ]);
                                PayByEmployeeHistoryModel::where([
                                    'employee_id' => $employee_id,
                                    'course_id' => $course_id,
                                    'accessible' => 1,
                                ])->update([
                                    'accessible' => 0,
                                    'accessibility_change_on' => date('Y-m-d'),
                                ]);
                            }

                        } else {
                            EmployeeCoursesModel::where([
                                'employee_id' => $employee_id,
                                'course_id' => $course_id,
                            ])->update([
                                'employee_course_date_assigned' => $dateAssigned,
                                'employee_course_due_date' => $dueDate,
                                'employee_course_status' => 3,
                            ]);
                            PayByEmployeeHistoryModel::where([
                                'employee_id' => $employee_id,
                                'course_id' => $course_id,
                                'accessible' => 1,
                            ])->update([
                                'accessible' => 0,
                                'accessibility_change_on' => date('Y-m-d'),
                            ]);
                        }
                    } else {
                        EmployeeCoursesModel::where([
                            'employee_id' => $employee_id,
                            'course_id' => $course_id,
                        ])->update([
                            'employee_course_date_assigned' => $dateAssigned,
                            'employee_course_due_date' => $dueDate,
                            'employee_course_status' => 2,
                        ]);
                    }
                    $response = ['message' => 'Employee course assign date updated successfully.'];
                    break;
                case 'manually':
                    if ($request->status == $this->status['pass']) {
                        $fileName = NULL;
                        $certificate_name = '';
                        $manualStatus = 0;
                        if (!empty($request->file) && $request->file != "undefined") {
                            $fileName = time() . '.' . $request->file->getClientOriginalExtension();
                            $request->file->move(public_path('employee/certificate_manual/'), $fileName);
                            $certificate_name = 'Manual';
                            $manualStatus = 1;
                        } else {
                            $isCeretificate = CourseCertificatesModel::
                            leftJoin('tbl_certificate', 'tbl_certificate.id', '=', 'tbl_course_certificate.certificate_id')->where('tbl_course_certificate.course_id', $course_id)->select('tbl_certificate.name')->first();
                            if ($isCeretificate != NULL) {

                                $certificate_name = $isCeretificate->name;
                            }
                        }
                        //$certificate_name =
                        $certificateComplitionDate = Carbon::now('UTC')->format('Y-m-d');
                        if (!empty($request->completed_date)) {
                            $certificateComplitionDate = $request->completed_date;
                        }
                        // $dueDays = 0;
                        // if ($userDueDays != null) {
                        //     $dueDays = (int)$userDueDays->employees_days_to_complete;
                        //     if ($employee->role_id == 3) {
                        //         $dueDays = (int)$userDueDays->managers_days_to_complete;
                        //     }
                        // }
                        $course_data = CourseModel::where('id', $course_id)->first();
                        $expiration_due = $course_data->certificate_validity;
                        $allData = $request->all();
                        if (isset($allData['expiration_date']) && $allData['expiration_date'] != '') {
                            $certificate_expiration_date = Carbon::parse($allData['expiration_date'])->format('Y-m-d');
                        } else {
                            $certificate_expiration_date = Carbon::parse($certificateComplitionDate)->addDays($expiration_due)->format('Y-m-d');
                        }
                        $certificate = new EmployeeCertificateModel();
                        $certificate->certificate_no = 0;
                        $certificate->certificate_name = 'manual';
                        $certificate->course_id = $course_id;
                        $certificate->employee_id = $employee_id;
                        $certificate->certificate_date = $certificateComplitionDate;
                        $certificate->certificate_expiration_date = $certificate_expiration_date;
                        $certificate->certificate_url = $fileName;
                        $certificate->manual = $manualStatus;
                        $certificate->created_at = Carbon::now('UTC');
                        $certificate->save();
                        $certificate_no = CourseTrait::getCertificateId($certificate->id);
                        $certificate->certificate_no = $certificate_no;
                        $certificate->save();
                        if ($certificateComplitionDate != '') {
                            $historyId = EmployeeCourseHistoryModel::insertGetId([
                                'employee_id' => $employee_id,
                                'course_id' => $course_id,
                                'attempt_date' => $certificateComplitionDate,
                                'attempt_status' => $course_status,
                                'certificate_id' => $certificate->id,
                                'is_manual' => '1',
                                'created_at' => Carbon::now('UTC'),
                            ]);
                            EmployeeCoursesModel::where('employee_id', $employee_id)->where('course_id', $course_id)->update([
                                'employee_course_status' => $course_status,
                                'employee_course_date_completed' => $certificateComplitionDate,
                            ]);
                        } else {
                            $historyId = EmployeeCourseHistoryModel::insertGetId([
                                'employee_id' => $employee_id,
                                'course_id' => $course_id,
                                'attempt_date' => $certificateComplitionDate,
                                'attempt_status' => $course_status,
                                'is_manual' => '1',
                                'created_at' => Carbon::now('UTC'),
                            ]);
                            EmployeeCoursesModel::where('employee_id', $employee_id)->where('course_id', $course_id)->update(['employee_course_status' => $course_status]);
                        }
                    } else if ($request->status == $this->status['fail']) {
                        $historyId = EmployeeCourseHistoryModel::insertGetId([
                            'employee_id' => $employee_id,
                            'course_id' => $course_id,
                            'attempt_date' => Carbon::now('UTC')->format('Y-m-d'),
                            'attempt_status' => $request->status,
                            'is_manual' => '1',
                            'created_at' => Carbon::now('UTC'),
                        ]);
                        EmployeeCoursesModel::where([
                            'employee_id' => $employee_id,
                            'course_id' => $course_id,
                        ])->update(['employee_course_status' => $request->status]);
                        PayByEmployeeHistoryModel::where([
                            'employee_id' => $employee_id,
                            'course_id' => $course_id,
                            'accessible' => 1,
                        ])->update([
                            'accessible' => 0,
                            'accessibility_change_on' => date('Y-m-d'),
                        ]);
                    }

                    $response = ['message' => 'Employee course updated manually successfully.'];
                    break;

                default:
                    $response = ['message' => 'Employee course did not update, try again.'];
                    break;
            }

            return response()->json($response, 200);
        } catch (Exception $th) {

            return response()->json(["message" => $th->getMessage()], 422);
        }
    }

    public function updateCourse(Request $request) {

        try {
            $completed_date = $request->completed_date;
            $course_status = $request->status;
            $course_id = $request->course_id;
            $employee_id = $request->employee_id;

            $certificate_expiration_date = $request->expiration_date;
            $employee = EmployeeModel::where('id', $request->employee_id)->first();
            if ($employee == NULL) {
                return response()->json(['Employee' => 'Invalid id no employee found.!'], 422);
            }
            $response = array();
            $userDueDays = CourseModel::where('id', $course_id)->select('employees_days_to_complete', 'managers_days_to_complete', 'reassignment_expiry', 'expiry_attempts')->first();
            switch ($request->type) {
                case 'date_assigned':
                    $courseexpire = EmployeeCoursesModel::leftjoin('tbl_employee', 'tbl_employee.id', '=', 'tbl_employee_courses.employee_id')->where('employee_id', $employee_id)->where('course_id', $course_id)->first();
                    $dueDays = 0;
                    if ($userDueDays != NULL) {
                        $getReassignmentExpiryStatus = $userDueDays->reassignment_expiry;
                        $getReassignmentExpiryAttempts = $userDueDays->expiry_attempts;
                        $getEmployeesDaystoComplete = $userDueDays->employees_days_to_complete;
                        $getManagersDaystoComplete = $userDueDays->managers_days_to_complete;
                    }
                    $defaultDueDate = "";
                    if ($courseexpire->role_id == 4) {
                        $defaultDueDate = Carbon::now('UTC')->addDays($getEmployeesDaystoComplete)->format('Y-m-d');
                    } else {

                        $defaultDueDate = Carbon::now('UTC')->addDays($getManagersDaystoComplete)->format('Y-m-d');
                    }
                    $dateAssigned = Carbon::parse($request->date_assigned)->format('Y-m-d');
                    $dueDate = Carbon::parse($request->due_date)->format('Y-m-d');
                    if ($dueDate < date('Y-m-d')) {
                        if ($getReassignmentExpiryStatus && $getReassignmentExpiryAttempts != NULL && $getReassignmentExpiryAttempts > 0) {
                            if ($courseexpire->reassignment_expiry_attempts != $getReassignmentExpiryAttempts && $courseexpire->reassignment_expiry_attempts < $getReassignmentExpiryAttempts) {
                                $reassignment_expiry_attempt = ($courseexpire->reassignment_expiry_attempts + 1);
                                EmployeeCoursesModel::where([
                                    'employee_id' => $employee_id,
                                    'course_id' => $course_id,
                                ])->update([
                                    'employee_course_date_assigned' => date('Y-m-d'),
                                    'employee_course_due_date' => $defaultDueDate,
                                    'employee_course_status' => 2,
                                    'reassignment_expiry_attempts' => $reassignment_expiry_attempt,
                                ]);
                            } else {
                                EmployeeCoursesModel::where([
                                    'employee_id' => $employee_id,
                                    'course_id' => $course_id,
                                ])->update([
                                    'employee_course_date_assigned' => $dateAssigned,
                                    'employee_course_due_date' => $dueDate,
                                    'employee_course_status' => 3,
                                ]);
                                PayByEmployeeHistoryModel::where([
                                    'employee_id' => $employee_id,
                                    'course_id' => $course_id,
                                    'accessible' => 1,
                                ])->update([
                                    'accessible' => 0,
                                    'accessibility_change_on' => date('Y-m-d'),
                                ]);
                            }
                        } else {
                            EmployeeCoursesModel::where([
                                'employee_id' => $employee_id,
                                'course_id' => $course_id,
                            ])->update([
                                'employee_course_date_assigned' => $dateAssigned,
                                'employee_course_due_date' => $dueDate,
                                'employee_course_status' => 3,
                            ]);

                            PayByEmployeeHistoryModel::where([
                                'employee_id' => $employee_id,
                                'course_id' => $course_id,
                                'accessible' => 1,
                            ])->update([
                                'accessible' => 0,
                                'accessibility_change_on' => date('Y-m-d'),
                            ]);
                        }
                    } else {
                        EmployeeCoursesModel::where([
                            'employee_id' => $employee_id,
                            'course_id' => $course_id,
                        ])->update([
                            'employee_course_date_assigned' => $dateAssigned,
                            'employee_course_due_date' => $dueDate,
                            'employee_course_status' => 2,
                        ]);
                    }
                    $response = ['message' => 'Employee course assign date updated successfully.'];
                    break;
                case 'manually':
                    if ($request->status == $this->status['pass']) {
                        $fileName = NULL;
                        $certificate_name = '';
                        $manualStatus = 0;
                        log::debug($course_id);
                        $courseExistInCourseFolders = CourseFolderAssignModel::select('tbl_employee_coursefolders.folder_id as course_folder_id')->leftjoin('tbl_employee_coursefolders', 'tbl_employee_coursefolders.folder_id', '=', 'tbl_course_coursefolder.course_folder_id')->where('course_id', $course_id)->where('employee_id', $employee_id)->groupby('tbl_employee_coursefolders.folder_id')->get();

                        if (count($courseExistInCourseFolders) > 0) {
                            log::debug("exist");
                            EmployeeCoursesModel::where('employee_id', $employee_id)->where('course_id', $course_id)->update([
                                'employee_course_status' => $course_status,
                                'employee_course_date_completed' => $request->completed_date,
                            ]);
                            foreach ($courseExistInCourseFolders as $courseExistInCourseFolder) {

                                if ($courseExistInCourseFolder) {

                                    $course_folder_id = $courseExistInCourseFolder->course_folder_id;

                                    $getAllCourses = CourseFolderAssignModel::where('course_folder_id', $course_folder_id)->get();
                                    $courses = array();
                                    foreach ($getAllCourses as $course_id_test) {
                                        array_push($courses, $course_id_test->course_id);
                                    }


                                    $checkIfEmployeeAssignedCourses = EmployeeCoursesModel::whereIn('course_id', $courses)->where('employee_id', $employee_id)->get();

                                    if (count($checkIfEmployeeAssignedCourses) == count($courses)) {

                                        $checkIfEmployeeCompletedCourseFolder = EmployeeCoursesModel::whereIn('course_id', $courses)->where('employee_id', $employee_id)->where('employee_course_status', '!=', '1');

                                        if ($checkIfEmployeeCompletedCourseFolder->count() == 0) {

                                            $testCourse = CourseFolderModel::rightJoin('tbl_course_folder_certificate', 'tbl_course_folder_certificate.folder_id', '=', 'tbl_course_folder.id')->leftJoin('tbl_certificate', 'tbl_certificate.id', '=', 'tbl_course_folder_certificate.certificate_id')->select('tbl_certificate.name as certificate_name', 'tbl_course_folder_certificate.expiry')->where('tbl_course_folder.id', $course_folder_id)->first();
                                            if ($testCourse) {
                                                if ($testCourse->expiry) {
                                                    $availableCertificateDays = (int)$testCourse->expiry;
                                                } else {
                                                    $availableCertificateDays = 0;
                                                }
                                                $certificateId = EmployeeCertificateModel::insertGetId([
                                                    'certificate_no' => '0',
                                                    'certificate_name' => $testCourse->certificate_name,
                                                    'course_id' => $course_folder_id,
                                                    'manual' => 1,
                                                    'employee_id' => $employee_id,
                                                    'certificate_date' => Carbon::now('UTC')->format('Y-m-d'),
                                                    'is_coursefolder_certificate' => 1,
                                                    'certificate_expiration_date' => Carbon::now('UTC')->addDays($availableCertificateDays)->format('Y-m-d'),
                                                    'created_at' => Carbon::now('UTC'),
                                                ]);
                                                $certificateNo = '2020-100' . $certificateId;
                                                EmployeeCertificateModel::where('id', $certificateId)->update(['certificate_no' => $certificateNo]);

                                            }
                                        }
                                    }
                                }
                            }
                        } else {
                            log::debug("manual course pass");
                            log::debug($course_id);
                            if (!empty($request->file) && $request->file != "undefined") {
                                log::debug("upload file");
                                $fileName = time() . '.' . $request->file->getClientOriginalExtension();
                                $request->file->move(public_path('employee/certificate_manual/'), $fileName);
                                $certificate_name = 'Manual';
                                $manualStatus = 1;
                            } else {

                                $isCeretificate = CourseCertificatesModel::
                                leftJoin('tbl_certificate', 'tbl_certificate.id', '=', 'tbl_course_certificate.certificate_id')->where('tbl_course_certificate.course_id', $course_id)->select('tbl_certificate.name')->first();
                                if ($isCeretificate != NULL) {
                                    log::debug("certificate availbale");
                                    $certificate_name = $isCeretificate->name;
                                }
                            }

                            $certificateComplitionDate = Carbon::now('UTC')->format('Y-m-d');
                            if (!empty($request->completed_date)) {
                                $certificateComplitionDate = $request->completed_date;
                            }

                            $course_data = CourseModel::where('id', $course_id)->first();
                            $expiration_due = $course_data->certificate_validity;
                            $allData = $request->all();
                            if (isset($allData['expiration_date']) && $allData['expiration_date'] != '') {
                                $certificate_expiration_date = Carbon::parse($allData['expiration_date'])->format('Y-m-d');
                            } else {
                                $certificate_expiration_date = Carbon::parse($certificateComplitionDate)->addDays($expiration_due)->format('Y-m-d');
                            }
                            log::debug("generate certifcate");
                            $certificate = new EmployeeCertificateModel();
                            $certificate->certificate_no = 0;
                            $certificate->certificate_name = 'manual';
                            $certificate->course_id = $course_id;
                            $certificate->employee_id = $employee_id;
                            $certificate->certificate_date = $certificateComplitionDate;
                            $certificate->certificate_expiration_date = $certificate_expiration_date;
                            $certificate->certificate_url = $fileName;
                            $certificate->manual = $manualStatus;
                            $certificate->created_at = Carbon::now('UTC');
                            $certificate->save();
                            $certificate_no = CourseTrait::getCertificateId($certificate->id);
                            $certificate->certificate_no = $certificate_no;
                            $certificate->save();

                            if ($certificateComplitionDate != '') {
                                $historyId = EmployeeCourseHistoryModel::insertGetId([
                                    'employee_id' => $employee_id,
                                    'course_id' => $course_id,
                                    'attempt_date' => Carbon::now('UTC')->format('Y-m-d'),
                                    'attempt_status' => $course_status,
                                    'certificate_id' => $certificate->id,
                                    'is_manual' => '1',
                                    'created_at' => Carbon::now('UTC'),
                                ]);
                                EmployeeCoursesModel::where('employee_id', $employee_id)->where('course_id', $course_id)->update([
                                    'employee_course_status' => $course_status,
                                    'employee_course_date_completed' => $certificateComplitionDate,
                                ]);
                            } else {
                                $historyId = EmployeeCourseHistoryModel::insertGetId([
                                    'employee_id' => $employee_id,
                                    'course_id' => $course_id,
                                    'attempt_date' => Carbon::now('UTC')->format('Y-m-d'),
                                    'attempt_status' => $course_status,
                                    'is_manual' => '1',
                                    'created_at' => Carbon::now('UTC'),
                                ]);
                                EmployeeCoursesModel::where('employee_id', $employee_id)->where('course_id', $course_id)->update(['employee_course_status' => $course_status]);
                            }
                        }
                    } else if ($request->status == $this->status['fail']) {
                        $historyId = EmployeeCourseHistoryModel::insertGetId([
                            'employee_id' => $employee_id,
                            'course_id' => $course_id,
                            'attempt_date' => Carbon::now('UTC')->format('Y-m-d'),
                            'attempt_status' => $request->status,
                            'is_manual' => '1',
                            'created_at' => Carbon::now('UTC'),
                        ]);
                        EmployeeCoursesModel::where([
                            'employee_id' => $employee_id,
                            'course_id' => $course_id,
                        ])->update(['employee_course_status' => $request->status]);
                        PayByEmployeeHistoryModel::where([
                            'employee_id' => $employee_id,
                            'course_id' => $course_id,
                            'accessible' => 1,
                        ])->update([
                            'accessible' => 0,
                            'accessibility_change_on' => date('Y-m-d'),
                        ]);
                    }

                    $response = ['message' => 'Employee course updated manually successfully.'];
                    break;

                default:
                    $response = ['message' => 'Employee course did not update, try again.'];
                    break;
            }

            return response()->json($response, 200);
        } catch (Exception $th) {

            return response()->json(["message" => $th->getMessage()], 422);
        }
    }

    public function updateCourseManually(Request $request) {

        $employess = explode(',', $request->employee_ids);
        $completed_date = $request->completed_date;
        $course_status = $request->status;
        $course_id = $request->course_id;
        $certificate_expiration_date = $request->expiration_date;
        foreach ($employess as $employee_id) {
            if ($request->status == $this->status['pass']) {
                $fileName = NULL;
                $certificate_name = '';
                $manualStatus = 0;
                if (!empty($request->file) && $request->file != "undefined") {
                    $fileName = time() . '.' . $request->file->getClientOriginalExtension();
                    $request->file->move(public_path('employee/certificate_manual/'), $fileName);
                    $certificate_name = 'Manual';
                    $manualStatus = 1;
                } else {
                    $isCeretificate = CourseCertificatesModel::
                    leftJoin('tbl_certificate', 'tbl_certificate.id', '=', 'tbl_course_certificate.certificate_id')->where('tbl_course_certificate.course_id', $course_id)->select('tbl_certificate.name')->first();
                    if ($isCeretificate != NULL) {

                        $certificate_name = $isCeretificate->name;
                    }
                }
                $certificateComplitionDate = Carbon::now('UTC')->format('Y-m-d');
                if (!empty($request->completed_date)) {
                    $certificateComplitionDate = $request->completed_date;
                }
                $course_data = CourseModel::where('id', $course_id)->first();
                $expiration_due = $course_data->certificate_validity;
                $allData = $request->all();
                if (isset($allData['expiration_date']) && $allData['expiration_date'] != '') {
                    $certificate_expiration_date = Carbon::parse($allData['expiration_date'])->format('Y-m-d');
                } else {
                    $certificate_expiration_date = Carbon::parse($certificateComplitionDate)->addDays($expiration_due)->format('Y-m-d');
                }
                $certificate = new EmployeeCertificateModel();
                $certificate->certificate_no = 0;
                $certificate->certificate_name = 'manual';
                $certificate->course_id = $course_id;
                $certificate->employee_id = $employee_id;
                $certificate->certificate_date = $certificateComplitionDate;
                $certificate->certificate_expiration_date = $certificate_expiration_date;
                $certificate->certificate_url = $fileName;
                $certificate->manual = $manualStatus;
                $certificate->created_at = Carbon::now('UTC');
                $certificate->save();
                $certificate_no = CourseTrait::getCertificateId($certificate->id);
                $certificate->certificate_no = $certificate_no;
                $certificate->save();
                if ($certificateComplitionDate != '') {
                    $historyId = EmployeeCourseHistoryModel::insertGetId([
                        'employee_id' => $employee_id,
                        'course_id' => $course_id,
                        'attempt_date' => $certificateComplitionDate,
                        'attempt_status' => $course_status,
                        'certificate_id' => $certificate->id,
                        'is_manual' => '1',
                        'created_at' => Carbon::now('UTC'),
                    ]);
                    EmployeeCoursesModel::where('employee_id', $employee_id)->where('course_id', $course_id)->update([
                        'employee_course_status' => $course_status,
                        'employee_course_date_completed' => $certificateComplitionDate,
                    ]);
                } else {
                    $historyId = EmployeeCourseHistoryModel::insertGetId([
                        'employee_id' => $employee_id,
                        'course_id' => $course_id,
                        'attempt_date' => $certificateComplitionDate,
                        'attempt_status' => $course_status,
                        'is_manual' => '1',
                        'created_at' => Carbon::now('UTC'),
                    ]);
                    EmployeeCoursesModel::where('employee_id', $employee_id)->where('course_id', $course_id)->update(['employee_course_status' => $course_status]);
                }
            } else if ($request->status == $this->status['fail']) {
                $historyId = EmployeeCourseHistoryModel::insertGetId([
                    'employee_id' => $employee_id,
                    'course_id' => $course_id,
                    'attempt_date' => Carbon::now('UTC')->format('Y-m-d'),
                    'attempt_status' => $request->status,
                    'is_manual' => '1',
                    'created_at' => Carbon::now('UTC'),
                ]);
                EmployeeCoursesModel::where([
                    'employee_id' => $employee_id,
                    'course_id' => $course_id,
                ])->update(['employee_course_status' => $request->status]);
                PayByEmployeeHistoryModel::where([
                    'employee_id' => $employee_id,
                    'course_id' => $course_id,
                    'accessible' => 1,
                ])->update([
                    'accessible' => 0,
                    'accessibility_change_on' => date('Y-m-d'),
                ]);
            }
        }

        $response = ['message' => 'Employee course updated manually successfully.'];

    }

    public function certificates(Request $request) {
        try {
            $id = $request->employee_id;
            $employee = EmployeeModel::find($id);
            if (empty($employee)) {
                return response()->json(['Employee' => 'Invalid id no employee found.!'], 422);
            }
            $data = [];
            $employee_certificates = EmployeeCertificateModel::select('tbl_employee_certificates.*')->leftjoin('tbl_course_certificate', 'tbl_course_certificate.course_id', '=', 'tbl_employee_certificates.course_id')->leftjoin('tbl_course_folder_certificate', 'tbl_course_folder_certificate.folder_id', '=', 'tbl_employee_certificates.course_id')->with('course', 'coursefolder')->where('employee_id', $id)->where('manual', '0')->whereDate('certificate_expiration_date', '>=', date("Y-m-d"))->orderBy('tbl_employee_certificates.created_at', 'DESC')->get();
            $total = EmployeeCertificateModel::where('employee_id', $id)->count();
            $data['data'] = $employee_certificates;
            $employee_certificates_expired = EmployeeCertificateModel::select('tbl_employee_certificates.*')->leftjoin('tbl_course_certificate', 'tbl_course_certificate.course_id', '=', 'tbl_employee_certificates.course_id')->leftjoin('tbl_course_folder_certificate', 'tbl_course_folder_certificate.folder_id', '=', 'tbl_employee_certificates.course_id')->with('course', 'coursefolder')->where('employee_id', $id)->where('manual', '0')->whereDate('certificate_expiration_date', '<', date("Y-m-d"))->orderBy('tbl_employee_certificates.created_at', 'DESC')->get();
            $total_expired = EmployeeCertificateModel::join('tbl_course_certificate', 'tbl_course_certificate.course_id', '=', 'tbl_employee_certificates.course_id')->where('employee_id', $id)->count();
            $data['data_expired'] = $employee_certificates_expired;
            $data['total'] = $total;
            $data['total_expired'] = $total_expired;

            return response()->json($data, 200);
        } catch (Exception $th) {

            return response()->json(['message' => $th->getMessage()], 200);
        }
    }

    public function additionalCertificates(Request $request) {
        $id = $request->employee_id;
        $employee = EmployeeModel::find($id);
        if (empty($employee)) {
            return response()->json(['Employee' => 'Invalid id no employee found.!'], 422);
        }
        $data = [];
        $employee_certificates = EmployeeCertificateModel::with('course')->where('employee_id', $id)->where('manual', '1')->orderBy('id', 'DESC')->get();
        $pdf_url = url() . '/employee/certificate_manual/';
        if ($employee_certificates->count() > 0) {
            foreach ($employee_certificates as $certificateValue) {
                if ($certificateValue->certificate_url != '') {
                    //   if (is_file('/employee/certificates/'.$certificateValue->certificate_url)) {
                    $certificateValue->certificate_url = $pdf_url . $certificateValue->certificate_url;
                    //  }
                }
            }
        }
        $total = EmployeeCertificateModel::where('employee_id', $id)->where('manual', '1')->count();
        $data['data'] = $employee_certificates;
        $data['total'] = $total;

        return response()->json($data, 200);
    }

    public function getCertificates($id) {
        try {
            $employee = EmployeeModel::find($id);
            if (empty($employee)) {
                return response()->json(['Employee' => 'Invalid id no employee found.!'], 422);
            }
            $data = EmployeeModel::with('certificates')->where('id', $id)->orderBy('created_at', 'DESC')->get();
            $pdf_url = url() . '/employee/certificates/';
            foreach ($data as $value) {
                foreach ($value->certificates as $certificateValue) {
                    if ($certificateValue->certificate_url != '') {
                        $certificateValue->certificate_url = $pdf_url . $certificateValue->certificate_url;
                    }
                }
            }

            return response()->json($data, 200);

        } catch (Exception $th) {

            return response()->json(['message' => $th->getMessage()], 200);
        }
    }

    public function uploadCertificate(Request $request) {
        $certificate = new EmployeeCertificateModel();
        $file = $request->certificate;
        $extension = $file->extension();
        $filename = rand() . '.' . $extension;
        $file->move('employee/certificates/', $filename);
        $picture_url = URL::to('employee/certificates/') . '/' . $filename;
        if (!empty($request->certificate_name)) {
            $certificate->certificate_name = $request->certificate_name;
        }
        $certificate->course_id = $request->course_id;
        $certificate->employee_id = $request->employee_id;
        $certificate->certificate_date = $request->certificate_date;
        $certificate->certificate_expiration_date = $request->certificate_expiration_date;
        $certificate->certificate_url = $picture_url;
        $certificate->save();

        return response()->json($certificate, 200);
    }

    public function uploadDocument(Request $request) {
        $title = $request->title;
        $type = $request->type;
        $description = $request->description;
        $url = "";
        if ($type == 'link') {
            $url = $request->url;
        }
        $fileName = "";
        if ($type == 'file') {

            $fileName = time() . '.' . $request->file->getClientOriginalExtension();
            $request->file->move(public_path('employee/documents/'), $fileName);
        }
        /*if(!empty($request->image)){
        $image=$request->image;
        $name = time().'.' . explode('/', explode(':', substr($image, 0, strpos($image, ';')))[1])[1];
        Image::make($request->image)->save(public_path('employee/documents/').$name);
        } */
        $document = new EmployeeDocumentModel();
        $document->employee_id = $request->user_id;
        $document->title = $title;
        $document->type = $type;
        $document->description = $description;
        $document->url = $url;
        $document->document = $fileName;
        $document->save();

        return response()->json('Document Uploaded Sucessfully..', 200);
    }

    public function employeeDocuments(Request $request) {

        $user_id = $request->employee_id;
        $documents = EmployeeDocumentModel::where('employee_id', $user_id)->get();

        return response()->json($documents, 200);
    }

    public function deleteDocument(Request $request) {
        $employee_id = $request->employee_id;
        $document_id = $request->document_id;

        return EmployeeDocumentModel::where('employee_id', $employee_id)->where('id', $document_id)->delete();

    }

    public function courseDue(Request $request) {
        $passed = "1";
        $where_data = [];
        $user = Auth::user();
        $where_data_manager = [];
        if ($user->role_id == 3) {

            $where_data_manager[] = [
                'tbl_employee.role_id',
                '!=',
                '2',
            ];
        }
        // $companyIds = CompanyModel::getAllCompanyIdsOfUsersAccoringUserRole($user->id, $user->role_id);
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

        } else {
            $companyIds = $companies['isLocations'];
            $where_dataa = "tbl_employee_company_locations.location_id";
        }

        if ($request->has('company_id')) {
            $company = CompanyModel::find($request->company_id);
            if ($company->parent_id == 0) {
                $where_dataa = 'tbl_employee_company_locations.company_id';
            } else {
                $where_dataa = 'tbl_employee_company_locations.location_id';
            }
            $companyIds = [$request->company_id];
        }

        $today = date('Y-m-d');
        $next_date = date('Y-m-d', strtotime('+7 days'));
        $data = [];
        $data1 = [];

        $employees = EmployeeCoursesModel::leftJoin('tbl_company', 'tbl_company.id', '=', 'tbl_employee_courses.company_id')->leftJoin('tbl_employee', 'tbl_employee.id', '=', 'tbl_employee_courses.employee_id')->leftjoin('tbl_employee_company_locations', 'tbl_employee_company_locations.employee_id', '=', 'tbl_employee_courses.employee_id')->select('tbl_employee_courses.employee_id', 'tbl_company.name as company_name')->where('tbl_employee.status', 1)->where($where_data_manager)->whereIn($where_dataa, $companyIds)->where('employee_course_status', '!=', 1)->whereBetween('employee_course_due_date', [
            $today,
            $next_date,
        ])->groupBy('employee_id')->get();

        foreach ($employees as $employee) {
            $id = $employee->employee_id;
            $employee_info = EmployeeModel::with('courses_due', 'location')->withCount('course_pass', 'course_fail', 'course_open', 'course_expired')->where('id', $id)->get();


            if (!empty($employee_info[0])) {
                for ($i = 0; $i < count($employee_info); $i++) {
                    $employee_id = $employee_info[$i]->id;
                    $employee_info[$i]['company_name'] = $employee->company_name;
                    $courses = $employee_info[$i]->courses_due;
                    for ($j = 0; $j < count($courses); $j++) {
                        if (!empty($courses[$j])) {
                            $course_id = $courses[$j]->id;
                            $course_info = EmployeeCoursesModel::where([
                                [
                                    'employee_id',
                                    $employee_id,
                                ],
                                [
                                    'course_id',
                                    $course_id,
                                ],
                            ])->whereBetween('employee_course_due_date', [
                                $today,
                                $next_date,
                            ])->get();
                            $employee_info[$i]->courses_due[$j]->employee_course_due_date = $course_info[0]->employee_course_due_date;
                            $employee_info[$i]->courses_due[$j]->employee_course_status = $course_info[0]->employee_course_status;
                            $employee_info[$i]->courses_due[$j]->employee_course_date_assigned = $course_info[0]->employee_course_date_assigned;
                            $employee_info[$i]->courses_due[$j]->employee_course_date_completed = $course_info[0]->employee_course_date_completed;
                        }
                    }
                }
                $data1[] = $employee_info[0];
            }
        }
        $total = EmployeeCoursesModel::select('tbl_employee_courses.employee_id')->leftjoin('tbl_employee_company_locations', 'tbl_employee_company_locations.employee_id', '=', 'tbl_employee_courses.employee_id')->whereIn($where_dataa, $companyIds)->whereBetween('employee_course_due_date', [
            $today,
            $next_date,
        ])->groupBy('employee_id')->get();
        $total = count($total);
        $data['total'] = $total;
        $data['employee'] = $data1;

        return $data;

    }

    public function nonComplianceEmployee(Request $request) {
        try {
            $user = Auth::User();
            $where_data_manager = [];
            if ($user->role_id == 3) {

                $where_data_manager[] = [
                    'tbl_employee.role_id',
                    '!=',
                    '2',
                ];
            }
            //$companyIds = CompanyModel::getAllCompanyIdsOfUsersAccoringUserRole($user->id, $user->role_id);
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
                $where_dataa = "tbl_employee_courses.company_id";

            } else {
                $company_id = $companies['isLocations'];
                $where_dataa = "tbl_employee_courses.company_id";
            }


            if ($request->has('company_id')) {
                $company = CompanyModel::find($request->company_id);
                if ($company->parent_id == 0) {
                    $where_dataa = 'tbl_employee_courses.company_id';
                } else {
                    $where_dataa = 'tbl_employee_courses.company_id';
                }
                $company_id = [$request->company_id];
            }

            $where_data = [];
            $or_where_data = [];
            //0 for fail
            array_push($where_data, [
                'employee_course_status',
                '0',
            ]);
            array_push($or_where_data, [
                'employee_course_due_date',
                '<',
                date('Y-m-d'),
            ]);
            $data1 = [];
            $where_name = [];
            $search = $request->search;
            $search = explode(" ", $search);
            foreach ($search as $key => $name) {
                $where_name[] = [
                    'full_name',
                    'like',
                    '%' . $name . '%',
                ];
            }
            $employees = EmployeeCoursesModel::leftJoin('tbl_company', 'tbl_company.id', '=', 'tbl_employee_courses.company_id')->leftjoin('tbl_employee_company_locations', 'tbl_employee_company_locations.employee_id', '=', 'tbl_employee_courses.employee_id')->leftjoin('tbl_employee', 'tbl_employee.id', '=', 'tbl_employee_courses.employee_id')->whereIn($where_dataa, $company_id)->where('tbl_employee.status', 1)->where($where_data_manager)->where(function($query) {
                    $query->where('employee_course_status', '0');
                    $query->orWhere('employee_course_status', '3');
                })->groupBy('tbl_employee_courses.employee_id')->select('tbl_employee_courses.employee_id', 'tbl_company.name as company_name')->get();

            foreach ($employees as $employee) {
                $id = $employee->employee_id;
                $employee_info = EmployeeModel::with('courses', 'location')->withCount('course_pass', 'course_fail', 'course_open', 'course_expired')->where('id', $id)->where($where_name)->get();
                if (!empty($employee_info[0])) {
                    for ($i = 0; $i < count($employee_info); $i++) {
                        $employee_id = $employee_info[$i]->id;
                        $employee_info[$i]['company_name'] = $employee->company_name;
                        $courses = $employee_info[$i]->courses;
                        for ($j = 0; $j < count($courses); $j++) {
                            if (!empty($courses[$j])) {
                                $course_id = $courses[$j]->id;
                                $course_info = EmployeeCoursesModel::where([
                                    [
                                        'employee_id',
                                        $employee_id,
                                    ],
                                    [
                                        'course_id',
                                        $course_id,
                                    ],
                                ])->get();

                                $employee_info[$i]->courses[$j]->employee_course_due_date = $course_info[0]->employee_course_due_date;
                                $employee_info[$i]->courses[$j]->employee_course_status = $course_info[0]->employee_course_status;
                                $employee_info[$i]->courses[$j]->employee_course_date_assigned = $course_info[0]->employee_course_date_assigned;
                                $employee_info[$i]->courses[$j]->employee_course_date_completed = $course_info[0]->employee_course_date_completed;
                            }
                        }
                    }
                    $data1[] = $employee_info[0];
                }
            }

            $data['employee'] = $data1;

            return $data;

        } catch (Exception $th) {

            return reponse()->json(['message' => $th->getMessage()], 422);
        }


    }

    public function report(Request $request) {
        $company_id = $request->company_id;
        $where_data = [];
        $location = $request->location_id;
        array_push($where_data, [
            'location_id',
            $location,
        ]);
        array_push($where_data, [
            'company_id',
            $company_id,
        ]);

        $employee = EmployeeModel::with('location', 'courses')->withCount('course_pass', 'course_fail', 'course_open')->where($where_data)->orderBy('created_at', 'DESC')->get();
        for ($i = 0; $i < count($employee); $i++) {
            $employee_id = $employee[$i]->id;
            $courses = $employee[$i]->courses;
            for ($j = 0; $j < count($courses); $j++) {
                if (!empty($courses[$j])) {
                    $course_id = $courses[$j]->id;
                    $course_info = EmployeeCoursesModel::where([
                        [
                            'employee_id',
                            $employee_id,
                        ],
                        [
                            'course_id',
                            $course_id,
                        ],
                    ])->get();
                    $certificate_info = EmployeeCertificateModel::where([
                        [
                            'employee_id',
                            $employee_id,
                        ],
                        [
                            'course_id',
                            $course_id,
                        ],
                    ])->get();
                    $employee[$i]->courses[$j]->employee_course_due_date = $course_info[0]->employee_course_due_date;
                    $employee[$i]->courses[$j]->employee_course_status = $course_info[0]->employee_course_status;
                    $employee[$i]->courses[$j]->employee_course_date_assigned = $course_info[0]->employee_course_date_assigned;
                    $employee[$i]->courses[$j]->employee_course_date_completed = $course_info[0]->employee_course_date_completed;
                    if (!empty($certificate_info[0])) {
                        $employee[$i]->courses[$j]->certificate_url = $certificate_info[0]->certificate_url;
                        $employee[$i]->courses[$j]->certificate_name = $certificate_info[0]->certificate_name;
                    }
                }
            }
        }

        return response()->json($employee, 200);
    }

    public function nonComplianceEmailList(Request $request) {
        $where_data = [];
        array_push($where_data, [
            'course_status',
            2,
        ]);
        if (!empty($request->company_id)) {
            $company_id = $request->company_id;
            array_push($where_data, [
                'company_id',
                $company_id,
            ]);
        }
        $type = $request->type;
        if ($type == 'non_compliant') {
            $employees = EmployeeCoursesModel::select('employee_id', 'company_id')->where($where_data)->groupBy('employee_id', 'company_id')->get();
        } else {
            $today = date('Y-m-d');
            $next_date = date('Y-m-d', strtotime('+7 days'));
            $employees = EmployeeCoursesModel::select('employee_id', 'company_id')->where($where_data)->whereBetween('employee_course_due_date', [
                $today,
                $next_date,
            ])->groupBy('employee_id', 'company_id')->get();
        }
        foreach ($employees as $id) {
            $company = CompanyModel::find($id['company_id']);
            $employee_data = EmployeeModel::find($id['employee_id']);
            $course_ids = EmployeeCoursesModel::leftJoin('tbl_course', 'tbl_course.id', '=', 'tbl_employee_courses.course_id')->select('tbl_course.name')->where([
                [
                    'employee_id',
                    $id['employee_id'],
                ],
                [
                    'employee_course_status',
                    '!=',
                    1,
                ],
            ])->get();
            $courses = [];
            foreach ($course_ids as $key => $value) {
                $courses[$key]['name'] = $value->name;
                // $course_id = $ids->course_id;
                // $data1 = CourseModel::select('name')->where('id', $course_id)->get();
                // if (!empty($data1[0]))
                // {
                //     $courses[] = $data1[0];
                // }
            }
            if (!empty($employee_data->employee_email)) {
                $email = $employee_data->employee_email;
                $data = array(
                    'first_name' => $employee_data->first_name,
                    'last_name' => $employee_data->last_name,
                    'company_name' => $company->name,
                    'email' => $employee_data->email,
                    'courses' => $courses,
                );

                Mail::send('expird_mail', $data, function($message) use ($email) {
                    $message->to($email)->subject(env('SITE_NAME'));
                    $message->from(env('MAIL_FROM_ADDRESS'), env('MAIL_FROM_NAME'));
                });
                CommonTrait::emailLog("Course Expire mail", $email, $id['employee_id']);
            }
        }

        return response()->json(['Email' => "Email sent successfully!"], 200);
    }

    public function resetPassword(Request $request) {

        if($request->has('user_id')) {
            $ids[] = [
                'id' => $request->user_id,
            ];
        } else {
            $ids = $request->ids;
        }
        foreach ($ids as $id) {
            $access_code = rand(100000, 999999);
            $user = EmployeeModel::where('id', $id['id'])->first();
            $user_name = $user->user_name;
            if (!empty($user->email)) {
                $email = $user->email;
                $now = Carbon::now('UTC');
                $expiry = $now->addDays(1);
                DB::table('tbl_employee')->where('id', $user->id)->update([
                    'is_password_reset' => 1,
                    'link_expiry' => $expiry,
                ]);
                //generate link and send email
                $link = encrypt($user->user_name);
                $link = env('LMS_URL') . '/#/reset_password?link=' . $link;
                $email = $user->email;
                $data['email'] = $user->email;
                $data['link'] = $link;
                $data['first_name'] = $user->first_name;
                $data['last_name'] = $user->last_name;
                $data['userId'] = Helper::maskUserId($user->id);

                if ((new UnsubscribeController())->wantToSendTheEmail($user->id) === FALSE) {
                    continue;
                }

                Mail::send('forget_password_reset', $data, function($message) use ($email) {
                    $message->to($email)->subject('Forgot Password Link - ' . env('SITE_NAME'));
                    // from is same email we set in .env file
                    $message->from(env('MAIL_FROM_ADDRESS'), env('MAIL_FROM_NAME'));
                });
                CommonTrait::emailLog("Forgot Password reset", $email, $user->id);
            }
        }

        return response()->json(['Email' => "Email sent successfully!"], 200);
    }

    public function downloadFile($file) {
        $complete_path = public_path() . '/employee/certificates/' . $file . ".pdf";
        $headers = [
            'Access-Control-Allow-Origin' => '*',
            'Access-Control-Allow-Methods' => 'GET, PUT, POST, DELETE, HEAD, OPTIONS',
            'Access-Control-Allow-Credentials' => 'true',
            'Access-Control-Max-Age' => '86400',
            'Access-Control-Allow-Headers' => 'Content-Type, Authorization, X-Requested-With',
        ];

        return response()->download($complete_path, $file, $headers);
    }

    public function resetAttempts(Request $request) {
        $employee_id = $request->employee_id;
        $type = $request->type;
        $course_id = $request->course_id;
        if ($type == "lesson") {
            $lesson_id = $request->lesson_id;

            $lesson_status = EmployeeCourseAttemptsModel::where([
                [
                    'lesson_test',
                    'lesson',
                ],
                [
                    'user_id',
                    $employee_id,
                ],
                [
                    'course_id',
                    $course_id,
                ],
                [
                    'lesson_test_id',
                    $lesson_id,
                ],
            ])->get();
            $attempts = $lesson_status[0]['attempts'];
            $totalattempts = $lesson_status[0]['total_attempts'] + $lesson_status[0]['attempts'];
            if ($lesson_status[0]['pass_fail'] == 0) {
                EmployeeCourseAttemptsModel::where([
                    [
                        'lesson_test',
                        'lesson',
                    ],
                    [
                        'user_id',
                        $employee_id,
                    ],
                    [
                        'course_id',
                        $course_id,
                    ],
                    [
                        'lesson_test_id',
                        $lesson_id,
                    ],
                ])->update([
                    'total_attempts' => $attempts,
                    'attempts' => '0',
                ]);
                EmployeeCoursesModel::where([
                    [
                        'employee_id',
                        $employee_id,
                    ],
                    [
                        'course_id',
                        $course_id,
                    ],
                ])->update(['employee_course_status' => '0']);
            }
        } else if ($type == "test") {
            $company_id = $request->company_id;
            $test_id = $request->test_id;
            $test_status = EmployeeCourseAttemptsModel::where([
                [
                    'lesson_test',
                    'test',
                ],
                [
                    'user_id',
                    $employee_id,
                ],
                [
                    'course_id',
                    $course_id,
                ],
                [
                    'lesson_test_id',
                    $test_id,
                ],
            ])->get();

            $attempts = $test_status[0]['attempts'];
            $totalattempts = $test_status[0]['total_attempts'] + $test_status[0]['attempts'];

            EmployeeCourseAttemptsModel::where([
                [
                    'user_id',
                    $employee_id,
                ],
                [
                    'course_id',
                    $course_id,
                ],
            ])->update([
                'total_attempts' => $totalattempts,
                'attempts' => '0',
                'pass_fail' => '0',
            ]);
            EmployeeCoursesModel::where([
                [
                    'employee_id',
                    $employee_id,
                ],
                [
                    'course_id',
                    $course_id,
                ],
            ])->update(['employee_course_status' => '0']);

        }
    }

    public function jobTitles() {
        try {

            $result = DB::table('tbl_job_title')->orderBy('name', 'ASC')->get();

            return response()->json($result, 200);

        } catch (Exception $th) {

            return response()->json($th->getMessage(), 422);
        }
    }

    public function getDemoTourVideo(Request $request) {
        try {
            $status = $request->video_status;

            $startFrom = $limit = 0;
            if ($request->page && $request->per_page) {
                $startFrom = ($request->page == 0) ? ($request->page * $request->per_page) : ($request->page - 1) * $request->per_page;
                $limit = $request->per_page;
            }

            $search = $request->search;
            $result = DB::table('tbl_tour_video');
            if (isset($search) && !empty($search)) {

                $result = $result->where(function($query) use ($search) {
                    $query->where('tbl_tour_video.name', 'like', '%' . $search . '%');
                    $query->orWhere('tbl_tour_video.type', 'like', '%' . $search . '%');
                    $query->orWhere('tbl_tour_video.vimeo_video_id', 'like', '%' . $search . '%');
                });
            }
            $columnName = [
                0 => 'tbl_tour_video.order',
                1 => 'tbl_tour_video.name',
                2 => 'tbl_tour_video.type',
            ];
            $orderBy = "";
            $orderColumn = "";
            if (isset($request->order) && isset($request->column)) {
                $orderBy = $request->order;
                $orderColumn = ($request->column < 4) ? $columnName[$request->column] : 'tbl_tour_video.order';
            }

            if (isset($status) && !empty($status)) {
                if ($status == 'Active') {
                    $status = 'True';
                } else {
                    $status = 'False';
                }

                $result = $result->where('tbl_tour_video.status', '=', $status);
            }
            if ($orderColumn != '' && $orderBy != '') {
                $result->orderBy($orderColumn, $orderBy);
            }

            $total = $result->count();

            if ($limit != '') {
                $result->skip($startFrom);
                $result->take($request->per_page);
            }

            $result = $result->get();

            return response()->json([
                'total' => $total,
                'data' => $result,
            ], 200);

        } catch (Exception $th) {

            return response()->json($th->getMessage(), 422);
        }
    }

    public function addTourVideo(Request $request) {

        $randNo = substr(str_shuffle("0123456789abcdefghijklmnopqrstuvwxyz"), 0, 8);
        $tourUp = new TourVideo;
        $tourUp->name = $request->get('video_name');
        $tourUp->tour_id = $randNo;
        $tourUp->vimeo_video_id = $request->get('video');
        $tourUp->discription = $request->get('video_description');
        $tourUp->type = $request->get('role');
        $tourUp->status = $request->get('status');


        if ($tourUp->save()) {
            return response()->json([], 200);
        } else {
            return response()->json([], 500);
        }


    }

    public function getTourVideo($id) {
        $getTour = TourVideo::where('id', $id)->first();

        return response()->json($getTour, 200);
    }

    public function updateTourVideo(Request $request, $id) {
        $tourUp = TourVideo::find($id);
        $tourUp->name = $request->get('video_name');
        $tourUp->vimeo_video_id = $request->get('video');
        $tourUp->discription = $request->get('video_description');
        $tourUp->type = $request->get('role');
        $tourUp->status = $request->get('status');
        $tourUp->save();

        return response()->json([], 200);

    }

    public function destroyTourVideo(Request $request) {
        // delete
        $id = $request->get('id');
        $shark = TourVideo::find($id);
        $shark->delete();

        // Session::flash('message', 'Successfully deleted the shark!');
        return response()->json([], 200);
    }

    public function updateTourVideoOrder(Request $request) {
        for ($i = 0; $i < count($request->data); $i++) {
            TourVideo::where('id', $request->data[$i])->update(['order' => $i + 1]);
        }
        $result = DB::table('tbl_tour_video');
        $result->orderby('tbl_tour_video.order', 'asc');
        $result = $result->get();

        return response()->json($result, 200);
    }

    public function allemployeeDocuments(Request $request) {
        $user = Auth::user();
        $columnName = [0 => 'tbl_employee.full_name'];
        $orderBy = "";
        $orderColumn = "";
        if (isset($request->order) && isset($request->column)) {
            $orderBy = $request->order;
            $orderColumn = ($request->column < 1) ? $columnName[$request->column] : 'tbl_employee.full_name';
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

        } else {
            $companyIds = $companies['isLocations'];
            $where_dataa = "tbl_employee_company_locations.location_id";
        }

        $result = EmployeeDocumentModel::leftjoin('tbl_employee_company_locations', 'tbl_employee_company_locations.employee_id', '=', 'tbl_employee_document.employee_id')->leftjoin('tbl_employee', 'tbl_employee.id', '=', 'tbl_employee_document.employee_id')->leftJoin('tbl_company', 'tbl_company.id', '=', DB::Raw('(CASE WHEN `tbl_employee_company_locations`.location_id = 0 then `tbl_employee_company_locations`.company_id ELSE `tbl_employee_company_locations`.location_id END)'))->where($where_dataa, $companyIds)->where('tbl_employee_document.employee_id', '!=', $user->id)->where('tbl_employee.status', '1')->select('tbl_company.name as location', 'tbl_employee.first_name', 'tbl_employee.last_name', 'tbl_employee_document.title', 'tbl_employee_document.url', 'tbl_employee_document.type', 'tbl_employee_document.document', 'tbl_employee_document.created_at');

        if ($orderColumn != '' && $orderBy != '') {
            $result->orderBy($orderColumn, $orderBy);
        }

        return response()->json($result->get(), 200);
    }

    public function employeeCourseFolderProgress() {
        $user = Auth::user();
        $employee_id = $user->id;

        $course_folder = EmployeeCourseFolderModel::select(DB::raw('tbl_course_folder.folder_name,
        (select count(*) from tbl_course_coursefolder  
        where tbl_course_coursefolder.course_folder_id = tbl_employee_coursefolders.folder_id) as course_count,
        (SELECT count(*) from tbl_course_coursefolder 
            left join tbl_employee_courses on tbl_employee_courses.course_id= tbl_course_coursefolder.course_id 
            where tbl_course_coursefolder.course_folder_id = tbl_employee_coursefolders.folder_id 
            && tbl_employee_courses.employee_course_status= 1 
            && tbl_employee_courses.employee_id = ' . $employee_id . ') as passed_course_count,
        (SELECT count(*) from tbl_course_coursefolder 
        left join tbl_employee_courses on tbl_employee_courses.course_id= tbl_course_coursefolder.course_id 
        where tbl_course_coursefolder.course_folder_id = tbl_employee_coursefolders.folder_id 
        && tbl_employee_courses.employee_course_status= 0 
        && tbl_employee_courses.employee_id = ' . $employee_id . ') as failed_course_count'))->leftjoin('tbl_course_folder', 'tbl_course_folder.id', '=', 'tbl_employee_coursefolders.folder_id')->where('employee_id', $employee_id)->limit(3)->get();

        return $course_folder;

    }

    public function notPurchasedCourses() {
        $user = Auth::user();
        $today = Carbon::now('UTC');

        $company = EmployeeCompanyLocationsModel::where('employee_id', $user->id)->first();
        $result = [];
        if ($company) {
            $result = CompanyCoursesModel::
            select('tbl_course.id', 'tbl_course.name', 'tbl_course.cost')->leftjoin('tbl_course', 'tbl_course.id', '=', 'tbl_company_courses.course_id')->where('company_id', $company->company_id)->whereNotIn('tbl_company_courses.course_id', EmployeeCoursesModel::
            select('course_id')->where('employee_id', $user->id))->get();


        } else {
            $result = CourseModel::
            select('tbl_course.id', 'tbl_course.name', 'tbl_course.cost')->whereNotIn('tbl_course.id', EmployeeCoursesModel::
            select('course_id')->where('employee_id', $user->id))->orderby('tbl_course.name', 'asc')->get();
        }

        return response()->json($result, 200);
    }

    public function purchaseCourse(Request $request) {
        $user = Auth::user();
        $payment_request = $request->payment;
        //return $payment_request;
        if (!empty($payment_request)) {
            $paymentResponse = PaymentModel::stripePayment($this->secret_key, $payment_request);
            if ($paymentResponse['status'] == 'error') {

                return response()->json([
                    'status' => 'error',
                    'message' => $paymentResponse['message'],
                ], 422);
            }
            $isPaymentResponse = $paymentResponse;
        }

        if (!empty($isPaymentResponse)) {
            foreach ($request->courses_id as $courseID)
            {
                $checkIfExist = EmployeeCoursesModel::where('employee_id', $user->id)->where('course_id', $courseID)->first();
                if ($checkIfExist) {
                    EmployeeCoursesModel::where('employee_id', $user->id)->where('course_id', $courseID)->delete();
                }
                $courseDueDate = CourseModel::select('employees_days_to_complete as due_days', 'for_managers', 'for_employees')->where('id', $courseID)->first();

                $due_days = 0;
                if ($courseDueDate != NULL) {
                    $due_days = (int)$courseDueDate->due_days;
                }
                $course_due_date = Carbon::now('UTC')->addDays($due_days)->format('Y-m-d');
                $employeeCourse = new EmployeeCoursesModel();
                $employeeCourse->employee_id = $user->id;
                $employeeCourse->course_id = $courseID;
                $employeeCourse->employee_course_date_assigned = date('Y-m-d');
                $employeeCourse->employee_course_due_date = $course_due_date;
                $employeeCourse->save();

            }
        }

        return response()->json(['Course Purchased successfully.'], 200);

    }


}
