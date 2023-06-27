<?php

namespace App\Http\Controllers;

use App\Models\RoleModel;
use App\Models\EmployeeModel;
use App\Models\CourseModel;
use App\Models\CourseFolderAssignModel;
use App\Models\CourseFolderModel;
use App\Models\LeadModel;
use App\Models\CourseCertificateModel;
use App\Models\EmployeeCertificateModel;
use App\Models\EmployeeCompanyLocationsModel;
use App\Models\CompanyDocumentModel;
use App\Models\UserOnboarding;
use App\Models\UserActivityLogModel;
use App\Models\UserLoginModel;
use App\Models\SurveyModel;
use App\Models\CompanyModel;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Config;
use DateTime;
use App\User;
use Validator;
use Carbon\Carbon;
use Exception;
use PDFMerger;
use Auth;
use Illuminate\Support\Facades\URL;
use Barryvdh\DomPDF\Facade as PDF;
use App\Models\EmployeeCoursesModel;
use Illuminate\Support\Facades\File;
use App\Http\Traits\CommonTrait;
use Illuminate\Support\Facades\Response;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use ZipArchive;

class UserController extends Controller {
    private $status;
    private $sucess;
    private $fail;
    use CommonTrait;

    /**
     * Construct function
     */
    public function __construct() {
        $this->status = config('constant.status');
        $this->sucess = config('constant.success');
        $this->fail = config('constant.fail');
    }


    public function register(Request $request) {

        $validator = Validator::make($request->all(), [
            'password' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['token' => 'Validation errors'], 422);
        }

        try {

            $token = 'Bearer' . sha1(time());
            $user = new EmployeeModel();
            $user->name = $request->name;
            $user->email = $request->email;
            $user->password = md5($request->password);
            $user->api_token = $token;

            $user->save();

            $authToken = $user->createToken('MyApp')->accessToken;

            return response()->json(['token' => $authToken], 200);
        } catch (Exception $ex) {

            return response()->json(['token' => $ex->getMessage()], $ex->getCode());
        }

        // $token='Bearer'.sha1(time());
        // $user = new EmployeeModel();
        // $user->name=$request->name;
        // $user->email=$request->email;
        // $user->password=md5($request->password);
        // $user->api_token=$token;
        // $user->save();

        // return response()->json(['token'=>$user->api_token],200);

    }


    public function login(request $request) {
        try {

            $userModel = new User();
            $password = md5($request->password);
            $email = $request->email;
            $token = 'Bearer' . sha1(time());
            // $login_type = filter_var( $email, FILTER_VALIDATE_EMAIL ) ? 'email' : 'user_name';
            $update = User::where(function($query) use ($email) {
                $query->where('user_name', '=', $email)->orWhere('email', '=', $email);
            })->update(['api_token' => $token]);
            $userInfo = User::where('password', $password)->where(function($query) use ($email) {
                    $query->where('user_name', '=', $email)->orWhere('email', '=', $email);
                })->first();

            //$userInfo = User::first();

            if ($userInfo == NULL) {
                log::debug("1");
                log::debug($email);
                log::debug($request->password);

                return response()->json(['message' => "Email or Password did not match, try again."], 422);
            }

            $getCompany = EmployeeCompanyLocationsModel::where('employee_id', $userInfo->id)->where('company_id', '!=', 0)->first();
            if ($getCompany != NULL) {
                if ($getCompany->location_id != 0) {
                    $location = CompanyModel::leftJoin('tbl_employee', 'tbl_employee.id', '=', 'tbl_company.employee_id')->select('tbl_employee.status as employee_status', 'tbl_company.status as company_status')->where('tbl_company.id', $getCompany->location_id)->first();
                    if ($location->company_status == 0) {
                        return response()->json(['message' => "We’re sorry, but your account is not active at this time.  Please contact us at <u>" . env('MAIL_SUPPORT') . "</u> if you need assistance reactivating your account."], 422);
                    }
                }
                $company = CompanyModel::leftJoin('tbl_employee', 'tbl_employee.id', '=', 'tbl_company.employee_id')->select('tbl_employee.status as employee_status', 'tbl_company.status as company_status')->where('tbl_company.id', $getCompany->company_id)->first();
                if ($company->company_status == 0) {

                    return response()->json(['message' => "We’re sorry, but your account is not active at this time.  Please contact us at <u>" . env('MAIL_SUPPORT') . "</u> if you need assistance reactivating your account."], 422);
                }
            }
            if ($userInfo->status == 0) {
                return response()->json(['message' => "We’re sorry, but your account is not active at this time.  Please contact us at <u>" . env('MAIL_SUPPORT') . "</u> if you need assistance reactivating your account."], 422);
            }

            if ($userInfo) {
                $token = $userInfo->createToken('MyApp')->accessToken;
                $role_id = $userInfo->role_id;
                $role = RoleModel::select('role')->where([
                    [
                        'id',
                        $role_id,
                    ],
                ])->first();
                $data = [];
                $data['role'] = $role->role;
                $data['token'] = $token;
                $data['full_name'] = $userInfo->full_name;
                $data['user_id'] = $userInfo->id;
                $data['token_type'] = "Bearer";
                $is_first_login_status = User::where(function($query) use ($email) {
                    $query->where('user_name', '=', $email)->orWhere('email', '=', $email);
                })->first();
                $updateValue = 0;

                if ($is_first_login_status->is_first_login === NULL) {
                    $updateValue = 1;
                } else if ($is_first_login_status->is_first_login === 1) {
                    $updateValue = 0;
                }
                User::where(function($query) use ($email) {
                    $query->where('user_name', '=', $email)->orWhere('email', '=', $email);
                })->update(['is_first_login' => $updateValue]);
                $userlogin = new UserLoginModel;
                $userlogin->employee_id = $userInfo->id;
                $userlogin->login_at = Carbon::now('UTC');
                $userlogin->save();

                return response()->json($data, 200);

            } else {
                log::debug("2");
                log::debug($email);
                log::debug($request->password);

                return response()->json(['message' => "Email or Password did not match, try again."], 422);
            }
        } catch (Exception $ex) {
            log::debug("3");
            log::debug($email);
            log::debug($request->password);

            return response()->json(['message' => $ex->getMessage()], 422);
        }

        // return response()->json($response);
        // $email=$request->email;
        // $password=md5($request->password);
        // $token='Bearer'.sha1(time());
        // EmployeeModel::where('email', $email)->update(['api_token' => $token]);
        // $user = EmployeeModel::where([['email', $email],['password', $password],])->get()->toArray();
        // if (!empty($user))
        // {
        //     $role_id=$user[0]['role_id'];
        //     $role = RoleModel::select('role')->where([['id', $role_id]])->get();
        //     $data=[];
        //     $data['role']=$role[0]->role;
        //     $data['token']=$user[0]['api_token'];
        //     $data['full_name']=$user[0]['full_name'];
        //     $data['user_id']=$user[0]['id'];
        //     return response()->json($data,200);
        // }
        //     return response()->json(['user'=>'Invalid Email & Password'],422);
    }

    public function loginCheck() {
        try {
            $user = Auth::user();
            $userData = User::where('id', Auth::user()->id)->first();
            $getCompany = EmployeeCompanyLocationsModel::where('employee_id', Auth::user()->id)->first();
            if ($getCompany) {
                $company_onoboarding_documents = CompanyDocumentModel::select('tbl_company_documents.id', 'tbl_company_documents.document')->whereNotIn('id', UserOnboarding::select('tbl_user_onboarding.document_id')->where('employee_id', '=', Auth::user()->id)->where('sign_status', '=', 1)->get()->toArray())->whereRaw("FIND_IN_SET(" . $user->role_id . ", tbl_company_documents.available_for)")->where('tbl_company_documents.company_id', '=', $getCompany->company_id);
                $data = $company_onoboarding_documents->get();
                if ($getCompany) {
                    $getCompanyDetail = CompanyModel::select('document_status')->where('id', $getCompany->company_id)->where('document_status', 1)->first();
                    $userData['company_onboarding_status'] = "";
                    if ($getCompanyDetail) {
                        if ($getCompanyDetail->document_status == 1 && count($data) > 0) {
                            $userData['company_onboarding_status'] = $getCompanyDetail;
                        }
                    }
                }
                $employee_id = $user->id;
                if ($user->role_id == 4) {
                    $getSurveys = SurveyModel::
                    whereIn('tbl_survey.id', function($query) use ($employee_id) {
                        $query->select('survey_id')->from('tbl_employee_survey')->where('employee_id', $employee_id);
                    })->whereNotIn('tbl_survey.id', DB::table('tbl_employee_post_survey_submissions')->where('employee_id', $employee_id)->select('tbl_employee_post_survey_submissions.survey_id'))->limit(1)->get();

                } else {
                    if ($user->role_id == 2) {
                        $role = "for_admins";
                    }
                    if ($user->role_id == 3) {
                        $role = "for_managers";
                    }
                    $company_id = $getCompany->company_id;
                    $getSurveys = SurveyModel::
                    whereIn('tbl_survey.id', function($query) use ($company_id, $role, $employee_id) {
                        $query->select('survey_id')->from('tbl_company_survey')->where('company_id', $company_id)->where($role, 1);
                    })->whereNotIn('tbl_survey.id', DB::table('tbl_employee_post_survey_submissions')->where('tbl_employee_post_survey_submissions.employee_id', $employee_id)->select('tbl_employee_post_survey_submissions.survey_id'))->limit(1)->get();
                }
                $userData['company_survey_status'] = $getSurveys;

            }

            return response()->json([$userData], 200);
        } catch (Exception $ex) {
            return response()->json(['message' => $ex->getMessage()], 422);
        }
    }

