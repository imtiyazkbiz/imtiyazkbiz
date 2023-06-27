<?php
namespace App\Http\Traits;
use App\Models\CompanyCoursesModel;
use App\Models\CompanyModel;
use App\Models\CourseCertificateModel;
use App\Models\CourseModel;
use App\Models\CourseResourceModel;
use App\Models\EmployeeCoursesModel;
use App\Models\EmployeeModel;
use App\Models\ZipCodeModel;
use App\Models\EmployeeCourseAttemptsModel;
use App\Models\CourseFolderAssignModel;
use App\Models\CourseRelateCertificate;
use App\Models\EmployeeCompanyLocationsModel;
use App\Models\EmployeeCourseFolderModel;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Auth;

trait CompanyTrait {
  public  function companyZip($zip_code,$city,$state)
  {
      $check_zip_code = ZipCodeModel::where('zip_code',$zip_code)->count();
      if ($check_zip_code==0) {
          $code = new ZipCodeModel;
          $code->zip_code = $zip_code;
          $code->city = $city;
          $code->state = $state;
          $code->save();
      }
  }


  public function companyAdmin($username,$first_name,$last_name,$password,$role,$employee_id,$user_type,$phone)
  {
      $token='Bearer'.sha1(time());
      $user = new EmployeeModel;
      $user->user_name=$username;
      $user->first_name=$first_name;
      $user->last_name=$last_name;
      $user->full_name=$first_name." ".$last_name;
      $user->email=$username;
      $user->type=$user_type;
      $user->access_code=$password;
      $user->password=md5($password);
      $user->api_token=$token;
      $user->role_id=$role;
      $user->phone_num=$phone;
      $user->save();
      return $user_id=$user->id;
  }
 
