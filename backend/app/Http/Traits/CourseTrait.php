<?php

namespace App\Http\Traits;

use App\Models\CompanyCoursesModel;
use App\Models\CompanyModel;
use App\Models\CourseCertificateModel;
use App\Models\CourseCertificatesModel;
use App\Models\CourseLessonGamificationModel;
use App\Models\CourseLessonModel;
use App\Models\CourseModel;
use App\Models\CourseQuizQuestionAnswerModel;
use App\Models\CourseQuizQuestionModel;
use App\Models\CourseRelateCertificate;
use App\Models\CourseResourceModel;
use App\Models\CourseTestModel;
use App\Models\EmployeeCertificateModel;
use App\Models\EmployeeCompanyLocationsModel;
use App\Models\EmployeeCourseAttemptsModel;
use App\Models\EmployeeCourseHistoryModel;
use App\Models\EmployeeCoursesModel;
use App\Models\EmployeeModel;
use Auth;
use Barryvdh\DomPDF\Facade as PDF;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\URL;

trait CourseTrait {

    public static function getCertificateId($certificate_id) {

        return '2020-100' . $certificate_id;
    }

    public function courseResources($resources, $id) {
        foreach ($resources as $resource) {
            $course_resource = new CourseResourceModel;
            $course_resource->course_id = $id;
            if ($resource['appear_status']) {
                $course_resource->appear_status = $resource['appear_status'];
            }
            $course_resource->course_resource_name = $resource['course_resource_name'];
            $course_resource->resource_type = $resource['course_resource_type'];
            if (!empty($resource['course_resource_url'])) {
                $course_resource->course_resource_url = $resource['course_resource_url'];
            } else {
                if (!empty($resource['course_resource'])) {
                    $file = $resource['course_resource'];
                    $fil_name = $file->getClientOriginalName();
                    $course_resource->resource_image = $fil_name;
                    $extension = $file->getClientOriginalExtension(); // getting image extension
                    $filename = time() . '.' . $extension;
                    $file->move('images/course/resources', $filename);
                    $file_path = URL::to('images/course/resources') . '/' . $filename;
                    $course_resource->course_resource = $file_path;
                } else {

                }
            }
            $course_resource->save();
        }
    }

    public function courseCompanies($companiess, $course_id) {

        if (is_array($companiess)) {
            $companies = $companiess;
        } else {
            $companies = array($companiess);
        }
        foreach ($companies as $company) {
            $courseCompay = CompanyCoursesModel::where([
                'course_id' => $course_id,
                'company_id' => $company,
            ])->first();
            if ($courseCompay == NULL) {
                $company_course = new CompanyCoursesModel();
                if (isset($company['id'])) {

                    $company_course->company_id = $company['id'];
                } else {
                    $company_course->company_id = $company;
                }

                $company_course->course_id = $course_id;
                $company_course->save();
            }
        }
    }

    public function courseCreateTest($test, $course_id) {
        $course_test = new CourseTestModel;
        $course_test->course_id = $course_id;
        $course_test->practice_test = $test['practice_test'];
        $course_test->enable_submit_button = $test['enable_submit_button'];
        $course_test->course_test_instruction = $test['course_test_instruction'];
        $course_test->course_test_pass_msg = $test['course_test_pass_msg'];
        $course_test->course_test_fail_msg = $test['course_test_fail_msg'];
        $course_test->course_no_of_questions = $test['course_no_of_questions'];
        $course_test->save();
        $course_test_id = $course_test->id;
        if (!empty($test['question_upload'])) {
            $upload_question = new CourseTestQuestionUploadModel;
            $upload_question->course_test_id = $course_test_id;
            $upload_question->column_question = $test['question_column'];
            $upload_question->column_option_1 = $test['opt1_column'];
            $upload_question->column_option_2 = $test['opt2_column'];
            if (!empty($test['opt3_column'])) {
                $upload_question->column_option_3 = $test['opt3_column'];
            }
            if (!empty($test['opt4_column'])) {
                $upload_question->column_option_4 = $test['opt4_column'];
            }
            $upload_question->column_correct_answer = $test['correct_answer_column'];
            $upload_question->column_question_status = $test['question_status_column'];
            $upload_question->test_question_url = $test['test_upload'];
            $upload_question->save();
        } else {
            if (!empty($test['questions'])) {
                $course_test_id = $course_test->id;
                $test_questions = $test['questions'];
                $parent = 'test';
                self::courseQuestion($test_questions, $course_test_id, $parent);
            }
        }

    }

    public function courseQuestion($questions, $parent_id, $parent) {

        foreach ($questions as $question) {

            $course_question = new CourseQuizQuestionModel;
            $course_question->parent_id = $parent_id;
            $course_question->question = $question['question'];
            $course_question->parent = $parent;
            if (!empty($question['question_status'])) {
                $course_question->status = $question['question_status'];
            }
            if (!empty($question['allowed_attempts'])) {
                $course_question->allowed_attempts = $question['allowed_attempts'];
            }
            $course_question->save();
            $course_question_id = $course_question->id;
            $course_question_answers = $question['answers'];
            foreach ($course_question_answers as $course_question_answer) {
                $course_lesson_question_answer = new CourseQuizQuestionAnswerModel;
                $course_lesson_question_answer->course_quiz_question_id = $course_question_id;
                $course_lesson_question_answer->course_quiz_question_option = $course_question_answer['course_quiz_question_option'];
                if (!empty($course_question_answer['course_quiz_correct_answer'])) {
                    $course_lesson_question_answer->course_quiz_correct_answer = $course_question_answer['course_quiz_correct_answer'];
                }
                $course_lesson_question_answer->save();
            }

        }


    }

