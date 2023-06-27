<?php

namespace App\Http\Controllers;

use App\Http\Traits\CommonTrait;
use App\Http\Traits\CompanyTrait;
use App\Http\Traits\LocationTrait;
use App\Models\CompanyCoursesModel;
use App\Models\CompanyModel;
use App\Models\CourseModel;
use App\Models\EmployeeCompanyLocationsModel;
use App\Models\EmployeeModel;
use App\Models\ZipCodeModel;
use Auth;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Validator;

class LocationController extends Controller {
    use LocationTrait;
    use CompanyTrait;

    private $status;
    private $sucess;
    private $fail;

    /**
     * Construct function
     */
    public function __construct() {
        $this->status = config('constant.status');
        $this->sucess = config('constant.success');
        $this->fail = config('constant.fail');
    }

    /**
     * To get the list of location a authenticated user has access
     * @return array|null
     */
    public static function getLocations() {
        $user = Auth::user();
        $locations = NULL;
        if ($user->role_id == 1) {
            $locations = CompanyModel::orderBy('name', 'ASC')->get();
        } else {
            $companies = EmployeeCompanyLocationsModel::where('employee_id', $user->id)->get();
            $locations = array_column($companies->toArray(), 'location_id');
            if (in_array(0, $locations)) {
                $company_id = $companies[0]->company_id;
                $locations = CompanyModel::where('status', 1)->where(function($query) use ($company_id) {
                    $query->where('id', $company_id);
                    $query->orWhere('parent_id', $company_id);
                })->orderBy('name', 'ASC')->get();
            } else {
                $locations = CompanyModel::where('status', 1)->whereIn('id', $locations)->orderBy('name', 'ASC')->get();
            }
        }

        return $locations;
    }

    public static function getUserCountOfCompanyBACKUP1($company_id) {
        $getEmployee = EmployeeCompanyLocationsModel::leftJoin('tbl_company', 'tbl_company.id', '=', DB::Raw('(CASE WHEN `tbl_employee_company_locations`.location_id = 0 then `tbl_employee_company_locations`.company_id ELSE `tbl_employee_company_locations`.location_id END)'))->leftJoin('tbl_employee', 'tbl_employee.id', '=', 'tbl_employee_company_locations.employee_id')->leftjoin('tbl_employee_courses', 'tbl_employee_courses.employee_id', '=', 'tbl_employee_company_locations.employee_id')->where('tbl_employee_company_locations.company_id', $company_id)->where('tbl_employee.type', '!=', 'individual')->where('tbl_employee.status', 1)->orWhere('tbl_employee_company_locations.location_id', $company_id)->groupBy('tbl_employee.id')->select('tbl_employee.*', 'tbl_company.name as company_name', 'tbl_employee_company_locations.company_id', 'tbl_employee_company_locations.location_id', DB::raw('max(CASE employee_course_status WHEN 3 THEN 3 WHEN 0 THEN 3 WHEN 2 THEN 2 WHEN 1 THEN 1 ELSE 0 END) AS pass_total'));

        return $getEmployee->get()->count();
    }