  public function getCompanyCourses($company_id,$search,$courseStatus)
  {
      $data=[];
      $where_data=[];
      $where=[];
      $user = Auth::user();      
      if (!empty($courseStatus)){
        $status=$courseStatus;
        if ($status=="Active"){
            $status=1;
        }
        elseif ($status=="Inactive"){
            $status=0;
        }
        array_push($where_data, ['company_course_status',$status]);
    }
    if ($search){
        $search = explode(" ", $search);
        foreach ($search as $key => $name)
        {
            $where[]=['tbl_course.name', 'like', '%' . $name . '%'];
        }
    }
        array_push($where_data, ['company_id', $company_id]);
      $total=CompanyCoursesModel::leftJoin('tbl_course', 'tbl_course.id', '=', 'tbl_company_courses.course_id')->where($where_data)->where($where)->count();
      $courses=CompanyCoursesModel::leftJoin('tbl_course', 'tbl_course.id', '=', 'tbl_company_courses.course_id')->where($where_data)->where($where)->orderBy('tbl_course.name', 'asc')->get();
      $company_name=CompanyModel::find($company_id);
      for ($i=0;$i<count($courses); $i++){
          $course_id=$courses[$i]->course_id; 
          $course_info=CourseModel::find($course_id);
          $course_certificate=CourseRelateCertificate::where('course_id',$course_id)->first();
          $course_resource=CourseResourceModel::leftjoin('tbl_resources','tbl_resources.id','=','tbl_course_resource.resource_id')->where('course_id',$course_id)->get();
          $location_count=CompanyCoursesModel::where('course_id',$course_id)->count();
          //$employee_count=EmployeeCoursesModel::where('course_id',$course_id)->count();
          $course_employee=EmployeeCoursesModel::leftJoin('tbl_employee_company_locations','tbl_employee_company_locations.employee_id', '=', 'tbl_employee_courses.employee_id')
          ->where('course_id', $course_id)
           ->groupby('tbl_employee_courses.employee_id');
          $isCompany =CompanyModel::getCompaniesByAdminUser($user->id);
                $company_id=[];
                 $where_data1="";
                    if ($isCompany['isParent'] != 0)
                    {
                        if (is_array($isCompany['isParent']))
                        {
                            $company_id = $isCompany['isParent'];
                        }
                        else
                        {
                            $company_id = array(
                                $isCompany['isParent']
                            );
                        }
                           $where_data1='tbl_employee_company_locations.company_id';
                    }
                    else
                    {
                        $company_id = $isCompany['isLocations'];
                           $where_data1='tbl_employee_company_locations.location_id';
                       
                    }
                    switch ($user->role_id) {
                        case 2:
                            $course_employee->whereIn($where_data1, $company_id);
                        break;
                        case 3:
                            $course_employee->whereIn($where_data1, $company_id);
                            break;  
                        default:
                            $course_employee->whereIn($where_data1, $company_id);
                        break;
                    }

          $employee_data=[];
          $get_course_employee = $course_employee->get()->toArray();
          for ($j=0; $j<count($get_course_employee); $j++){
              $employee_data[]=EmployeeModel::find($get_course_employee[$j]['employee_id']);
          }
          $courses[$i]['info']=$course_info;
          $courses[$i]['certificate']=$course_certificate;
          $courses[$i]['location_count']=$location_count;
          $courses[$i]['employee_count']=count($get_course_employee);
          $courses[$i]['course_resource']=$course_resource;
          $courses[$i]['course_employee']=$employee_data;
      }

      $data['total']=$total;
      $data['courses']=$courses;
      $data['company_name']=$company_name;
  
      return $data;
  }
  public function getCompanyCourseFolders($company_id,$search,$courseStatus)
  {
      $data=[];
      $where=[];
      $user = Auth::user();      
     
    if ($search){
        $search = explode(" ", $search);
        foreach ($search as $key => $name)
        {
            $where[]=['tbl_course_folder.folder_name', 'like', '%' . $name . '%'];
        }
    }
      $coursefolders=DB::table('tbl_company_coursefolders')
       ->select('tbl_company_coursefolders.*','tbl_course_folder.*')
       ->leftJoin('tbl_course_folder', 'tbl_course_folder.id', '=', 'tbl_company_coursefolders.folder_id')
       ->where($where)
       ->where('tbl_company_coursefolders.company_id',$company_id)
       ->groupby('tbl_company_coursefolders.folder_id')
       ->orderBy('tbl_course_folder.folder_name', 'asc')
       ->get();
     foreach($coursefolders as $coursefolder => $value){
        $folder_id=$value->folder_id; 
        $course_employee=EmployeeCourseFolderModel::leftJoin('tbl_employee_company_locations','tbl_employee_company_locations.employee_id', '=', 'tbl_employee_coursefolders.employee_id')
         ->where('folder_id', $folder_id)
         ->groupby('tbl_employee_coursefolders.employee_id');
            $isCompany =CompanyModel::getCompaniesByAdminUser($user->id);
              $company_id=[];
              $where_data1="";
                  if ($isCompany['isParent'] != 0)
                  {
                      if (is_array($isCompany['isParent']))
                      {
                          $company_id = $isCompany['isParent'];
                      }
                      else
                      {
                          $company_id = array(
                              $isCompany['isParent']
                          );
                      }

                 $where_data1='tbl_employee_company_locations.company_id';
                  }
                  else
                  {
                      $company_id = $isCompany['isLocations'];
                    $where_data1='tbl_employee_company_locations.location_id'; 
                  }
                  switch ($user->role_id) {
                      case 2:
                          $course_employee->whereIn($where_data1, $company_id);
                      break;
                      case 3:
                          $course_employee->whereIn($where_data1, $company_id);
                          break;  
                      default:
                          $course_employee->whereIn($where_data1, $company_id);
                      break;
                  }
                 
                  $get_course_employee = $course_employee->get(); 
                  $value->employee_count=count($get_course_employee);
     }
      $data['total']=count($coursefolders);
      $data['coursefolders']=$coursefolders;
     
      return $data;
  }