    public function courseDuplicate($id) {
        $course = CourseModel::find($id);
        $id = json_decode($course);
        $id = $id->id;
        $lesson = CourseLessonModel::with('questions')->where('course_id', $id)->get()->toArray();
        for ($i = 0; $i < count($lesson); $i++) {
            for ($j = 0; $j < count($lesson[$i]['questions']); $j++) {
                $question_id = $lesson[$i]['questions'][$j]['id'];
                $answers = CourseQuizQuestionAnswerModel::where('course_quiz_question_id', $question_id)->get()->toArray();
                $lesson[$i]['questions'][$j]['answers'] = $answers;
            }
        }
        $course->lessons = $lesson;
        $test = CourseTestModel::with('questions')->where('course_id', $id)->get()->toArray();
        for ($x = 0; $x < count($test); $x++) {
            for ($y = 0; $y < count($test[$x]['questions']); $y++) {
                $question_id = $test[$x]['questions'][$y]['id'];
                $answers = CourseQuizQuestionAnswerModel::where('course_quiz_question_id', $question_id)->get()->toArray();
                $test[$x]['questions'][$y]['answers'] = $answers;
            }
        }
        $course->tests = $test;
        $resources = CourseResourceModel::leftjoin('tbl_resources', 'tbl_resources.id', '=', 'tbl_course_resource.resource_id')->where('course_id', $id)->get()->toArray();
        $certificates = CourseCertificateModel::where('course_id', $id)->get()->toArray();
        $course->resources = $resources;
        $course->certificates = $certificates;

        $newCourse = new CourseModel;
        $newCourse->course_name = $course->course_name;
        $newCourse->course_length = $course->course_length;
        $newCourse->course_allow_attempts = $course->course_allow_attempts;
        $newCourse->course_cost = $course->course_cost;
        $newCourse->course_description = $course->course_description;
        $newCourse->course_status = $course->course_status;
        $newCourse->course_in_store = $course->course_in_store;
        //        if (!empty($request->certificate_id)){
        //            $course->certificate_id=$request->certificate_id;
        //        }
        $newCourse->save();
        $course_id = $newCourse->id;

        foreach ($course->resources as $resource) {

            $course_resource = new CourseResourceModel;
            $course_resource->course_id = $course_id;
            $course_resource->course_resource_name = $resource['course_resource_name'];
            if (!empty($resource['course_resource'])) {
                $course_resource->course_resource = $resource['course_resource'];
            } else {
                $course_resource->course_resource_url = $resource['course_resource_url'];
            }
            $course_resource->save();
        }

        self::courseLesson($course->lessons, $course_id);
        if (!empty($course->tests)) {
            self::courseTest($course->tests, $course_id);
        }
        if (!empty($course->certificates)) {
            self::courseCertificate($course->certificates, $course_id);
        }

        return $newCourse;

    }

    public function courseLesson($lessons, $course_id) {
        foreach ($lessons as $lesson) {
            $course_lesson = new CourseLessonModel;
            $course_lesson->course_id = $course_id;
            $course_lesson->course_lesson_name = $lesson['course_lesson_name'];
            $course_lesson->type = $lesson['type'];
            if (!empty($lesson['course_lesson_video'])) {
                $course_lesson->course_lesson_video = $lesson['course_lesson_video'];
            }
            if (!empty($lesson['course_lesson_pdf'])) {
                $fileName = "";
                if (gettype($lesson['course_lesson_pdf']) != "string") {
                    $fileName = time() . '.' . $lesson['course_lesson_pdf']->getClientOriginalExtension();
                    $lesson['course_lesson_pdf']->move(public_path('employee/documents/'), $fileName);
                } else {
                    $fileName = $lesson['course_lesson_pdf'];
                }

                $course_lesson->course_lesson_video = $fileName;
            }
            $time_seconds = "";
            if ($lesson['course_timer_value']) {
                $str_time = $lesson['course_timer_value'];

                sscanf($str_time, "%d:%d:%d", $hours, $minutes, $seconds);

                $time_seconds = isset($seconds) ? $hours * 3600 + $minutes * 60 + $seconds : $hours * 60 + $minutes;

            }

            $course_lesson->timer_status = $lesson['course_next_button_timer_status'];
            $course_lesson->timer_value = $lesson['course_timer_value'];
            $course_lesson->timer_value_insec = $time_seconds;

            $course_lesson->course_lesson_content = $lesson['course_lesson_content'];
            $course_lesson->course_lesson_quiz = $lesson['course_lesson_quiz'];
            if ($lesson['quizStatus'] == TRUE) {
                $course_lesson->quiz_status = 1;
            } else {
                $course_lesson->quiz_status = 0;
            }

            $course_lesson->allowed_attempts = $lesson['allowed_attempts'];
            if (!empty($lesson['passing_rate'])) {
                $course_lesson->passing_rate = $lesson['passing_rate'];
            }
            if (!empty($lesson['no_of_questions'])) {
                $course_lesson->no_of_questions = $lesson['no_of_questions'];
            }
            $course_lesson->save();
            if (!empty($lesson['course_lesson_gamification'])) {
                foreach ($lesson['course_lesson_gamification'] as $gamifications) {

                    CourseLessonGamificationModel::insert([
                        'course_id' => $course_id,
                        'lesson_id' => $course_lesson->id,
                        'content' => $gamifications,
                        'created_at' => Carbon::now('UTC'),
                    ]);
                }
            }
            if (!empty($lesson['questions'])) {
                $course_lesson_id = $course_lesson->id;
                $lesson_questions = $lesson['questions'];
                $parent = 'lesson';
                self::courseQuestion($lesson_questions, $course_lesson_id, $parent);
            }
        }
    }

