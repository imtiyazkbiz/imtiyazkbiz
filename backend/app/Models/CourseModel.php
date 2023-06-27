<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

//use App\Models\CourseLessonModel;

class CourseModel extends Model {
    protected $table = "tbl_course";

    public static function getCourseReport($data, $reportType) {
        $result = array();
        foreach ($data as $key => $value) {
            $result[$key]['Company Name'] = ucfirst($value->comapny_name);
            if ($value->location_id != 0) {
                $result[$key]['Company Name'] = ucfirst($value->child_company_name);
            }
            $result[$key]['First Name'] = ucfirst($value->first_name);
            $result[$key]['Last Name'] = ucfirst($value->last_name);
            $result[$key]['Email'] = $value->email;
            $result[$key]['Username'] = $value->user_name;
            $result[$key]['Role'] = ($value->type == 'location_manager') ? 'Manager' : (($value->type == 'company-admin') ? "Admin" : ucfirst($value->type));
            $result[$key]['Job Title'] = $value->job_title;
            $result[$key]['Course Name'] = $value->course_name;
            //  $result[$key]['Expires'] = date('m/d/Y',strtotime($value->employee_course_due_date));
            switch ($reportType) {
                case 'open_course':
                    $result[$key]['Expires'] = date('m/d/Y', strtotime($value->employee_course_due_date));
                    break;
                case 'non_compliance':
                    $result[$key]['expired'] = date('m/d/Y', strtotime($value->employee_course_due_date));
                    break;
                case 'compliance':
                    $result[$key]['Date Completed'] = date('m/d/Y', strtotime($value->employee_course_date_completed));
                    break;
                default:
                    $result[$key]['Expires'] = date('m/d/Y', strtotime($value->employee_course_due_date));
                    break;
            }
        }

        return $result;
    }

    public static function assignCoursetoCompany($courses, $company_id) {
        try {

            foreach ($courses as $id) {
                $isCompanyCourse = DB::table('tbl_company_courses')->where([
                    'course_id' => $id,
                    'company_id' => $company_id,
                ])->first();
                if ($isCompanyCourse == NULL) {
                    DB::table('tbl_company_courses')->insert([
                        'company_id' => $company_id,
                        'course_id' => $id,
                        'company_course_status' => 1,
                        'created_at' => Carbon::now('UTC'),
                    ]);
                }
            }

            return TRUE;
        } catch (Exception $th) {

            return FALSE;
        }
    }

    public static function assignCourseFoldertoCompany($folders, $company_id) {
        try {

            foreach ($folders as $id) {
                $isCompanyCourseFolder = DB::table('tbl_company_coursefolders')->where([
                    'folder_id' => $id,
                    'company_id' => $company_id,
                ])->first();
                if ($isCompanyCourseFolder == NULL) {
                    DB::table('tbl_company_coursefolders')->insert([
                        'company_id' => $company_id,
                        'folder_id' => $id,
                        'course_folder_status' => 1,
                        'created_at' => Carbon::now('UTC'),
                    ]);
                }
            }

            return TRUE;
        } catch (Exception $th) {

            return FALSE;
        }
    }

    public static function getCourseSurveyDetails($course_id, $survey_id) {
        return DB::table('tbl_course_survey')->leftJoin('tbl_course', 'tbl_course.id', '=', 'tbl_course_survey.course_id')->leftJoin('tbl_survey', 'tbl_survey.id', '=', 'tbl_course_survey.survey_id')->where([
            'tbl_course_survey.course_id' => $course_id,
            'tbl_course_survey.survey_id' => $survey_id,
        ])->select('tbl_course.name as course_name', 'tbl_survey.name as survey_name')->first();
    }

    public static function getCoursePretestDetails($course_id, $pretest_id) {
        return DB::table('tbl_pre_test')->leftJoin('tbl_course', 'tbl_course.id', '=', 'tbl_pre_test.course_id')->where([
            'tbl_pre_test.course_id' => $course_id,
            'tbl_pre_test.id' => $pretest_id,
        ])->select('tbl_course.name as course_name', 'tbl_pre_test.name as pretest_name')->first();
    }

