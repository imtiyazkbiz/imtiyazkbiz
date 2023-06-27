<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class CourseCertificateModel extends Model
{
    protected $table="tbl_certificate";

    public function course()
    { 
        return $this->belongsToMany('App\Models\CourseModel','tbl_course_certificate', 'certificate_id','course_id');
    }
    public function courseCount()
    {
        return $this->belongsToMany('App\Models\CourseModel');
    }
    public function employee()
    {
        return $this->belongsToMany('App\Models\CourseModel','tbl_course_certificate', 'certificate_id','course_id');
    }
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */

     public static function getCertificateData($course, $employee) {
            $date = Carbon::now('UTC');
          
            $user_id = $employee->user_id;
            $certificate_no = $course->certificate_no;
            $date = Carbon::now('UTC');          
            $completed_date = Carbon::parse($course->employee_course_date_completed)
                                ->format('m-d-Y');  
            $expiration_date = Carbon::parse($course->certificate_expiration_date)                              
                                ->format('m-d-Y'); 
             $dob = $employee->dob ? Carbon::parse($employee->dob)->format('m-d-Y')  : 'N/A';  
                                                           
        switch ($course->template_name) {
            case 'NY ATAP':
                $companyId = CompanyModel::getCompantIdByEmployeeId($user_id);                
                $company = CompanyModel::where('id', $companyId)->select('name')->first();
                $data = [    
                        'employee_first_name' => $employee->first_name,
                        'employee_last_name' => $employee->last_name,
                        'name_of_course' => $course->course_name,
                        'date_of_birth' => $dob,
                        'student_home_address' => $employee->address,
                        'city_state_zip' => $employee->city.'/'.$employee->state.'/'.$employee->zipcode,
                        'current_employer' => $company->name,
                        'completion_date'=> $completed_date,
                        'expiration_date'=> $expiration_date,
                        'certificate_no' => $certificate_no, 
                        'signature_title_1'=>$course->signature_title_1,
                        'signature_title_2'=>$course->signature_title_2,  
                        'text'  =>    $course->custom_text,                         
                    
                    ];
                break;
            case 'Reg Food Handler':
                $data = [   
                    'employee_first_name' => $employee->first_name,
                    'employee_last_name' => $employee->last_name,
                    'name_of_course' => $course->course_name,
                    'completion_date'=> $completed_date,
                    'expiration_date'=> $expiration_date,
                    'date_of_birth' => $dob,
                    'certificate_no' => $certificate_no,                        
                    'signature_title_1'=>$course->signature_title_1,
                    'signature_title_2'=>$course->signature_title_2,
                
                ];
                break;
            case 'ANSI Food Handler':
                $companyId = CompanyModel::getCompantIdByEmployeeId($user_id);
                $company = CompanyModel::where('id', $companyId)->select('logo')->first();            
                $data = [         
                    'employee_first_name' => $employee->first_name,
                    'employee_last_name' => $employee->last_name,
                    'name_of_course' => $course->course_name,
                    'completion_date' => $completed_date,
                    'expiration_date' => $expiration_date,
                    'date_of_birth' => $dob,
                    'certificate_no' => $certificate_no,                             
                    'signature_title_1' => $course->signature_title_1,
                    'signature_title_2' => $course->signature_title_2,
                  //  'logo' => $company->logo
                ];
                break;
            case 'Generic':
                $data = [   
                    'employee_first_name' => $employee->first_name,
                    'employee_last_name' => $employee->last_name,
                    'name_of_course' => $course->course_name,                  
                    'completion_date'=> $completed_date,
                    'expiration_date'=> $expiration_date,
                    'certificate_no' => $certificate_no,         
                    'signature_title_1'=>$course->signature_title_1,
                    'signature_title_2'=>$course->signature_title_2,
                    'text'  =>    $course->custom_text,                
                ];
                break;
            case 'Florida Food Handler':
                $data = [
                    'employee_first_name' => $employee->first_name,
                    'employee_last_name' => $employee->last_name,
                    'name_of_course' => $course->course_name,
                    'completion_date'=> $completed_date,
                    'expiration_date'=> $expiration_date,
                    'date_of_birth' => $dob,
                    'certificate_no' => $certificate_no, 
                    'signature_title_1'=>$course->signature_title_1,
                    'signature_title_2'=>$course->signature_title_2,                      
                ];
                break;            
            default:           
            $data = [
                'title' =>$course->course_name,
                'text'=>$course->custom_text,          
                'employee_name'=>$employee->full_name,
                'signature_title_1'=>$course->signature_title_1,
                'signature_title_2'=>$course->signature_title_2,
                'certificate_no' => $certificate_no,
                'completion_date'=> $completed_date,
                'expiration_date'=> $expiration_date 
                ];
                break;
        }
        
        return $data;
     }

}