    public function updateFirstLoginStatus() {
        try {
            EmployeeModel::where('id', Auth::user()->id)->update(['is_first_login' => 0]);

            return;
        } catch (Exception $ex) {
            return response()->json(['message' => $ex->getMessage()], 422);
        }
    }

    public function loginReport(Request $request) {
        try {
            $columnName = [
                0 => 'tbl_employee.first_name',
                1 => 'tbl_employee.last_name',
                2 => 'tbl_employee.user_name',
                3 => 'tbl_user_login_info.login_at',
                4 => 'tbl_user_login_info.logout_at',
                5 => 'tbl_company.name',
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
                $orderColumn = ($request->column < 6) ? $columnName[$request->column] : 'tbl_employee.first_name';
            }
            $where_data = [];
            $user = Auth::user();
            if (!empty($request->search)) {
                $search = $request->search;
                $search = explode(" ", $search);
                foreach ($search as $key => $name) {
                    $where_data[] = [
                        'tbl_employee.full_name',
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

            $data = UserLoginModel::select('tbl_user_login_info.*', DB::Raw('(CONVERT_TZ(tbl_user_login_info.login_at,"+00:00", "-04:00") ) as login_at'), DB::Raw('(CONVERT_TZ(tbl_user_login_info.logout_at,"+00:00", "-04:00") ) as logout_at'), 'tbl_employee.first_name', 'tbl_employee.last_name', 'tbl_employee.user_name', 'tbl_company.name as company_name')->leftjoin('tbl_employee', 'tbl_user_login_info.employee_id', '=', 'tbl_employee.id')->leftjoin('tbl_employee_company_locations', 'tbl_employee_company_locations.employee_id', '=', 'tbl_user_login_info.employee_id')->leftJoin('tbl_company', 'tbl_company.id', '=', DB::Raw('(CASE WHEN `tbl_employee_company_locations`.location_id = 0 then `tbl_employee_company_locations`.company_id ELSE `tbl_employee_company_locations`.location_id END)'))->where($where, $companyIds)->where($where_data);
            $data->groupBy('tbl_employee_company_locations.employee_id', 'tbl_user_login_info.login_at');
            if ($orderColumn != '' && $orderBy != '') {
                $data->orderBy($orderColumn, $orderBy);
            }

            $records = array();
            $getSheet = array();
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

                    foreach ($records as $key => $value) {
                        $getSheet[$key]['First Name'] = ucfirst($value->first_name);
                        $getSheet[$key]['Last Name'] = ucfirst($value->last_name);
                        $getSheet[$key]['User Name'] = $value->user_name;
                        $getSheet[$key]['Login Time'] = $value->login_at ? Carbon::parse($value->login_at)->format('m-d-Y H:i') : 'N/A';
                        $getSheet[$key]['Logout Time'] = ($value->logout_at != '0000-00-00 00:00:00') ? Carbon::parse($value->logout_at)->format('m-d-Y H:i') : 'N/A';
                        $getSheet[$key]['Location'] = $value->company_name ? $value->company_name : '-';

                    }
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

                foreach ($records as $key => $value) {
                    $getSheet[$key]['First Name'] = ucfirst($value->first_name);
                    $getSheet[$key]['Last Name'] = ucfirst($value->last_name);
                    $getSheet[$key]['User Name'] = $value->user_name;
                    $getSheet[$key]['Login Time'] = $value->login_at ? Carbon::parse($value->login_at)->format('m-d-Y H:i') : 'N/A';
                    $getSheet[$key]['Logout Time'] = ($value->logout_at != '0000-00-00 00:00:00') ? Carbon::parse($value->logout_at)->format('m-d-Y H:i') : 'N/A';
                    $getSheet[$key]['Location'] = $value->company_name ? $value->company_name : '-';

                }
            }

            return response()->json([
                'report' => $records,
                'download' => $getSheet,
                'total' => $total,
            ], 200);
        } catch (Exception $ex) {
            return response()->json(['message' => $ex->getMessage()], 422);
        }
    }

public function activityReport(Request $request) {
        try {
            $columnName = [
                0 => 'tbl_employee.first_name',
                1 => 'tbl_employee.last_name',
                2 => 'tbl_company.name',
                3 => 'tbl_activity_log.ip',
                4 => 'tbl_course.name',
                5 => 'tbl_activity_log.total_time_spent',
                6 => 'tbl_activity_log.event',
                7 => 'tbl_activity_log.created_at',
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
                $orderColumn = ($request->column < 8) ? $columnName[$request->column] : 'tbl_employee.first_name';
            }
            $where_data = [];
            $total = 0;
            $user = Auth::user();
            if ($request->course_id) {
                array_push($where_data, [
                    'tbl_course.id',
                    $request->course_id,
                ]);

            }
            $where = "";
            $companyIds = [];
            if ($request->company_id != '' && $request->company_id != 0) {
                if (is_array($request->company_id)) {
                    $companyIds = $request->company_id;
                } else {
                    $companyIds = array($request->company_id);
                }
                $companies = CompanyModel::where('id', $companyIds)->first();
                if ($companies->parent_id != 0) {
                    $where = 'tbl_employee_company_locations.location_id';
                } else {
                    $where = 'tbl_employee_company_locations.company_id';
                    array_push($where_data, [
                        'tbl_employee_company_locations.location_id',
                        0,
                    ]);
                }
            } else {
                if ($user->role_id != 1) {
                    $companies = CompanyModel::getCompaniesByAdminUser($user->id);
                    if ($companies['isParent'] != 0) {
                        if (is_array($companies['isParent'])) {
                            $companyIds = $companies['isParent'];
                        } else {
                            $companyIds = array(
                                $companies['isParent'],
                            );
                        }
                        $where = 'tbl_employee_company_locations.company_id';


                    } else {
                        $companyIds = $companies['isLocations'];
                        $where = 'tbl_employee_company_locations.location_id';
                    }
                }
            }
            $data = UserActivityLogModel::select('tbl_activity_log.*', 'tbl_course.name', 'tbl_employee.first_name', 'tbl_employee.last_name', 'tbl_employee.user_name', 'tbl_company.name as company_name')->leftjoin('tbl_course', 'tbl_activity_log.course_id', '=', 'tbl_course.id')->leftjoin('tbl_employee', 'tbl_activity_log.user_id', '=', 'tbl_employee.id')->leftjoin('tbl_employee_company_locations', 'tbl_employee_company_locations.employee_id', '=', 'tbl_activity_log.user_id')->leftJoin('tbl_company', 'tbl_company.id', '=', DB::Raw('(CASE WHEN `tbl_employee_company_locations`.location_id = 0 then `tbl_employee_company_locations`.company_id ELSE `tbl_employee_company_locations`.location_id END)'))->where($where_data);
            if ($where) {
                $data->whereIn($where, $companyIds);
            }
            if (!empty($request->search)) {
                $search = $request->search;
                $data->where(function($query) use ($search) {
                    $query->orWhere('tbl_employee.first_name', 'like', '%' . $search . '%');
                    $query->orWhere('tbl_employee.last_name', 'like', '%' . $search . '%');
                    $query->orWhere('tbl_company.name', 'like', '%' . $search . '%');
                    $query->orWhere('tbl_course.name', 'like', '%' . $search . '%');
                    $query->orWhere('tbl_activity_log.event', 'like', '%' . $search . '%');
                    $query->orWhere('tbl_activity_log.created_at', 'like', '%' . $search . '%');
                });

            }
            $data->groupBy('tbl_activity_log.user_id', 'created_at');
            if (!empty($data->get()->toArray())) {
                $total = count($data->get()->toArray());
            }
            if ($orderColumn != '' && $orderBy != '') {
                $data->orderBy($orderColumn, $orderBy);
            }
            if ($limit != '') {
                $data->skip($startFrom);
                $data->take($limit);
            }

            $records = array();
            $getSheet = array();
            if ($user->role_id == 1) {
                if (!empty($request->search) || !empty($request->company_id) || !empty($request->course_id)) {
                    $records = $data->get();
                    foreach ($records as $key => $value) {
                        $getSheet[$key]['First Name'] = ucfirst($value->first_name);
                        $getSheet[$key]['Last Name'] = ucfirst($value->last_name);
                        $getSheet[$key]['User Name'] = $value->user_name;
                        $getSheet[$key]['Location'] = $value->company_name ? $value->company_name : '-';
                        $getSheet[$key]['IP'] = $value->ip;
                        $getSheet[$key]['Course'] = $value->name ? $value->name : '-';
                        $getSheet[$key]['Total Time Spent'] = $value->total_time_spent ? gmdate("H:i:s", $value->total_time_spent) : '-';
                        $getSheet[$key]['Activity Performed'] = $value->event;
                        $getSheet[$key]['Date'] = Carbon::parse($value->created_at)->format('m-d-Y');
                    }
                }
            } else {
                $records = $data->get();
                foreach ($records as $key => $value) {
                    $getSheet[$key]['First Name'] = ucfirst($value->first_name);
                    $getSheet[$key]['Last Name'] = ucfirst($value->last_name);
                    $getSheet[$key]['User Name'] = $value->user_name;
                    $getSheet[$key]['Location'] = $value->company_name ? $value->company_name : '-';
                    $getSheet[$key]['IP'] = $value->ip;
                    $getSheet[$key]['Course'] = $value->name ? $value->name : '-';
                    $getSheet[$key]['Total Time Spent'] = $value->total_time_spent ? gmdate("H:i:s", $value->total_time_spent) : '-';
                    $getSheet[$key]['Activity Performed'] = $value->event;
                    $getSheet[$key]['Date'] = Carbon::parse($value->created_at)->format('m-d-Y');
                }
            }

            return response()->json([
                'report' => $records,
                'download' => $getSheet,
                'total' => $total,
            ], 200);
        } catch (Exception $ex) {
            return response()->json(['message' => $ex->getMessage()], 422);
        }
    }

    public function activityReportBACKUP1(Request $request) {
        try {
            $columnName = [
                0 => 'tbl_employee.first_name',
                1 => 'tbl_employee.last_name',
                2 => 'tbl_company.name',
                3 => 'tbl_activity_log.ip',
                4 => 'tbl_course.name',
                5 => 'tbl_activity_log.total_time_spent',
                6 => 'tbl_activity_log.event',
                7 => 'tbl_activity_log.created_at',
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
                $orderColumn = ($request->column < 8) ? $columnName[$request->column] : 'tbl_employee.first_name';
            }
            $where_data = [];
            $user = Auth::user();
            if (!empty($request->search)) {
                $search = $request->search;
                $search = explode(" ", $search);
                foreach ($search as $key => $name) {
                    $where_data[] = [
                        'tbl_employee.full_name',
                        'like',
                        '%' . $name . '%',
                    ];
                }
            }
            if ($request->course_id) {
                array_push($where_data, [
                    'tbl_course.id',
                    $request->course_id,
                ]);

            }
            $where = [];
            $companyIds = [];
            if ($request->company_id != '' && $request->company_id != 0) {
                if (is_array($request->company_id)) {
                    $companyIds = $request->company_id;
                } else {
                    $companyIds = array($request->company_id);
                }
                $companies = CompanyModel::whereIn('id', $companyIds)->first();
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

            //  $data=  UserActivityLogModel::select('tbl_activity_log.*','tbl_course.name','tbl_employee.first_name','tbl_employee.last_name','tbl_employee.user_name','tbl_company.name as company_name')
            $data = UserActivityLogModel::
            select(DB::raw('DATE(tbl_activity_log.created_at) as date'), 'tbl_activity_log.*', 'tbl_course.name', 'tbl_employee.first_name', 'tbl_employee.last_name', 'tbl_employee.user_name', 'tbl_company.name as company_name', DB::raw("JSON_ARRAYAGG(JSON_OBJECT('ip', ip,'event', event,'course', tbl_course.name,'time_spent', total_time_spent)) AS list"))->leftjoin('tbl_course', 'tbl_activity_log.course_id', '=', 'tbl_course.id')->leftjoin('tbl_employee', 'tbl_activity_log.user_id', '=', 'tbl_employee.id')->leftjoin('tbl_employee_company_locations', 'tbl_employee_company_locations.employee_id', '=', 'tbl_activity_log.user_id')->leftJoin('tbl_company', 'tbl_company.id', '=', DB::Raw('(CASE WHEN `tbl_employee_company_locations`.location_id = 0 then `tbl_employee_company_locations`.company_id ELSE `tbl_employee_company_locations`.location_id END)'))->whereIn($where, $companyIds)->where($where_data);


            $data->groupBy('tbl_activity_log.user_id', 'date');
            if ($orderColumn != '' && $orderBy != '') {
                $data->orderBy($orderColumn, $orderBy);
            }
            if ($limit != '') {
                $data->skip($startFrom);
                $data->take($limit);
            }

            $records = array();
            $getSheet = array();
            $total = 0;
            if ($user->role_id == 1) {
                if (!empty($request->search) || !empty($request->company_id) || !empty($request->course_id)) {
                    $records = $data->get();

                    if (!empty($records->toArray())) {
                        $total = count($records->toArray());
                    }
                    foreach ($records as $key => $value) {
                        $getSheet[$key]['First Name'] = ucfirst($value->first_name);
                        $getSheet[$key]['Last Name'] = ucfirst($value->last_name);
                        $getSheet[$key]['User Name'] = $value->user_name;
                        $getSheet[$key]['Location'] = $value->company_name ? $value->company_name : '-';
                        $getSheet[$key]['IP'] = $value->ip;
                        $getSheet[$key]['Course'] = $value->name ? $value->name : '-';
                        $getSheet[$key]['Total Time Spent'] = $value->total_time_spent ? gmdate("H:i:s", $value->total_time_spent) : '-';
                        $getSheet[$key]['Activity Performed'] = $value->event;
                        $getSheet[$key]['Date'] = Carbon::parse($value->created_at)->format('m-d-Y');

                    }
                }
            } else {
                $records = $data->get();

                if (!empty($records->toArray())) {
                    $total = count($records->toArray());
                }
                foreach ($records as $key => $value) {
                    $getSheet[$key]['First Name'] = ucfirst($value->first_name);
                    $getSheet[$key]['Last Name'] = ucfirst($value->last_name);
                    $getSheet[$key]['User Name'] = $value->user_name;
                    $getSheet[$key]['Location'] = $value->company_name ? $value->company_name : '-';
                    $getSheet[$key]['IP'] = $value->ip;
                    $getSheet[$key]['Course'] = $value->name ? $value->name : '-';
                    $getSheet[$key]['Total Time Spent'] = $value->total_time_spent ? gmdate("H:i:s", $value->total_time_spent) : '-';
                    $getSheet[$key]['Activity Performed'] = $value->event;
                    $getSheet[$key]['Date'] = Carbon::parse($value->created_at)->format('m-d-Y');
                }
            }

            return response()->json([
                'report' => $records,
                'download' => $getSheet,
                'total' => $total,
            ], 200);
        } catch (Exception $ex) {
            return response()->json(['message' => $ex->getMessage()], 422);
        }
    }

    public function activityReportBACKUP(Request $request) {
        try {
            $columnName = [
                0 => 'tbl_employee.first_name',
                1 => 'tbl_employee.last_name',
                2 => 'tbl_company.name',
                3 => 'tbl_activity_log.ip',
                4 => 'tbl_course.name',
                5 => 'tbl_activity_log.total_time_spent',
                6 => 'tbl_activity_log.event',
                7 => 'tbl_activity_log.created_at',
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
                $orderColumn = ($request->column < 8) ? $columnName[$request->column] : 'tbl_employee.first_name';
            }
            $where_data = [];
            $total = 0;
            $user = Auth::user();
            if ($request->course_id) {
                array_push($where_data, [
                    'tbl_course.id',
                    $request->course_id,
                ]);

            }
            $where = "";
            $companyIds = [];
            if ($request->company_id != '' && $request->company_id != 0) {
                if (is_array($request->company_id)) {
                    $companyIds = $request->company_id;
                } else {
                    $companyIds = array($request->company_id);
                }
                $companies = CompanyModel::where('id', $companyIds)->first();
                if ($companies->parent_id != 0) {
                    $where = 'tbl_employee_company_locations.location_id';
                } else {
                    $where = 'tbl_employee_company_locations.company_id';
                    array_push($where_data, [
                        'tbl_employee_company_locations.location_id',
                        0,
                    ]);
                }
            } else {
                if ($user->role_id != 1) {
                    $companies = CompanyModel::getCompaniesByAdminUser($user->id);
                    if ($companies['isParent'] != 0) {
                        if (is_array($companies['isParent'])) {
                            $companyIds = $companies['isParent'];
                        } else {
                            $companyIds = array(
                                $companies['isParent'],
                            );
                        }
                        $where = 'tbl_employee_company_locations.company_id';


                    } else {
                        $companyIds = $companies['isLocations'];
                        $where = 'tbl_employee_company_locations.location_id';
                    }
                }
            }
            $data = UserActivityLogModel::select('tbl_activity_log.*', 'tbl_course.name', 'tbl_employee.first_name', 'tbl_employee.last_name', 'tbl_employee.user_name', 'tbl_company.name as company_name')->leftjoin('tbl_course', 'tbl_activity_log.course_id', '=', 'tbl_course.id')->leftjoin('tbl_employee', 'tbl_activity_log.user_id', '=', 'tbl_employee.id')->leftjoin('tbl_employee_company_locations', 'tbl_employee_company_locations.employee_id', '=', 'tbl_activity_log.user_id')->leftJoin('tbl_company', 'tbl_company.id', '=', DB::Raw('(CASE WHEN `tbl_employee_company_locations`.location_id = 0 then `tbl_employee_company_locations`.company_id ELSE `tbl_employee_company_locations`.location_id END)'))->where($where_data);
            if ($where) {
                $data->whereIn($where, $companyIds);
            }
            if (!empty($request->search)) {
                $search = $request->search;
                $data->where(function($query) use ($search) {
                    $query->orWhere('tbl_employee.first_name', 'like', '%' . $search . '%');
                    $query->orWhere('tbl_employee.last_name', 'like', '%' . $search . '%');
                    $query->orWhere('tbl_company.name', 'like', '%' . $search . '%');
                    $query->orWhere('tbl_course.name', 'like', '%' . $search . '%');
                    $query->orWhere('tbl_activity_log.event', 'like', '%' . $search . '%');
                    $query->orWhere('tbl_activity_log.created_at', 'like', '%' . $search . '%');
                });

            }
            $data->groupBy('tbl_activity_log.user_id', 'created_at');
            if (!empty($data->get()->toArray())) {
                $total = count($data->get()->toArray());
            }
            if ($orderColumn != '' && $orderBy != '') {
                $data->orderBy($orderColumn, $orderBy);
            }
            // if ($limit != '' && $request->has('isExcelDownload') === FALSE) {
            //     $data->skip($startFrom);
            //     $data->take($limit);
            // }

            $records = array();
            $getSheet = array();
            if ($user->role_id == 1) {
                if (!empty($request->search) || !empty($request->company_id) || !empty($request->course_id)) {
                    $records = $data->get();
                    foreach ($records as $key => $value) {
                        $getSheet[$key]['First Name'] = ucfirst($value->first_name);
                        $getSheet[$key]['Last Name'] = ucfirst($value->last_name);
                        $getSheet[$key]['User Name'] = $value->user_name;
                        $getSheet[$key]['Location'] = $value->company_name ? $value->company_name : '-';
                        $getSheet[$key]['IP'] = $value->ip;
                        $getSheet[$key]['Course'] = $value->name ? $value->name : '-';
                        $getSheet[$key]['Total Time Spent'] = $value->total_time_spent ? gmdate("H:i:s", $value->total_time_spent) : '-';
                        $getSheet[$key]['Activity Performed'] = $value->event;
                        $getSheet[$key]['Date'] = Carbon::parse($value->created_at)->format('m-d-Y');
                    }
                }
            } else {
                $records = $data->get();
                foreach ($records as $key => $value) {
                    $getSheet[$key]['First Name'] = ucfirst($value->first_name);
                    $getSheet[$key]['Last Name'] = ucfirst($value->last_name);
                    $getSheet[$key]['User Name'] = $value->user_name;
                    $getSheet[$key]['Location'] = $value->company_name ? $value->company_name : '-';
                    $getSheet[$key]['IP'] = $value->ip;
                    $getSheet[$key]['Course'] = $value->name ? $value->name : '-';
                    $getSheet[$key]['Total Time Spent'] = $value->total_time_spent ? gmdate("H:i:s", $value->total_time_spent) : '-';
                    $getSheet[$key]['Activity Performed'] = $value->event;
                    $getSheet[$key]['Date'] = Carbon::parse($value->created_at)->format('m-d-Y');
                }
            }

            return response()->json([
                'report' => $records,
                'download' => $getSheet,
                'total' => $total,
            ], 200);
        } catch (Exception $ex) {
            return response()->json(['message' => $ex->getMessage()], 422);
        }
    }

    public function logout() {
        $result = UserLoginModel::where('employee_id', Auth::user()->id)->where('logout_at', '0000-00-00 00:00:00')->update(['logout_at' => Carbon::now('UTC')]);

        return response()->json($result, 200);
    }

    public function addActivity(Request $request) {
        $user_id = Auth::user()->id;
        if ($user_id != "1") {
            $userlogin = new UserActivityLogModel;
            $userlogin->user_id = $user_id;
            $userlogin->ip = $_SERVER['REMOTE_ADDR'];
            $userlogin->event = $request->event;
            $userlogin->created_at = Carbon::now('UTC');
            $userlogin->updated_at = Carbon::now('UTC');
            $userlogin->save();
        }
    }

    public function profile() {
        try {
            if (!Auth::guard('api')->user()) {
                $response['status'] = 'error';
                $response['statusCode'] = 401;
                $response['message'] = 'User token has been expired.';
                $response['data'] = NULL;

                return response()->json($response);
            }
            $user = Auth::guard('api')->user();

            if (!empty($user)) {

                $userInfo['email'] = $user->email;
                $userInfo['created_date'] = $user->created_at;

                $response['status'] = 'success';
                $response['statusCode'] = 200;
                $response['message'] = 'User profile details.';
                $response['data'] = $userInfo;
            } else {
                $response['status'] = 'error';
                $response['statusCode'] = 401;
                $response['message'] = 'User does not exist.';
                $response['data'] = NULL;
            }
        } catch (Exception $ex) {
            $response['status'] = 'error';
            $response['statusCode'] = $ex->getCode();
            $response['message'] = $ex->getMessage();
            $response['data'] = NULL;
            Log::info('Request', $response);
        }

        return response()->json($response);
    }

    public function discountRules() {
        try {

            $result = array();
            $result['courses'] = CourseModel::where([
                'in_store' => 1,
                'status' => 1,
            ])->where('company_specific', '!>', 0)->select('id', 'name', 'description', 'cost', 'course_type', 'is_discounted_course')->orderBy('name', 'ASC')->get();

            $result['non_discounted_courses'] = CourseModel::where([
                'in_store' => 1,
                'status' => 1,
            ])->where('company_specific', '!>', 0)->where('is_discounted_course', '=', 0)->select('id', 'name', 'description', 'cost', 'course_type', 'is_discounted_course', 'discounted_course_comment')->orderBy('name', 'ASC')->get();

            $result['course_folders'] = CourseFolderModel::with('activeCourses')->where('tbl_course_folder.folder_status', '=', 1)->get();


            $result['services'] = DB::table('tbl_services')->where('status', 1)->select('id', 'name', 'price', 'frequency')->get();

            $result['corporate_discount_rules'] = DB::table('tbl_corporate_discount_rules')->where('status', 1)->select('id', 'calculation_type', 'discount_value', 'type', 'operator', 'calculation_min_value', 'calculation_max_value')->get();

            $result['discount_rules'] = DB::table('tbl_discount_rules')->where('status', 1)->get();

            return response()->json($result, 200);
        } catch (Exception $th) {

            return response()->json($th->getMessage(), 422);
        }


    }

    public function lead(request $request) {
        $validator = Validator::make($request->all(), [
                'first_name' => 'required',
                'last_name' => 'required',
                'user_type' => 'required',
                'company_name' => 'required',
                'number_of_locations' => 'required',
                'number_of_employees' => 'required',
                'email' => 'required|email',
                'phone_num' => 'required',
            ]);

        if ($validator->fails()) {
            $errors = json_decode($validator->errors());
            foreach ($errors as $error) {
                $message[] = $error[0];
            }
            $message = implode("<br/>", $message);

            return response()->json(['message' => $message], 422);
        }
        // $perLocationPrice = User::getPerLocationCost($request->number_of_employees, $request->number_of_locations);

        try {
            $result = array();
            $data = $request->all();

            $data['course_ids'] = json_encode($request->course_ids);
            $data['special_courses'] = json_encode($request->special_courses);
            $totalUser = $request->number_of_employees;
            $course_array = array();
            if (is_array($request->course_ids)) {
                $course_array = $request->course_ids;
            } else {
                array_push($course_array, $request->course_ids);
            }

            $courses = CourseModel::whereIn('id', $course_array)->select('id', 'name', 'description', 'cost', 'course_type')->get();
            $courseCosts = array_column($courses->toArray(), 'cost');
            $totalCost = array_sum($courseCosts);
            $totalspecialCourseCost = 0;
            $courses1 = "";
            if ($request->user_type == 'corporate') {
                if (count($request->special_courses) > 0) {
                    foreach ($request->special_courses as $special_course) {

                        $course_id[] = $special_course['id'];
                        $user[] = $special_course['users'];
                    }
                    $courses1 = CourseModel::whereIn('id', $course_id)->select('id', 'name', 'description', 'cost', 'course_type')->orderByRaw('FIELD(id, ' . implode(", ", $course_id) . ')')->get();
                    $specialcourseCosts = array_column($courses1->toArray(), 'cost');
                    $calculateSpecialCourseAmount[] = 0;
                    for ($i = 0; $i < count($specialcourseCosts); $i++) {
                        $calculateSpecialCourseAmount[] = ($specialcourseCosts[$i] * $user[$i]) / 12;
                    }

                    $totalspecialCourseCost = array_sum($calculateSpecialCourseAmount);
                }
            }

            $totalCourse = count($course_array);
            $disocuntCost = 0;
            $isDiscoount = [];
            $subTatal = 0;
            $perYearCost = 0;
            $perMonthCost = 0;
            $perLocationPrice = 0;
            if ($request->user_type == 'corporate') {
                foreach ($courses as $value) {
                    $subTatal = User::calculateCorporateDiscount($totalUser, $value->cost);

                    $perYearCost = ($subTatal * $totalUser);
                    $perMonthCost = $perMonthCost + ($perYearCost / 12);
                }

                $perMonthCost = $perMonthCost;
                $perLocationPrice = User::getPerLocationCost($perMonthCost + $totalspecialCourseCost, $request->number_of_employees, $request->number_of_locations);
                //  $perMonthCost = $totalCost;
                //    $isDiscoount = User::getDiscountOffer($totalCourse, $totalUser, $perMonthCost, $request->user_type);
                //  if ($isDiscoount != null) {
                //    $disocuntCost = User::calculateDiscountPercentage($perMonthCost+$perLocationPrice+$totalspecialCourseCost, $isDiscoount->discount_value, $isDiscoount->discount_type);
                // }
            } else if ($request->user_type == 'individual') {
                $perMonthCost = $totalCost;
                //  $isDiscoount = User::getDiscountOffer($totalCourse, $totalUser, $perMonthCost, $request->user_type);
                //  if ($isDiscoount != null) {
                //    $disocuntCost = User::calculateDiscountPercentage($perMonthCost, $isDiscoount->discount_value, $isDiscoount->discount_type);

                // }
            }

            $result['type'] = $request->user_type;
            $result['number_of_employees'] = $request->number_of_employees;
            $result['number_of_locations'] = $request->number_of_locations;
            $result['courses'] = $courses;
            $result['special_courses'] = $courses1;
            $result['discount'] = $isDiscoount;
            $result['discount_value'] = round($disocuntCost, 2);
            $result['per_month_total_cost'] = round($perMonthCost, 2);
            $result['per_location_cost'] = round($perLocationPrice, 2);
            if ($request->user_type == 'corporate') {
                if (count($request->course_ids) > 0) {
                    $result['sub_total'] = (round($perLocationPrice, 2) * $request->number_of_locations);
                } else {
                    $result['sub_total'] = round($totalspecialCourseCost, 2);
                }
            } else {

                $result['sub_total'] = round($perMonthCost, 2);
            }
            $result['discount_value'] = round($disocuntCost, 2);
            $result['special_courses_users'] = $request->special_courses;
            $discountValueApply = $result['sub_total'] - $disocuntCost;
            $result['total'] = round($discountValueApply, 2);
            $totalYearCost = $discountValueApply * 12;
            if (count($request->course_ids) > 0) {
                $discountYearCost = $totalYearCost - ($totalYearCost * 10) / 100;
                $result['onlySpecialCourse'] = 0;
            } else {
                $discountYearCost = $totalYearCost;
                $result['onlySpecialCourse'] = 1;
            }
            // $discountYearCost = $totalYearCost;
            $result['perYearCost'] = round($discountYearCost, 2);
            $data['created_at'] = Carbon::now('UTC');
            // if (!$request->user_id) {


            $insert = LeadModel::insertGetId($data);
            $result['user_id'] = $insert;
            $result['message'] = "User info submitted successfully.";
            if ($insert) {
                $data['type'] = $result['type'];
                $data['courses'] = $result['courses'];
                $data['special_courses'] = $result['special_courses_users'];
                $data['perYearCost'] = $result['perYearCost'];
                $data['discount'] = $result['discount'];
                $data['sub_total'] = $result['sub_total'];
                $data['discount_value'] = $result['discount_value'];
                $data['total_discounted'] = $result['total'];
                $data['per_location'] = round($result['total'] / $result['number_of_locations'], 2);
                $data['per_employee'] = round($result['total'] / $result['number_of_employees'], 2);
                $data['user_id'] = $result['user_id'];
                $data['promo_code'] = $request->promo_code ? $request->promo_code : "";

                if ($request->user_type == 'individual') {
                    $data['course_cost'] = $request->promo_code ? $request->course_cost : "";
                    $data['discounted_cost'] = $request->promo_code ? $request->discounted_cost : "";

                } else {
                    $data['original_cost'] = $request->promo_code ? $request->course_cost : "";
                    $data['costPerMonth'] = $request->promo_code ? $request->discounted_cost : "";
                    $data['costPerYear'] = $request->promo_code ? $request->total_cost_per_year : "";
                    $data['costPerLocation'] = $request->promo_code ? $request->per_location : "";
                    $data['costPerUser'] = $request->promo_code ? $request->per_user : "";
                }
                if (count($request->course_ids) > 0) {
                    $data['onlySpecialCourse'] = 0;
                } else {
                    $data['onlySpecialCourse'] = 1;
                }
                $tt = User::sendLeadToSupport($data);
                $result['mailstatus'] = $tt;

                return response()->json($result, 200);
            }
            //   }

            $result['user_id'] = $request->user_id;
            $result['message'] = "User info resubmitted successfully.";

            return response()->json($result, 200);

        } catch (Exception $th) {

            return response()->json(['message' => $th->getMessage()], 422);
        }
    }

    public function minLocationLead(request $request) {
        $validator = Validator::make($request->all(), [
                'first_name' => 'required',
                'last_name' => 'required',
                'user_type' => 'required',
                'company_name' => 'required',
                'number_of_locations' => 'required',
                'number_of_employees' => 'required',
                'email' => 'required|email',
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
            $result = array();
            $noOfLocation = $request->number_of_locations;
            $noOfEmployees = $request->number_of_employees;
            $price = DB::table('tbl_member_price_per_location')->where('member_min_value', '<', $noOfEmployees)->where('member_max_value', '>=', $noOfEmployees)->first();
            $foodManagerPrice = 0;
            $sexualHarassmentPrice = 0;
            $coursePrice = 0;
            $smsPrice = 0;
            $perLocationPrice = $price->price * $noOfLocation;

            if ($request->is_foodManager == TRUE) {
                $foodManagerPrice = 9.75 * ($request->no_of_managers * $noOfLocation);

            }
            if ($request->is_sexualHarassment == TRUE) {
                $sexualHarassmentPrice = 5 * $noOfLocation;

            }
            if (count($request->course_ids) > 0) {
                $coursePrice = count($request->course_ids) * 2 * $noOfLocation;
            }
            if ($request->sms_notification) {
                $smsPrice = 3 * $noOfLocation;
            }

            $course_array = array();
            if (is_array($request->course_ids)) {
                $course_array = $request->course_ids;
            } else {
                array_push($course_array, $request->course_ids);
            }

            $courses = CourseModel::whereIn('id', $course_array)->select('id', 'name', 'description', 'cost', 'course_type')->get();

            $totalPricePerMonth = $perLocationPrice + $foodManagerPrice + $sexualHarassmentPrice + $coursePrice + $smsPrice;
            $totalPricePerYear = $totalPricePerMonth * 12;
            $totalPricePerYearDiscount = $totalPricePerYear - ($totalPricePerYear * 10) / 100;
            $data['first_name'] = $request->first_name;
            $data['last_name'] = $request->last_name;
            $data['company_name'] = $request->company_name;
            $data['number_of_locations'] = $request->number_of_locations;
            $data['number_of_employees'] = $request->number_of_employees;
            $data['email'] = $request->email;
            $data['user_type'] = $request->user_type;
            $data['course_ids'] = json_encode($request->course_ids);
            $data['created_at'] = Carbon::now('UTC');


            $result['courses'] = $courses;
            $result['type'] = $request->user_type;
            $result['perYearCost'] = round($totalPricePerYearDiscount, 2);
            $result['totalPricePerMonth'] = round($totalPricePerMonth, 2);

            $insert = LeadModel::insertGetId($data);
            $result['user_id'] = $insert;
            if ($insert) {
                $no_of_employees = "";
                if ($request->number_of_employees == 10) {
                    $no_of_employees = "1-10";

                } else if ($request->number_of_employees == 30) {
                    $no_of_employees = "11-30";

                } else if ($request->number_of_employees == 50) {
                    $no_of_employees = "31-50";
                } else if ($request->number_of_employees == 100) {
                    $no_of_employees = "51-100";
                } else if ($request->number_of_employees == 250) {
                    $no_of_employees = "101-250";
                } else {
                    $no_of_employees = "250+";
                }
                $data['type'] = $result['type'];
                $data['courses'] = $result['courses'];
                $data['perYearCost'] = $result['perYearCost'];
                $data['perMonthCost'] = $result['totalPricePerMonth'];
                $data['number_of_employeess'] = $no_of_employees;
                $data['user_id'] = $result['user_id'];

                $user_id = 0;
                $email = config('mail.support');
                CommonTrait::emailLog("1-3 Location User Lead", $email, $user_id);
                Mail::send('1_3_location_signup', $data, function($message) use ($email) {
                    $message->bcc('supporttest@yopmail.com', 'Shrami');
                    $message->to($email)->subject(env('SITE_NAME') . ' - New Lead');
                    $message->from(env('MAIL_FROM_ADDRESS'), env('MAIL_FROM_NAME'));
                });

                //  return response()->json($result, 200);
            }

            $result['perMonthCost'] = $totalPricePerMonth;
            $result['perYearCost'] = $totalPricePerYearDiscount;
            $result['message'] = "User info resubmitted successfully.";

            return response()->json($result, 200);

        } catch (Exception $th) {

            return response()->json(['message' => $th->getMessage()], 422);
        }
    }

    public function contact(Request $request) {
        $validator = Validator::make($request->all(), [
                'name' => 'required',
                'phone' => 'required',
                'email' => 'required|email',
                'message' => 'required',
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
            $request->created_at = Carbon::now('UTC');
            $insert = DB::table('tbl_contact')->insert($request->all());
            if ($insert) {
                User::contact($request->all());

                return response()->json(["message" => "Your query sent successfully, we will contact you as soon as possible."], 200);
            } else {

                return response()->json(['message' => "Something is wrong, try again."], 422);
            }
        } catch (Exception $th) {

            return response()->json(['message' => $th->getMessage()], 422);
        }
    }

    public function saveCertificatePdf(Request $request) {
        ini_set('max_execution_time', 900);
        ini_set('memory_limit', '2048M');
        try {
            $user = Auth::user();
            $type = "preview";
            $course_id = $request->course_id;
            $user_id = $request->employee_id;
            $employee_certifiicate_id = $request->certificate_id;

            $file_names = [];
            $not_merged_users = [];
            for ($i = 0; $i < count($user_id); $i++) {

                $fileName = $user_id[$i] . '_' . $course_id[$i] . '_' . $employee_certifiicate_id[$i] . '_certificate.pdf';
                $fileNameManual = $user_id[$i] . '_' . $course_id[$i] . '_' . $employee_certifiicate_id[$i] . '_certificate_manual.pdf';
                $employee = EmployeeModel::where(['tbl_employee.id' => $user_id[$i]])->select('tbl_employee.id as user_id', 'first_name', 'last_name', 'full_name', 'dob', 'address', 'city', 'state', 'zipcode')->first();
                $course = CourseModel::leftJoin('tbl_course_certificate', 'tbl_course.id', '=', 'tbl_course_certificate.course_id')->leftJoin('tbl_certificate', 'tbl_course_certificate.certificate_id', '=', 'tbl_certificate.id')->leftJoin('tbl_templates', 'tbl_templates.id', '=', 'tbl_certificate.template_id')->select('tbl_course.course_name_certificate as course_name', 'tbl_certificate.*', 'tbl_templates.name as template_name', 'tbl_course.certificate_validity')->where('tbl_course.id', $course_id[$i])->first();
                $employee_course_date_completed = EmployeeCertificateModel::where([
                    'tbl_employee_certificates.employee_id' => $user_id[$i],
                    'tbl_employee_certificates.course_id' => $course_id[$i],
                    'tbl_employee_certificates.id' => $employee_certifiicate_id[$i],
                ])->select('tbl_employee_certificates.certificate_no', 'tbl_employee_certificates.certificate_url', 'tbl_employee_certificates.manual', 'tbl_employee_certificates.certificate_date as employee_course_date_completed', 'tbl_employee_certificates.certificate_expiration_date')->first();

                if ($course->template_name == '') {
                    echo "Course does not have certificate, Contact to " . env('SITE_NAME') . ".";
                    exit;
                }

                if ($employee_course_date_completed->manual == 1) {
                    $type = 'application/pdf';
                    $headers = ['Content-Type' => $type];
                    $response = new BinaryFileResponse(public_path('employee/certificate_manual/' . $employee_course_date_completed->certificate_url), 200, $headers);
                    File::copy(public_path('employee/certificate_manual/' . $employee_course_date_completed->certificate_url), public_path('employee/certificate_manual/saved_pdf/' . $fileNameManual));
                    if (!$this->checkPdfEncription(public_path('employee/certificate_manual/saved_pdf/' . $fileNameManual))) {
                        array_push($file_names, $fileNameManual);
                    } else {
                        array_push($not_merged_users, $employee->full_name);
                    }
                } else {

                    $course->employee_course_date_completed = $employee_course_date_completed->employee_course_date_completed;
                    $course->certificate_no = $employee_course_date_completed->certificate_no;
                    $course->certificate_expiration_date = $employee_course_date_completed->certificate_expiration_date;
                    $data = CourseCertificateModel::getCertificateData($course, $employee);

                    $template = strtolower($course->template_name);
                    $template = str_replace(' ', '_', $template);
                    $date = Carbon::now('UTC');
                    $file_name = $template . rand() . '.pdf';
                    $orientation = 'landscape';
                    $customPaper = 'letter';
                    $pdf = PDF::loadView('templates.' . $template, $data)->setPaper($customPaper, $orientation);

                    // $output=$pdf->stream($file_name,array('Attachment'=>0))->header('Content-Type','application/pdf');
                    $dom_pdf = $pdf->getDomPDF();
                    $canvas = $dom_pdf->get_canvas();
                    file_put_contents(public_path() . "/employee/certificate_manual/saved_pdf/" . $fileName, $pdf->output());

                    if (!$this->checkPdfEncription(public_path() . "/employee/certificate_manual/saved_pdf/" . $fileName)) {
                        array_push($file_names, $fileName);
                    } else {
                        array_push($not_merged_users, $employee->full_name);
                    }
                }
            }

            $pdf = new PDFMerger();

            foreach ($file_names as $fileName) {

                $pdfFilePath = public_path() . '/employee/certificate_manual/saved_pdf/' . $fileName;

                $pdf->addPDF($pdfFilePath, 'all');

            }
            $first_name = strtolower(str_replace(' ', '_', $user->first_name));
            $pathForTheMergedPdf = public_path() . "/employee/certificate_manual/saved_pdf/" . $first_name . '_' . $user->id . '_' . $course_id[0] . '_cert.pdf';

            $pdf->merge('file', $pathForTheMergedPdf);

            return response()->json([
                'file_name' => $first_name . '_' . $user->id . '_' . $course_id[0] . '_cert.pdf',
                'user_not_merged' => implode("</br>", $not_merged_users),
            ], 200);
        } catch (Exception $th) {
            return response()->json(['message' => $th->getMessage()], 422);
            //echo $th->getMessage();
            //exit;
        }
    }

    public function checkPdfEncription($filename) {
        $handle = fopen($filename, "r");
        $contents = fread($handle, filesize($filename));
        fclose($handle);

        if (stristr($contents, "/Encrypt"))
            return TRUE; else
            return FALSE;
    }

    public function downloadCourseCertificate($type, $course_id, $user_id, $employee_certifiicate_id, $is_coursefolder=0) {
        try {
            $employee = EmployeeModel::where(['tbl_employee.id' => $user_id])->select('tbl_employee.id as user_id', 'role_id', 'first_name', 'last_name', 'full_name', 'dob', 'address', 'city', 'state', 'zipcode')->first();
            $employee = EmployeeModel::where(['tbl_employee.id' => $user_id])->select('tbl_employee.id as user_id', 'role_id', 'first_name', 'last_name', 'full_name', 'dob', 'address', 'city', 'state', 'zipcode')->first();
            if ($is_coursefolder == 1) {
                $course = CourseFolderModel::leftJoin('tbl_course_folder_certificate', 'tbl_course_folder.id', '=', 'tbl_course_folder_certificate.folder_id')
                    ->leftJoin('tbl_certificate', 'tbl_course_folder_certificate.certificate_id', '=', 'tbl_certificate.id')
                    ->leftJoin('tbl_templates', 'tbl_templates.id', '=', 'tbl_certificate.template_id')
                    ->select('tbl_course_folder.folder_name as course_folder', 'tbl_certificate.*', 'tbl_templates.name as template_name')
                    ->where('tbl_course_folder.id', $course_id)->first();
            } else {
                $course = CourseModel::leftJoin('tbl_course_certificate', 'tbl_course.id', '=', 'tbl_course_certificate.course_id')->leftJoin('tbl_certificate', 'tbl_course_certificate.certificate_id', '=', 'tbl_certificate.id')->leftJoin('tbl_templates', 'tbl_templates.id', '=', 'tbl_certificate.template_id')->select('tbl_course.course_name_certificate as course_folder', 'tbl_certificate.*', 'tbl_templates.name as template_name', 'tbl_course.certificate_validity')->where('tbl_course.id', $course_id)->first();
            }

            $employee_course_date_completed = EmployeeCertificateModel::where([
                'tbl_employee_certificates.employee_id' => $user_id,
                'tbl_employee_certificates.course_id' => $course_id,
                'tbl_employee_certificates.id' => $employee_certifiicate_id,
            ])->select('tbl_employee_certificates.certificate_no', 'tbl_employee_certificates.certificate_url', 'tbl_employee_certificates.manual', 'tbl_employee_certificates.certificate_date as employee_course_date_completed', 'tbl_employee_certificates.certificate_expiration_date')->first();
            if ($employee_course_date_completed->manual == 1) {
                if ($type == 'download') {
                    return response()->download(public_path('employee/certificate_manual/' . $employee_course_date_completed->certificate_url));
                } else {
                    $type = 'application/pdf';
                    $headers = ['Content-Type' => $type];
                    $response = new BinaryFileResponse(public_path('employee/certificate_manual/' . $employee_course_date_completed->certificate_url), 200, $headers);
                    return $response;
                }
            }


            $data = [
                'course_folder' => $course->course_folder,
                'employee_name' => $employee->full_name,
                'employee_first_name' => $employee->full_name,
                'employee_last_name' => '',
                'name_of_course' => $course->course_folder,
                'signature_title_1' => $course->signature_title_1,
                'signature_title_2' => $course->signature_title_2,
                'completion_date' => Carbon::parse($employee_course_date_completed->employee_course_date_completed)->format('m-d-Y'),
                'expiration_date' => Carbon::parse($employee_course_date_completed->certificate_expiration_date)->format('m-d-Y'),
                'certificate_no' => $employee_course_date_completed->certificate_no,
                'text' => $course->content_for_employee,
                'date_of_birth' => $employee->dob,
            ];
            $template = strtolower($course->template_name);
            $template = str_replace(' ', '_', $template);
            $date = Carbon::now('UTC');
            $file_name = $template . rand() . '.pdf';
            $orientation = 'landscape';
            $customPaper = 'letter';
            $pdf = PDF::loadView('templates.' . $template, $data)->setPaper($customPaper, $orientation);
            if ($type == 'download') {
                return $pdf->download($file_name);
            } else {
                return $pdf->stream($file_name, array('Attachment' => 0));
            }
        } catch (Exception $th) {
            echo $th->getMessage();
            exit;
        }
    }


    public function downloadAllCourseCertificate($course_id, $user_id) {
        try {

            $course_ids = explode("_", $course_id);
            $employee = EmployeeModel::where('id', $user_id)->select('id as user_id', 'first_name', 'last_name', 'full_name', 'dob', 'address', 'city', 'state', 'zipcode')->first();
            if ($employee == NULL) {
                echo "User does not exist.";
                exit;
            }
            $all_certificate = EmployeeCertificateModel::leftJoin('tbl_course', 'tbl_course.id', '=', 'tbl_employee_certificates.course_id')->leftJoin('tbl_course_certificate', 'tbl_course.id', '=', 'tbl_course_certificate.course_id')->leftJoin('tbl_certificate', 'tbl_course_certificate.certificate_id', '=', 'tbl_certificate.id')->leftJoin('tbl_templates', 'tbl_templates.id', '=', 'tbl_certificate.template_id')->select('tbl_course.course_name_certificate as course_name', 'tbl_certificate.*', 'tbl_templates.name as template_name', 'tbl_employee_certificates.certificate_no', 'tbl_employee_certificates.certificate_date as employee_course_date_completed', 'tbl_employee_certificates.manual', 'tbl_employee_certificates.certificate_url', 'tbl_employee_certificates.certificate_expiration_date')->where('tbl_employee_certificates.employee_id', $user_id)->whereIn('tbl_employee_certificates.id', $course_ids)->get();
            $folder_name = $employee->first_name . $user_id;
            $path = public_path() . '/employee/certificates/';
            File::makeDirectory($path, $mode = 0777, TRUE, TRUE);
            $all_files = array();
            if ($all_certificate->count() == 0) {

                echo "Certificate did not find.";
                exit;
            }


            foreach ($all_certificate as $key => $value) {
                if ($value->manual == 1) {
                    File::copy(public_path('employee/certificate_manual/' . '/' . $value->certificate_url), public_path('employee/certificates/' . '/' . $value->certificate_url));

                    //    $file_url_name = $value->certificate_url;
                    //    $file_name= URL::to('employee/certificate_manual/').'/'.$value->certificate_url;
                    //    $pdf = PDF::loadFile($file_name);
                    //    $pdf->save(storage_path('../public/employee/certificates/').$file_url_name);

                } else {
                    $data = CourseCertificateModel::getCertificateData($value, $employee);
                    $certificate_no = "2020-100" . $user_id;
                    $template = strtolower($value->template_name);
                    $template = str_replace(' ', '_', $template);
                    $date = Carbon::now('UTC');
                    $file_name = $template . rand() . '.pdf';
                    $orientation = 'landscape';
                    $customPaper = 'letter';
                    $pdf = PDF::loadView('templates.' . $template, $data)->setPaper($customPaper, $orientation);
                    $pdf->save(storage_path('../public/employee/certificates/') . $file_name);
                }
            }
            $zip = new ZipArchive;
            $fileName = 'certificatesArchive.zip';
            if (is_file(public_path() . '/' . $fileName)) {

                File::delete(public_path() . '/' . $fileName);
            }


            $files = File::files($path);
            if ($zip->open(public_path($fileName), ZipArchive::CREATE) === TRUE) {

                foreach ($files as $key => $value) {
                    $relativeNameInZipFile = basename($value);
                    $zip->addFile($value, $relativeNameInZipFile);

                }
                $zip->close();
            }

            foreach ($files as $file) { // iterate files
                if (is_file($file)) {
                    unlink($file); // delete file
                }
            }
            // File::deleteDirectory($path);

            //  return response()->download(public_path($fileName));


            return response()->download(public_path($fileName));

        } catch (Exception $th) {

            echo $th->getMessage();
            exit;
        }
    }

    public function previewCertificate($certificate_id) {

        $course = CourseCertificateModel::where('tbl_certificate.id', $certificate_id)->leftJoin('tbl_templates', 'tbl_templates.id', '=', 'tbl_certificate.template_id')->select('tbl_certificate.*', 'tbl_templates.name as template_name')->first();
        if ($course == NULL) {

            return response()->json(['message' => 'Certificate is not available.'], 422);
        }
        $template = strtolower($course->template_name);
        $template = str_replace(' ', '_', $template);
        $date = Carbon::now('UTC');
        $data = [
            'employee_first_name' => 'Firstname',
            'employee_last_name' => 'Lastname',
            'name_of_course' => $course->name,
            'date_of_birth' => '01-01-2000',
            'student_home_address' => 'A186',
            'city_state_zip' => 'Noida/UP/201301',
            'current_employer' => 'Chetu India',
            'completion_date' => '12-10-2020',
            'expiration_date' => '12-10-2020',
            'certificate_no' => '2020-100426',
            'signature_title_1' => $course->signature_title_1,
            'signature_title_2' => $course->signature_title_2,

            'text' => $course->custom_text,
        ];

        $file_name = $template . rand() . '.pdf';
        $orientation = 'landscape';
        $customPaper = 'letter';

        $pdf = PDF::loadView('templates.' . $template, $data)->setPaper($customPaper, $orientation);

        return $pdf->stream($file_name, array('Attachment' => 0));

    }

    public function sendResetPasswordLink(Request $request) {
        $validator = Validator::make($request->all(), [
                'user_name' => 'required',
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
            $userName = $request->user_name;
            $user = DB::table('tbl_employee')->where('user_name', $userName)->orWhere('email', $userName)->first();
            if ($user == NULL) {
                return response()->json(['message' => "Username or email does not exist, Try another."], 422);
            }
            if ($user->email == '') {
                return response()->json(['message' => "There is no email address attached with your account, Please contact support."], 422);
            }
            if ($user->id) {
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

                Mail::send('forget_password_reset', $data, function($message) use ($email) {
                    $message->to($email)->subject('Forget Password Link');
                    $message->from(env('MAIL_FROM_ADDRESS'), env('MAIL_FROM_NAME'));
                });
                CommonTrait::emailLog("Forgot Password reset", $email, $user->id);

                return response()->json(['message' => "Password reset link has been sent on your registered email " . CommonTrait::hideEmail($user->email) . ", Please check your inbox."], 200);
            } else {
                return response()->json(['message' => "Email or Username did not match any record."], 422);
            }
        } catch (Exception $th) {
            return response()->json(['message' => $th->getMessage()], 422);
        }
    }


    public function resetPassword(Request $request) {
        $validator = Validator::make($request->all(), [
                'link' => 'required',
                'new_password' => 'required',
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
            $encrytedLink = trim($request->link);
            $userName = decrypt($encrytedLink);
            $user = DB:: table('tbl_employee')->where('user_name', $userName)->orWhere('email', $userName)->first();
            if ($user->id) {
                $now = Carbon::now('UTC');
                $password = md5($request->new_password);

                if ($now->lt($user->link_expiry) || $user->is_password_reset == 1) {
                    DB:: table('tbl_employee')->where('id', $user->id)->update([
                        'access_code' => $request->new_password,
                        'password' => $password,
                        'is_password_reset' => 0,
                    ]);

                    return response()->json(['message' => "Password has been rest successfully."], 200);
                } else {
                    return response()->json(['message' => "This link has expired! Regenerate new link."], 422);
                }
            } else {
                return response()->json(['message' => "This link is incorrect!"], 422);
            }
        } catch (Exception $th) {
            return response()->json(['message' => $th->getMessage()], 422);
        }
    }

    public function sendWeeklyLead() {
        $today = date('Y-m-d');
        $previous_date = date('Y-m-d', strtotime('-7 days'));
        $getPastWeekLead = LeadModel::select('first_name', 'last_name', 'email')->whereBetween('created_at', [
                $previous_date,
                $today,
            ])->get();
        log::debug($getPastWeekLead);


        //   Excel::download($getPastWeekLead, 'students.xlsx');


        // $excel=Excel::create('Filename', function($excel) {

        //     $excel->sheet('Sheetname', function($sheet) {

        //         $sheet->fromArray(array(
        //             array('data1', 'data2'),
        //             array('data3', 'data4')
        //         ));

        //     });

        // })->export('xls');

        // log::debug($excel);
        $data = [
            'first_name' => "test",
        ];
        $email = 'test1234@yopmail.com';
        Mail::send('weekly_lead', $data, function($message) use ($email) {
            $message->attach(public_path('employee/documents/1598451914.xlsx'));
            $message->to($email)->subject('Weekly Lead Report');
            $message->from(env('MAIL_FROM_ADDRESS'), env('MAIL_FROM_NAME'));
        });
    }

    public function downloadCourseFolderCertificates($user_id) {
        try {

            $employee = EmployeeModel::where('id', $user_id)->select('id as user_id', 'first_name', 'last_name', 'full_name', 'dob', 'address', 'city', 'state', 'zipcode')->first();
            if ($employee == NULL) {
                echo "User does not exist.";
                exit;
            }
            $all_certificate = EmployeeCertificateModel::
            leftJoin('tbl_course', 'tbl_course.id', '=', 'tbl_employee_certificates.course_id')->leftJoin('tbl_course_certificate', 'tbl_course.id', '=', 'tbl_course_certificate.course_id')->leftJoin('tbl_course_folder', 'tbl_course_folder.id', '=', 'tbl_employee_certificates.course_id')->leftJoin('tbl_course_folder_certificate', 'tbl_course_folder.id', '=', 'tbl_course_folder_certificate.folder_id')->leftJoin('tbl_certificate', DB::Raw('(CASE WHEN `tbl_employee_certificates`.is_coursefolder_certificate = 1 then `tbl_course_folder_certificate`.certificate_id ELSE `tbl_course_certificate`.certificate_id END)'), '=', 'tbl_certificate.id')->leftJoin('tbl_templates', 'tbl_templates.id', '=', 'tbl_certificate.template_id')->select('tbl_course.course_name_certificate as course_name', 'tbl_course_folder.folder_name as folder_name', 'tbl_certificate.*', 'tbl_templates.name as template_name', 'tbl_employee_certificates.certificate_no', 'tbl_employee_certificates.certificate_date as employee_course_date_completed', 'tbl_employee_certificates.manual', 'tbl_employee_certificates.certificate_url', 'tbl_employee_certificates.certificate_expiration_date', 'tbl_employee_certificates.is_coursefolder_certificate')->where('tbl_employee_certificates.employee_id', $user_id)->get();

            $folder_name = $employee->first_name . $user_id;
            $path = public_path() . '/employee/certificates/';
            $source_path = public_path() . '/employee/certificate_manual/';
            File::makeDirectory($path, $mode = 0777, TRUE, TRUE);
            $all_files = array();
            if ($all_certificate->count() == 0) {

                echo "Certificate not found.";
                exit;
            }


            foreach ($all_certificate as $key => $value) {
                if ($value->manual == 1) {
                    File::copy($source_path . $value->certificate_url, $path . $value->certificate_url);
                } else {
                    //   $data = CourseCertificateModel::getCertificateData($value, $employee);
                    $data = [
                        'course_folder' => $value->course_name ? $value->course_name : $value->folder_name,
                        'employee_name' => $employee->full_name,
                        'completion_date' => Carbon::parse($value->employee_course_date_completed)->format('m-d-Y'),
                        'expiration_date' => Carbon::parse($value->certificate_expiration_date)->format('m-d-Y'),
                        'certificate_no' => $value->certificate_no,
                        'text' => $value->content_for_employee,
                    ];
                    $certificate_no = "2020-100" . $user_id;
                    $template = strtolower($value->template_name);
                    $template = str_replace(' ', '_', $template);
                    $date = Carbon::now('UTC');
                    $file_name = $template . rand() . '.pdf';
                    $orientation = 'landscape';
                    $customPaper = 'letter';
                    if ($template) {
                        $pdf = PDF::loadView('templates.' . $template, $data)->setPaper($customPaper, $orientation);
                        $pdf->save(storage_path('../public/employee/certificates/') . $file_name);
                    }
                }
            }
            $zip = new ZipArchive;
            $fileName = 'certificatesArchive.zip';
            if (is_file(public_path() . '/' . $fileName)) {

                File::delete(public_path() . '/' . $fileName);
            }


            $files = File::files($path);
            if ($zip->open(public_path($fileName), ZipArchive::CREATE) === TRUE) {

                foreach ($files as $key => $value) {
                    $relativeNameInZipFile = basename($value);
                    $zip->addFile($value, $relativeNameInZipFile);

                }
                $zip->close();
            }


            foreach ($files as $file) { // iterate files
                if (is_file($file)) {
                    unlink($file); // delete file
                }
            }

            return response()->download(public_path($fileName));

        } catch (Exception $th) {

            echo $th->getMessage();
            exit;
        }


    }

    public function courses_data(Request $request)
    { 
        if($request->course_ids)
        {
            $course_array = array();
            if (is_array($request->course_ids)) {
                $course_array = $request->course_ids;
            } else {
                array_push($course_array, $request->course_ids);
            }

            $courses = CourseModel::whereIn('id', $course_array)->select('id', 'name', 'description', 'cost', 'course_type')->get();
            $total = 0;
            $courses_name = [];
            foreach ($courses as $course)
            {
                $total += $course->cost;
                $courses_name[] = $course->name; 

            }
            $result['total'] = round($total, 2);
            $result['courses'] = $courses;
            $result['courses_name'] = implode(", ",$courses_name);

            return $result;
        }
        else{
            return response()->json(['message' => 'Please Select Course(s)'], 422);
        }
    }

}