    public function allCertificates($company_id,$status)
    {
        $data=[];
        $courses=CompanyCoursesModel::where('company_id',$company_id)->get();
        $total=CompanyCoursesModel::where('company_id',$company_id)->count();
        for ($i=0; $i<count($courses); $i++){
            $course_id=$courses[$i]->course_id;
            $course=CourseModel::find($course_id);
            if($status=="Employee"){
                $employee_count=EmployeeCoursesModel::where([['employee_course_status',1],['company_id',$company_id],['course_id',$course_id],['employee_course_status',1]])->count();
            }elseif($status=="Company"){
                $employee_count=EmployeeCourseAttemptsModel::where([['user_id',$company_id],['course_id',$course_id]])->count();
            }
            $courses[$i]->employee_count=$employee_count;
            $courses[$i]->course_name=$course->name;
        }
        $data['courses']=$courses;
        $data['total']=$total;
        return $data;
    }
    public function assignEmployeeCourses($location_id,$employee_id,$company_id){
        $courses=CompanyCoursesModel::where('company_id',$location_id)->get();
       
        for ($i=0;$i<count($courses); $i++){
            $employee = new EmployeeCoursesModel();
            $employee->employee_id=$employee_id;
            $employee->company_id=$company_id;
            $employee->course_id=$courses[$i]['course_id'];
            $employee->employee_course_due_date=$courses[$i]['due_date'];
            $employee->employee_course_date_assigned=date('Y-m-d');
            $employee->save();

        }
    }
    public function assigenEmployeeCourse($employee_id,$company_id,$employee_courses,$employee_type)
    {
      
      foreach($employee_courses as $employee_course){
            if ($employee_type == 'employee' || $employee_type == 'Employee') {
                $courseDueDate = CourseModel::select('employees_days_to_complete as due_days')->where('id', $employee_course)->first();
            } else {
                $courseDueDate = CourseModel::select('managers_days_to_complete as due_days')->where('id', $employee_course)->first();
            }            
            $due_days = (int)$courseDueDate->due_days;
            $course_due_date = Carbon::now('UTC')->addDays($due_days)->format('Y-m-d');  

        $employee = new EmployeeCoursesModel();
        $employee->employee_id=$employee_id;
        $employee->company_id=$company_id;
        $employee->course_id=$employee_course;
        $employee->employee_course_due_date=$course_due_date;
        $employee->employee_course_date_assigned=date('Y-m-d');
        $employee->save();
      }
    }
    public function assigenEmployeeCourseFolder($employee_id,$company_id,$employee_coursefolders,$employee_type){
        if($employee_coursefolders) {      
            $folder_id = $employee_coursefolders;
            $getAllCoursesOfFolder1=CourseFolderAssignModel::where('course_folder_id',$folder_id)->get();
            $id1=array();
            foreach($getAllCoursesOfFolder1 as $courses){
                array_push($id1,$courses->course_id);
            }
            $ids=implode(",",$id1);
            $getAllCoursesOfFolder=CourseFolderAssignModel::select('tbl_course_coursefolder.*','tbl_course.next_course')
            ->leftjoin('tbl_course','tbl_course.id','=','tbl_course_coursefolder.course_id')
            ->whereRaw('tbl_course_coursefolder.course_id not IN (SELECT next_course FROM tbl_course WHERE id IN ('.$ids.'))')
            ->where('course_folder_id',$folder_id)->get();
           
            $id=array();
            foreach($getAllCoursesOfFolder as $courses){
                array_push($id,$courses->course_id);
            }
            $data = new EmployeeCourseFolderModel();
            $data->employee_id= $employee_id;
            $data->folder_id = $folder_id;
            $data->save();
            $this->assigenEmployeeCourse($employee_id,$company_id,$id,$employee_type);  
        }
    }
    public function assignCourse($employee_id,$employee_courses, $employee_type){
     foreach($employee_courses as $employee_course){
         if ($employee_type == 'employee') {
                $courseDueDate = CourseModel::select('employees_days_to_complete as due_days')->where('id', $employee_course)->first();
            } else {
                $courseDueDate = CourseModel::select('managers_days_to_complete as due_days')->where('id', $employee_course)->first();
            }            
            $due_days = (int)$courseDueDate->due_days;
            $course_due_date = Carbon::now('UTC')->addDays($due_days)->format('Y-m-d');  

        $employee = new EmployeeCoursesModel();
        $employee->employee_id=$employee_id;
        $employee->course_id=$employee_course;
        $employee->employee_course_due_date=$course_due_date;
        $employee->employee_course_date_assigned=date('Y-m-d');
        $employee->save();
      }
    }
   public function assignCourseFolder($employee_id,$employee_courses, $employee_type){
    foreach($employee_courses as $employee_course){
        $folder_id = $employee_course;
        $getAllCoursesOfFolder1=CourseFolderAssignModel::where('course_folder_id',$folder_id)->get();
        $id1=array();
        foreach($getAllCoursesOfFolder1 as $courses){
            array_push($id1,$courses->course_id);
        }
        $ids=implode(",",$id1);
        $getAllCoursesOfFolder=CourseFolderAssignModel::select('tbl_course_coursefolder.*','tbl_course.next_course')
        ->leftjoin('tbl_course','tbl_course.id','=','tbl_course_coursefolder.course_id')
        ->whereRaw('tbl_course_coursefolder.course_id not IN (SELECT next_course FROM tbl_course WHERE id IN ('.$ids.'))')
        ->where('course_folder_id',$folder_id)->get();
       
        $id=array();
        foreach($getAllCoursesOfFolder as $courses){
            array_push($id,$courses->course_id);
        }
        $data = new EmployeeCourseFolderModel();
        $data->employee_id= $employee_id;
        $data->folder_id = $folder_id;
        $data->save();
        $this->assigenEmployeeCourse($employee_id,$company_id=0,$id,$employee_type);  

    }
    }
}
