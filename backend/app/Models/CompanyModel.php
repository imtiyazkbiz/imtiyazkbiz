<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class CompanyModel extends Model {
    protected $table = "tbl_company";

    /**
     * The attributes excluded from the model's JSON form.
     * @var array
     */
    public static function getChildCompanyId($companyId) {

        $result = CompanyModel::where([
            'tbl_company.id' => $companyId,
            'tbl_company.parent_id' => 0,
        ])->leftJoin('tbl_company as tc_parent', 'tc_parent.parent_id', '=', 'tbl_company.id')->select('tc_parent.id')->get();

        if ($result->count() > 0) {

            return array_column($result->toArray(), 'id');
        }

        return [];
    }

    public static function getUsers($companyId, $reportType) {
        //$request->report_type => active_user/all_user
        $checkChildCompany = CompanyModel::whereIn('id', $companyId)->select('parent_id')->first();

        if ($checkChildCompany->parent_id > 0) {

            $result = EmployeeCompanyLocationsModel::leftJoin('tbl_employee', 'tbl_employee_company_locations.employee_id', '=', 'tbl_employee.id')->leftJoin('tbl_roles', 'tbl_roles.id', '=', 'tbl_employee.role_id')->leftJoin('tbl_job_title', 'tbl_job_title.id', '=', 'tbl_employee.job_title_id')->select('tbl_employee.first_name', 'tbl_employee.dob', 'tbl_employee_company_locations.company_id', 'tbl_employee_company_locations.location_id', 'tbl_employee.status', 'tbl_employee.last_name', 'tbl_employee.user_name', 'tbl_job_title.name as job_title', 'tbl_roles.role as type', 'tbl_employee.email', 'tbl_employee.phone_num', 'tbl_employee.city', 'tbl_employee.state', 'tbl_employee.address')->whereIn('tbl_employee_company_locations.location_id', $companyId);

        } else {

            $result = EmployeeCompanyLocationsModel::leftJoin('tbl_employee', 'tbl_employee_company_locations.employee_id', '=', 'tbl_employee.id')->leftJoin('tbl_roles', 'tbl_roles.id', '=', 'tbl_employee.role_id')->leftJoin('tbl_job_title', 'tbl_job_title.id', '=', 'tbl_employee.job_title_id')->select('tbl_employee.first_name', 'tbl_employee_company_locations.company_id', 'tbl_employee_company_locations.location_id', 'tbl_employee.status', 'tbl_employee.last_name', 'tbl_employee.user_name', 'tbl_job_title.name as job_title', 'tbl_roles.role as type', 'tbl_employee.email', 'tbl_employee.phone_num', 'tbl_employee.city', 'tbl_employee.state', 'tbl_employee.address')->whereIn('tbl_employee_company_locations.company_id', $companyId);

        }

        if ($reportType == 'active_user') {
            $result->where('tbl_employee.status', '1');
        }

        $result->whereIn('tbl_employee.role_id', [
            2,
            3,
            4,
        ]);

        $getReport = array();
        $headerReport = array(
            'Company Name' => '',
            'First Namee' => '',
            'Last Name' => '',
            'Email' => '',
            'Username' => '',
            'Role' => '',
            'Job Title' => '',
            'Phone Number' => '',
            'D/O/B' => '',
            'Address' => '',
            'City' => '',
            'State' => '',
            'User Status' => '',
        );
        if ($result->count() > 0) {

            foreach ($result->get() as $key => $value) {
                if ($value->location_id == 0) {
                    $getCompany = CompanyModel::where('id', $value->company_id)->select('name')->first();
                } else {
                    $getCompany = CompanyModel::where('id', $value->location_id)->select('name')->first();
                }
                $getReport[$key]['Company Name'] = '';
                if ($getCompany != NULL) {
                    $getReport[$key]['Company Name'] = ucfirst($getCompany->name);
                }
                $getReport[$key]['First Name'] = ucfirst($value->first_name);
                $getReport[$key]['Last Name'] = ucfirst($value->last_name);
                $getReport[$key]['Email'] = $value->email;
                $getReport[$key]['Username'] = $value->user_name;
                $getReport[$key]['Role'] = ($value->type == 'location_manager') ? 'Manager' : ($value->type == 'company-admin') ? "Admin" : ucfirst($value->type);
                $getReport[$key]['Job Title'] = ucfirst($value->job_title);
                $getReport[$key]['Phone Number'] = $value->phone_num;
                $getReport[$key]['D/O/B'] = $value->dob ? date('m/d/Y', strtotime($value->dob)) : "N/A";
                $getReport[$key]['Address'] = ucwords($value->address);
                $getReport[$key]['City'] = ucfirst($value->city);
                $getReport[$key]['State'] = ucfirst($value->state);
                $getReport[$key]['User Status'] = ($value->status == 1) ? "Active" : "Inactive";
            }
        } else {
            $getReport = $headerReport;
        }


        return $getReport;
    }

    public static function getCompnayAdmin($company_id, $company_parent_id) {
        $getEmloyee = EmployeeCompanyLocationsModel::leftJoin('tbl_employee', 'tbl_employee.id', '=', 'tbl_employee_company_locations.employee_id')->select('tbl_employee.id', 'tbl_employee.first_name', 'tbl_employee.last_name', 'tbl_employee.full_name', 'tbl_employee.user_name', 'tbl_employee.email', 'tbl_employee.phone_num', 'tbl_employee.access_code');
        if ($company_parent_id == 0) {

            $getEmloyee->where('tbl_employee_company_locations.company_id', $company_id)->where('tbl_employee_company_locations.location_id', 0)->where('tbl_employee.role_id', 2)->groupBy('tbl_employee_company_locations.employee_id');

        } else {
            $getEmloyee->where('tbl_employee_company_locations.location_id', $company_id)->where('tbl_employee.role_id', 2)->groupBy('tbl_employee_company_locations.employee_id');

        }

        return $getEmloyee->get();
    }

    public static function getCompnayManager($company_id, $company_parent_id) {
        $getEmloyee = EmployeeCompanyLocationsModel::leftJoin('tbl_employee', 'tbl_employee.id', '=', 'tbl_employee_company_locations.employee_id')->select('tbl_employee.id', 'tbl_employee.first_name', 'tbl_employee.last_name', 'tbl_employee.full_name', 'tbl_employee.user_name', 'tbl_employee.email', 'tbl_employee.phone_num', 'tbl_employee.access_code');
        if ($company_parent_id == 0) {

            $getEmloyee->where('tbl_employee_company_locations.company_id', $company_id)->where('tbl_employee_company_locations.location_id', 0)->where('tbl_employee.role_id', 3)->groupBy('tbl_employee_company_locations.employee_id');

        } else {
            $getEmloyee->where('tbl_employee_company_locations.location_id', $company_id)->where('tbl_employee.role_id', 3)->groupBy('tbl_employee_company_locations.employee_id');

        }

        return $getEmloyee->get();
    }

    public static function getEmployeeIdByCompnay($company_id) {
        $getEmloyee = CompanyModel::where('id', $company_id)->first();
        // $getEmloyee =  EmployeeCompanyLocationsModel::leftJoin('tbl_employee', 'tbl_employee.id', '=', 'tbl_employee_company_locations.employee_id')
        //                 ->where('tbl_employee_company_locations.company_id', $company_id)
        //                 ->where('tbl_employee.role_id', 2)
        //                ->select('tbl_employee.id', 'tbl_employee.role_id', 'tbl_employee_company_locations.employee_id', 'tbl_employee_company_locations.company_id')
        //                 ->get();


        if ($getEmloyee != NULL) {

            return $getEmloyee->employee_id;
        }

        return FALSE;
    }
    /**
     * The attributes that are mass assignable.
     * @var array
     */

    public static function getCompantIdByEmployeeId($empId) {
        $getEmloyee = EmployeeCompanyLocationsModel::select('company_id', 'location_id', 'employee_id')->where('tbl_employee_company_locations.employee_id', $empId)->orderBy('tbl_employee_company_locations.created_at', 'asc')->first();
        if ($getEmloyee == NULL) {

            return 0;
        }
        if ($getEmloyee->location_id == 0) {

            return $getEmloyee->company_id;
        }

        return $getEmloyee->location_id;
    }

    public static function getParentIdOfLocationByUserId($empId) {
        $getEmloyee = EmployeeCompanyLocationsModel::select('company_id', 'location_id', 'employee_id')->where('tbl_employee_company_locations.employee_id', $empId)->first();

        if ($getEmloyee != NULL) {

            return $getEmloyee->company_id;
        }

        return 0;

    }

    public static function getParentCompany($where_data, $food_manager = NULL) {
        return CompanyModel::select(DB::raw('tbl_company.*, 
                (select count(*) from tbl_company_courses left join tbl_course on tbl_course.id=tbl_company_courses.course_id where tbl_company_courses.company_id = tbl_company.id && tbl_course.status=1) as courses_count,
                (select count(DISTINCT tbl_employee_company_locations.employee_id) from  tbl_employee_company_locations Left Join  tbl_employee ON tbl_employee.id=tbl_employee_company_locations.employee_id where tbl_employee_company_locations.company_id = tbl_company.id && tbl_employee_company_locations.location_id = 0 && tbl_employee.status=1) as employees_count,
                (select count(DISTINCT tbl_employee_company_locations.employee_id) from  tbl_employee_company_locations Left Join  tbl_employee ON tbl_employee.id=tbl_employee_company_locations.employee_id where tbl_employee_company_locations.company_id = tbl_company.id  && tbl_employee.status=1) as total_employees_count,
                (select count(*) from tbl_company as locations where (locations.parent_id = tbl_company.id || locations.id = tbl_company.id) && locations.status= 1) as locations_count,
                (select count(DISTINCT tbl_employee_courses.employee_id) from  tbl_employee_courses Left Join  tbl_employee_company_locations ON tbl_employee_company_locations.employee_id=tbl_employee_courses.employee_id where tbl_employee_company_locations.company_id = tbl_company.id  && tbl_employee_courses.course_id in ("1","75") && tbl_employee_courses.employee_course_status=1 && year(tbl_employee_courses.employee_course_date_assigned)=YEAR(CURDATE())) as food_manager_pass_count,
                (select count(DISTINCT tbl_employee_courses.employee_id) from  tbl_employee_courses Left Join  tbl_employee_company_locations ON tbl_employee_company_locations.employee_id=tbl_employee_courses.employee_id Left join tbl_employee on tbl_employee.id= tbl_employee_courses.employee_id where tbl_employee_company_locations.company_id = tbl_company.id  && tbl_employee_courses.course_id  in ("1","75") && tbl_employee_courses.employee_course_status=2 && tbl_employee.status=1 && year(tbl_employee_courses.employee_course_date_assigned)=YEAR(CURDATE())) as food_manager_open_count,
                (select count(DISTINCT tbl_employee_courses.employee_id) from  tbl_employee_courses Left Join  tbl_employee_company_locations ON tbl_employee_company_locations.employee_id=tbl_employee_courses.employee_id Left join tbl_employee on tbl_employee.id= tbl_employee_courses.employee_id where tbl_employee_company_locations.company_id = tbl_company.id  && tbl_employee_courses.course_id in ("1","75") && tbl_employee_courses.employee_course_status=0 && tbl_employee.status=1 && year(tbl_employee_courses.employee_course_date_assigned)=YEAR(CURDATE())) as food_manager_fail_count,
                (select count(DISTINCT tbl_employee_courses.employee_id) from  tbl_employee_courses Left Join  tbl_employee_company_locations ON tbl_employee_company_locations.employee_id=tbl_employee_courses.employee_id Left join tbl_employee on tbl_employee.id= tbl_employee_courses.employee_id where tbl_employee_company_locations.company_id = tbl_company.id  && tbl_employee_courses.course_id in ("1","75") && tbl_employee_courses.employee_course_status=3 && tbl_employee.status=1 && year(tbl_employee_courses.employee_course_date_assigned)=YEAR(CURDATE())) as food_manager_expired_count,
                (select tbl_company_food_manager.fm_certificate_count  from  tbl_company_food_manager where tbl_company_food_manager.company_id = tbl_company.id && year=YEAR(CURDATE())) as fm_certificate_count'))->where('tbl_company.parent_id', 0)->where($where_data);

    }

    public static function getChildCompany($where_data) {

        return CompanyModel::select(DB::raw('tbl_company.*, 
                (select count(*) from tbl_company_courses left join tbl_course on tbl_course.id=tbl_company_courses.course_id where tbl_company_courses.company_id = tbl_company.parent_id && tbl_course.status= 1) as courses_count,
                (select count(DISTINCT tbl_employee_company_locations.employee_id) from  tbl_employee_company_locations Left Join  tbl_employee ON tbl_employee.id=tbl_employee_company_locations.employee_id where tbl_employee_company_locations.location_id = tbl_company.id && tbl_employee.status=1) as employees_count,
                (select count(*) from tbl_company as locations where (locations.parent_id = tbl_company.id || locations.id = tbl_company.id) && locations.status= 1) as locations_count'))->where('tbl_company.parent_id', '!=', 0)->where($where_data);

    }

    public static function getParentChildCompany($where_data) {

        return CompanyModel::select(DB::raw('tbl_company.*, 
                (select count(*) from tbl_company_courses left join tbl_course on tbl_course.id=tbl_company_courses.course_id where tbl_company_courses.company_id = tbl_company.parent_id && tbl_course.status= 1) as courses_count,
                (select count(DISTINCT tbl_employee_company_locations.employee_id) from  tbl_employee_company_locations Left Join  tbl_employee ON tbl_employee.id=tbl_employee_company_locations.employee_id where CASE WHEN `tbl_company`.parent_id = 0 then (`tbl_employee_company_locations`.company_id  = tbl_company.id) && (`tbl_employee_company_locations`.location_id  = 0) ELSE `tbl_employee_company_locations`.location_id  = tbl_company.id END  && tbl_employee.status=1) as employees_count,
                (select count(*) from tbl_company as locations where (locations.parent_id = tbl_company.id || locations.id = tbl_company.id) && locations.status= 1) as locations_count'))->where($where_data);

    }

    public static function getCompaniesByAdminUser($user_id) {
        $getCompany = EmployeeCompanyLocationsModel::where(['employee_id' => $user_id])->get();
        $isParent = 0;
        $isLocations = array();
        foreach ($getCompany as $value) {
            if ($value->location_id == 0) {
                $isParent = $value->company_id;
            } else {
                array_push($isLocations, $value->location_id);
            }
        }

        return [
            'isParent' => $isParent,
            'isLocations' => $isLocations,
        ];
    }

    public static function getCompaniesNameOfUser($user_id) {
        $getCompanyIds = EmployeeCompanyLocationsModel::where('employee_id', $user_id)->get();
        $companyIds = array();
        foreach ($companyIds as $value) {
            if ($value->location_id == 0) {
                $companyIds[$key] = $value->company_id;
            } else {
                $companyIds[$key] = $value->location_id;
            }
        }
        $company = '';
        $getCompanyNames = CompanyModel::whereIn('id', $companyIds)->select('name')->get();
        if ($getCompanyNames->count() > 0) {
            $companyName = array_column($getCompanyNames->toArray(), 'name');
            $company = implode(",", $companyName);
        }

        return $company;
    }

    public static function getAllCompanyIdsOfUsersAccoringUserRole($user_id, $role_id) {
        $status = config('constant.status');
        $companyIds = array();
        switch ($role_id) {
            case $status['user_role']['company-admin']:

                $getCompany = EmployeeCompanyLocationsModel::where(['employee_id' => $user_id])->orderBy('location_id', 'asc')->first();
                if ($getCompany->location_id == 0) {
                    $getCompanies = EmployeeCompanyLocationsModel::where('company_id', $getCompany->company_id)->select('location_id')->get()->toArray();
                    $companyIds = array_column($getCompanies, 'id');
                    array_push($companyIds, $getCompany->company_id);
                } else {
                    array_push($companyIds, $getCompany->location_id);
                }
                break;
            case $status['user_role']['manager']:
                $getCompany = EmployeeCompanyLocationsModel::where([
                    'employee_id' => $user_id,
                    'location_id' => 0,
                ])->first();
                if ($getCompany != NULL) {
                    $getCompanies = EmployeeCompanyLocationsModel::where(['company_id' => $getCompany->company_id])->where('location_id', '!=', 0)->select('location_id as id')->groupBy('location_id')->get()->toArray();

                    $companyIds = array_column($getCompanies, 'id');
                    array_push($companyIds, $getCompany->company_id);
                } else {
                    $getCompanies = EmployeeCompanyLocationsModel::where(['employee_id' => $user_id])->select('location_id')->groupBy('location_id')->get()->toArray();

                    $companyIds = array_column($getCompanies, 'location_id');
                }
                break;
            default:
                $companyIds = $request->company_id;
                break;
        }

        return $companyIds;
    }

    public function location() {
        return $this->hasOne('App\Models\ZipCodeModel', 'zip_code', 'company_zip');
    }

    public function courses() {
        return $this->belongsToMany('App\Models\CourseModel', 'tbl_company_courses', 'company_id', 'course_id')->where('tbl_course.status', 1)->orderby('tbl_course.name', 'asc');
    }

    public function coursefolders()
    {
        return $this->belongsToMany('App\Models\CourseFolderModel','tbl_company_coursefolders', 'company_id','folder_id')->where('tbl_course_folder.folder_status',1)->orderby('tbl_course_folder.folder_name','asc');
    }

    public function activeCourses() {
        return $this->belongsToMany('App\Models\CourseModel', 'tbl_company_courses', 'company_id', 'course_id')->where('course_status', 1);
    }

    public function employees() {
        return $this->belongsToMany('App\Models\EmployeeModel', 'tbl_employee_company_locations', 'location_id', 'employee_id');
    }

    public function locations() {

    }
}