    public static function getLessonOfCourse($user_id, $course_id, $lessons) {
        $result = array();
        foreach ($lessons as $key => $lessonsValue) {

            $result[$key] = $lessonsValue;

            $lesson_attempts = $lessonsValue->allowed_attempts;
            $attempts = EmployeeCourseAttemptsModel::select('attempts', 'pass_fail')->where([
                [
                    'user_id',
                    $user_id,
                ],
                [
                    'course_id',
                    $course_id,
                ],
                [
                    'lesson_test_id',
                    $lessonsValue->id,
                ],
                [
                    'lesson_test',
                    'lesson',
                ],
            ])->get();

            if ($attempts->count() > 0) {
                $result[$key]->remaining_attempts = $lesson_attempts - $attempts[0]->attempts;
                $result[$key]->result = $attempts[0]->pass_fail;
            } else {
                $result[$key]->remaining_attempts = $lesson_attempts;
                $result[$key]->result = 0;
            }

            $questions = CourseQuizQuestionModel::where([
                [
                    'parent_id',
                    $lessonsValue->id,
                ],
                [
                    'parent',
                    'lesson',
                ],
            ]);

            if ($lessonsValue->no_of_questions) {
                $questions->limit($lessonsValue->no_of_questions);
            }

            $questionArray = array();
            foreach ($questions->get() as $questionKey => $value) {
                $question_id = $value->id;

                $questionArray[$questionKey]['id'] = $value->id;
                $questionArray[$questionKey]['parent_id'] = $value->parent_id;
                $questionArray[$questionKey]['question'] = $value->question;
                $questionArray[$questionKey]['allowed_attempts'] = $value->allowed_attempts;
                $questionArray[$questionKey]['parent'] = $value->parent;
                $questionArray[$questionKey]['status'] = $value->status;
                $questionArray[$questionKey]['created_at'] = $value->created_at;
                $questionArray[$questionKey]['updated_at'] = $value->updated_at;

                $answers = CourseQuizQuestionAnswerModel::where('course_quiz_question_id', $question_id)->get();
                $questionArray[$questionKey]['answers'] = $answers;
            }

            $result[$key]['questions'] = $questionArray;

        }

        return $result;

    }

    public static function getTestOfCourse($user_id, $course_id, $tests, $allow_attempts) {
        $result = array();
        foreach ($tests as $key => $testValue) {
            $result[$key] = $testValue;
            $attempts = EmployeeCourseAttemptsModel::select('attempts', 'pass_fail')->where([
                [
                    'user_id',
                    $user_id,
                ],
                [
                    'course_id',
                    $course_id,
                ],
                [
                    'lesson_test_id',
                    $testValue->id,
                ],
                [
                    'lesson_test',
                    'test',
                ],
            ])->get();

            if ($attempts->count() > 0) {
                $result[$key]->remaining_attempts = $allow_attempts - $attempts[0]->attempts;
                $result[$key]->result = $attempts[0]->pass_fail;
            } else {
                $result[$key]->remaining_attempts = $allow_attempts;
                $result[$key]->result = 0;
            }

            $questions = CourseQuizQuestionModel::where([
                [
                    'parent_id',
                    $testValue->id,
                ],
                [
                    'parent',
                    'test',
                ],
            ]);

            if ($testValue->course_no_of_questions) {
                $questions->limit($testValue->course_no_of_questions);
            }

            $questionArray = array();
            foreach ($questions->get() as $questionKey => $value) {
                $question_id = $value->id;
                $questionArray[$questionKey]['id'] = $value->id;
                $questionArray[$questionKey]['parent_id'] = $value->parent_id;
                $questionArray[$questionKey]['question'] = $value->question;
                $questionArray[$questionKey]['allowed_attempts'] = $value->allowed_attempts;
                $questionArray[$questionKey]['parent'] = $value->parent;
                $questionArray[$questionKey]['status'] = $value->status;
                $questionArray[$questionKey]['created_at'] = $value->created_at;
                $questionArray[$questionKey]['updated_at'] = $value->updated_at;

                $answers = CourseQuizQuestionAnswerModel::where('course_quiz_question_id', $question_id)->get();
                $questionArray[$questionKey]['answers'] = $answers;
            }

            $result[$key]['questions'] = $questionArray;

        }

        return $result;
    }