    public function courseTest($tests, $course_id) {
        foreach ($tests as $test) {
            $course_test = new CourseTestModel;
            $course_test->course_id = $course_id;
            $course_test->practice_test = $test['practice_test'];
            $course_test->enable_submit_button = $test['enable_submit_button'];
            $course_test->course_test_instruction = $test['course_test_instruction'];
            $course_test->course_test_pass_msg = $test['course_test_pass_msg'];
            $course_test->course_test_fail_msg = $test['course_test_fail_msg'];
            $course_test->course_no_of_questions = $test['course_no_of_questions'];
            $course_test->save();
            $course_test_id = $course_test->id;
            if (!empty($test['question_upload'])) {
                $upload_question = new CourseTestQuestionUploadModel;
                $upload_question->course_test_id = $course_test_id;
                $upload_question->column_question = $test['question_column'];
                $upload_question->column_option_1 = $test['opt1_column'];
                $upload_question->column_option_2 = $test['opt2_column'];
                if (!empty($test['opt3_column'])) {
                    $upload_question->column_option_3 = $test['opt3_column'];
                }
                if (!empty($test['opt4_column'])) {
                    $upload_question->column_option_4 = $test['opt4_column'];
                }
                $upload_question->column_correct_answer = $test['correct_answer_column'];
                $upload_question->column_question_status = $test['question_status_column'];
                $upload_question->test_question_url = $test['test_upload'];
                $upload_question->save();
            } else {
                if (!empty($test['questions'])) {
                    $course_test_id = $course_test->id;
                    $test_questions = $test['questions'];
                    $parent = 'test';
                    self::courseQuestion($test_questions, $course_test_id, $parent);
                }
            }
        }

    }

    public function courseCertificate($certificates, $course_id) {
        foreach ($certificates as $certificate) {
            $course_certificate = new CourseCertificateModel;
            $course_certificate->course_id = $course_id;
            $course_certificate->course_certificate_name = $certificate['course_certificate_name'];
            $course_certificate->course_certificate_date = $certificate['course_certificate_date'];
            $course_certificate->course_certificate_valid = $certificate['course_certificate_valid'];
            $course_certificate->course_certificate_custom_text = $certificate['course_certificate_custom_text'];
            $course_certificate->save();
        }
    }

    public function courseDestory($id) {
        $course = CourseModel::find($id);
        $id = json_decode($course);
        $id = $id->id;
        $lesson = CourseLessonModel::with('questions')->where('course_id', $id)->get()->toArray();
        for ($i = 0; $i < count($lesson); $i++) {
            for ($j = 0; $j < count($lesson[$i]['questions']); $j++) {
                $question_id = $lesson[$i]['questions'][$j]['id'];
                CourseQuizQuestionAnswerModel::where('course_quiz_question_id', $question_id)->delete();
                CourseQuizQuestionModel::destroy($question_id);
            }
        }
        $test = CourseTestModel::with('questions')->where('course_id', $id)->get()->toArray();
        for ($x = 0; $x < count($test); $x++) {
            for ($y = 0; $y < count($test[$x]['questions']); $y++) {
                $question_id = $test[$x]['questions'][$y]['id'];
                CourseQuizQuestionAnswerModel::where('course_quiz_question_id', $question_id)->delete();
                CourseQuizQuestionModel::destroy($question_id);
            }
        }
        EmployeeCourseHistoryModel::where('course_id', $id)->delete();
        CompanyCoursesModel::where('course_id', $id)->delete();
        EmployeeCoursesModel::where('course_id', $id)->delete();
        CourseLessonModel::where('course_id', $id)->delete();
        CourseTestModel::where('course_id', $id)->delete();
        EmployeeCertificateModel::where('course_id', $id)->delete();
        CourseRelateCertificate::where('course_id', $id)->delete();
        CourseResourceModel::where('course_id', $id)->delete();

        CourseModel::destroy($id);

        return TRUE;

    }

    public function courseEdit($id) {
        $user = Auth::User();
        $employee_id = $user->id;

        $courseNameStatus = EmployeeCompanyLocationsModel::select('tbl_company.secondary_course_status')->leftjoin('tbl_company', 'tbl_company.id', '=', 'tbl_employee_company_locations.company_id')->where('tbl_employee_company_locations.employee_id', $employee_id)->first();
        $selectData = "";
        if ($courseNameStatus) {
            $selectData = DB::Raw('(CASE WHEN ' . $courseNameStatus->secondary_course_status . ' && `tbl_course`.secondary_course_name!="" then `tbl_course`.secondary_course_name  ELSE `tbl_course`.name END) as name');
        } else {
            $selectData = 'tbl_course.name';
        }
        $course = CourseModel::select('tbl_course.*', $selectData)->with('certificates')->where('id', $id)->get();
        $id = $course[0]['id'];
        $lesson = CourseLessonModel::with('questions', 'gamificationData')->where('course_id', $id)->orderby('order', 'asc')->get()->toArray();
        for ($i = 0; $i < count($lesson); $i++) {
            for ($j = 0; $j < count($lesson[$i]['questions']); $j++) {
                $question_id = $lesson[$i]['questions'][$j]['id'];
                $answers = CourseQuizQuestionAnswerModel::where('course_quiz_question_id', $question_id)->get()->toArray();
                $lesson[$i]['questions'][$j]['answers'] = $answers;
            }
        }
        $course[0]->lessons = $lesson;
        $test = CourseTestModel::with('questions')->where('course_id', $id)->get()->toArray();
        for ($x = 0; $x < count($test); $x++) {
            for ($y = 0; $y < count($test[$x]['questions']); $y++) {
                $question_id = $test[$x]['questions'][$y]['id'];
                $answers = CourseQuizQuestionAnswerModel::where('course_quiz_question_id', $question_id)->get()->toArray();
                $test[$x]['questions'][$y]['answers'] = $answers;
            }
        }
        $course[0]->tests = $test;
        $resources = CourseResourceModel::leftjoin('tbl_resources', 'tbl_resources.id', '=', 'tbl_course_resource.resource_id')->where('course_id', $id)->get()->toArray();
        $resourceData = array();
        foreach ($resources as $value) {
            if ($value['name'] != '' && $value['type'] != '') {
                array_push($resourceData, $value);
            }

        }
        //$certificates=CourseModel::with('certificates')->where('id',$id)->get()->toArray();
        $course[0]->resources = $resourceData;

        //        $course->certificates=$certificates;
        return $course;
    }

