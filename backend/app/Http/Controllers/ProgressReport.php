<?php
namespace App\Http\Controllers;
use App\Models\EmployeeModel;
use http\Env\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use Intervention\Image\Facades\Image;
use App\Models\EmployeeCompanyLocationsModel;
use App\Models\EmployeeCoursesModel;
use App\Models\EmailLogsModel;
use App\Http\Traits\CommonTrait;
use Illuminate\Support\Facades\DB;
use App\Models\CompanyModel;
use Validator;
use Auth;

class ProgressReport extends Controller
{

    private $status;
    private $sucess;
    private $fail;

    /**
     * Construct function
     */
    public function __construct()
    {
        $this->status = config('constant.status');
        $this->sucess = config('constant.success');
        $this->fail = config('constant.fail');
    }
    public function generateProgressReport()
    {
        $result= "No Admin / Managers with active progress report found.";
        $user = Auth::user();
        $getParentCompany= EmployeeCompanyLocationsModel::where('employee_id',$user->id)->first();
        $getAdminManagers = EmployeeModel::leftjoin('tbl_employee_company_locations','tbl_employee_company_locations.employee_id','=','tbl_employee.id')
        ->where('tbl_employee_company_locations.company_id',$getParentCompany->company_id)
        ->where('tbl_employee.status', 1)
        ->where('tbl_employee.progress_status', 1)
        ->where(function($query){
            return $query
            ->where('tbl_employee.role_id', $this->status['user_role']['company-admin'])
            ->orWhere('tbl_employee.role_id', $this->status['user_role']['manager']);
        })->groupBy('tbl_employee_company_locations.employee_id')->get();

        $finalResult=[];
        if(count($getAdminManagers) > 0){
            foreach ($getAdminManagers as $getAdminManager)
            {
                $result=self::getCompanies($getAdminManager);
                array_push($finalResult, $result);
            }
            return $finalResult;
        }else{
            return $finalResult;
        }
    }
    public function mondayProgressReport()
    {
        $result= "No Admin / Managers with active progress report.";
        $getAdminManagers = EmployeeModel::select('*', 'tbl_employee.id as employee_id')->where('status', 1)->where('progress_status', 1)
        ->where(function($query){
            return $query
            ->where('tbl_employee.role_id', $this->status['user_role']['company-admin'])
            ->orWhere('tbl_employee.role_id', $this->status['user_role']['manager']);
        })->get();
        if(count($getAdminManagers) > 0){
            foreach ($getAdminManagers as $getAdminManager)
            {
                $result= self::getCompanies($getAdminManager);
            }
        }else{
            return $result;
        }
    }
    public function getCompanies($user)
    {
        $company_name = self::getCompanyNameByUserId($user->employee_id);
        $companies = CompanyModel::getCompaniesByAdminUser($user->employee_id);
        $company_id=[];
        if ($companies['isParent'] != 0)
        {
            if (is_array($companies['isParent']))
            {
                $company_id = $companies['isParent'];
            }
            else
            {
                $company_id = array(
                    $companies['isParent']
                );
            }

            $getemployees = EmployeeCompanyLocationsModel::select('tbl_employee.id as employee_id', 'tbl_company.id as company_id', 'tbl_company.name as company_name')->leftjoin('tbl_employee', 'tbl_employee.id', '=', 'tbl_employee_company_locations.employee_id')
                ->leftjoin('tbl_company', 'tbl_company.id', '=', 'tbl_employee_company_locations.company_id')
                ->where('tbl_employee.status',1)
                ->whereIn('tbl_employee_company_locations.company_id', $company_id)->groupby('employee_id')->get();

        }
        else
        {
            $company_id = $companies['isLocations'];
            $getemployees = EmployeeCompanyLocationsModel::select('tbl_employee.id as employee_id', 'tbl_company.id as company_id', 'tbl_company.name as company_name')->leftjoin('tbl_employee', 'tbl_employee.id', '=', 'tbl_employee_company_locations.employee_id')
                ->leftjoin('tbl_company', 'tbl_company.id', '=', 'tbl_employee_company_locations.location_id')
                ->where('tbl_employee.status',1)
                ->whereIn('tbl_employee_company_locations.location_id', $company_id)->groupby('employee_id')->get();

        }
        $result1 = self::getEmployeesData($getemployees, $user->email, $user->employee_id , $company_name);
        return $result1;
    }
    public function getCompanyNameByUserId($user_id)
    {
     
        $getCompanyIds = EmployeeCompanyLocationsModel::select(DB::raw('group_concat(company_id) as company_id') , DB::raw('group_concat(location_id) as location_id') , 'employee_id')->where('employee_id', $user_id)
            ->get()->toArray();
        $company = [];
        foreach ($getCompanyIds as $company_ids)
        {
            
            $Company_ids = $company_ids['company_id'];
            $exloadingCompany = explode(",", $company_ids['company_id']);
            $Location_ids = explode(",", $company_ids['location_id']);
            if (count($Location_ids) > 1)
            {
                $company[] = $exloadingCompany[0];
            }
            else
            {
                if (in_array(0, $Location_ids))
                {
                    $company[] = $exloadingCompany[0];
                }
                else
                {
                    $company[] = $Location_ids[0];
                }
            }

        }
       
        $company = implode(",", $company);
        $getCompanyname = CompanyModel::where('id', $company)->get()
            ->toArray();

        return $getCompanyname[0]['name'];
    }
  