    public function certificates() {
        return $this->belongsToMany('App\Models\CourseCertificateModel', 'tbl_course_certificate', 'course_id', 'certificate_id');

    }

    public function resources() {
        return $this->belongsToMany('App\Models\Resource', 'tbl_course_resource', 'course_id', 'resource_id');
    }

    public function employees() {
        return $this->belongsToMany('App\Models\EmployeeModel', 'tbl_employee_courses', 'course_id', 'employee_id');
    }

    public function locations() {

    }

    public function lessons() {
        return $this->hasMany('App\Models\CourseLessonModel', 'course_id')->orderby('order', 'asc');
    }

    public function tests() {
        return $this->hasMany('App\Models\CourseTestModel', 'course_id');
    }

    public function companies() {
        return $this->belongsToMany('App\Models\CompanyModel', 'tbl_company_courses', 'course_id', 'company_id');
    }

    public function passedEmployees() {
        return $this->belongsToMany('App\Models\EmployeeModel', 'tbl_employee_courses', 'course_id', 'employee_id')->where('tbl_employee_courses.employee_course_status', 1);
    }

    public function sendEmailToAssignCourseEmployee($employees, $courses) {
        $courses = $request->courses;
        $employees = $request->employees;
        $employeess = [];
        $coursess = [];
        $course_names = [];
        $employee_emails = [];

        foreach ($employees as $employee_id) {
            array_push($employeess, $employee_id);
        }
        foreach ($courses as $course_id) {
            array_push($coursess, $course_id);
        }
        if (!empty($coursess) && !empty($employeess)) {
            $course_array = array();
            if (is_array($coursess)) {
                $course_array = $coursess;
            } else {
                array_push($course_array, $coursess);
            }

            $courses = CourseModel::whereIn('id', $course_array)->get();
            $employeesData = EmployeeModel::whereIn('id', $employeess)->get();
            if ($courses->count() > 0 && $employeesData->count() > 0) {
                $course_names = array();
                foreach ($courses as $course_name) {
                    $name = $course_name->name;
                    array_push($course_names, $name);
                }
                foreach ($employeesData as $value) {
                    if ($value->type == 'individual') {
                        $content = 'You have selected to view the following course(s):';
                    } else {
                        $isCompany = EmployeeCompanyLocationsModel::where('employee_id', $value->id)->select('company_id', 'location_id')->first();
                        $companyName = "";
                        if ($isCompany != NULL) {
                            if ($isCompany->location_id == 0) {
                                $getCompany = CompanyModel::where('id', $isCompany->company_id)->first();
                            } else {
                                $getCompany = CompanyModel::where('id', $isCompany->location_id)->first();
                            }
                            if ($getCompany != NULL) {
                                $companyName = $getCompany->name;
                            }
                        }
                        $content = 'Your employer, ' . ucwords($companyName) . ', has assigned you the following course(s):';
                    }

                    $data = array(
                        'courses' => $course_names,
                        'content' => $content,
                        'first_name' => $value->first_name,
                        'last_name' => $value->last_name,
                    );
                    if ($value->email != '') {
                        CommonTrait::sendEmailToAssginCourseEmployee($data, $value->email, $value->id);
                    }
                }
            }
        }
        //  $dataCourses=CourseModel::whereIn('id',$coursess)->get();
        //  foreach($dataCourses as $course_name){
        //    $name=$course_name->name;  
        //    array_push($course_names, $name);
        //  }
        //     $data = array(
        //         'courses' => $course_names,
        //     );

        //     Mail::send('course_assigned', $data, function ($message) use ($employee_emails)
        //     {
        //         $message->to($employee_emails)->subject('Train 321 - Course(s) Now Available!');
        //         // from is same email we set in .env file
        //         $message->from(env('MAIL_FROM_ADDRESS'), env('MAIL_FROM_NAME'));
        //     });

    }

}