    public function deleteLessonQuestion($id) {

        CourseQuizQuestionAnswerModel::where('course_quiz_question_id', $id)->delete();
        //        foreach ($option_ids as $option_id) {
        //            CourseQuizQuestionAnswerModel::destroy($option_id->id);
        //        }
        CourseQuizQuestionModel::destroy($id);
    }

    public function updateQuestion($questions, $parent, $parent_id) {
        foreach ($questions as $question) {
            $answers = $question['answers'];
            if (!empty($question['id'])) {

                $question_id = $question['id'];
                if (!empty($questions['allowed_attempts'])) {
                    $quizQuestions = CourseQuizQuestionModel::where('id', $question_id)->update([
                            'question' => $question['question'],
                            'status' => $question['status'],
                            'allowed_attempts' => $question['allowed_attempts'],
                        ]);
                } else {
                    $quizQuestions = CourseQuizQuestionModel::where('id', $question_id)->update([
                            'question' => $question['question'],
                            'status' => $question['status'],
                        ]);
                }

                foreach ($answers as $answer) {

                    if (!empty($answer['id'])) {
                        $answer_id = $answer['id'];
                        $quizAnswers = CourseQuizQuestionAnswerModel::where('id', $answer_id)->update([
                                'course_quiz_question_option' => $answer['course_quiz_question_option'],
                                'course_quiz_correct_answer' => $answer['course_quiz_correct_answer'],
                            ]);
                    } else {
                        $quizAnswer = new CourseQuizQuestionAnswerModel;
                        $quizAnswer->course_quiz_question_id = $question_id;
                        $quizAnswer->course_quiz_question_option = $answer['course_quiz_question_option'];
                        $quizAnswer->course_quiz_correct_answer = $answer['course_quiz_correct_answer'];
                        $quizAnswer->save();
                    }
                }

            } else {

                $quizQuestion = new CourseQuizQuestionModel;
                $quizQuestion->parent = $parent;
                $quizQuestion->parent_id = $parent_id;
                $quizQuestion->question = $question['question'];
                $quizQuestion->status = $question['status'];
                if (!empty($question['allowed_attempts'])) {
                    $quizQuestion->allowed_attempts = $question['allowed_attempts'];
                }
                $quizQuestion->save();
                $quizQuestionId = $quizQuestion->id;
                foreach ($answers as $answer) {
                    $quizAnswer = new CourseQuizQuestionAnswerModel;
                    $quizAnswer->course_quiz_question_id = $quizQuestionId;
                    $quizAnswer->course_quiz_question_option = $answer['course_quiz_question_option'];
                    $quizAnswer->course_quiz_correct_answer = $answer['course_quiz_correct_answer'];
                    $quizAnswer->save();
                }

            }
        }


    }

    public function courseAssign($course_idss, $assign_to, $company_id) {
        $assigned = 0;
        $alreadyinProgress = 0;
        $alreadyPassed = 0;
        if (is_array($course_idss)) {
            $course_ids = $course_idss;
        } else {
            $course_ids = array($course_idss);
        }
        foreach ($course_ids as $course_id) {

            if ($assign_to == 'employee') {

                $courseDueDate = CourseModel::select('employees_days_to_complete as due_days', 'for_managers', 'for_employees')->where('id', $course_id)->first();
            } else {
                $courseDueDate = CourseModel::select('managers_days_to_complete as due_days', 'for_managers', 'for_employees')->where('id', $course_id)->first();
            }

            $due_days = 0;
            if ($courseDueDate != NULL) {
                $due_days = (int)$courseDueDate->due_days;
            }
            $course_due_date = Carbon::now('UTC')->addDays($due_days)->format('Y-m-d');
            if ($assign_to[0]['assign_to'] == 'location') {
                if ($assign_to[0]['location_id'] != 'all_location') {
                    $belongCompany = CompanyModel::where('id', $assign_to[0]['location_id'])->first();
                    $join = '';
                    if ($belongCompany->parent_id == 0) {
                        $join = 'tbl_employee_company_locations.company_id';
                    } else {
                        $join = 'tbl_employee_company_locations.location_id';
                    }
                    $employees = DB::table('tbl_employee_company_locations')->where($join, $assign_to[0]['location_id'])->get();

                } else {
                    $employees = DB::table('tbl_employee_company_locations')->where('company_id', $company_id)->get();
                }

            } else if ($assign_to[0]['assign_to'] == 'job_title') {
                $employees = DB::table('tbl_employee_company_locations')->leftJoin('tbl_employee', 'tbl_employee.id', '=', 'tbl_employee_company_locations.employee_id')->where('tbl_employee_company_locations.company_id', $company_id)->where('tbl_employee.job_title_id', $assign_to[0]['job_title'])->select('tbl_employee_company_locations.*')->groupBy('tbl_employee_company_locations.employee_id')->get();
            } else {
                $employees = $assign_to[0]['employee_ids'];
            }
            if ($employees) {
                foreach ($employees as $id) {
                    if (is_array($id)) {
                        $employee_id = $id['id'];
                        $company_id = CompanyModel::getCompantIdByEmployeeId($id['id']);
                    } else {
                        $employee_id = $id->employee_id;
                    }
                    $categoryData = CourseModel::where('id', $course_id)->first();
                    $isBelongToCategory = $categoryData->category;
                    if ($isBelongToCategory) {
                        $categoryData = CourseModel::where('category', $isBelongToCategory)->get();
                        $categoryCourseId = [];
                        foreach ($categoryData as $data) {
                            array_push($categoryCourseId, $data->id);
                        }
                        EmployeeCoursesModel::where('employee_id', $employee_id)->whereIn('course_id', $categoryCourseId)->where('employee_course_status', '!=', '1')->delete();
                    }
                    $isCourse = EmployeeCoursesModel::where([
                        [
                            'employee_id',
                            $employee_id,
                        ],
                        [
                            'course_id',
                            $course_id,
                        ],
                    ])->first();
                    if ($isCourse != NULL) {
                        if ($isCourse->employee_course_status == 0 || $isCourse->employee_course_status == 3) {
                            // assign the course.
                            $assigned++;
                            log::debug("Assigned faild course");
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

                            $employeeCourse = new EmployeeCoursesModel();
                            $employeeCourse->employee_id = $employee_id;
                            $employeeCourse->course_id = $course_id;
                            $employeeCourse->company_id = $company_id;
                            $employeeCourse->employee_course_date_assigned = date('Y-m-d');
                            $employeeCourse->employee_course_due_date = $course_due_date;
                            $employeeCourse->save();
                        } else if ($isCourse->employee_course_status == 1) {

                            $employeeCertiifcates = EmployeeCertificateModel::where('course_id', $course_id)->where('employee_id', $employee_id)->where('certificate_expiration_date', '>=', date('Y-m-d'))->get();

                            if (count($employeeCertiifcates) < 1) {
                                $assigned++;
                                log::debug("Assigned Expired course");
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
                                // assign the course
                                $employeeCourse = new EmployeeCoursesModel();
                                $employeeCourse->employee_id = $employee_id;
                                $employeeCourse->course_id = $course_id;
                                $employeeCourse->company_id = $company_id;
                                $employeeCourse->employee_course_date_assigned = date('Y-m-d');
                                $employeeCourse->employee_course_due_date = $course_due_date;
                                $employeeCourse->save();
                            } else {
                                $alreadyPassed++;
                                log::debug("Course is passed ");
                                // not assign (Course is passed )
                            }
                        } else {
                            $alreadyinProgress++;
                            log::debug("Course in progrees");
                            // not assign (Course in progrees )
                        }
                    } else {
                        $assigned++;
                        log::debug("first time assign");
                        $employeeCourse = new EmployeeCoursesModel();
                        $employeeCourse->employee_id = $employee_id;
                        $employeeCourse->course_id = $course_id;
                        $employeeCourse->company_id = $company_id;
                        $employeeCourse->employee_course_date_assigned = date('Y-m-d');
                        $employeeCourse->employee_course_due_date = $course_due_date;
                        $employeeCourse->save();
                    }
                }
            }
        }

        return [
            'assigned' => $assigned,
            'alreadyPassed' => $alreadyPassed,
            'alreadyInProgress' => $alreadyinProgress,
            'employees' => $employees,
        ];
        //return response()->json(['assigned'=>$assigned, 'alreadyPassed'=>$alreadyPassed,'alreadyInProgress'=>$alreadyinProgress], 200);   
    }