    public function SendProgressReport($data, $email, $employee_id, $company_name)
    {
        try
        {
            $data['company_name'] = $company_name;
            if (count($data['recent_completions']) > 0 || count($data['employee_course_due']) > 0)
            {
                if($email){
                    Mail::send('progress_report', $data, function ($message) use ($email)
                    {
                        $message->to($email)->subject('Progress Report');
                        $message->from(env('MAIL_FROM_ADDRESS') , env('MAIL_FROM_NAME'));
                    });
                    CommonTrait::emailLog("Progress Report", $email, $employee_id ); 
                 
                }else{
                }

            }
            else
            {
                return "No Completions and Course Due.";
            }
        }
        catch(Exception $th)
        {
            return response()->json(["message" => $th->getMessage() ], 422);
        }

    }
    public function getEmployeesData($getemployees, $email, $employee_id ,$company_name)
    {
        $today = date('Y-m-d');
        $next_date = date('Y-m-d', strtotime('+7 days'));
        $previous_date = date('Y-m-d', strtotime('-7 days'));
        $result = array();
        $employee_ids[] = 0;
        foreach ($getemployees as $employee)
        {
            $employee_ids[] = $employee->employee_id;
        }

        $result['recent_completions'] = EmployeeCoursesModel::select('tbl_employee.id as employee_id', 'tbl_employee.full_name', 'tbl_course.name', 'tbl_company.name as company_name', 'tbl_employee_courses.employee_course_date_completed')
            ->leftjoin('tbl_employee', 'tbl_employee.id', '=', 'tbl_employee_courses.employee_id')
            ->leftjoin('tbl_course', 'tbl_course.id', '=', 'tbl_employee_courses.course_id')
            ->leftjoin('tbl_company', 'tbl_company.id', '=', 'tbl_employee_courses.company_id')
            ->whereIn('tbl_employee_courses.employee_id', $employee_ids)->where('tbl_employee_courses.employee_course_status', 1)
            ->where('tbl_course.status', 1)
            ->whereBetween('employee_course_date_completed', [$previous_date, $today])
            ->whereNotNull('tbl_employee_courses.employee_course_date_completed')
            ->orderBy('tbl_employee_courses.employee_course_date_completed', 'desc')
            ->get()
            ->toArray();
        

        $result['employee_course_due'] = EmployeeCoursesModel::select('tbl_employee.id as employee_id', 'tbl_employee.full_name', 'tbl_course.name', 'tbl_company.name as company_name', 'tbl_employee_courses.employee_course_due_date')
            ->leftjoin('tbl_employee', 'tbl_employee.id', '=', 'tbl_employee_courses.employee_id')
            ->leftjoin('tbl_course', 'tbl_course.id', '=', 'tbl_employee_courses.course_id')
            ->leftjoin('tbl_company', 'tbl_company.id', '=', 'tbl_employee_courses.company_id')
            ->whereIn('tbl_employee_courses.employee_id', $employee_ids)
            ->whereBetween('employee_course_due_date', [$today, $next_date])
            ->where('tbl_employee_courses.employee_course_status', '!=', 1)
            ->where('tbl_course.status', 1)
            ->get()
            ->toArray();

           
          
        $result1=self::SendProgressReport($result, $email, $employee_id, $company_name);
        return $result1;
    }
}

?>
