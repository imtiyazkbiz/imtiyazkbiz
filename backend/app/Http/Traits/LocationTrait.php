<?php
namespace App\Http\Traits;
use App\Models\CompanyModel;
use App\Models\CourseCertificateModel;
use App\Models\CourseModel;
use App\Models\CourseResourceModel;
use App\Models\EmployeeCertificateModel;
use App\Models\EmployeeCoursesModel;
use App\Models\EmployeeModel;
use App\Models\CompanyCoursesModel;
use App\Models\ZipCodeModel;
use Stripe\Terminal\Location;

trait LocationTrait {
  public function getLocationCourses($location_id)
  {
      $data=[];
      $total=CompanyCoursesModel::where([['company_id',$location_id],['company_course_status',1]])->count();
      $courses=CompanyCoursesModel::where([['company_id',$location_id],['company_course_status',1]])->get();
      $location_name=CompanyModel::find($location_id);
      for ($i=0;$i<count($courses); $i++){
          $course_id=$courses[$i]->course_id;
          $course_info=CourseModel::find($course_id);
          $course_certificate=CourseCertificateModel::where('id',$course_id)->first();
          $course_resource=CourseResourceModel::leftjoin('tbl_resources','tbl_resources.id','=','tbl_course_resource.resource_id')->where('course_id',$course_id)->get();
          $location_count=CompanyCoursesModel::where('course_id',$course_id)->count();
          $employee_count=EmployeeCoursesModel::where('course_id',$course_id)->count();
          $courses[$i]['info']=$course_info;
          $courses[$i]['certificate']=$course_certificate;
          $courses[$i]['location_count']=$location_count;
          $courses[$i]['employee_count']=$employee_count;
          $courses[$i]['course_resource']=$course_resource;
      }
      $data['total']=$total;
      $data['courses']=$courses;
      $data['location_name']=$location_name->name;
      return $data;
  }

}