    public function courseEmployee($id, $company_id) {
        $user = Auth::user();
        $location_id = '';
        $employees = EmployeeCoursesModel::leftJoin('tbl_company', 'tbl_company.id', '=', 'tbl_employee_courses.company_id')->leftJoin('tbl_employee_company_locations', 'tbl_employee_company_locations.employee_id', '=', 'tbl_employee_courses.employee_id')->leftJoin('tbl_employee', 'tbl_employee.id', '=', 'tbl_employee_courses.employee_id')->select('tbl_company.name as company_name', 'tbl_company.id as company_id', 'tbl_employee_courses.employee_id', 'employee_course_status', 'employee_course_due_date', 'employee_course_date_completed', 'tbl_employee.first_name', 'tbl_employee.last_name')->where([
                [
                    'course_id',
                    $id,
                ],
            ])->groupby('tbl_employee_courses.employee_id');
        switch ($user->role_id) {
            case 2 || 3:
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

                    $employees->whereIn('tbl_employee_company_locations.company_id', $company_id);

                } else {
                    $company_id = $companies['isLocations'];
                    $employees->whereIn('tbl_employee_company_locations.location_id', $company_id);

                }
                break;

            default:
                $employees->whereIn('tbl_employee_company_locations.company_id', $company_id);
                break;
        }

        // for ($i=0; $i<count($employees); $i++){
        //     $employee_id=$employees[$i]->employee_id;
        //     $employee_info=EmployeeModel::where('id',$employee_id)->get();
        //   if (!empty($employee_info[0])) {
        //       if (!empty($employee_info[0]->first_name)) {
        //           $employees[$i]->first_name = $employee_info[0]->first_name;
        //       }
        //       if (!empty($employee_info[0]->last_name)) {
        //           $employees[$i]->last_name = $employee_info[0]->last_name;
        //       }
        //       if (!empty($employee_info[0]->location_id)) {
        //           $location_id = $employee_info[0]->location_id;
        //       }
        //       if(!empty($location_id)){
        //           $location=LocationModel::find($location_id);
        //           $employees[$i]->employee_location=$location->location_name;
        //       }
        //   }

        // }