    public function register(Request $request) {

        $company = CompanyModel::where('id', $request->location_company_id)->get();
        $companyAllowedLocations = $company[0]->location_num;
        $company_name = $company[0]->name;
        $company_type = $company[0]->type;
        $username = $request->username;
        $first_name = $request->first_name;
        $last_name = $request->last_name;
        if ($request->location_admin) {
            //$email = $request->location_admin;
            $email_data = EmployeeModel::where('id', $request->location_admin[0])->get();
            $email = $email_data[0]->email;
        } else {
            $email = $request->email;
        }
        $password = $request->password;
        $location_phone = $request->location_phone;
        $user_phone = $request->user_phone;
        if (!empty($request->location_zip_code)) {
            $this->companyZip($request->location_zip_code, $request->location_city, $request->location_state);
        }
        $location = new CompanyModel;
        $location->name = $request->location_name;
        $location->type = $company_type;
        $location->weekly_report = $request->weekly_report;
        $location->employee_num = $request->location_employee_count;
        $location->email = $email;
        $location->phone = $location_phone;
        $location->address_1 = $request->location_address_1;
        $location->company_zip = $request->location_zip_code;
        $location->city = $request->location_city;
        $location->state = $request->location_state;
        $location->parent_id = $request->location_company_id;
        if ($request->location_admin) {
            $location->employee_id = $request->location_admin[0];
        }
        $location->status = $request->status;
        $location->sms_status = $request->sms_message;
        $location->save();
        $location_id = $location->id;

        if (!empty($request->first_name && $request->last_name)) {
            $role = 2;
            $employee = NULL;
            $user_type = "admin";
            $phone = "";
            $user_id = $this->companyAdmin($username, $first_name, $last_name, $password, $role, $employee, $user_type, $phone);
            $data = new EmployeeCompanyLocationsModel();
            $data->employee_id = $user_id;
            $data->company_id = $request->location_company_id;
            if ($request->location_company_id) {
                $data->location_id = $location_id;
            }
            $data->save();

            CompanyModel::where('id', $location_id)->update([
                'employee_id' => $user_id,
            ]);
        } else if (!empty($request->location_admin)) {
            $admins = [];
            $role = 2;
            $user_type = "admin";

            EmployeeModel::whereIn('id', $request->location_admin)->update([
                'type' => $user_type,
                'role_id' => $role,
            ]);

            foreach ($request->location_admin as $locations) {
                $data = new EmployeeCompanyLocationsModel();
                $data->employee_id = $locations;
                $data->company_id = $request->location_company_id;
                if ($request->location_company_id) {
                    $data->location_id = $location_id;
                }
                $data->save();
            }

        }
        if (!empty($request->location_manager)) {
            $role = 3;
            $user_type = "location_manager";

            EmployeeModel::whereIn('id', $request->location_manager)->update([
                'type' => $user_type,
                'role_id' => $role,
            ]);

            foreach ($request->location_manager as $locations) {
                $data = new EmployeeCompanyLocationsModel();
                $data->employee_id = $locations;
                $data->company_id = $request->location_company_id;
                if ($request->location_company_id) {
                    $data->location_id = $location_id;
                }
                $data->save();
            }

        }
        $data = array(
            "location_name" => $request->location_name,
            "company_name" => $company_name,
        );
        $email = config('mail.support');
        Mail::send('new_location_added', $data, function($message) use ($email) {
            $message->to($email)->subject(env('SITE_NAME') . ' - New Location Added');
            $message->from(env('MAIL_FROM_ADDRESS'), env('MAIL_FROM_NAME'));
        });
        CommonTrait::emailLog("New Location Added", $email, 0);
        $getActiveCompanyLocationsCount = CompanyModel::where('parent_id', $request->location_company_id)->where('status', 1)->count();
        // Adding extra location
        if ($getActiveCompanyLocationsCount > $companyAllowedLocations) {
            $data = array(
                "type" => "location",
                "location_name" => $request->location_name,
                "company_name" => $company_name,
                "status" => "added",
            );
            $email = config('mail.support');
            Mail::send('charge_company', $data, function($message) use ($email) {
                $message->to($email)->subject(env('SITE_NAME') . ' - Addtional Location Added');
                $message->from(env('MAIL_FROM_ADDRESS'), env('MAIL_FROM_NAME'));
            });
            CommonTrait::emailLog("Addtional Location Added", $email, 0);
        }

        return response()->json($location, 200);
    }

    public function bulkLocation(Request $request) {
        DB::beginTransaction();
        try {
            $data = $request->locations;
            $company_id = $request->company_id;

            $employee_id = CompanyModel::getEmployeeIdByCompnay($company_id);
            if ($employee_id == FALSE) {

                return response()->json(['message' => "Company employee does not exist, try another"], 422);
            }

            $cityState = array();
            $locationData = array();
            foreach ($data as $key => $locations) {
                $get_loication_type = DB::table('tbl_company_type')->where('type', $locations['location_type'])->select('id')->first();
                if ($get_loication_type == NULL) {

                    return response()->json(['message' => "Invalid location type " . $locations['location_type'] . " for " . $locations['location_name']], 422);
                }
                $isZipCode = ZipCodeModel::where('zip_code', $locations['zipcode'])->first();
                if ($isZipCode == NULL) {
                    ZipCodeModel::insert([
                        'city' => $locations['city'],
                        'state' => $locations['state'],
                        'zip_code' => $locations['zipcode'],
                    ]);
                }
                $location_id = CompanyModel::insertGetId([
                    'employee_id' => $employee_id,
                    'parent_id' => $company_id,
                    'type' => $get_loication_type->id,
                    'name' => $locations['location_name'],
                    'location_num' => 0,
                    'employee_num' => $locations['location_employee_count'],
                    'address_1' => $locations['address'],
                    'city' => $locations['city'],
                    'state' => $locations['state'],
                    'phone' => $locations['telephone_number'],
                    'company_zip' => $locations['zipcode'],
                    'weekly_report' => "1",
                    'created_at' => Carbon::now('UTC'),
                ]);
                EmployeeCompanyLocationsModel::insert([
                    'employee_id' => $employee_id,
                    'company_id' => $company_id,
                    'location_id' => $location_id,
                    'created_at' => Carbon::now('UTC'),
                ]);
            }

            DB::commit();

            return response()->json(['message' => 'Locations added successfully.'], 200);
        } catch (Exception $th) {
            DB::rollback();

            return response()->json(['message' => $th->getMessage()], 422);
        }
    }

    public function allLocations(Request $request) {
        try {
            $user = Auth::user();
            if ($user->role_id == 1) {
                $locations = CompanyModel::where('status', 1)->orderBy('name', 'ASC')->get();
            } else {
                $companies = EmployeeCompanyLocationsModel::where('employee_id', $user->id)->get();
                $locations = array_column($companies->toArray(), 'location_id');
                if (in_array(0, $locations)) {
                    $company_id = $companies[0]->company_id;
                    $locations = CompanyModel::where('status', 1)->where(function($query) use ($company_id) {
                        $query->where('id', $company_id);
                        $query->orWhere('parent_id', $company_id);
                    })->orderBy('name', 'ASC')->get();
                } else {
                    $locations = CompanyModel::where('status', 1)->whereIn('id', $locations)->orderBy('name', 'ASC')->get();
                }
            }

            return response()->json($locations, 200);
        } catch (Exception $th) {

            return response()->json(['message' => $th->getMessage()], 200);
        }
    }

    public function allCompanyLocation(Request $request) {
        try {
            $user = Auth::user();
            if ($user->role_id == 1 || $user->role_id == 5) {
                if ($request->onlyParent) {
                    $locations = CompanyModel::where('status', 1)->where('parent_id', 0)->orderBy('name', 'ASC')->get();

                    return response()->json($locations, 200);
                }
                $company_id = $request->company_id;
                if ($company_id) {
                    $locations = CompanyModel::where('status', 1)->where(function($query) use ($company_id) {
                        $query->where('id', $company_id);
                        $query->orWhere('parent_id', $company_id);
                    })->orderBy('name', 'ASC')->get();
                } else {
                    $locations = CompanyModel::where('status', 1)->orderBy('name', 'ASC')->get();
                }
            } else {
                $companies = EmployeeCompanyLocationsModel::where('employee_id', $user->id)->get();
                $locations = array_column($companies->toArray(), 'location_id');
                if (in_array(0, $locations)) {
                    $company_id = $companies[0]->company_id;
                    $locations = CompanyModel::where('status', 1)->where(function($query) use ($company_id) {
                        $query->where('id', $company_id);
                        $query->orWhere('parent_id', $company_id);
                    })->orderBy('name', 'ASC')->get();
                } else {
                    $locations = CompanyModel::where('status', 1)->whereIn('id', $locations)->orderBy('name', 'ASC')->get();
                }
            }

            return response()->json($locations, 200);
        } catch (Exception $th) {

            return response()->json(['message' => $th->getMessage()], 200);
        }
    }

    public function getLocationName($id) {
        $location = CompanyModel::where('id', $id)->get();

        $data['location'] = $location;

        return response()->json($data, 200);
    }