        return $employees->get();
    }

    public function courseFullData($course_id, $user_id) {
        $user = Auth::User();
        $employee_id = $user->id;
        $secondCourseStatus = 0;
        $courseNameStatus = EmployeeCompanyLocationsModel::select('tbl_company.secondary_course_status')->leftjoin('tbl_company', 'tbl_company.id', '=', 'tbl_employee_company_locations.company_id')->where('tbl_employee_company_locations.employee_id', $employee_id)->first();
        if ($courseNameStatus) {
            $secondCourseStatus = $courseNameStatus->secondary_course_status;
        }
        $course = CourseModel::select('tbl_course.*', DB::Raw('(CASE WHEN ' . $secondCourseStatus . ' && `tbl_course`.secondary_course_name!="" then `tbl_course`.secondary_course_name  ELSE `tbl_course`.name END) as name'))->with('lessons', 'tests')->where('id', $course_id)->get();
        $nextcourse = $course[0]->next_course;
        $course_name = "";
        if ($nextcourse > 0) {
            $getcoursename = CourseModel::where('id', $nextcourse)->first();
            $course_name = $getcoursename->name;
        }
        $course[0]->next_course_name = $course_name;
        $lessons = $course[0]->lessons;
        $tests = $course[0]->tests;
        $number_of_test = count($tests);
        $number_of_lesson = count($lessons);
        for ($i = 0; $i < $number_of_lesson; $i++) {
            $lesson_id = $lessons[$i]->id;
            $lesson_attempts = $lessons[$i]->allowed_attempts;
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
                    $lesson_id,
                ],
                [
                    'lesson_test',
                    'lesson',
                ],
            ])->get();
            if ($attempts->count() > 0) {
                $course[0]->lessons[$i]->remaining_attempts = $lesson_attempts - $attempts[0]->attempts;
                $course[0]->lessons[$i]->result = $attempts[0]->pass_fail;
            } else {
                $course[0]->lessons[$i]->remaining_attempts = $lesson_attempts;
                $course[0]->lessons[$i]->result = 0;
            }
            $course[0]->lessons[$i]->is_last_lesson = 0;
            if (($number_of_lesson - 1) == $i && $number_of_test == 0) {
                $course[0]->lessons[$i]->is_last_lesson = 1;
            }
            $questions = CourseQuizQuestionModel::where([
                [
                    'parent_id',
                    $lesson_id,
                ],
                [
                    'parent',
                    'lesson',
                ],
                [
                    'status',
                    1,
                ],
            ]);
            if ($lessons[0]->no_of_questions) {
                $questions->inRandomOrder()->limit($lessons[0]->no_of_questions);
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
            $course[0]->lessons[$i]['questions'] = $questionArray;
        }
        for ($x = 0; $x < count($tests); $x++) {
            $test_id = $tests[$x]->id;
            $test_attempts = $course[0]->allow_attempts;
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
                    $test_id,
                ],
                [
                    'lesson_test',
                    'test',
                ],
            ])->get();
            if ($attempts->count() > 0) {
                $course[0]->tests[$x]->remaining_attempts = $test_attempts - $attempts[0]->attempts;
                $course[0]->tests[$x]->result = $attempts[0]->pass_fail;
            } else {
                $course[0]->tests[$x]->remaining_attempts = $test_attempts;
                $course[0]->tests[$x]->result = 0;
            }
            $course[0]->tests[$x]->is_last_lesson = 0;
            if (($number_of_test - 1) == $x) {
                $course[0]->tests[$x]->is_last_lesson = 1;
            }
            $testsQuestions = CourseQuizQuestionModel::where([
                [
                    'parent_id',
                    $test_id,
                ],
                [
                    'parent',
                    'test',
                ],
                [
                    'status',
                    1,
                ],
            ]);
            $testsQuestions->inRandomOrder();
            if ($tests[0]->course_no_of_questions) {
                $testsQuestions->limit($tests[0]->course_no_of_questions);
            }
            $questionArrayTests = array();
            foreach ($testsQuestions->get() as $questionKey => $value) {
                $question_id = $value->id;
                $questionArrayTests[$questionKey]['id'] = $value->id;
                $questionArrayTests[$questionKey]['parent_id'] = $value->parent_id;
                $questionArrayTests[$questionKey]['question'] = $value->question;
                $questionArrayTests[$questionKey]['allowed_attempts'] = $value->allowed_attempts;
                $questionArrayTests[$questionKey]['parent'] = $value->parent;
                $questionArrayTests[$questionKey]['status'] = $value->status;
                $questionArrayTests[$questionKey]['created_at'] = $value->created_at;
                $questionArrayTests[$questionKey]['updated_at'] = $value->updated_at;
                $answers = CourseQuizQuestionAnswerModel::where('course_quiz_question_id', $question_id)->get();
                $questionArrayTests[$questionKey]['answers'] = $answers;
            }
            $course[0]->tests[$x]['questions'] = $questionArrayTests;
        }

        return $course;
    }

    public function coursePass($course_id, $user_id, $pass) {
        EmployeeCoursesModel::where([
            [
                'course_id',
                $course_id,
            ],
            [
                'employee_id',
                $user_id,
            ],
        ])->update([
            "employee_course_status" => $pass,
            "employee_course_date_completed" => date('Y-m-d'),
        ]);

    }

    public function courseFail($course_id, $user_id, $pass) {
        EmployeeCoursesModel::where([
            [
                'course_id',
                $course_id,
            ],
            [
                'employee_id',
                $user_id,
            ],
        ])->update([
            "employee_course_status" => $pass,
        ]);

    }

    public function courseSubmits($course_id, $user_id, $test_lesson, $test_lesson_id, $pass) {
        $lesson_check = EmployeeCourseAttemptsModel::where([
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
                $test_lesson_id,
            ],
            [
                'lesson_test',
                $test_lesson,
            ],
        ])->count();
        if ($lesson_check === 0) {
            $lesson_attempts = new EmployeeCourseAttemptsModel;
            $lesson_attempts->user_id = $user_id;
            $lesson_attempts->course_id = $course_id;
            $lesson_attempts->lesson_test = $test_lesson;
            $lesson_attempts->lesson_test_id = $test_lesson_id;
            $lesson_attempts->attempts = 1;
            $lesson_attempts->pass_fail = $pass;
            $lesson_attempts->save();
        } else {
            EmployeeCourseAttemptsModel::where([
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
                    $test_lesson_id,
                ],
                [
                    'lesson_test',
                    $test_lesson,
                ],
            ])->increment('attempts', 1);
            EmployeeCourseAttemptsModel::where([
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
                    $test_lesson_id,
                ],
                [
                    'lesson_test',
                    $test_lesson,
                ],
            ])->update([
                    'pass_fail' => $pass,
                ]);
        }
    }

    public function PassEmployee($course_id, $company_id, $certificate_status, $interface) {
        if ($interface == "Employee") {
            $data = [];
            $where_data = [];
            if (!empty($company_id)) {
                $employees = EmployeeCoursesModel::where([
                    [
                        'company_id',
                        $company_id,
                    ],
                    [
                        'course_id',
                        $course_id,
                    ],
                    [
                        'employee_course_status',
                        1,
                    ],
                ])->get();
                $total = EmployeeCoursesModel::where([
                    [
                        'company_id',
                        $company_id,
                    ],
                    [
                        'course_id',
                        $course_id,
                    ],
                    [
                        'employee_course_status',
                        1,
                    ],
                ])->count();
            } else {
                $employees = EmployeeCoursesModel::where([
                    [
                        'course_id',
                        $course_id,
                    ],
                    [
                        'employee_course_status',
                        1,
                    ],
                ])->get();
                $total = EmployeeCoursesModel::where([
                    [
                        'course_id',
                        $course_id,
                    ],
                    [
                        'employee_course_status',
                        1,
                    ],
                ])->count();
            }
            if ($certificate_status == "Active Certificates") {
                array_push($where_data, [
                    'certificate_expiration_date',
                    '>=',
                    date('Y-m-d'),
                ]);
            } else if ($certificate_status == "Expired Certificates") {
                array_push($where_data, [
                    'certificate_expiration_date',
                    '<',
                    date('Y-m-d'),
                ]);
            }

            for ($i = 0; $i < count($employees); $i++) {
                $employee_id = $employees[$i]->employee_id;
                $course_ids = $employees[$i]->course_id;
                $course_info = CourseModel::find($course_ids);
                $employee_info = EmployeeModel::find($employee_id);
                $certificate_info = EmployeeCertificateModel::where([
                    [
                        'employee_id',
                        $employee_id,
                    ],
                    [
                        'course_id',
                        $course_ids,
                    ],
                ])->where($where_data)->get();

                $employees[$i]->first_name = $employee_info->first_name;
                $employees[$i]->last_name = $employee_info->last_name;
                $employees[$i]->course_name = $course_info->course_name;

                if (!empty($certificate_info[0]->certificate_name)) {
                    $employees[$i]->certificate_name = $certificate_info[0]->certificate_name;
                    $employees[$i]->certificate_date = $certificate_info[0]->certificate_date;
                    $employees[$i]->certificate_expiration_date = $certificate_info[0]->certificate_expiration_date;
                    $employees[$i]->certificate_url = $certificate_info[0]->certificate_url;
                }
            }

            $data['employees'] = $employees;
            $data['total'] = $total;

            return $data;
        }
        if ($interface == "Company") {
            $data = [];
            $where_data = [];
            if (!empty($company_id)) {
                $employees = EmployeeCertificateModel::where([
                    [
                        'employee_id',
                        $company_id,
                    ],
                    [
                        'course_id',
                        $course_id,
                    ],
                ])->get();
            }
            if ($certificate_status == "Active Certificates") {
                array_push($where_data, [
                    'certificate_expiration_date',
                    '>=',
                    date('Y-m-d'),
                ]);
            } else if ($certificate_status == "Expired Certificates") {
                array_push($where_data, [
                    'certificate_expiration_date',
                    '<',
                    date('Y-m-d'),
                ]);
            }
            for ($i = 0; $i < count($employees); $i++) {
                $employee_id = $employees[$i]->employee_id;
                $course_ids = $employees[$i]->course_id;
                $course_info = CourseModel::find($course_ids);
                $employee_info = CompanyModel::find($employee_id);
                $certificate_info = EmployeeCertificateModel::where([
                    [
                        'employee_id',
                        $employee_id,
                    ],
                    [
                        'course_id',
                        $course_ids,
                    ],
                ])->where($where_data)->get();

                $employees[$i]->first_name = $employee_info->company_admin;
                $employees[$i]->last_name = "";
                $employees[$i]->course_name = $course_info->course_name;

                if (!empty($certificate_info[0]->certificate_name)) {
                    $employees[$i]->certificate_name = $certificate_info[0]->certificate_name;
                    $employees[$i]->certificate_date = $certificate_info[0]->certificate_date;
                    $employees[$i]->certificate_expiration_date = $certificate_info[0]->certificate_expiration_date;
                    $employees[$i]->certificate_url = $certificate_info[0]->certificate_url;
                }

            }
            $data['employees'] = $employees;
            $data['total'] = "";

            return $data;
        }
    }

    public function passEmployeeExcel($course_id, $company_id) {
        if (!empty($company_id)) {
            $employees = EmployeeCoursesModel::where([
                [
                    'company_id',
                    $company_id,
                ],
                [
                    'course_id',
                    $course_id,
                ],
                [
                    'employee_course_status',
                    1,
                ],
            ])->get();
        } else {
            $employees = EmployeeCoursesModel::where([
                [
                    'course_id',
                    $course_id,
                ],
                [
                    'employee_course_status',
                    1,
                ],
            ])->get();
        }
        for ($i = 0; $i < count($employees); $i++) {
            $employee_id = $employees[$i]->employee_id;
            $course_ids = $employees[$i]->course_id;
            $employee_info = EmployeeModel::find($employee_id);
            $certificate_info = EmployeeCertificateModel::where([
                [
                    'employee_id',
                    $employee_id,
                ],
                [
                    'course_id',
                    $course_ids,
                ],
            ])->get();
            $employees[$i]->first_name = $employee_info->first_name;
            $employees[$i]->last_name = $employee_info->last_name;
            if (!empty($certificate_info[0]->certificate_name)) {
                $employees[$i]->certificate_name = $certificate_info[0]->certificate_name;
                $employees[$i]->certificate_date = $certificate_info[0]->certificate_date;
                $employees[$i]->certificate_expiration_date = $certificate_info[0]->certificate_expiration_date;
                $employees[$i]->certificate_url = $certificate_info[0]->certificate_url;
            }
        }

        return $employees;
    }

    public function courseExcel($course_status, $id = "") {
        if ($course_status == "Active") {
            $course_status = 1;
        }
        if ($course_status == "Inactive") {
            $course_status = 0;
        }
        if ($id != "") {
            $getCompanyId = EmployeeCompanyLocationsModel::where('company_id', $id)->first();
            if ($getCompanyId) {
                $company_id = $getCompanyId->company_id;
            } else {
                $getCompanyId = EmployeeCompanyLocationsModel::where('location_id', $id)->first();
                if ($getCompanyId) {
                    $company_id = $getCompanyId->company_id;
                } else {
                    return response()->json(['course' => 'Invaild id no course found.!'], 422);
                }
            }
            if ($course_status >= "0") {
                $courses = CourseModel::leftjoin('tbl_company_courses', 'tbl_company_courses.course_id', '=', 'tbl_course.id')->where('tbl_company_courses.company_id', $company_id)->where('status', $course_status)->withCount('resources', 'companies', 'employees', 'lessons', 'passedEmployees')->orderBy('name', 'ASC')->get();
            } else {
                $courses = CourseModel::leftjoin('tbl_company_courses', 'tbl_company_courses.course_id', '=', 'tbl_course.id')->where('tbl_company_courses.company_id', $company_id)->withCount('resources', 'companies', 'employees', 'lessons', 'passedEmployees')->orderBy('name', 'ASC')->get();
            }
        } else {
            if ($course_status >= "0") {
                $courses = CourseModel::where('status', $course_status)->withCount('resources', 'companies', 'employees', 'lessons', 'passedEmployees')->orderBy('name', 'ASC')->get();
            } else {
                $courses = CourseModel::withCount('resources', 'companies', 'employees', 'lessons', 'passedEmployees')->orderBy('name', 'ASC')->get();
            }
        }

        $total = count($courses);
        $data['courses'] = $courses;
        $data['total'] = $total;

        return response()->json($data, 200);

    }

    public function pdfCertificate($course_id, $user_id, $interface) {

        $certificate_id = CourseCertificatesModel::where('course_id', $course_id)->first();
        $certificate_info = CourseCertificateModel::leftJoin('tbl_templates', 'tbl_templates.id', '=', 'tbl_certificate.template_id')->where('tbl_certificate.id', $certificate_id->certificate_id)->select('tbl_certificate.*', 'tbl_templates.name as template')->first();

        $template = strtolower($certificate_info->template);
        $template = str_replace(' ', '_', $template);

        if (empty($interface)) {
            $user_name = EmployeeModel::find($user_id);
            $user_name = $user_name->full_name;
        } else {
            $user_name = CompanyModel::find($user_id);
            $user_name = $user_name->company_admin;
        }

        $certificate_valid = $certificate_info->valid;
        $date = date('Y-m-d', strtotime('+' . $certificate_valid . 'years'));
        $pass_date = date('Y-m-d');

        $employee_certificate = new EmployeeCertificateModel();
        //$employee_certificate->certificate_no="2020-1001";
        $employee_certificate->certificate_name = $certificate_info->name;
        $employee_certificate->course_id = $course_id;
        $employee_certificate->employee_id = $user_id;
        $employee_certificate->certificate_date = $pass_date;
        $employee_certificate->certificate_expiration_date = $date;
        //$employee_certificate->certificate_url=$certificate_url;
        $employee_certificate->save();
        $employee_certificate->id;
        $certificate_no = "2020-100" . $employee_certificate->id;
        $newDate = $data = [
            'title' => $certificate_info->name,
            'text' => $certificate_info->custom_text,
            'employee_name' => 'Amar Singh',
            'signature_title_1' => $certificate_info->signature_title_1,
            'signature_title_2' => $certificate_info->signature_title_2,
            'certificate_no' => $certificate_no,
            'completion_date' => date("m-d-Y", strtotime($pass_date)),
            'expiration_date' => date("m-d-Y", strtotime($date)),
        ];
        $file_name = rand() . '.pdf';
        $orientation = 'landscape';
        $customPaper = array(
            0,
            0,
            950,
            950,
        );
        $pdf = PDF::loadView('templates.' . $template, $data)->setPaper($customPaper, $orientation);
        $pdf->save(storage_path('../public/employee/certificates/') . $file_name);
        $certificate_url = URL::to('employee/certificates/') . '/' . $file_name;
        $employee_certificate = EmployeeCertificateModel::
        where('id', $employee_certificate->id)->update([
                'certificate_no' => $certificate_no,
                'certificate_url' => $certificate_url,
            ]);

        return $employee_certificate;
    }

    public function downloadCertificate($course_id, $user_id) {
        $course_id = "17";
        $certificate_id = CourseCertificatesModel::where('course_id', $course_id)->get();
        $certificate_info = CourseCertificateModel::find($certificate_id[0]->certificate_id);
        $user_name = EmployeeModel::find($user_id);
        $certificate_valid = $certificate_info->certificate_valid;
        $date = date('Y-m-d', strtotime('+' . $certificate_valid . 'years'));
        $pass_date = date('Y-m-d');
        //        $exp_date=$pass_date->modify('+100 years')->format('d/m/Y H:i:s');
        $data = [
            'title' => $certificate_info->certificate_name,
            'text' => $certificate_info->certificate_custom_text,
            'employee_name' => $user_name->employee_full_name,
            'signature_title_1' => $certificate_info->signature_title_1,
            'signature_title_2' => $certificate_info->signature_title_2,
        ];
        $file_name = rand() . '.pdf';

        $pdf = PDF::loadView('certificate', $data);

        return $pdf->download($file_name);

        // return PDF::loadFile('http://localhost:8000/public/employee/certificates/931896451.pdf')->stream('download.pdf');

        /* $pdf->save(storage_path('../public/employee/certificates/').$file_name);
         echo base_path('/public/employee/certificates/'.$file_name)
         die('dwde');*/
        // echo $pdf->download($file_name);

    }
}