    public function listLocations(Request $request) {
        $validator = Validator::make($request->all(), [

            'role' => 'required',
        ]);

        if ($validator->fails()) {
            $errors = json_decode($validator->errors());
            foreach ($errors as $error) {
                $message[] = $error[0];
            }
            $message = implode(",", $message);

            return response()->json(['user' => $message], 422);
        }
        try {
            $user = Auth::User();
            $data = [];
            $where_data = [];
            $search = explode(" ", $request->search);
            foreach ($search as $key => $name) {
                $where_data[] = [
                    'name',
                    'like',
                    '%' . $name . '%',
                ];
            }

            $status = ($request->location_status == "Active") ? 1 : 0;
            if ($request->location_status != 'All') {
                array_push($where_data, [
                    'tbl_company.status',
                    $status,
                ]);
            }

            $location_ids = [];
            $parentdata = array();
            $childdata = array();
            $accessToParentCompany = FALSE;
            if ($request->role == "admin") {
                $companies = CompanyModel::getCompaniesByAdminUser($user->id);
                if ($companies == NULL) {
                    return response()->json(['message' => 'User does not have any company.'], 422);
                }
                $company_id = [];
                if ($companies['isParent'] != 0) {
                    $accessToParentCompany = TRUE;
                    if (is_array($companies['isParent'])) {
                        $company_id = $companies['isParent'];
                    } else {
                        $company_id = array(
                            $companies['isParent'],
                        );
                    }

                    $parentdata = CompanyModel::whereIn('tbl_company.id', $company_id)->select('tbl_company.*')->get();
                    $childdata = CompanyModel::whereIn('tbl_company.parent_id', $company_id)->where($where_data)->select('tbl_company.*')->get();
                } else {
                    $company_id = $companies['isLocations'];
                    $parentCompoany = CompanyModel::find($company_id)->first();
                    if (!empty($parentCompoany)) {
                        $parentdata = CompanyModel::whereIn('tbl_company.id', [$parentCompoany->id])->select('tbl_company.*')->get();
                    }
                    $childdata = CompanyModel::whereIn('tbl_company.id', $company_id)->select('tbl_company.*')->get();
                }
                $getParentdata = array();
                $getChildData = array();
                foreach ($parentdata as $key => $value) {
                    $getParentdata[$key] = $value;
                    $getParentdata[$key]['active_user_count'] = EmployeeCompanyLocationsModel::leftjoin('tbl_employee', 'tbl_employee.id', '=', 'tbl_employee_company_locations.employee_id')->where('company_id', $value->id)->where('location_id', 0)->where('tbl_employee.status', 1)->distinct('employee_id')->count();
                    $getParentdata[$key]['admin'] = EmployeeCompanyLocationsModel::leftjoin('tbl_employee', 'tbl_employee.id', '=', 'tbl_employee_company_locations.employee_id')->where('company_id', $value->id)->where('location_id', 0)->where('tbl_employee.role_id', 2)->first();
                    $getParentdata[$key]['accessToParentCompany'] = $accessToParentCompany;
                }
                foreach ($childdata as $key => $value) {
                    $getChildData[$key] = $value;
                    $getChildData[$key]['active_user_count'] = self::getUserCountOfCompany($value->id);
                    $getChildData[$key]['admin'] = EmployeeCompanyLocationsModel::leftjoin('tbl_employee', 'tbl_employee.id', '=', 'tbl_employee_company_locations.employee_id')->where('location_id', $value->id)->where('tbl_employee.role_id', 2)->first();
                }
                $data['parentdata'] = $getParentdata;
                $data['childdata'] = $getChildData;

            } else if ($request->role == "manager") {

                $companies = CompanyModel::getCompaniesByAdminUser($user->id);
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
                } else {
                    $company_id = $companies['isLocations'];
                }

                $data = CompanyModel::leftJoin('tbl_employee', 'tbl_employee.id', '=', 'tbl_company.employee_id')->whereIn('tbl_company.id', $company_id)->select('tbl_company.*', 'tbl_employee.id as employee_id', 'tbl_employee.role_id', 'tbl_employee.first_name', 'tbl_employee.last_name', 'tbl_employee.full_name', 'tbl_employee.user_name', 'tbl_employee.job_title_id as job_title', 'tbl_employee.type', 'tbl_employee.email', 'tbl_employee.phone_num', 'tbl_employee.access_code', 'tbl_employee.password', 'tbl_company.status', 'tbl_employee.address', 'tbl_employee.dob', 'tbl_employee.city', 'tbl_employee.state', 'tbl_employee.progress_status', 'tbl_employee.zipcode')->get();

            } else if ($request->role == "super-admin") {

                if (!$request->company_id) {

                    return response()->json(['message' => 'Company is not valid, Try another.'], 422);
                }

                $locations = CompanyModel::where('id', $request->company_id)->first();
                if ($locations->parent_id == 0) {

                    $parentdata = CompanyModel::where('tbl_company.id', $locations->id)->select('tbl_company.*')->get();


                    $childdata = CompanyModel::where('tbl_company.parent_id', $locations->id)->where($where_data)->select('tbl_company.*')->get();

                } else {

                    $childdata = CompanyModel::where('tbl_company.id', $locations->id)->select('tbl_company.*')->get();
                }

                $getParentdata = array();
                $getChildData = array();
                foreach ($parentdata as $key => $value) {
                    $getParentdata[$key] = $value;
                    //$getParentdata[$key]['active_user_count'] = EmployeeCompanyLocationsModel::leftjoin('tbl_employee', 'tbl_employee.id', '=', 'tbl_employee_company_locations.employee_id')->where('company_id', $value->id)->where('tbl_employee.status', 1)->distinct('employee_id')->count();
                    $getParentdata[$key]['active_user_count'] = EmployeeCompanyLocationsModel::leftjoin('tbl_employee', 'tbl_employee.id', '=', 'tbl_employee_company_locations.employee_id')->where('tbl_employee_company_locations.company_id', $value->id)->where('tbl_employee.status', 1)->where('tbl_employee_company_locations.location_id', 0)->count();
                    //$getParentdata[$key]['active_user_count'] = self::getUserCountOfCompany($value->id);
                    $getParentdata[$key]['admin'] = EmployeeCompanyLocationsModel::leftjoin('tbl_employee', 'tbl_employee.id', '=', 'tbl_employee_company_locations.employee_id')->where('company_id', $value->id)->where('location_id', 0)->where('tbl_employee.role_id', 2)->first();
                }
                foreach ($childdata as $key => $value) {
                    $getChildData[$key] = $value;
                    //$getChildData[$key]['active_user_count'] = EmployeeCompanyLocationsModel::leftjoin('tbl_employee', 'tbl_employee.id', '=', 'tbl_employee_company_locations.employee_id')->where('location_id', $value->id)->where('tbl_employee.status', 1)->count();
                    $getChildData[$key]['active_user_count'] = self::getUserCountOfCompany($value->id);
                    $getChildData[$key]['admin'] = EmployeeCompanyLocationsModel::leftjoin('tbl_employee', 'tbl_employee.id', '=', 'tbl_employee_company_locations.employee_id')->where('location_id', $value->id)->where('tbl_employee.role_id', 2)->first();
                }
                $data['parentdata'] = $getParentdata;
                $data['childdata'] = $getChildData;

            }

            return response()->json($data, 200);
        } catch (Exception $ex) {

            return response()->json($ex->getMessage(), 422);
        }
    }

    public static function getUserCountOfCompany($company_id) {
        $getEmployee = EmployeeCompanyLocationsModel::leftJoin('tbl_company', 'tbl_company.id', '=', DB::Raw('(CASE WHEN `tbl_employee_company_locations`.location_id = 0 then `tbl_employee_company_locations`.company_id ELSE `tbl_employee_company_locations`.location_id END)'))->leftJoin('tbl_employee', 'tbl_employee.id', '=', 'tbl_employee_company_locations.employee_id')->leftjoin('tbl_employee_courses', 'tbl_employee_courses.employee_id', '=', 'tbl_employee_company_locations.employee_id')->where('tbl_employee_company_locations.company_id', $company_id)->where('tbl_employee.type', '!=', 'individual')->orWhere('tbl_employee_company_locations.location_id', $company_id)->where('tbl_employee.status', 1)->groupBy('tbl_employee.id')->select('tbl_employee.*', 'tbl_company.name as company_name', 'tbl_employee_company_locations.company_id', 'tbl_employee_company_locations.location_id', DB::raw('max(CASE employee_course_status WHEN 3 THEN 3 WHEN 0 THEN 3 WHEN 2 THEN 2 WHEN 1 THEN 1 ELSE 0 END) AS pass_total'));

        return $getEmployee->get()->count();
    }

    /**
     * To get the Children of current company
     *
     * @param int $companyId company id for which you want to get the child companies
     *
     * @return array
     */
    public function getChildCompaniesIds($companyId) {
        $companies = [];
        $parentCompanies = EmployeeCompanyLocationsModel::where('company_id', $companyId)->where('location_id', '!=', 0)->get();
        if (!empty(count($parentCompanies))) {
            foreach ($parentCompanies as $parentCompany) {
                $companies[] = array_merge([$parentCompany->location_id], $this->getChildCompaniesIds($parentCompany->location_id));
            }
        }

        return $companies;
    }

    /**
     * To get the parent of current company
     *
     * @param int $companyId company id for which you want to get the child companies
     *
     * @return array
     */
    public function getParentCompaniesIds($companyId) {
        $companies = [];
        $parentCompanies = EmployeeCompanyLocationsModel::where('location_id', $companyId)->get();
        if (!empty(count($parentCompanies))) {
            foreach ($parentCompanies as $parentCompany) {
                $companies[] = array_merge([$parentCompany->company_id], $this->getParentCompaniesIds($parentCompany->company_id));
            }
        }

        return $companies;
    }

    /**
     * To get the multidimensional array values into single dimension array
     *
     * @param array $array
     *
     * @return array
     */
    public function getValuesFromArray($array) {
        $files = array();
        foreach ($array as $key => $value) {
            if (is_array($value)) {
                $inner_array = $this->getValuesFromArray($value);
                foreach ($inner_array as $inner_value) {
                    $files[] = $inner_value;
                }
            } else {
                $files[] = $value;
            }
        }
        $result = $files;

        return array_filter($result);
    }

    public function parentList(Request $request) {
        $company_id = $request->company_id;
        $data = [];
        $child_locations = CompanyModel::where('parent_id', $company_id)->where('status', '1')->count();
        $employee = EmployeeCompanyLocationsModel::where('company_id', $company_id)->where('location_id', "0")->count();
        $data['child_locations'] = $child_locations;
        $data['employee_count'] = $employee;

        return response()->json($data, 200);
    }

    public function updateStatus(Request $request, $id) {
        try {
            $companyData = CompanyModel::where('id', $request->company_id)->first();
            $LocationData = CompanyModel::where('id', $id)->first();
            $getActiveCompanyLocationsCount = CompanyModel::where('parent_id', $request->company_id)->count();
            $companyAllowedLocations = $companyData->location_num;
            $companyName = $companyData->name;
            if (($request->status == 1 || $request->status == TRUE) && ($getActiveCompanyLocationsCount > $companyAllowedLocations)) {
                $data = array(
                    "type" => "location",
                    "location_name" => $LocationData->name,
                    "company_name" => $companyName,
                    "status" => "activated",
                );
                $email = config('mail.support');
                Mail::send('charge_company', $data, function($message) use ($email) {
                    $message->to($email)->subject(env('SITE_NAME') . ' - Location Activated');
                    $message->from(env('MAIL_FROM_ADDRESS'), env('MAIL_FROM_NAME'));
                });
                CommonTrait::emailLog("Location Activated", $email, 0);
            }

            CompanyModel::where('id', $id)->update(['status' => $request->status]);

            return response()->json(['Status' => 'Update Successfully..!'], 200);
        } catch (Exception $th) {
            return response()->json(['message' => $th->getMessage()], 422);
        }
    }

    public function edit($id) {
        $location = CompanyModel::select('tbl_company.*')->where('tbl_company.id', $id)->get();
        $location[0]['company_documents'] = DB::table('tbl_company_documents')->where('company_id', $id)->get();
        if (empty($location)) {
            return response()->json(['Location' => 'Invalid id no location found..!'], 422);
        }
        $admins = array();
        $where_data = "";
        $where_dataa = [];
        foreach ($location as $key => $value) {
            if ($value->parent_id == 0) {
                $where_data = "tbl_employee_company_locations.company_id";
                array_push($where_dataa, [
                    "tbl_employee_company_locations.location_id",
                    0,
                ]);
            } else {
                $where_data = "tbl_employee_company_locations.location_id";
            }
            $data = EmployeeCompanyLocationsModel::leftJoin('tbl_employee', 'tbl_employee_company_locations.employee_id', '=', 'tbl_employee.id')->where($where_data, $value->id)->where($where_dataa)->where('tbl_employee.role_id', 2)->groupBy('employee_id')->get()->toArray();
            $data1 = EmployeeCompanyLocationsModel::leftJoin('tbl_employee', 'tbl_employee_company_locations.employee_id', '=', 'tbl_employee.id')->where($where_data, $value->id)->where($where_dataa)->where('tbl_employee.role_id', 3)->groupBy('employee_id')->get()->toArray();

        }
        $location['admin'] = $data;
        $location['manager'] = $data1;

        return response()->json($location, 200);
    }

    public function update(Request $request, $id) {
        $location = CompanyModel::find($id);
        if (empty($location)) {
            return response()->json(['Location' => 'Invalid id no location found.!'], 422);
        }
        $manager_id = $location->location_manager_id;
        if (!empty($request->location_zip_code)) {
            $this->companyZip($request->location_zip_code, $request->location_city, $request->location_state);
        }
        CompanyModel::where('id', $id)->update([
            'name' => $request->location_name,
            'type' => $request->location_type,
            'employee_num' => $request->location_employee_count,
            'location_num' => $request->location_phone_num,
            'phone' => $request->location_phone,
            'address_1' => $request->location_address_1,
            'city' => $request->location_city,
            'state' => $request->location_state,
            'company_zip' => $request->location_zip_code,
            'employee_id' => $request->location_admin,
            'status' => $request->status,
            'sms_status' => $request->sms_message,
            'document_status' => $request->document_status,
            'post_login_survey_status' => $request->post_login_survey_status,
        ]);

        if ($manager_id != $request->location_manager_id) {
            CompanyModel::where('id', $id)->update(['location_manager_id' => $request->location_manager_id,]);
        }

        return response()->json(['Location' => 'update successfully..!'], 200);
    }

    public function locationCourse($id) {
        $user = Auth::user();
        $location = EmployeeCompanyLocationsModel::
        rightJoin('tbl_company_courses', 'tbl_company_courses.company_id', '=', 'tbl_employee_company_locations.company_id')->leftJoin('tbl_course', 'tbl_course.id', '=', 'tbl_company_courses.course_id')->leftJoin('tbl_course', 'tbl_course.id', '=', 'tbl_company_courses.course_id')->where('tbl_employee_company_locations.employee_id', $id);
        if (empty($location)) {
            return response()->json(['Location' => 'Invalid id no company found.!'], 422);
        }


        $data = CompanyModel::with('courses')->where('id', $id)->get();

        return response()->json($data, 200);
    }

    public function employeeList($id) {
        $location = CompanyModel::find($id);
        if (empty($location)) {
            return response()->json(['Location' => 'Invalid id no location found.!'], 422);
        }
        $employees = EmployeeModel::join('tbl_employee_company_locations', 'tbl_employee.id', '=', 'tbl_employee_company_locations.employee_id')->where('tbl_employee_company_locations.location_id', $id)->get();

        return response()->json($employees, 200);
    }

    public function locationCourses(Request $request) {
        $location_id = $request->location_id;
        $location = CompanyModel::find($location_id);
        if (empty($location)) {
            return response()->json(['Location' => 'Invalid id no company found.!'], 422);
        }
        $data = $this->getLocationCourses($location_id);

        return response()->json($data, 200);
    }

    public function passEmployees(Request $request) {
        $location_id = $request->location_id;
        $course_id = $request->course_id;
        $course = CourseModel::find($course_id);
        if (empty($course)) {
            return response()->json(['course' => 'Invalid id no course found.!'], 422);
        }
        $data = $this->PassEmployee($course_id, $location_id);

        return response()->json($data, 200);
    }

    public function unassignCourse(Request $request) {
        $location_id = $request->location_id;
        $course_id = $request->course_id;
        CompanyCoursesModel::where([
            [
                'company_id',
                $location_id,
            ],
            [
                'course_id',
                $course_id,
            ],
        ])->delete();
        $this->unassignCourses($course_id, $location_id);

        return response()->json(['course' => 'Successfully un-assign.!'], 200);
    }

}

