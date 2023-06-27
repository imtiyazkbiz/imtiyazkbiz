<?php

namespace App\Http\Controllers;

use App\Helpers\Helper;
use App\Http\Traits\CommonTrait;
use App\Http\Traits\CompanyTrait;
use App\Http\Traits\CourseTrait;
use App\Imports\QuestionsImport;
use App\Models\CertificateModel;
use App\Models\CompanyCoursesModel;
use App\Models\CompanyModel;
use App\Models\CourseCertificateModel;
use App\Models\CourseCertificatesModel;
use App\Models\CourseFolderAssignModel;
use App\Models\CourseFolderModel;
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
use App\Models\EmployeeCourseFolderModel;
use App\Models\EmployeeCourseHistoryModel;
use App\Models\EmployeeCoursesModel;
use App\Models\EmployeeModel;
use App\Models\PayByEmployeeHistoryModel;
use App\Models\PaymentModel;
use App\Models\PreTestModel;
use App\Models\ProctoredExam;
use App\Models\SurveyModel;
use App\Models\UserActivityLogModel;
use App\User;
use Auth;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use Maatwebsite\Excel\Facades\Excel;
use phpDocumentor\Reflection\Types\False_;
use Validator;
use ZanySoft\Zip\Zip;

//use Excel;

class CourseController extends Controller {
    use CompanyTrait, CourseTrait, CommonTrait;

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
        $this->base_url = env('MARCHANTE_SOLUTION_URL');
        $this->profile_id = env('MARCHANTE_SOLUTION_PROFILE_ID');
        $this->profile_key = env('MARCHANTE_SOLUTION_PROFILE_KEY');
        $this->status = config('constant.status');
        $this->sucess = config('constant.success');
        $this->fail = config('constant.fail');
    }

    public static function cronForNextCourseAssignment() {
        try {
            $passedCourses = EmployeeCourseHistoryModel::where([
                'attempt_status' => 1,
                'assignment_status' => 0,
            ])->get();

            foreach ($passedCourses as $passed) {
                $course_id = $passed->course_id;
                $user_id = $passed->employee_id;
                $getnextcourse = CourseModel::where('id', $course_id)->first();
                $nextCourseId = $getnextcourse->next_course;
                $assignmentGap = $getnextcourse->assignment_gap;
                $getnextcoursedata = CourseModel::where('id', $nextCourseId)->first();
                $getuserdata = EmployeeModel::where('id', $user_id)->first();
                $getusercompanydata = EmployeeCompanyLocationsModel::where('employee_id', $user_id)->first();

                if ($nextCourseId > 0 && $assignmentGap > 0) {
                    $getStatus = EmployeeCoursesModel::where([
                        [
                            'employee_id',
                            $user_id,
                        ],
                        [
                            'course_id',
                            $course_id,
                        ],
                    ])->first();
                    if ($getStatus != NULL) {
                        $getCompletionDate = $getStatus->employee_course_date_completed;
                        $gap = 0;
                        if ($getnextcourse != NULL) {
                            $gap = (int)$getnextcourse->assignment_gap;
                        }
                        $todayDate = date('Y-m-d');
                        //$todayDate= "2021-01-30";

                        $nextAssignmentDate = Carbon::parse($getCompletionDate)->addDays($gap)->format('Y-m-d');
                        if ($todayDate == $nextAssignmentDate) {
                            if ($getnextcoursedata->status == 1 && $getuserdata->status == 1) {
                                self::nextCourseAssignment($nextCourseId, $user_id, $course_id);
                            }
                        }
                    }

                }
            }
        } catch (Exception $th) {
            return response()->json(['message' => $th->getMessage()], 422);
        }
    }

    public static function nextCourseAssignment($course_id, $user_id, $current_course_id) {
        try {
            $is_company_id = 0;
            $is_company_id = CompanyModel::getCompantIdByEmployeeId($user_id);

            $courseDueDate = CourseModel::select('employees_days_to_complete as due_days')->where('id', $course_id)->first();
            $due_days = 0;
            if ($courseDueDate != NULL) {
                $due_days = (int)$courseDueDate->due_days;
            }
            $course_due_date = Carbon::now('UTC')->addDays($due_days)->format('Y-m-d');
            $isCourse = EmployeeCoursesModel::where([
                [
                    'employee_id',
                    $user_id,
                ],
                [
                    'course_id',
                    $course_id,
                ],
            ])->first();
            $count = 0;
            if ($isCourse != NULL) {
                if ($isCourse->employee_course_status !== 2) {
                    EmployeeCoursesModel::where('id', $isCourse->id)->delete();
                    EmployeeCourseAttemptsModel::where([
                        [
                            'user_id',
                            $user_id,
                        ],
                        [
                            'course_id',
                            $course_id,
                        ],
                    ])->delete();

                } else {
                    $count = 1;
                }
            }
            $employee_id = $user_id;
            if ($count == 0) {
                $employeeCourse = new EmployeeCoursesModel();
                $employeeCourse->employee_id = $user_id;
                $employeeCourse->course_id = $course_id;
                $employeeCourse->company_id = $is_company_id;
                $employeeCourse->employee_course_date_assigned = date('Y-m-d');
                $employeeCourse->employee_course_due_date = $course_due_date;
                $employeeCourse->save();

                EmployeeCourseHistoryModel::where([
                    [
                        'course_id',
                        $current_course_id,
                    ],
                    [
                        'employee_id',
                        $user_id,
                    ],
                    [
                        'attempt_status',
                        '1',
                    ],
                ])->update(['assignment_status' => '1']);

                $employee_info = EmployeeModel::join('tbl_employee_company_locations', 'tbl_employee_company_locations.employee_id', '=', 'tbl_employee.id')->where('tbl_employee.id', $user_id)->get();
            } else {
                $update = EmployeeCoursesModel::where([
                    [
                        'employee_id',
                        $user_id,
                    ],
                    [
                        'course_id',
                        $course_id,
                    ],
                ])->update([
                    'employee_course_due_date' => $course_due_date,
                    'employee_course_date_assigned' => date('Y-m-d'),
                ]);
                EmployeeCourseHistoryModel::where([
                    [
                        'course_id',
                        $current_course_id,
                    ],
                    [
                        'employee_id',
                        $user_id,
                    ],
                    [
                        'attempt_status',
                        '1',
                    ],
                ])->update(['assignment_status' => '1']);
            }


        } catch (Exception $th) {

            return response()->json(['message' => $th->getMessage()], 422);
        }

    }

    public static function assignnextcourse(Request $request) {
        try {
            $course_id = $request->course_id;
            $user_id = $request->user_id;
            $history_id = $request->history_id;
            $getnextcourse = CourseModel::where('id', $course_id)->first();
            $nextCourseId = $getnextcourse->next_course;
            $assignmentGap = $getnextcourse->assignment_gap;
            $getnextcoursedata = CourseModel::where('id', $nextCourseId)->first();
            $getuserdata = EmployeeModel::where('id', $user_id)->first();
            $getusercompanydata = EmployeeCompanyLocationsModel::where('employee_id', $user_id)->first();
            if ($getusercompanydata) {
                $companycourses = CompanyCoursesModel::leftJoin('tbl_company', 'tbl_company.id', '=', 'tbl_company_courses.company_id')->where('tbl_company_courses.company_id', $getusercompanydata->company_id)->where('tbl_company_courses.course_id', $nextCourseId)->where('tbl_company.status', 1)->get();
            }
            if ($nextCourseId > 0 && $assignmentGap == 0 && $getnextcoursedata->status == 1 && $getuserdata->status == 1) {

                self::nextCourseAssignment($nextCourseId, $user_id, $course_id);

                return response()->json([
                    'message' => 'You have been automatically assigned the next course, ' . $getnextcoursedata->name,
                    'status' => 'Success',
                ], 200);
            } else {
                return response()->json([
                    'message' => 'No course assignment',
                    'status' => 'No Course',
                ], 200);
            }
        } catch (Exception $th) {
            return response()->json(['message' => $th->getMessage()], 422);
        }
    }

    public function create(Request $request) {

        $validator = Validator::make($request->all(), [
            'course_name' => 'required|string',
            'course_length' => 'required',
            'course_allow_attempts' => 'required|integer',
            'course_cost' => 'required',
            'course_description' => 'required|string',
            'course_status' => 'required',
            'course_in_store' => 'required',
            'lessons' => 'required|array',
        ]);
        if ($validator->fails()) {
            $errors = json_decode($validator->errors());
            foreach ($errors as $error) {
                $message[] = $error[0];
            }
            $message = implode(",", $message);

            return response()->json(['message' => $message], 422);
        }

        try {
            DB::beginTransaction();
            $str = $request->course_due;
            $filteredNumbers = array_filter(preg_split("/\D+/", $str));
            $firstOccurence = reset($filteredNumbers);
            $course_due_date = Carbon::now('UTC')->format('Y-m-d');
            $course = new CourseModel;
            $course->name = $request->course_name;
            $course->secondary_course_name = $request->secondary_course_name;
            $course->course_name_certificate = $request->course_name_certificate;
            $course->length = $request->course_length;
            $course->allow_attempts = $request->course_allow_attempts;
            $course->passing_percent = $request->course_pass_rate;
            $course->certificate_available = $request->certificateavilable;
            $course->next_course = $request->nextcourse;
            if ($request->companyspecific) {
                $course->company_specific = $request->companyspecific;
            }
            $type = $request->course_type;
            $authentication = $request->course_2fa;
            $discounted = $request->discounted_course;
            $weekly_report = $request->weekly_report;
            $course->for_managers = $request->formanager;
            $course->for_employees = $request->foremployee;
            $course->employees_days_to_complete = $request->employees_days_to_complete;
            $course->reassignment_expiry = $request->reassignment_expiry;
            $course->expiry_attempts = $request->expiry_attempts;
            $course->managers_days_to_complete = $request->manager_days_to_complete;
            $course->assignment_gap = $request->assignment_gap;
            $course->certificate_validity = $request->certificate_validity;
            $course->due_date = $course_due_date;
            $course->pass_message = $request->pass_message;
            $course->fail_message = $request->fail_message;
            $course->course_type = $type;
            $course->is_2fa_required = $authentication;
            $course->is_discounted_course = $discounted;
            $course->discounted_course_comment = $request->discounted_course_comment;
            $course->is_weekly_report = $weekly_report;
            $course->cost = $request->course_cost;
            $course->description = $request->course_description;
            $course->status = $request->course_status;
            $course->in_store = $request->course_in_store;
            $course->food_safe_online_proctored_exam = $request->food_safe_online_proctored_exam;
            $course->save();

            if (!empty($request->courseResources)) {
                $course->resources()->sync(explode(',', $request->courseResources));
            }

            $course_id = $course->id;
            if ($request->nextcourse == "this_course") {
                CourseModel::where('id', $course_id)->update(['next_course' => $course_id]);
            }
            if (!empty($request->certificate_ids)) {

                $relation = new CourseRelateCertificate;
                $relation->course_id = $course_id;
                $relation->certificate_id = $request->certificate_ids;
                $relation->save();

            }
            if (!empty($request->survey_ids)) {
                foreach ($request->survey_ids as $id) {
                    DB::table('tbl_course_survey')->insertGetId([
                        'course_id' => $course_id,
                        'survey_id' => $id['id'],
                        'created_at' => Carbon::now('UTC'),
                    ]);
                }
            }
            // if (!empty($request->resources)) {
            //   $this->courseResources($request->resources, $course_id);
            // }
            $this->courseLesson($request->lessons, $course_id);
            if (!empty($request->tests)) {
                $this->courseTest($request->tests, $course_id);
            }
            if (!empty($request->certificates)) {
                $this->courseCertificate($request->certificates, $course_id);
            }
            if (!empty($request->companies)) {
                $this->courseCompanies($request->companies, $course_id);
            }
            if (!empty($request->companyspecific)) {
                $this->courseCompanies($request->companyspecific, $course_id);
            }

            if (is_array($request->pretests) && !empty($request->pretests)) {
                $pre_test = $request->pretests[0];
                $dataPreTest = array(
                    'course_id' => $course_id,
                    'name' => $pre_test['name'],
                    'instruction' => $pre_test['instructions'],
                    'number_of_questions' => 0,
                    'created_at' => Carbon::now('UTC'),
                );
                $preTestId = PreTestModel::insertGetId($dataPreTest);
                if ($preTestId) {
                    if (is_array($pre_test['pretest_questions']) && !empty($pre_test['pretest_questions'])) {
                        $insertQuestionAnswer = PreTestModel::preTestQuestion($preTestId, $pre_test['pretest_questions']);
                    }
                }
            }
            // if (is_array($request->surveytests) && !empty($request->surveytests)) {
            //     $survey = $request->surveytests[0];
            //     $dataSurvey = array(
            //         'name' =>  $survey['name'],
            //         'instruction' =>  $survey['instructions'],
            //         'number_of_questions' =>  isset($survey['number_of_questions'])?$survey['number_of_questions']:0,
            //         'created_at' =>  Carbon::now('UTC')
            //     );
            //     $surveyId = SurveyModel::insertGetId($dataSurvey);
            //     DB::table('tbl_course_survey')->insertGetId([
            //         'course_id' => $course_id,
            //         'survey_id' => $surveyId,
            //         'created_at' =>  Carbon::now('UTC')
            //     ]);
            //     if ($surveyId) {
            //         if (is_array($survey['surveytest_questions']) && !empty($survey['surveytest_questions'])) {
            //             $insertQuestionAnswer = SurveyModel::saveSurveyQuestion($surveyId, $survey['surveytest_questions']);
            //         }
            //     }
            // }
            DB::commit();

            return response()->json($course, 200);

        } catch (Exception $th) {
            DB::rollback();

            return response()->json(['message' => $th->getMessage()], 422);
        }


    }

    public function duplicate($id) {
        $course = CourseModel::find($id);
        if (empty($course)) {
            return response()->json(['course' => 'Invaild id no course found.!'], 422);
        }
        $newCourse = $this->courseDuplicate($id);

        return response()->json($newCourse, 200);
    }

    public function edit($id) {
        try {

            $course = CourseModel::find($id);
            if (empty($course)) {
                return response()->json(['course' => 'Invaild id no course found.!'], 422);
            }
            $courseResources = $course->resources()->where('status', 1)->get();
            $course = $this->courseEdit($id);
            $course[0]['course_employees'] = EmployeeCoursesModel::leftjoin('tbl_employee', 'tbl_employee.id', '=', 'tbl_employee_courses.employee_id')->where('course_id', $id)->where('tbl_employee.type', 'employee')->count();

            $course[0]['course_companies'] = CompanyCoursesModel::getCompanyOfCourse($id);
            $status = 0;
            $course[0]['pretest'] = PreTestModel::getPreTestQuestion($id, $status);

            $course[0]['survey'] = SurveyModel::getSurveyPreTestQuestion($id, $status);

            $course[0]['courseResources'] = $courseResources;

            return response()->json($course, 200);

        } catch (Exception $th) {

            return response()->json(['message' => $th->getMessage()], 422);
        }


    }

    public function update(Request $request, $id) {
        $str = $request->course_due;
        $filteredNumbers = array_filter(preg_split("/\D+/", $str));
        $firstOccurence = reset($filteredNumbers);
        $course_due_date = Date('Y-m-d', strtotime('+' . $firstOccurence . ' days'));
        $course = CourseModel::find($id);
        if (empty($course)) {
            return response()->json(['course' => 'Invaild id no course found.!'], 422);
        }

        $next_course = $request->next_course;


        $type = $request->course_type;
        $authentication = $request->course_2fa;
        $discounted = $request->discounted_course;
        $weekly_report = $request->weekly_report;
        $course = CourseModel::where('id', $id)->update([
            'name' => $request->course_name,
            'secondary_course_name' => $request->secondary_course_name,
            'course_name_certificate' => $request->course_name_certificate,
            'length' => $request->course_length,
            'due_days' => $request->course_due,
            'due_date' => $course_due_date,
            'allow_attempts' => $request->course_allow_attempts,
            'pass_message' => $request->course_pass_message,
            'fail_message' => $request->course_fail_message,
            'cost' => $request->course_cost,
            'course_type' => $type,
            'description' => $request->course_description,
            'in_store' => $request->course_in_store,
            'status' => $request->course_status,
            'assignment_gap' => $request->assignment_gap,
            'for_employees' => $request->foremployee,
            'for_managers' => $request->formanager,
            'passing_percent' => $request->course_pass_rate,
            'managers_days_to_complete' => $request->manager_days_to_complete,
            'employees_days_to_complete' => $request->employees_days_to_complete,
            'reassignment_expiry' => $request->reassignment_expiry,
            'expiry_attempts' => $request->expiry_attempts,
            'company_specific' => $request->company_specific,
            'next_course' => $next_course,
            'certificate_validity' => $request->certificate_validity,
            'certificate_available' => $request->certificateavilable,
            'is_2fa_required' => $authentication,
            'is_discounted_course' => $discounted,
            'discounted_course_comment' => $request->discounted_course_comment,
            'is_weekly_report' => $weekly_report,
            'food_safe_online_proctored_exam' => $request->food_safe_online_proctored_exam,
            'category' => $request->course_category,
        ]);
        if ($request->courseResources) {
            CourseModel::where('id', $id)->first()->resources()->sync($request->courseResources);
        }
        if ($request->course_certificate) {
            $relation = new CourseRelateCertificate;
            $relation->course_id = $id;
            $relation->certificate_id = $request->course_certificate;
            $relation->save();
        }
        if ($request->course_survey) {
            DB::table('tbl_course_survey')->insertGetId([
                'course_id' => $id,
                'survey_id' => $request->course_survey,
                'created_at' => Carbon::now('UTC'),
            ]);
        }
        $getCompanies = CompanyCoursesModel::whereNotIn('company_id', $request->assigned_companies)->where(['course_id' => $id])->get();


        CompanyCoursesModel::where(['course_id' => $id])->delete();


        if ($request->assigned_companies) {
            $this->courseCompanies($request->assigned_companies, $id);
            foreach ($getCompanies as $company) {
                $getempData = EmployeeCoursesModel::leftjoin('tbl_employee_company_locations', 'tbl_employee_company_locations.employee_id', '=', 'tbl_employee_courses.employee_id')->where('tbl_employee_courses.course_id', $id)->where('tbl_employee_courses.employee_course_status', '!=', '1')->where('tbl_employee_company_locations.company_id', $company->company_id)->delete();
            }
        }
        if ($request->company_specific) {
            $this->courseCompanies($request->company_specific, $id);
        }
        if ($request->lessonOrder) {
            for ($i = 0; $i < count($request->lessonOrder); $i++) {
                CourseLessonModel::where('id', $request->lessonOrder[$i])->update(['order' => $i + 1]);

            }

            return response()->json($course, 200);

        }
    }

    public function destroy($id) {
        $course = CourseModel::find($id);
        if (empty($course)) {
            return response()->json(['course' => 'Invaild id no course found.!'], 422);
        }
        $this->courseDestory($id);

        return response()->json([], 200);

    }

    public function readAll(Request $request) {
        $status = $request->course_status;
        $data = [];
        $where_data = [];
        $startFrom = "";
        $limit = "";
        $requestData = $request->all();
        if (isset($requestData['page']) && isset($requestData['per_page'])) {
            $startFrom = ($requestData['page'] == 0) ? ($requestData['page'] * $requestData['per_page']) : ($requestData['page'] - 1) * $requestData['per_page'];
            $limit = $requestData['per_page'];
        }
        if (!empty($request->search)) {
            $search = $request->search;
            $search = explode(" ", $search);
            foreach ($search as $key => $name) {
                $where_data[] = [
                    'name',
                    'like',
                    '%' . $name . '%',
                ];
            }
        }
        if (!empty($status)) {
            $status = strtolower($status);
            if ($status == "inactive") {
                array_push($where_data, [
                    'status',
                    0,
                ]);
            } else if ($status == "active") {
                array_push($where_data, [
                    'status',
                    1,
                ]);
            }

            // $courses=CourseModel::where('course_status',$status)->offset($offset)->limit($limit)->orderBy('course_name', 'ASC')->get();

        }

        if ($request->company_id != "") {
            $id = $request->company_id;
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
            $courses = CourseModel::select('tbl_course.*')->leftjoin('tbl_company_courses', 'tbl_company_courses.course_id', '=', 'tbl_course.id')->where('tbl_company_courses.company_id', $company_id)->where($where_data);
            $total = $courses->count();
            if ($limit != '') {
                $courses->skip($startFrom);
                $courses->take($limit);
            }
        } else {
            $courses = CourseModel::where($where_data);
            $total = $courses->count();
            if ($limit != '') {
                $courses->skip($startFrom);
                $courses->take($limit);
            }
        }
        // if (!empty($request->location_status) || !empty($request->search))
        // {
        //     $courses = CourseModel::where($where_data)->orderBy('name', 'ASC')
        //         ->get();
        //     $total = count($courses);
        // }else
        // {
        //     $courses = CourseModel::where($where_data)->orderBy('name', 'ASC')
        //         ->get();
        //     $total = count($courses);
        // }
        $data['courses'] = $courses->orderBy('name', 'ASC')->get();
        $data['total'] = $total;

        return response()->json($data, 200);
    }

    public function assignCourseFolder(Request $request) {
        $folder_id = $request->folder_id;
        $assignTo = $request->assign_to;

        $ids = $assignTo[0]['course_ids'];
        foreach ($ids as $id) {
            $folder = new CourseFolderAssignModel;
            $folder->course_id = $id['id'];
            $folder->course_folder_id = $folder_id;
            $folder->save();
        }

        return response()->json(['Course' => 'Course assigned Successfully'], 200);
    }

    public function allCourseFolders(Request $request) {
      
        $status = $request->folder_status;
        $data = [];
        $where_data = [];
        if (!empty($request->search)) {
            $search = $request->search;
            $search = explode(" ", $search);
            foreach ($search as $key => $name) {
                $where_data[] = [
                    'folder_name',
                    'like',
                    '%' . $name . '%',
                ];
            }
        }
        if (!empty($status)) {
            if ($status == "Inactive") {
                $status = 0;
            } else if ($status == "Active") {
                $status = 1;
            }
            array_push($where_data, [
                'folder_status',
                $status,
            ]);
            // $courses=CourseModel::where('course_status',$status)->offset($offset)->limit($limit)->orderBy('course_name', 'ASC')->get();

        }
        if (!empty($request->folder_status) || !empty($request->search)) {
            $courseFolders = CourseFolderModel::withCount('courses')->where($where_data)->orderBy('folder_name', 'ASC')->get();
            $total = count($courseFolders);
        } else {
            $courseFolders = CourseFolderModel::withCount('courses')->where($where_data)->orderBy('folder_name', 'ASC')->get();
            $total = count($courseFolders);
        }
        $data['folders'] = $courseFolders;
        $data['total'] = $total;

        return response()->json($data, 200);

    }

    public function folderCourses(Request $request) {
        $folder_id = $request->folder_id;
        $data = [];
        $where_data = [];
        if (!empty($request->search)) {
            $search = $request->search;
            $search = explode(" ", $search);
            foreach ($search as $key => $name) {
                $where_data[] = [
                    'name',
                    'like',
                    '%' . $name . '%',
                ];
            }
        }

        if (!empty($folder_id)) {
            array_push($where_data, [
                'course_folder_id',
                $folder_id,
            ]);
        }
        $courses = CourseFolderAssignModel::where('course_folder_id', $folder_id)->get();
        $total = count($courses);
        for ($i = 0; $i < count($courses); $i++) {
            $course_id = $courses[$i]['course_id'];
            $course_info = CourseModel::find($course_id);
            $courses[$i]->name = $course_info->name;
            $courses[$i]->status = $course_info->status;
        }
        $data['courses'] = $courses;
        $data['total'] = $total;

        return response()->json($data, 200);
    }

    public function getAllCourses() {
        $status = 1;
        $courses = CourseModel::where('course_status', $status)->orderBy('course_name', 'ASC')->get();
        $data['courses'] = $courses;
        $data['total'] = count($courses);

        return response()->json($data, 200);
    }

    public function readAllCertificates() {
        $courseCertificate = CourseCertificateModel::all();

        return response()->json($courseCertificate, 200);
    }

    public function unassignedCertificates() {
        $certificate = CourseCertificateModel::orderby('tbl_certificate.name', 'asc')->get();

        return response()->json($certificate, 200);
    }

    public function surveytests() {
        $survey = SurveyModel::where('survey_type', '!=', "post-login")->orderby('tbl_survey.name', 'asc')->get();

        return response()->json($survey, 200);
    }

    public function addCertificate(Request $request) {
        $validator = Validator::make($request->all(), [
            'course_certificate_name' => 'required',
            'course_certificate_template' => 'required',
            'signature_title_1' => 'required',
            'signature_title_2' => 'required',
        ], [
                'course_certificate_name.required' => 'Certificate name is required.',
                'course_certificate_template.required' => 'Template is required.',

                'signature_title_1.required' => 'Signature title is required.',
                'signature_title_2.required' => 'Signature description is required.',
            ]


        );
        if ($validator->fails()) {
            $errors = json_decode($validator->errors());
            foreach ($errors as $error) {
                $message[] = $error[0];
            }

            // $message = implode(",", $message);

            return response()->json(['message' => $message[0]], 422);
        }
        try {
            $certificate = new CourseCertificateModel;
            $certificate->template_id = $request->course_certificate_template;
            $certificate->name = $request->course_certificate_name;
            $certificate->date = Carbon::now('UTC')->format('Y-m-d');
            $certificate->valid = 365;
            $certificate->custom_text = $request->course_certificate_custom_text;
            $certificate->signature_title_1 = $request->signature_title_1;
            $certificate->signature_title_2 = $request->signature_title_2;
            $certificate->save();
            $certificate_id = $certificate->id;
            if (isset($request->course_id)) {
                $relation = new CourseRelateCertificate;
                $relation->course_id = $request->course_id;
                $relation->certificate_id = $certificate_id;
                $relation->save();
            }

            return response()->json($certificate, 200);

        } catch (Exception $th) {

            return response()->json(['message' => $th->getMessage()], 422);
        }
    }

    public function addCourseFolder(Request $request) {
        $this->validate($request, [
            'folder_name' => 'required',
            'folder_description' => 'required',
        ]);
        $status = 0;

        if ($request->folder_status == TRUE || $request->folder_status == 1) {
            $status = 1;
        }

        $courseFolder = new CourseFolderModel;
        $courseFolder->folder_name = $request->folder_name;
        $courseFolder->folder_description = $request->folder_description;
        $courseFolder->folder_status = $status;
        $courseFolder->save();

        if ($request->certificate_id) {
            DB::table('tbl_course_folder_certificate')->insert([
                'folder_id' => $courseFolder->id,
                'certificate_id' => $request->certificate_id,
                'expiry' => $request->expiry,
            ]);
        }

        return response()->json($courseFolder, 200);

    }

    public function addTest(Request $request) {

        $course_id = $request->course_id;
        $this->courseTest($request->tests, $course_id);
    }

    public function addLesson(Request $request) {
        $course_id = $request->course_id;
        $this->courseLesson($request->lessons, $course_id);
    }

    public function coursePassedMsg(Request $request) {
        $course_id = $request->course_id;
        $data = CourseTestModel::where('course_id', $course_id)->get();

        return response()->json($data, 200);
    }

    public function editCertificate($id) {
        $certificate = CourseCertificateModel::find($id);
        if (empty($certificate)) {
            return response()->json(['certificate' => 'Invaild id no certificate found.!'], 422);
        }

        return response()->json($certificate, 200);
    }

    public function updateCertificate(Request $request, $id) {
        $validator = Validator::make($request->all(), [
            'course_certificate_name' => 'required',
            'course_certificate_template' => 'required',
            'signature_title_1' => 'required',
            'signature_title_2' => 'required',
        ], [
                'course_certificate_name.required' => 'Certificate name is required.',
                'course_certificate_template.required' => 'Template is required.',

                'signature_title_1.required' => 'Signature title is required.',
                'signature_title_2.required' => 'Signature description is required.',
            ]


        );
        if ($validator->fails()) {
            $errors = json_decode($validator->errors());
            foreach ($errors as $error) {
                $message[] = $error[0];
            }

            return response()->json(['message' => $message[0]], 422);
        }
        try {
            $certificate = CourseCertificateModel::find($id);
            if (empty($certificate)) {
                return response()->json(['certificate' => 'Invalid id no certificate found.!'], 422);
            }
            $courseCertificate = CourseCertificateModel::where('id', $id)->update([
                'name' => $request->course_certificate_name,
                'template_id' => $request->course_certificate_template,
                'custom_text' => $request->course_certificate_custom_text,
                'signature_title_1' => $request->signature_title_1,
                'signature_title_2' => $request->signature_title_2,
            ]);
            if ($courseCertificate) {

                return response()->json(['message' => 'Certificate updated successfully.'], 200);
            } else {

                return response()->json(['message' => 'Something is wrong, try again..'], 422);
            }
        } catch (Exception $th) {

            return response()->json(['message' => $th->getMessage()], 422);
        }
    }

    public function editCourseFolder($id) {
        $courseFolder = CourseFolderModel::select('tbl_course_folder.*', 'tbl_course_folder_certificate.certificate_id', 'tbl_course_folder_certificate.expiry')->leftjoin('tbl_course_folder_certificate', 'tbl_course_folder_certificate.folder_id', '=', 'tbl_course_folder.id')->where('tbl_course_folder.id', $id)->first();
        if (empty($courseFolder)) {
            return response()->json(['folder' => 'Invaild id no course folder found.!'], 422);
        }

        return response()->json($courseFolder, 200);
    }

    public function updateCourseFolder(Request $request, $id) {
        $folder = CourseFolderModel::find($id);
        if (empty($folder)) {
            return response()->json(['folder' => 'Invalid id no course folder found.!'], 422);
        }
        $status = 0;
        if ($request->folder_status == TRUE || $request->folder_status == 1) {
            $status = 1;
        }
        $courseFolder = CourseFolderModel::where('id', $id)->update([
            'folder_name' => $request->folder_name,
            'folder_description' => $request->folder_description,
            'folder_status' => $status,
        ]);
        if ($request->certificate_id) {
            DB::table('tbl_course_folder_certificate')->where('folder_id', $id)->delete();
            DB::table('tbl_course_folder_certificate')->insert([
                'folder_id' => $id,
                'certificate_id' => $request->certificate_id,
                'expiry' => $request->expiry,
            ]);
        }

        return response()->json($courseFolder, 200);
    }
    public function destoryCertificate($id, $isCertificate = NULL) {

        $certificate = CourseCertificateModel::find($id);
        if (empty($certificate)) {
            return response()->json(['certificate' => 'Invalid id no certificate found.!'], 422);
        }
        if ($isCertificate != NULL && $isCertificate == "deleteCertificate") {
            CourseCertificateModel::destroy($id);
        }

        CourseRelateCertificate::where('certificate_id', $id)->delete();

        return response()->json([], 200);
    }

    public function unassignCertificate($id, $course_id) {
        CourseRelateCertificate::where('certificate_id', $id)->where('course_id', $course_id)->delete();

        return response()->json([], 200);
    }

    public function destoryCourseFolder($id) {
        $courseFolder = CourseFolderModel::find($id);
        if (empty($courseFolder)) {
            return response()->json(['folder' => 'Invalid id no course folder found.!'], 422);
        }
        CourseFolderModel::destroy($id);

        return response()->json([], 200);
    }

    public function removeCourseFolder($id) {
        $courseFolder = CourseFolderAssignModel::find($id);
        if (empty($courseFolder)) {
            return response()->json(['folder' => 'Invalid id no course folder found.!'], 422);
        }
        CourseFolderAssignModel::destroy($id);

        return response()->json([], 200);
    }

    public function deleteQuestionOption($id) {
        $answer = CourseQuizQuestionAnswerModel::find($id);
        if (empty($answer)) {
            return response()->json(['option' => 'Invalid id no option found.!'], 422);
        }
        CourseQuizQuestionAnswerModel::destroy($id);

        return response()->json([], 200);
    }

    public function deleteQuestion($id) {
        $question = CourseQuizQuestionModel::find($id);
        if (empty($question)) {
            return response()->json(['question' => 'Invalid id no question found.!'], 422);
        }
        $this->deleteLessonQuestion($id);

        return response()->json([], 200);
    }

    public function deleteLesson($id) {
        $lesson = CourseLessonModel::find($id);
        if (empty($lesson)) {
            return response()->json(['lesson' => 'Invalid id no lesson found.!'], 422);
        }
        $question_ids = CourseQuizQuestionModel::where([
            [
                'parent_id',
                $id,
            ],
            [
                'parent',
                'lesson',
            ],
        ])->get();
        foreach ($question_ids as $question_id) {
            $this->deleteLessonQuestion($question_id->id);
        }
        CourseLessonModel::destroy($id);

        return response()->json([], 200);
    }

    public function deleteTest($id) {
        $test = CourseTestModel::find($id);
        if (empty($test)) {
            return response()->json(['test' => 'Invalid id no test found.!'], 422);
        }
        $question_ids = CourseQuizQuestionModel::where([
            [
                'parent_id',
                $id,
            ],
            [
                'parent',
                'test',
            ],
        ])->get();
        foreach ($question_ids as $question_id) {
            $this->deleteLessonQuestion($question_id->id);
        }
        CourseTestModel::destroy($id);

        return response()->json([], 200);
    }

    public function updateLesson(Request $request) {
        $lesson = CourseLessonModel::find($request->lesson_id);
        if (empty($lesson)) {
            return response()->json(['lesson' => 'Invalid id no lesson found.!'], 422);
        }
        $id = $request->lesson_id;

        $questions = $request->questions;
        $time_seconds = "";
        if ($request->course_timer_value) {
            $str_time = $request->course_timer_value;

            sscanf($str_time, "%d:%d:%d", $hours, $minutes, $seconds);

            $time_seconds = isset($seconds) ? $hours * 3600 + $minutes * 60 + $seconds : $hours * 60 + $minutes;

        }

        $newLesson = CourseLessonModel::where('id', $id)->update([
            'course_lesson_name' => $request->course_lesson_name,
            'timer_status' => $request->course_next_button_timer_status,
            'timer_value' => $request->course_timer_value,
            'timer_value_insec' => $time_seconds,
            'course_lesson_content' => $request->course_lesson_content,
            'course_lesson_quiz' => $request->course_lesson_quiz,
            'allowed_attempts' => $request->allowed_attempts,
            'quiz_status' => $request->quizStatus,
            'passing_rate' => $request->passing_rate,
            'no_of_questions' => $request->no_of_questions,
            'type' => $request->type,
        ]);
        if (!empty($request->course_lesson_video)) {
            $newLesson = CourseLessonModel::where('id', $id)->update(['course_lesson_video' => $request->course_lesson_video]);
        }
        if (!empty($request->course_lesson_gamification)) {
            CourseLessonGamificationModel::where([
                'course_id' => $request->course_id,
                'lesson_id' => $id,
            ])->delete();
            foreach ($request->course_lesson_gamification as $gamifications) {
                CourseLessonGamificationModel::insert([
                    'course_id' => $request->course_id,
                    'lesson_id' => $id,
                    'content' => $gamifications,
                    'created_at' => Carbon::now('UTC'),
                ]);
            }
        }
        if (!empty($request->course_lesson_pdf)) {
            $fileName = "";
            $fileName = time() . '.' . $request->course_lesson_pdf->getClientOriginalExtension();
            $request->course_lesson_pdf->move(public_path('employee/documents/'), $fileName);
            $newLesson = CourseLessonModel::where('id', $id)->update(['course_lesson_video' => $fileName]);
        }
        $parent = 'lesson';
        $parent_id = $id;
        if ($request->questions) {
            $this->updateQuestion($questions, $parent, $parent_id);
        }

        return response()->json(['Lesson' => 'Update successfully..!'], 200);
    }

    public function updateTest(Request $request, $id) {
        $lesson = CourseTestModel::find($id);
        if (empty($lesson)) {
            return response()->json(['test' => 'Invalid id no test found.!'], 422);
        }

        //return $request->lessons;
        $questions = $request->questions;
        CourseTestModel::where('id', $id)->update([
            'practice_test' => $request->practice_test,
            'enable_submit_button' => $request->enable_submit_button,
            'course_no_of_questions' => $request->course_no_of_questions,
            'course_test_instruction' => $request->course_test_instruction,
            'course_test_pass_msg' => $request->course_test_pass_msg,
            'course_test_fail_msg' => $request->course_test_fail_msg,
        ]);
        $parent = 'test';
        $parent_id = $id;
        $this->updateQuestion($questions, $parent, $parent_id);

        return response()->json(['Test' => 'Update successfully..!'], 200);
    }

    public function resources(Request $request) {
        $course_id = $request->course_id;
        $course = CourseModel::find($course_id);
        if (empty($course)) {
            return response()->json(['Course' => 'Invalid id no course found.!'], 422);
        }
        $resources = $request->resources;

        $this->courseResources($resources, $course_id);

        return response()->json(['Resource' => 'Added successfully..!'], 200);
    }

    public function deleteresources($id) {
        $course = CourseResourceModel::find($id);
        if (empty($course)) {
            return response()->json(['Resource' => 'Invalid id no resource found.!'], 422);
        }
        CourseResourceModel::destroy($id);

        return response()->json([], 200);
    }

    public function certificatesList() {
        try {

            $user = Auth::user();
            switch ($user->role_id) {
                case 1 || 5:
                    $certificates = CertificateModel::select(DB::raw('tbl_certificate.*,
                    (select count(*) from tbl_course_certificate where tbl_course_certificate.certificate_id = tbl_certificate.id) as course_count,
                    (select count(*) from tbl_employee_certificates left join tbl_course_certificate  on tbl_course_certificate.course_id = tbl_employee_certificates.course_id where tbl_course_certificate.certificate_id = tbl_certificate.id) as employee_count'))->get();
                    break;
                case 2:
                    $getCompany = EmployeeCompanyLocationsModel::where('employee_id', $user->id)->get();
                    $comppanyIds = array();
                    $parentCompany = 0;
                    foreach ($getCompany as $value) {
                        $parentCompany = $value->company_id;
                        if ($value->location_id == 0) {
                            array_push($comppanyIds, $value->company_id);
                        } else {
                            array_push($comppanyIds, $value->location_id);
                        }
                    }
                    $company = CompanyModel::where('employee_id', $user->id)->first();
                    $certificates = EmployeeCoursesModel::rightJoin('tbl_employee_certificates', function($query) {
                        $query->on('tbl_employee_certificates.course_id', '=', 'tbl_employee_courses.course_id');
                        $query->on('tbl_employee_certificates.employee_id', '=', 'tbl_employee_courses.employee_id');
                    })->leftJoin('tbl_course_certificate', 'tbl_course_certificate.course_id', '=', 'tbl_employee_certificates.course_id')->rightJoin('tbl_certificate', 'tbl_course_certificate.certificate_id', '=', 'tbl_certificate.id')->leftJoin('tbl_course', 'tbl_course.id', '=', 'tbl_employee_courses.course_id')->select(DB::raw('tbl_certificate.name as certificate_name, tbl_certificate.id as id, tbl_course.name as course_name, tbl_course.id as course_id'))->whereIn('tbl_employee_courses.company_id', $comppanyIds)->groupBy('tbl_certificate.name')->get();
                    foreach ($certificates as $value) {
                        $value->employee_count = EmployeeCoursesModel::rightJoin('tbl_employee_certificates', function($query) {
                            $query->on('tbl_employee_certificates.course_id', '=', 'tbl_employee_courses.course_id');
                            $query->on('tbl_employee_certificates.employee_id', '=', 'tbl_employee_courses.employee_id');
                        })->where([
                            'tbl_employee_courses.course_id' => $value->course_id,
                        ])->whereIn('tbl_employee_courses.company_id', $comppanyIds)->count();
                    }
                    break;
                case 3:
                    $getCompany = EmployeeCompanyLocationsModel::where('employee_id', $user->id)->get();
                    $comppanyIds = array();
                    $parentCompany = 0;
                    foreach ($getCompany as $value) {
                        $parentCompany = $value->company_id;
                        if ($value->location_id == 0) {
                            array_push($comppanyIds, $value->company_id);
                        } else {
                            array_push($comppanyIds, $value->location_id);
                        }
                    }
                    $company = CompanyModel::where('employee_id', $user->id)->first();
                    $certificates = EmployeeCoursesModel::rightJoin('tbl_employee_certificates', function($query) {
                        $query->on('tbl_employee_certificates.course_id', '=', 'tbl_employee_courses.course_id');
                        $query->on('tbl_employee_certificates.employee_id', '=', 'tbl_employee_courses.employee_id');
                    })->leftJoin('tbl_course_certificate', 'tbl_course_certificate.course_id', '=', 'tbl_employee_certificates.course_id')->rightJoin('tbl_certificate', 'tbl_course_certificate.certificate_id', '=', 'tbl_certificate.id')->leftJoin('tbl_course', 'tbl_course.id', '=', 'tbl_employee_courses.course_id')->select(DB::raw('tbl_certificate.name as certificate_name, tbl_certificate.id as id, tbl_course.name as course_name, tbl_course.id as course_id'))->whereIn('tbl_employee_courses.company_id', $comppanyIds)->groupBy('tbl_certificate.name')->get();

                    foreach ($certificates as $value) {
                        $value->employee_count = EmployeeCoursesModel::rightJoin('tbl_employee_certificates', function($query) {
                            $query->on('tbl_employee_certificates.course_id', '=', 'tbl_employee_courses.course_id');
                            $query->on('tbl_employee_certificates.employee_id', '=', 'tbl_employee_courses.employee_id');
                        })->where([
                            'tbl_employee_courses.course_id' => $value->course_id,
                        ])->whereIn('tbl_employee_courses.company_id', $comppanyIds)->count();
                    }
                    break;
                default:
                    $certificates = CertificateModel::select(DB::raw('tbl_certificate.*,
                        (select count(*) from tbl_course_certificate where tbl_course_certificate.certificate_id = tbl_certificate.id) as course_count,
                        (select count(*) from tbl_employee_certificates left join tbl_course_certificate  on tbl_course_certificate.course_id = tbl_employee_certificates.course_id where tbl_course_certificate.certificate_id = tbl_certificate.id) as employee_count'))->get();
                    break;
            }

            $data['certificates'] = $certificates;
            $data['total'] = count($certificates->toArray());

            return response()->json($data, 200);

        } catch (Exception $th) {

            return response()->json(['message' => $th->getMessage()], 422);
        }
    }

    public function certificatesFilterList(Request $request) {
        $currentDate = Carbon::now('UTC')->format('Y-m-d');

        try {
            $where_data = array();
            $where_dataa = [];
            $where_dataaa = [];
            $search = explode(" ", $request->search);
            foreach ($search as $key => $name) {
                $where_dataa[] = [
                    'tbl_certificate.name',
                    'like',
                    '%' . $name . '%',
                ];
                $where_dataaa[] = [
                    'tbl_course.name',
                    'like',
                    '%' . $name . '%',
                ];
            }
            $user = Auth::user();
            switch ($user->role_id) {
                case 1:
                case 5:
                    $certificates = CertificateModel::select(DB::raw('tbl_certificate.*,
                                (select count(*) from tbl_course_certificate where tbl_course_certificate.certificate_id = tbl_certificate.id) as course_count,
                                (select count(*) from tbl_employee_certificates left join tbl_course_certificate  on tbl_course_certificate.course_id = tbl_employee_certificates.course_id where tbl_course_certificate.certificate_id = tbl_certificate.id AND tbl_employee_certificates.certificate_expiration_date >= "' . $currentDate . '"  ) as employee_count'))->where($where_dataa)->orderby('tbl_certificate.name', 'asc')->get();

                    break;
                case 2:
                case 3:
                    $companies = CompanyModel::getCompaniesByAdminUser($user->id);
                    $where_data = "";
                    $companyIds = [];
                    if ($companies['isParent'] != 0) {
                        if (is_array($companies['isParent'])) {
                            $companyIds = $companies['isParent'];
                        } else {
                            $companyIds = array(
                                $companies['isParent'],
                            );
                        }

                        $where_data = 'tbl_employee_company_locations.company_id';

                    } else {
                        $companyIds = $companies['isLocations'];
                        $where_data = 'tbl_employee_company_locations.location_id';

                    }
                    $certificates = EmployeeCoursesModel::rightJoin('tbl_employee_certificates', function($query) {
                        $query->on('tbl_employee_certificates.course_id', '=', 'tbl_employee_courses.course_id');
                        $query->on('tbl_employee_certificates.employee_id', '=', 'tbl_employee_courses.employee_id');
                    })->leftJoin('tbl_course_certificate', 'tbl_course_certificate.course_id', '=', 'tbl_employee_certificates.course_id')->leftjoin('tbl_employee_company_locations', 'tbl_employee_company_locations.employee_id', '=', 'tbl_employee_courses.employee_id')->rightJoin('tbl_certificate', 'tbl_course_certificate.certificate_id', '=', 'tbl_certificate.id')->leftJoin('tbl_course', 'tbl_course.id', '=', 'tbl_employee_courses.course_id')->leftJoin('tbl_company', 'tbl_company.id', '=', 'tbl_employee_company_locations.company_id')->select('tbl_certificate.name as certificate_name', 'tbl_certificate.id as id', DB::Raw('(CASE WHEN tbl_company.secondary_course_status && `tbl_course`.secondary_course_name!="" then `tbl_course`.secondary_course_name  ELSE `tbl_course`.name END) as course_name'), 'tbl_course.id as course_id')->whereIn($where_data, $companyIds)->where($where_dataaa)->groupBy('tbl_course.id')->get();
                    foreach ($certificates as $value) {
                        $value->employee_count = EmployeeCoursesModel::rightJoin('tbl_employee_certificates', function($query) {
                            $query->on('tbl_employee_certificates.course_id', '=', 'tbl_employee_courses.course_id');
                            $query->on('tbl_employee_certificates.employee_id', '=', 'tbl_employee_courses.employee_id');
                        })->leftjoin('tbl_employee_company_locations', 'tbl_employee_company_locations.employee_id', '=', 'tbl_employee_courses.employee_id')->where([
                            'tbl_employee_courses.course_id' => $value->course_id,
                        ])->whereDate('tbl_employee_certificates.certificate_expiration_date', '>=', $currentDate)->whereIn($where_data, $companyIds)->count();
                    }
                    break;

                default:
                    $certificates = CertificateModel::select(DB::raw('tbl_certificate.*,
                        (select count(*) from tbl_course_certificate where tbl_course_certificate.certificate_id = tbl_certificate.id) as course_count,
                        (select count(*) from tbl_employee_certificates left join tbl_course_certificate  on tbl_course_certificate.course_id = tbl_employee_certificates.course_id where tbl_course_certificate.certificate_id = tbl_certificate.id AND tbl_employee_certificates.certificate_expiration_date >= "' . $currentDate . '" ) as employee_count'))->where($where_dataa)->orderby('tbl_certificate.name', 'asc')->get();
                    break;
            }

            $data['certificates'] = $certificates;
            $data['total'] = count($certificates->toArray());

            return response()->json($data, 200);

        } catch (Exception $th) {

            return response()->json(['message' => $th->getMessage()], 422);
        }


    }

    public function allCertificates() {
        $certificate = CourseCertificateModel::with('course')->withCount('course')->get();

        return response()->json($certificate, 200);
    }

    public function updateResource(Request $request) {
        $id = $request->resource_id;
        $course_resource = CourseResourceModel::find($id);
        if (empty($course_resource)) {
            return response()->json(['Resource' => 'Invalid id no resource found.!'], 422);
        }
        //        return $request->course_resource_url;
        if ($request->course_resource_type == 'link' && !empty($request->course_resource_url)) {
            CourseResourceModel::where('id', $id)->update([
                'appear_status' => $request->appear_status,
                'course_resource_name' => $request->course_resource_name,
                'resource_type' => $request->course_resource_type,
                'course_resource_url' => $request->course_resource_url,
            ]);
        }
        if ($request->course_resource_type == 'file' && !empty($request->course_resource)) {
            $file = $request->course_resource;
            $fil_name = $file->getClientOriginalName();
            $extension = $file->getClientOriginalExtension(); // getting image extension

            $filename = time() . '.' . $extension;
            $file->move('images/course/resources', $filename);
            $file_path = URL::to('images/course/resources') . '/' . $filename;
            CourseResourceModel::where('id', $id)->update([
                'appear_status' => $request->appear_status,
                'course_resource_name' => $request->course_resource_name,
                'resource_image' => $fil_name,
                'resource_type' => $request->course_resource_type,
                'course_resource' => $file_path,
            ]);
        }

        return response()->json(['Resource' => 'update successfully.!'], 200);
    }

    public function readFile(Request $request) {
        $file = $request->file('quizFile');
        $fileData = Excel::toArray(new QuestionsImport, $file);
        $resultData[0] = array();


        foreach ($fileData[0] as $key => $value) {
            if ($request->file == "employee") {
                if ($key > 0) {

                    if ($value[6] != '') {
                        $input = $value[6];

                        $value[6] = "(" . substr($input, 0, 3) . ") " . substr($input, 3, 3) . "-" . substr($input, 6, 4);

                    }
                }
            }

            array_push($resultData[0], $value);

        }

        return json_encode($resultData);
        //dd($fileData, json_encode($fileData));y
    }

    public function updateStatus(Request $request, $id) {
        $employee = CourseModel::find($id);
        if (empty($employee)) {
            return response()->json(['Course' => 'Invalid id no course found.!'], 422);
        }
        CourseModel::where('id', $id)->update(['status' => $request->status]);

        $status = $request->status;
        CompanyCoursesModel::where('course_id', $id)->update(['company_course_status' => $status]);
        EmployeeCoursesModel::where('course_id', $id)->update(['course_status' => $status]);


        return response()->json(['Status' => 'Update Successfully..!'], 200);
    }

    public function updateFolderStatus(Request $request, $id) {
        $folder = CourseFolderModel::find($id);
        if (empty($folder)) {
            return response()->json(['folder' => 'Invalid id no folder found.!'], 422);
        }
        CourseFolderModel::where('id', $id)->update(['folder_status' => $request->status]);

        return response()->json(['Status' => 'Updated Successfully..!'], 200);
    }

    public function courseAssignment(Request $request) {
        try {
            $id = array();
            if ($request->folder_id) {

                foreach ($request->folder_id as $folder_id) {
                    // $folder_id = $request->folder_id;
                    //  log::debug($folder_id);
                    $getAllCoursesOfFolder1 = CourseFolderAssignModel::where('course_folder_id', $folder_id)->get();
                    $id1 = array();
                    foreach ($getAllCoursesOfFolder1 as $courses) {
                        array_push($id1, $courses->course_id);
                    }
                    $ids = implode(",", $id1);
                    $getAllCoursesOfFolder = CourseFolderAssignModel::select('tbl_course_coursefolder.*', 'tbl_course.next_course')->leftjoin('tbl_course', 'tbl_course.id', '=', 'tbl_course_coursefolder.course_id')->whereRaw('tbl_course_coursefolder.course_id not IN (SELECT next_course FROM tbl_course WHERE id IN (' . $ids . '))')->where('course_folder_id', $folder_id)->get();


                    foreach ($getAllCoursesOfFolder as $courses) {
                        array_push($id, $courses->course_id);
                    }
                    foreach ($request->assign_to[0]['employee_ids'] as $emp_id) {
                        $isAlreadyAssignCourseFolder = EmployeeCourseFolderModel::where('employee_id', $emp_id['id'])->where('folder_id', $folder_id)->count();
                        if ($isAlreadyAssignCourseFolder == 0) {
                            $data = new EmployeeCourseFolderModel();
                            $data->employee_id = $emp_id['id'];
                            $data->folder_id = $folder_id;
                            $data->save();
                        }
                    }
                }
            }
            if ($request->course_id) {
                $id = $request->course_id;
            }
            $company_id = $request->company_id;
            $assignTo = $request->assign_to;
            if (is_array($id)) {
                $checkedforEmployeeCoursess = array();
                foreach ($id as $course_ids) {
                    $checkCourses = CourseModel::select('for_employees')->where('id', $course_ids)->first();
                    $foremployees = $checkCourses->for_employees;
                    if ($foremployees == 0) {
                        array_push($checkedforEmployeeCoursess, $foremployees);
                    }
                }

                $checkedEmployees = array();
                foreach ($assignTo[0]['employee_ids'] as $emp_id) {
                    $checkEmployee = EmployeeModel::select('type')->where('id', $emp_id)->first();
                    if ($checkEmployee->type == "employee") {
                        array_push($checkedEmployees, $checkEmployee->type);
                    }
                }
                if (count($checkedforEmployeeCoursess) > 0 && count($checkedEmployees) > 0) {
                    return response()->json(['message' => 'Course can only be assign to managers.'], 422);
                }
            }
            $message = $this->courseAssign($id, $assignTo, $company_id);

            $employeess = array();
            $coursess = array();
            if (isset($assignTo[0]['employee_ids'])) {
                foreach ($assignTo[0]['employee_ids'] as $employee_id) {
                    array_push($employeess, $employee_id['id']);
                }
            } else {
                foreach ($message['employees'] as $employee_id) {
                    array_push($employeess, $employee_id->employee_id);
                }
            }
            $coursess = $id;
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
                            'userId' => Helper::maskUserId($value->id),
                        );
                        if ($value->email != '') {
                            CommonTrait::sendEmailToAssginCourseEmployee($data, $value->email, $value->id);
                        }
                    }
                }
            }

            return $message;

        } catch (Exception $th) {

            return response()->json(['message' => $th->getMessage()], 422);
        }
    }

    public function assignEmail(Request $request) {

        try {

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
                            $content = 'You are assigned to following course(s):';
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
                            'userId' => Helper::maskUserId($value->id),
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

            return response()->json(['message' => "Courses assigned successfully."], 200);
        } catch (Exception $th) {

            return response()->json(['message' => $th->getMessage()], 422);
        }

    }

    public function courseUnassignment(Request $request) {
        $course_id = $request->course_id;
        $company_id = $request->company_id;
        $unassignTo = $request->unassign_to;

        $course = CourseModel::find($course_id);
        if (empty($course)) {
            return response()->json(['Course' => 'Invalid id no course found.!'], 422);
        }
        $ids = $unassignTo[0]['employee_ids'];
        foreach ($ids as $id) {
            EmployeeCoursesModel::where([
                [
                    'employee_id',
                    $id['id'],
                ],
            ])->whereIn('course_id', $course_id)->delete();
            EmployeeCourseAttemptsModel::where([
                [
                    'user_id',
                    $id['id'],
                ],
            ])->whereIn('course_id', $course_id)->delete();
        }

        return response()->json(['Course' => 'Course Unassigned Successfully'], 200);
    }

    public function courseEmployees(Request $request) {
        $id = $request->course_id;
        $company_id = $request->company_id;
        $course = CourseModel::find($id);
        $data = [];
        if (empty($course)) {
            return response()->json(['Course' => 'Invalid id no course found.!'], 422);
        }

        $t = $this->courseEmployee($id, $company_id);
        $data['total'] = count($t);
        $data['data'] = $t;

        return response()->json($data, 200);
    }

    public function deleteLocation(Request $request) {
        $course_id = $request->course_id;
        $location_id = $request->location_id;
        CompanyCoursesModel::where([
            [
                'course_id',
                $course_id,
            ],
            [
                'company_id',
                $location_id,
            ],
        ])->delete();

        return response()->json(['Location' => 'Deleted Successfully..!'], 200);
    }

    public function courseData(Request $request) {
        $course_id = $request->course_id;
        $user_id = $request->user_id;
        $course = CourseModel::find($course_id);
        if (empty($course)) {

            return response()->json(['Course' => 'Invalid id no course found.!'], 422);
        }
        $data = $this->courseFullData($course_id, $user_id);
        $timerValue = EmployeeCoursesModel::where('course_id', $request->course_id)->where('employee_id', $user_id)->first();
        if ($timerValue) {
            $data[0]['timer_value'] = $timerValue->time_taken;
        }
        $status = 1;
        $is_pre_test = PreTestModel::getPreTestQuestion($course_id, $status);
        $data[0]['pretest_status'] = FALSE;
        if (!empty($is_pre_test)) {
            $isUserAttempts = EmployeeCourseAttemptsModel::where([
                'user_id' => $user_id,
                'course_id' => $request->course_id,
                'lesson_test' => 'pre_test',
                'lesson_test_id' => $is_pre_test['id'],
            ])->first();
            if ($isUserAttempts != NULL) {
                $data[0]['pretest_status'] = TRUE;
            }
        }
        $data[0]['pretest'] = $is_pre_test;
        $data[0]['survey'] = SurveyModel::getSurveyPreTestQuestion($request->course_id, $status);

        return response()->json($data, 200);
    }

    public function gamificationData(Request $request) {
        $course_id = $request->course_id;
        $lesson_id = $request->lesson_id;
        $gamification_data = DB::table('tbl_course_lesson_gamification')->where([
            'lesson_id' => $lesson_id,
            'course_id' => $course_id,
        ])->get();

        return response()->json($gamification_data, 200);
    }

    public function updateTimerValue(Request $request) {
        $time_seconds = "";
        if ($request->sec_time) {
            $time_seconds = isset($request->sec_time) ? $request->hour_time * 3600 + $request->min_time * 60 + $request->sec_time : $request->hour_time * 60 + $request->min_time;

        }
        EmployeeCoursesModel::where([
            [
                'course_id',
                $request->course_id,
            ],
            [
                'employee_id',
                $request->employee_id,
            ],
        ])->update(['time_taken' => $time_seconds]);

        $activity = new UserActivityLogModel;
        $activity->user_id = $request->employee_id;
        $activity->ip = $_SERVER['REMOTE_ADDR'];
        $activity->event = "Took Course";
        $activity->course_id = $request->course_id;
        $activity->total_time_spent = $time_seconds;
        $activity->created_at = Carbon::now('UTC');
        $activity->updated_at = Carbon::now('UTC');
        $activity->save();
    }

    public function allCourse() {
        $data = CourseModel::where([
            [
                'in_store',
                1,
            ],
            [
                'status',
                1,
            ],
        ])->get();

        return response()->json($data, 200);
    }

    public function allCourses() {
        $course = CourseModel::where([
            [
                'in_store',
                1,
            ],
            [
                'status',
                1,
            ],
        ])->get();

        return response()->json($course, 200);
    }

    public function unassignedCoursess(Request $request) {
        $folder_id = $request->folder_id;
        $course_folder = CourseFolderAssignModel::select('course_id')->where('course_folder_id', $folder_id)->get();
        $course = CourseModel::whereNotIn('id', $course_folder)->where('tbl_course.status', 1)->orderBy('tbl_course.name', 'ASC')->get();

        return response()->json($course, 200);
    }

    public function courseSubmit(Request $request) {
        $this->validate($request, [
            'course_id' => 'required|integer',
            'user_id' => 'required|integer',
        ]);
        $course_id = $request->course_id;
        $user_id = $request->user_id;
        $interface = $request->interface;
        $test_lesson_id = $request->test_lesson_id;
        $test_lesson = $request->test_lesson;
        $pass = $request->pass;
        $course_pass = $request->course_pass;
        $course_attempts = CourseModel::find($course_id);
        $course_certificate = CourseCertificatesModel::where('course_id', $course_id)->count();
        $user_attempts = EmployeeCourseAttemptsModel::where([
            [
                'user_id',
                $user_id,
            ],
            [
                'course_id',
                $course_id,
            ],
        ])->get()->toArray();
        if ($course_pass == 1) {


            $this->coursePass($course_id, $user_id, $course_pass);

            if (!empty($course_certificate)) {
                $this->pdfCertificate($course_id, $user_id, $interface);
            }
        } else if (!empty($user_attempts)) {

            $attempts = $course_attempts->course_allow_attempts - $user_attempts[0]['attempts'];
            if ($attempts <= 0) {
                $course_pass = 0;
                $this->courseFail($course_id, $user_id, $course_pass);
            }
        }

        $this->courseSubmits($course_id, $user_id, $test_lesson, $test_lesson_id, $pass);
    }

    public function generateCertificate(Request $request) {
        $course_id = $request->course_id;
        $employee_id = $request->employee_id;
        $this->downloadCertificate($course_id, $employee_id);
    }

    public function downloadCerts(Request $request) {
        $zip_file = time() . '.zip';
        $zip = Zip::create(base_path('public/employee/' . $zip_file));
        $assignTo = $request->assign_to;
        $employee_id = $request->employee_id;
        $ids = $assignTo[0]['ids'];
        foreach ($ids as $id) {
            $course_certificates = EmployeeCertificateModel::where('course_id', $id)->where('employee_id', $employee_id)->get();
            foreach ($course_certificates as $certificate) {
                $file_name = explode('employee/certificates/', $certificate->certificate_url);
                $file = $file_name[1];
                $zip->add(base_path('/public/employee/certificates/' . $file));
            }
        }
        $zip->close();

        return URL() . "/employee/" . $zip_file;
    }

    public function certificate() {
        return view('certificate');
    }

    public function unassignCourse(Request $request) {
        $company_id = $request->company_id;
        $course_id = $request->course_id;
        CompanyCoursesModel::where([
            [
                'course_id',
                $course_id,
            ],
            [
                'company_id',
                $company_id,
            ],
        ])->delete();
        $getempData = EmployeeCoursesModel::leftjoin('tbl_employee_company_locations', 'tbl_employee_company_locations.employee_id', '=', 'tbl_employee_courses.employee_id')->where('tbl_employee_courses.course_id', $course_id)->where('tbl_employee_courses.employee_course_status', '!=', '1')->where('tbl_employee_company_locations.company_id', $company_id)->delete();

        return response()->json(['Course' => 'Unassigned Successfully..!'], 200);
    }

    public function companies(Request $request) {
        $course_id = $request->course_id;
        $data = [];
        $companies = CourseModel::with('companies')->where('id', $course_id)->orderBy('created_at', 'DESC')->get();
        $data['companies'] = $companies;

        return response()->json($data, 200);
    }

    public function passEmployeesBACKUP(Request $request) {
        try {
            $user = Auth::user();
            $currentDate = Carbon::now('UTC')->format('Y-m-d');
            $columnName = [
                0 => 'tbl_employee.first_name',
                1 => 'tbl_employee.last_name',
                2 => 'tbl_employee_certificates.certificate_date',
                3 => 'tbl_employee_certificates.certificate_expiration_date',
                4 => 'tbl_company.name',
                6 => 'tbl_course.name',
            ];
            $orderBy = '';
            $orderColumn = "";
            if (isset($request->order) && isset($request->column)) {
                $orderBy = $request->order;
                $orderColumn = ($request->column < 5) ? $columnName[$request->column] : 'tbl_employee.first_name';
            }
            switch ($user->role_id) {
                case 1:
                case 5:

                    $employees = CourseCertificatesModel::

                    rightJoin('tbl_employee_certificates', 'tbl_employee_certificates.course_id', '=', 'tbl_course_certificate.course_id')

                        // ->leftJoin('tbl_employee_courses', function($query){
                        //     $query->on('tbl_employee_courses.employee_id', '=', 'tbl_employee_certificates.employee_id');
                        //     $query->on('tbl_employee_courses.course_id', '=', 'tbl_employee_certificates.course_id');
                        // })

                        ->leftJoin('tbl_employee', 'tbl_employee.id', '=', 'tbl_employee_certificates.employee_id')->leftJoin('tbl_course', 'tbl_course.id', '=', 'tbl_employee_certificates.course_id')
                        //  ->leftJoin('tbl_certificate', 'tbl_certificate.id', '=', 'tbl_course_certificate.certificate_id')
                        ->leftJoin('tbl_employee_company_locations', 'tbl_employee_certificates.employee_id', '=', 'tbl_employee_company_locations.employee_id')->leftJoin('tbl_company', 'tbl_company.id', '=', DB::Raw('(CASE WHEN `tbl_employee_company_locations`.location_id = 0 then `tbl_employee_company_locations`.company_id ELSE `tbl_employee_company_locations`.location_id END)'))->select('tbl_company.name as company_name', 'tbl_course.name as course_name', 'tbl_employee.id as employee_id', 'tbl_course_certificate.certificate_id as id', 'tbl_course_certificate.course_id as course_id', 'tbl_employee.first_name', 'tbl_employee_certificates.certificate_expiration_date', 'tbl_employee_certificates.id as employee_certifcate_id', 'tbl_employee.last_name', 'tbl_employee_certificates.certificate_date as employee_course_date_completed')->where('tbl_course_certificate.certificate_id', $request->certificate_id)->where('tbl_employee.status', 1);
                    if ($request->company_id != '' && $request->company_id != 0) {
                        $companyIds = $request->company_id;
                        $companies = CompanyModel::where('id', $companyIds)->first();
                        if ($companies->parent_id != 0) {
                            $where_data = 'tbl_employee_company_locations.location_id';
                        } else {
                            $where_data = 'tbl_employee_company_locations.company_id';
                        }
                        $employees->where($where_data, $companyIds);
                    }
                    if ($request->certificate_status == 'Expired Certificates') {
                        $employees->whereDate('tbl_employee_certificates.certificate_expiration_date', '<', $currentDate);
                    } else if ($request->certificate_status == 'Active Certificates') {
                        $employees->whereDate('tbl_employee_certificates.certificate_expiration_date', '>', $currentDate);
                    }

                    if (!empty($request->search)) {
                        $search = $request->search;
                        $employees->where(function($query) use ($search) {
                            $query->orWhere('tbl_employee.full_name', 'like', '%' . $search . '%');
                            $query->orWhere('tbl_employee.first_name', 'like', '%' . $search . '%');
                            $query->orWhere('tbl_employee.last_name', 'like', '%' . $search . '%');
                            $query->orWhere('tbl_course.name', 'like', '%' . $search . '%');
                            $query->orWhere('tbl_company.name', 'like', '%' . $search . '%');
                        });
                    }
                    break;
                case 2:
                case 3:

                    $where_data = "";
                    $companyIds = [];

                    if ($request->course_id || $request->certificate_id) {
                        $whereDataa = [
                            'tbl_course_certificate.course_id' => $request->course_id,
                            'tbl_course_certificate.certificate_id' => $request->certificate_id,
                        ];
                    } else {
                        $whereDataa = [];
                    }
                    if ($request->company_id != '' && $request->company_id != 0) {
                        $companyIds = array($request->company_id);
                        $companies = CompanyModel::whereIn('id', $companyIds)->first();
                        if ($companies->parent_id != 0) {
                            $where_data = 'tbl_employee_company_locations.location_id';
                        } else {
                            $where_data = 'tbl_employee_company_locations.company_id';
                        }
                    } else {
                        $companies = CompanyModel::getCompaniesByAdminUser($user->id);


                        if ($companies['isParent'] != 0) {
                            if (is_array($companies['isParent'])) {
                                $companyIds = $companies['isParent'];
                            } else {
                                $companyIds = array(
                                    $companies['isParent'],
                                );
                            }
                            $where_data = 'tbl_employee_company_locations.company_id';
                        } else {
                            $companyIds = $companies['isLocations'];
                            $where_data = 'tbl_employee_company_locations.location_id';
                        }
                    }

                    $employees = EmployeeCompanyLocationsModel::
                    leftJoin('tbl_company', 'tbl_company.id', '=', DB::Raw('(CASE WHEN `tbl_employee_company_locations`.location_id = 0 then `tbl_employee_company_locations`.company_id ELSE `tbl_employee_company_locations`.location_id END)'))->leftJoin('tbl_employee', 'tbl_employee.id', '=', 'tbl_employee_company_locations.employee_id')->rightJoin('tbl_employee_certificates', 'tbl_employee_certificates.employee_id', '=', 'tbl_employee.id')->leftJoin('tbl_employee_courses', function($query) {
                        $query->on('tbl_employee_courses.employee_id', '=', 'tbl_employee_certificates.employee_id');
                        $query->on('tbl_employee_courses.course_id', '=', 'tbl_employee_certificates.course_id');
                    })->rightJoin('tbl_course_certificate', 'tbl_course_certificate.course_id', '=', 'tbl_employee_certificates.course_id')->leftJoin('tbl_course', 'tbl_course.id', '=', 'tbl_employee_certificates.course_id')->leftJoin('tbl_certificate', 'tbl_certificate.id', '=', 'tbl_course_certificate.certificate_id')->whereIn($where_data, $companyIds)->where($whereDataa)->where('tbl_employee.status', 1);
                    if ($request->certificate_status == 'Expired Certificates') {
                        $employees->whereDate('tbl_employee_certificates.certificate_expiration_date', '<', $currentDate);
                    } else if ($request->certificate_status == 'Active Certificates') {
                        $employees->whereDate('tbl_employee_certificates.certificate_expiration_date', '>=', $currentDate);
                    }
                    $employees->select('tbl_company.name as company_name', 'tbl_certificate.name as certificate_name', DB::Raw('(CASE WHEN tbl_company.secondary_course_status && `tbl_course`.secondary_course_name!="" then `tbl_course`.secondary_course_name  ELSE `tbl_course`.name END) as course_name'), 'tbl_employee.id as employee_id', 'tbl_course_certificate.certificate_id as id', 'tbl_course_certificate.course_id as course_id', 'tbl_employee.first_name', 'tbl_employee_certificates.certificate_expiration_date', 'tbl_employee.last_name', 'tbl_employee_courses.employee_course_status', 'tbl_employee_certificates.certificate_date as employee_course_date_completed', 'tbl_employee_certificates.id as employee_certifcate_id');

                    if (!empty($request->search)) {
                        $search = $request->search;
                        $employees->where(function($query) use ($search) {
                            $query->orWhere('tbl_employee.full_name', 'like', '%' . $search . '%');
                            $query->orWhere('tbl_employee.first_name', 'like', '%' . $search . '%');
                            $query->orWhere('tbl_employee.last_name', 'like', '%' . $search . '%');
                            $query->orWhere('tbl_course.name', 'like', '%' . $search . '%');
                            $query->orWhere('tbl_company.name', 'like', '%' . $search . '%');
                        });
                    }
                    break;

                default:
                    $employees = CourseCertificatesModel::rightJoin('tbl_employee_certificates', 'tbl_employee_certificates.course_id', '=', 'tbl_course_certificate.course_id')->leftJoin('tbl_employee_courses', function($query) {
                        $query->on('tbl_employee_courses.employee_id', '=', 'tbl_employee_certificates.employee_id');
                        $query->on('tbl_employee_courses.course_id', '=', 'tbl_employee_certificates.course_id');
                    })->leftJoin('tbl_employee', 'tbl_employee.id', '=', 'tbl_employee_courses.employee_id')->leftJoin('tbl_course', 'tbl_course.id', '=', 'tbl_employee_courses.course_id')->leftJoin('tbl_certificate', 'tbl_certificate.id', '=', 'tbl_course_certificate.certificate_id')->select('tbl_employee_certificates.id as employee_certifcate_id', 'tbl_certificate.name as certificate_name', 'tbl_course.name as course_name', 'tbl_employee.id as employee_id', 'tbl_course_certificate.certificate_id as id', 'tbl_course_certificate.course_id as course_id', 'tbl_employee.first_name', 'tbl_employee_certificates.certificate_expiration_date', 'tbl_employee.last_name', 'tbl_employee_courses.employee_course_status', 'tbl_employee_certificates.certificate_date as employee_course_date_completed')->where('tbl_course_certificate.certificate_id', $request->certificate_id)->where('tbl_employee.status', 1);
                    if ($request->certificate_status == 'Expired Certificates') {
                        $employees->whereDate('tbl_employee_certificates.certificate_expiration_date', '<', $currentDate);
                    } else if ($request->certificate_status == 'Active Certificates') {
                        $employees->whereDate('tbl_employee_certificates.certificate_expiration_date', '>', $currentDate);
                    }
                    if (!empty($request->search)) {
                        $search = $request->search;
                        $employees->where(function($query) use ($search) {
                            $query->orWhere('tbl_employee.full_name', 'like', '%' . $search . '%');
                            $query->orWhere('tbl_employee.first_name', 'like', '%' . $search . '%');
                            $query->orWhere('tbl_employee.last_name', 'like', '%' . $search . '%');
                            $query->orWhere('tbl_course.name', 'like', '%' . $search . '%');
                            $query->orWhere('tbl_company.name', 'like', '%' . $search . '%');
                        });
                    }
                    break;
            }
            if ($orderColumn != '' && $orderBy != '') {
                $employees->orderBy($orderColumn, $orderBy);
            }
            $getSheet = array();
            foreach ($employees->get() as $key => $value) {
                $getSheet[$key]['First Name'] = ucfirst($value->first_name);
                $getSheet[$key]['Last Name'] = ucfirst($value->last_name);
                $getSheet[$key]['Location Name'] = ucfirst($value->company_name);
                $getSheet[$key]['Certificate Name'] = ucfirst($value->course_name);
                $getSheet[$key]['Completion Date'] = date('m/d/Y', strtotime($value->employee_course_date_completed));
                $getSheet[$key]['Expiration Date'] = date('m/d/Y', strtotime($value->certificate_expiration_date));
            }


            return response()->json([
                'employee' => $employees->get(),
                'download' => $getSheet,
            ], 200);


        } catch (Exception $th) {

            return response()->json(['message' => $th->getMessage()], 422);
        }
    }

    public function passEmployees(Request $request) {
        try {
            $user = Auth::user();
            $currentDate = Carbon::now('UTC')->format('Y-m-d');
            $columnName = [
                0 => 'tbl_employee.first_name',
                1 => 'tbl_employee.last_name',
                2 => 'tbl_employee_certificates.certificate_date',
                3 => 'tbl_employee_certificates.certificate_expiration_date',
                4 => 'tbl_company.name',
                6 => 'tbl_course.name',
            ];
            $orderBy = '';
            $orderColumn = "";
            if (isset($request->order) && isset($request->column)) {
                $orderBy = $request->order;
                $orderColumn = ($request->column < 5) ? $columnName[$request->column] : 'tbl_employee.first_name';
            }
            switch ($user->role_id) {
                case 1:
                case 5:

                    $is_folder_certificate = DB::table('tbl_course_folder_certificate')->where('certificate_id', $request->certificate_id)->count();
                    if ($is_folder_certificate == 0) {
                        $employees = EmployeeCertificateModel::
                        rightJoin('tbl_course_certificate', 'tbl_course_certificate.course_id', '=', 'tbl_employee_certificates.course_id')->leftJoin('tbl_employee', 'tbl_employee.id', '=', 'tbl_employee_certificates.employee_id')->leftJoin('tbl_course', 'tbl_course.id', '=', 'tbl_employee_certificates.course_id')->leftJoin('tbl_employee_company_locations', 'tbl_employee_certificates.employee_id', '=', 'tbl_employee_company_locations.employee_id')->leftJoin('tbl_company', 'tbl_company.id', '=', DB::Raw('(CASE WHEN `tbl_employee_company_locations`.location_id = 0 then `tbl_employee_company_locations`.company_id ELSE `tbl_employee_company_locations`.location_id END)'))->select('tbl_company.name as company_name', 'tbl_course.name as course_name', 'tbl_employee.id as employee_id', 'tbl_course_certificate.certificate_id as id', 'tbl_course_certificate.course_id as course_id', 'tbl_employee.first_name', 'tbl_employee_certificates.certificate_expiration_date', 'tbl_employee_certificates.id as employee_certifcate_id', 'tbl_employee.last_name', 'tbl_employee_certificates.certificate_date as employee_course_date_completed')->where('tbl_course_certificate.certificate_id', $request->certificate_id)->where('tbl_employee.status', 1);
                    } else {
                        $employees = EmployeeCertificateModel::
                        rightJoin('tbl_course_folder_certificate', 'tbl_course_folder_certificate.folder_id', '=', 'tbl_employee_certificates.course_id')->leftJoin('tbl_employee', 'tbl_employee.id', '=', 'tbl_employee_certificates.employee_id')->leftJoin('tbl_course_folder', 'tbl_course_folder.id', '=', 'tbl_employee_certificates.course_id')->leftJoin('tbl_employee_company_locations', 'tbl_employee_certificates.employee_id', '=', 'tbl_employee_company_locations.employee_id')->leftJoin('tbl_company', 'tbl_company.id', '=', DB::Raw('(CASE WHEN `tbl_employee_company_locations`.location_id = 0 then `tbl_employee_company_locations`.company_id ELSE `tbl_employee_company_locations`.location_id END)'))->select('tbl_company.name as company_name', 'tbl_course_folder.folder_name as course_name', 'tbl_employee.id as employee_id', 'tbl_course_folder_certificate.certificate_id as id', 'tbl_course_folder_certificate.course_id as course_id', 'tbl_employee.first_name', 'tbl_employee_certificates.certificate_expiration_date', 'tbl_employee_certificates.id as employee_certifcate_id', 'tbl_employee.last_name', 'tbl_employee_certificates.certificate_date as employee_course_date_completed')->where('tbl_course_folder_certificate.certificate_id', $request->certificate_id)->where('tbl_employee.status', 1);
                    }
                    if ($request->company_id != '' && $request->company_id != 0) {
                        $companyIds = $request->company_id;
                        $companies = CompanyModel::where('id', $companyIds)->first();
                        if ($companies->parent_id != 0) {
                            $where_data = 'tbl_employee_company_locations.location_id';
                        } else {
                            $where_data = 'tbl_employee_company_locations.company_id';
                        }
                        $employees->where($where_data, $companyIds);
                    }
                    if ($request->certificate_status == 'Expired Certificates') {
                        $employees->whereDate('tbl_employee_certificates.certificate_expiration_date', '<', $currentDate);
                    } else if ($request->certificate_status == 'Active Certificates') {
                        $employees->whereDate('tbl_employee_certificates.certificate_expiration_date', '>', $currentDate);
                    }
                    $employees->groupby('tbl_employee_certificates.id');
                    if ($is_folder_certificate == 0) {
                        $employees->select('tbl_company.name as company_name', 'tbl_employee_certificates.is_coursefolder_certificate as is_coursefolder', 'tbl_course.name as course_name', 'tbl_employee.id as employee_id', 'tbl_course_certificate.certificate_id as id', 'tbl_course_certificate.course_id as course_id', 'tbl_employee.first_name', 'tbl_employee_certificates.certificate_expiration_date', 'tbl_employee.last_name', 'tbl_employee_certificates.certificate_date as employee_course_date_completed', 'tbl_employee_certificates.id as employee_certifcate_id', 'tbl_employee_certificates.is_proctored_exam', DB::raw("CONCAT( '[', GROUP_CONCAT(JSON_OBJECT('first_name', tbl_employee.first_name,'last_name', tbl_employee.last_name,'employee_id', tbl_employee.id,'location', tbl_company.name)),']') AS list"));
                    } else {
                        $employees->select('tbl_company.name as company_name', 'tbl_employee_certificates.is_coursefolder_certificate as is_coursefolder', 'tbl_course_folder.folder_name as course_name', 'tbl_employee.id as employee_id', 'tbl_course_folder_certificate.certificate_id as id', 'tbl_course_folder_certificate.folder_id as course_id', 'tbl_employee.first_name', 'tbl_employee_certificates.certificate_expiration_date', 'tbl_employee.last_name', 'tbl_employee_certificates.certificate_date as employee_course_date_completed', 'tbl_employee_certificates.id as employee_certifcate_id', 'tbl_employee_certificates.is_proctored_exam', DB::raw("CONCAT( '[', GROUP_CONCAT(JSON_OBJECT('first_name', tbl_employee.first_name,'last_name', tbl_employee.last_name,'employee_id', tbl_employee.id,'location', tbl_company.name)),']') AS list"));
                    }
                    if (!empty($request->search)) {
                        $search = $request->search;
                        $employees->where(function($query) use ($search) {
                            $query->orWhere('tbl_employee.full_name', 'like', '%' . $search . '%');
                            $query->orWhere('tbl_employee.first_name', 'like', '%' . $search . '%');
                            $query->orWhere('tbl_employee.last_name', 'like', '%' . $search . '%');
                            $query->orWhere('tbl_course.name', 'like', '%' . $search . '%');
                            $query->orWhere('tbl_company.name', 'like', '%' . $search . '%');
                        });
                    }
                    break;
                case 2:
                case 3:

                    $where_data = "";
                    $companyIds = [];

                    if (!$request->is_folder && ($request->course_id || $request->certificate_id)) {
                        $whereDataa = [
                            'tbl_course_certificate.course_id' => $request->course_id,
                            'tbl_course_certificate.certificate_id' => $request->certificate_id,
                        ];
                    } else if ($request->is_folder) {
                        $whereDataa = [
                            'tbl_course_folder_certificate.folder_id' => $request->course_id,
                            'tbl_course_folder_certificate.certificate_id' => $request->certificate_id,
                        ];
                    } else {
                        $whereDataa = [];
                    }
                    if ($request->company_id != '' && $request->company_id != 0) {
                        $companyIds = array($request->company_id);
                        $companies = CompanyModel::whereIn('id', $companyIds)->first();
                        if ($companies->parent_id != 0) {
                            $where_data = 'tbl_employee_company_locations.location_id';
                        } else {
                            $where_data = 'tbl_employee_company_locations.company_id';
                        }
                    } else {
                        $companies = CompanyModel::getCompaniesByAdminUser($user->id);


                        if ($companies['isParent'] != 0) {
                            if (is_array($companies['isParent'])) {
                                $companyIds = $companies['isParent'];
                            } else {
                                $companyIds = array(
                                    $companies['isParent'],
                                );
                            }
                            $where_data = 'tbl_employee_company_locations.company_id';
                        } else {
                            $companyIds = $companies['isLocations'];
                            $where_data = 'tbl_employee_company_locations.location_id';
                        }
                    }
                    if (!$request->is_folder) {
                        $employees = EmployeeCompanyLocationsModel::
                        leftJoin('tbl_company', 'tbl_company.id', '=', DB::Raw('(CASE WHEN `tbl_employee_company_locations`.location_id = 0 then `tbl_employee_company_locations`.company_id ELSE `tbl_employee_company_locations`.location_id END)'))->leftJoin('tbl_employee', 'tbl_employee.id', '=', 'tbl_employee_company_locations.employee_id')->rightJoin('tbl_employee_certificates', 'tbl_employee_certificates.employee_id', '=', 'tbl_employee.id')->leftJoin('tbl_employee_courses', function($query) {
                            $query->on('tbl_employee_courses.employee_id', '=', 'tbl_employee_certificates.employee_id');
                            $query->on('tbl_employee_courses.course_id', '=', 'tbl_employee_certificates.course_id');
                        })->rightJoin('tbl_course_certificate', 'tbl_course_certificate.course_id', '=', 'tbl_employee_certificates.course_id')->leftJoin('tbl_course', 'tbl_course.id', '=', 'tbl_employee_certificates.course_id')->leftJoin('tbl_certificate', 'tbl_certificate.id', '=', 'tbl_course_certificate.certificate_id')->whereIn($where_data, $companyIds)->where($whereDataa)->where('tbl_employee.status', 1);
                        if ($request->certificate_status == 'Expired Certificates') {
                            $employees->whereDate('tbl_employee_certificates.certificate_expiration_date', '<', $currentDate);
                        } else if ($request->certificate_status == 'Active Certificates') {
                            $employees->whereDate('tbl_employee_certificates.certificate_expiration_date', '>=', $currentDate);
                        }
                        $employees->groupby('tbl_employee_courses.course_id', 'tbl_employee_courses.employee_id', 'tbl_employee_courses.employee_course_date_completed');
                        $employees->select('tbl_company.name as company_name', 'tbl_employee_certificates.is_coursefolder_certificate as is_coursefolder', 'tbl_course.name as course_name', 'tbl_employee.id as employee_id', 'tbl_course_certificate.certificate_id as id', 'tbl_course_certificate.course_id as course_id', 'tbl_employee.first_name', 'tbl_employee_certificates.certificate_expiration_date', 'tbl_employee.last_name', 'tbl_employee_certificates.certificate_date as employee_course_date_completed', 'tbl_employee_certificates.id as employee_certifcate_id', 'tbl_employee_certificates.is_proctored_exam', DB::raw("CONCAT( '[', GROUP_CONCAT(JSON_OBJECT('first_name', tbl_employee.first_name,'last_name', tbl_employee.last_name,'employee_id', tbl_employee.id,'location', tbl_company.name)),']') AS list"));

                        if (!empty($request->search)) {
                            $search = $request->search;
                            $employees->where(function($query) use ($search) {
                                $query->orWhere('tbl_employee.full_name', 'like', '%' . $search . '%');
                                $query->orWhere('tbl_employee.first_name', 'like', '%' . $search . '%');
                                $query->orWhere('tbl_employee.last_name', 'like', '%' . $search . '%');
                                $query->orWhere('tbl_course.name', 'like', '%' . $search . '%');
                                $query->orWhere('tbl_company.name', 'like', '%' . $search . '%');
                            });
                        }

                    } else {
                        $employees = EmployeeCompanyLocationsModel::

                        leftJoin('tbl_company', 'tbl_company.id', '=', DB::Raw('(CASE WHEN `tbl_employee_company_locations`.location_id = 0 then `tbl_employee_company_locations`.company_id ELSE `tbl_employee_company_locations`.location_id END)'))->leftJoin('tbl_employee', 'tbl_employee.id', '=', 'tbl_employee_company_locations.employee_id')->rightJoin('tbl_employee_certificates', 'tbl_employee_certificates.employee_id', '=', 'tbl_employee.id')->leftJoin('tbl_employee_coursefolders', function($query) {
                            $query->on('tbl_employee_coursefolders.employee_id', '=', 'tbl_employee_certificates.employee_id');
                            $query->on('tbl_employee_coursefolders.folder_id', '=', 'tbl_employee_certificates.course_id');
                        })->rightJoin('tbl_course_folder_certificate', 'tbl_course_folder_certificate.folder_id', '=', 'tbl_employee_certificates.course_id')->leftJoin('tbl_course_folder', 'tbl_course_folder.id', '=', 'tbl_employee_certificates.course_id')->leftJoin('tbl_certificate', 'tbl_certificate.id', '=', 'tbl_course_folder_certificate.certificate_id')->whereIn($where_data, $companyIds)->where($whereDataa)->where('tbl_employee.status', 1);
                        if ($request->certificate_status == 'Expired Certificates') {
                            $employees->whereDate('tbl_employee_certificates.certificate_expiration_date', '<', $currentDate);
                        } else if ($request->certificate_status == 'Active Certificates') {
                            $employees->whereDate('tbl_employee_certificates.certificate_expiration_date', '>=', $currentDate);
                        }
                        $employees->groupby('tbl_employee_coursefolders.folder_id', 'tbl_employee_coursefolders.employee_id');
                        $employees->select('tbl_company.name as company_name', 'tbl_employee_certificates.is_coursefolder_certificate as is_coursefolder', 'tbl_course_folder.folder_name as course_name', 'tbl_employee.id as employee_id', 'tbl_course_folder_certificate.certificate_id as id', 'tbl_course_folder_certificate.folder_id as course_id', 'tbl_employee.first_name', 'tbl_employee_certificates.certificate_expiration_date', 'tbl_employee.last_name', 'tbl_employee_certificates.certificate_date as employee_course_date_completed', 'tbl_employee_certificates.id as employee_certifcate_id', 'tbl_employee_certificates.is_proctored_exam', DB::raw("CONCAT( '[', GROUP_CONCAT(JSON_OBJECT('first_name', tbl_employee.first_name,'last_name', tbl_employee.last_name,'employee_id', tbl_employee.id,'location', tbl_company.name)),']') AS list"));

                        if (!empty($request->search)) {
                            $search = $request->search;
                            $employees->where(function($query) use ($search) {
                                $query->orWhere('tbl_employee.full_name', 'like', '%' . $search . '%');
                                $query->orWhere('tbl_employee.first_name', 'like', '%' . $search . '%');
                                $query->orWhere('tbl_employee.last_name', 'like', '%' . $search . '%');
                                $query->orWhere('tbl_course_folder.folder_name', 'like', '%' . $search . '%');
                                $query->orWhere('tbl_company.name', 'like', '%' . $search . '%');
                            });
                        }

                    }
                    break;

                default:
                    $employees = CourseCertificatesModel::rightJoin('tbl_employee_certificates', 'tbl_employee_certificates.course_id', '=', 'tbl_course_certificate.course_id')->leftJoin('tbl_employee_courses', function($query) {
                        $query->on('tbl_employee_courses.employee_id', '=', 'tbl_employee_certificates.employee_id');
                        $query->on('tbl_employee_courses.course_id', '=', 'tbl_employee_certificates.course_id');
                    })->leftJoin('tbl_employee', 'tbl_employee.id', '=', 'tbl_employee_courses.employee_id')->leftJoin('tbl_course', 'tbl_course.id', '=', 'tbl_employee_courses.course_id')->leftJoin('tbl_certificate', 'tbl_certificate.id', '=', 'tbl_course_certificate.certificate_id')->select('tbl_employee_certificates.id as employee_certifcate_id', 'tbl_course.name as course_name', 'tbl_employee.id as employee_id', 'tbl_course_certificate.certificate_id as id', 'tbl_course_certificate.course_id as course_id', 'tbl_employee.first_name', 'tbl_employee_certificates.certificate_expiration_date', 'tbl_employee.last_name', 'tbl_employee_certificates.certificate_date as employee_course_date_completed')->where('tbl_course_certificate.certificate_id', $request->certificate_id)->where('tbl_employee.status', 1);
                    if ($request->certificate_status == 'Expired Certificates') {
                        $employees->whereDate('tbl_employee_certificates.certificate_expiration_date', '<', $currentDate);
                    } else if ($request->certificate_status == 'Active Certificates') {
                        $employees->whereDate('tbl_employee_certificates.certificate_expiration_date', '>', $currentDate);
                    }
                    if (!empty($request->search)) {
                        $search = $request->search;
                        $employees->where(function($query) use ($search) {
                            $query->orWhere('tbl_employee.full_name', 'like', '%' . $search . '%');
                            $query->orWhere('tbl_employee.first_name', 'like', '%' . $search . '%');
                            $query->orWhere('tbl_employee.last_name', 'like', '%' . $search . '%');
                            $query->orWhere('tbl_course.name', 'like', '%' . $search . '%');
                            $query->orWhere('tbl_company.name', 'like', '%' . $search . '%');
                        });
                    }
                    break;
            }
            if ($orderColumn != '' && $orderBy != '') {
                $employees->orderBy($orderColumn, $orderBy);
            }
            $getSheet = array();
            foreach ($employees->get() as $key => $value) {
                $getSheet[$key]['First Name'] = ucfirst($value->first_name);
                $getSheet[$key]['Last Name'] = ucfirst($value->last_name);
                $getSheet[$key]['Location Name'] = ucfirst($value->company_name);
                $getSheet[$key]['Certificate Name'] = ucfirst($value->course_name);
                $getSheet[$key]['Completion Date'] = date('m/d/Y', strtotime($value->employee_course_date_completed));
                $getSheet[$key]['Expiration Date'] = date('m/d/Y', strtotime($value->certificate_expiration_date));
            }


            return response()->json([
                'employee' => $employees->get(),
                'download' => $getSheet,
            ], 200);


        } catch (Exception $th) {

            return response()->json(['message' => $th->getMessage()], 422);
        }
    }

    public function passEmployeesExcel(Request $request) {
        $course_id = $request->course_id;
        $company_id = $request->company_id;
        $course = CourseModel::find($course_id);
        if (empty($course)) {
            return response()->json(['Course' => 'Invalid id no course found.!'], 422);
        }
        $data = $this->passEmployeeExcel($course_id, $company_id);

        return response()->json($data, 200);
    }

    public function coursesExcel(Request $request) {
        $course_status = $request->course_status;

        $data = $this->courseExcel($course_status, $request->company_id);

        return response()->json($data, 200);
    }

    public function printCertificates(Request $request) {
        $zip_file = time() . '.zip';
        $zip = Zip::create(base_path('public/' . $zip_file));
        $assignTo = $request->assign_to;
        $employee_id = $request->employee_id;
        $ids = $assignTo[0]['course_ids'];
        foreach ($ids as $id) {
            $course_certificates = EmployeeCertificateModel::where('course_id', $id)->where('employee_id', $employee_id)->get();
            foreach ($course_certificates as $certificate) {
                $file_name = explode('employee/certificates/', $certificate->certificate_url);
                $file = $file_name[1];
                $zip->add(base_path('/public/employee/certificates/' . $file));
            }
        }
        $zip->close();

        return URL() . "/" . $zip_file;

    }

    public function totalPassedCourse(Request $request) {
        $validator = Validator::make($request->all(), [
            'course_id' => 'required',
        ]);

        if ($validator->fails()) {
            $errors = json_decode($validator->errors());
            foreach ($errors as $error) {
                $message[] = $error[0];
            }
            $message = implode(",", $message);

            return response()->json(['message' => $message], 422);
        }
        try {
            $course_id = $request->course_id;
            $employee_id = Auth::user()->id;
            $result = array();


            $course_lessons = EmployeeCoursesModel::rightJoin('tbl_course_lesson', function($join) {
                $join->on('tbl_course_lesson.course_id', '=', 'tbl_employee_courses.course_id');
                // $join->on('tbl_course_lesson.id', '=', 'tbl_employee_course_attempts.lesson_test_id');
            })->leftJoin('tbl_employee_course_attempts', function($join) {
                $join->on('tbl_course_lesson.course_id', '=', 'tbl_employee_course_attempts.course_id');
                $join->on('tbl_employee_courses.employee_id', '=', 'tbl_employee_course_attempts.user_id');
                $join->on('tbl_course_lesson.id', '=', 'tbl_employee_course_attempts.lesson_test_id');
            })->select('tbl_employee_course_attempts.pass_fail', 'tbl_employee_course_attempts.lesson_test as lesson_type', 'tbl_course_lesson.id as course_lesson_id', 'tbl_course_lesson.type', 'tbl_course_lesson.course_lesson_name', 'tbl_course_lesson.course_lesson_content')->where([
                'tbl_employee_courses.employee_id' => $employee_id,
                'tbl_employee_courses.course_id' => $course_id,
            ])->orderby('tbl_course_lesson.order', 'asc')->get()->toArray();

            // return response()->json(['message'=> $course_lessons, 'user' =>  Auth::user()->id, 'course' => $course_id],422);
            foreach ($course_lessons as $value) {
                if ($value['pass_fail'] == NULL) {

                    $value['pass_fail'] = 0;
                }

                if ($value['lesson_type'] == NULL) {

                    $value['lesson_type'] = 'lesson';
                }
                array_push($result, $value);
            }
            $course_tests = EmployeeCoursesModel::rightJoin('tbl_course_test', function($join) {
                $join->on('tbl_course_test.course_id', '=', 'tbl_employee_courses.course_id');
                // $join->on('tbl_course_test.id', '=', 'tbl_employee_course_attempts.lesson_test_id');
            })->leftJoin('tbl_employee_course_attempts', function($join) {
                $join->on('tbl_course_test.course_id', '=', 'tbl_employee_course_attempts.course_id');
                $join->on('tbl_employee_courses.employee_id', '=', 'tbl_employee_course_attempts.user_id');
                $join->on('tbl_course_test.id', '=', 'tbl_employee_course_attempts.lesson_test_id');
            })->select('tbl_employee_course_attempts.pass_fail', 'tbl_employee_course_attempts.lesson_test  as lesson_type', 'tbl_course_test.id as course_test_id', 'tbl_course_test.course_test_instruction')->where([
                'tbl_employee_courses.employee_id' => $employee_id,
                'tbl_employee_courses.course_id' => $course_id,
            ])->get();

            foreach ($course_tests as $value) {
                if ($value['pass_fail'] == NULL) {

                    $value['pass_fail'] = 0;
                }
                if ($value['lesson_type'] == NULL) {

                    $value['lesson_type'] = 'test';
                }
                array_push($result, $value);
            }

            $data['tests'] = $result;

            return response()->json([
                'message' => '',
                'data' => $data,
            ], 200);

        } catch (Exception $th) {

            return response()->json(['message' => $th->getMessage()], 422);
        }
    }

    public function downloadCertificates($id) {
        $zip_file = time() . '.zip';
        $zip = Zip::create(base_path('public/' . $zip_file));
        $course_certificates = EmployeeCertificateModel::where('course_id', $id)->where('manual', '0')->get();

        foreach ($course_certificates as $certificate) {
            $file_name = explode('employee/certificates/', $certificate->certificate_url);
            $file = $file_name[1];
            $zip->add(base_path('/public/employee/certificates/' . $file));
        }
        $zip->close();

        return URL() . "/" . $zip_file;
    }

    public function certificateReport(Request $request) {
        //$request->report_type => (open_course=2=expire)/(non_complaint=expired=0,3)/(complaint=Date Completed, Manual Passed Date=1)

        $validator = Validator::make($request->all(), [
            'report_type' => 'required',

            'course_id' => 'required',
        ]);

        if ($validator->fails()) {
            $errors = json_decode($validator->errors());
            foreach ($errors as $error) {
                $message[] = $error[0];
            }
            $message = implode(",", $message);

            return response()->json(['message' => $message], 422);
        }

        $company_id = [];
        $whereData = "";
        if ($request->company_id) {

            $company_id = $request->company_id;


            $checkChildCompany = CompanyModel::where('id', $company_id)->first();

            if ($checkChildCompany == NULL) {

                return response()->json(['message' => 'Company is not valid, try another.'], 422);
            }
            $company_id = array();
            if ($checkChildCompany->parent_id == 0) {
                $whereData = "tbl_employee_company_locations.company_id";
                array_push($company_id, $checkChildCompany->id);
            } else {
                $whereData = "tbl_employee_company_locations.location_id";
                array_push($company_id, $checkChildCompany->id);
            }
        } else {
            $user = Auth::user();
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
                $whereData = "tbl_employee_company_locations.company_id";
            } else {
                $company_id = $companies['isLocations'];
                $whereData = "tbl_employee_company_locations.location_id";
            }
        }
        try {
            $courseId = (int)$request->course_id;
            $getReport = array();

            $result = EmployeeCoursesModel::

            leftJoin('tbl_employee', 'tbl_employee.id', '=', 'tbl_employee_courses.employee_id')->leftjoin('tbl_employee_company_locations', 'tbl_employee_company_locations.employee_id', '=', 'tbl_employee_courses.employee_id')->leftJoin('tbl_company', 'tbl_company.id', '=', DB::Raw('(CASE WHEN `tbl_employee_company_locations`.location_id = 0 then `tbl_employee_company_locations`.company_id ELSE `tbl_employee_company_locations`.location_id END)'))->leftJoin('tbl_roles', 'tbl_roles.id', '=', 'tbl_employee.role_id')->leftJoin('tbl_course', 'tbl_course.id', '=', 'tbl_employee_courses.course_id')->leftJoin('tbl_job_title', 'tbl_job_title.id', '=', 'tbl_employee.job_title_id')->select('tbl_company.id as company_id', 'tbl_company.name as child_company_name', 'tbl_company.name as comapny_name', 'tbl_employee.id as employee_id', 'tbl_employee.first_name as first_name', 'tbl_employee.last_name as last_name', 'tbl_job_title.name as job_title', 'tbl_employee.user_name', 'tbl_course.name as course_name', 'tbl_course.id as course_id', 'tbl_employee_courses.employee_course_status', 'tbl_employee_courses.employee_course_date_completed', 'tbl_employee_courses.employee_course_date_assigned', 'tbl_employee_courses.employee_course_due_date', 'tbl_roles.role as type', 'tbl_employee.email', 'tbl_employee.phone_num', 'tbl_employee.city', 'tbl_employee.state', 'tbl_employee.address')->where('tbl_employee.status', '1')->whereIn($whereData, $company_id);


            if ($courseId > 0) {
                $result->where('tbl_employee_courses.course_id', $courseId);
            }

            $headerReport = array(
                "Company Name" => '',
                "First Name" => '',
                "Last Name" => '',
                "Email" => '',
                "Username" => '',
                "Role" => '',
                "Job Title" => '',
                "Course Name" => '',
            );
            $message = "";

            switch ($request->report_type) {
                case 'open_course':
                    $result->where('tbl_employee_courses.employee_course_status', 2);
                    $getReport = CourseModel::getCourseReport($result->get(), $request->report_type);
                    if (empty($getReport)) {
                        $headerReport["Expires"] = '';
                        array_push($getReport, $headerReport);
                        $message = "No Open Courses at this Time";
                    }

                    break;
                case 'non_compliance':
                    $result->where(function($query) {
                        $query->where('tbl_employee_courses.employee_course_status', 0);
                        $query->orWhere('tbl_employee_courses.employee_course_status', 3);
                    });
                    $getReport = CourseModel::getCourseReport($result->get(), $request->report_type);
                    if (empty($getReport)) {
                        $getReport = array();
                        $headerReport["Expired"] = '';
                        array_push($getReport, $headerReport);
                        $message = "No Expired Courses at this Time.";
                    }

                    break;
                case 'compliance':
                    $result->where('tbl_employee_courses.employee_course_status', 1);
                    $getReport = CourseModel::getCourseReport($result->get(), $request->report_type);
                    if (empty($getReport)) {
                        $getReport = array();
                        $headerReport["Expires"] = '';
                        $headerReport["Date Completed"] = '';
                        array_push($getReport, $headerReport);
                        $message = "No Compliant Courses at this Time";
                    }
                    break;
                default:
                    $result->where('tbl_employee_courses.employee_course_status', 2);
                    if (empty($getReport)) {
                        $headerReport["Expires"] = '';
                        array_push($getReport, $headerReport);
                        $message = "No records found.";
                    }
                    $getReport = CourseModel::getCourseReport($result->get(), $request->report_type);
                    break;
            }

            if ($result->count() == 0) {

                return response()->json([
                    'message' => $message,
                    'data' => $getReport,
                ], 422);
            }

            return response()->json($getReport, 200);

        } catch (Exception $ex) {

            return response()->json($ex->getMessage(), 422);
        }

    }

    public function preTest(Request $request) {
        $validator = Validator::make($request->all(), [
            'course_id' => 'required',
            'name' => 'required',
            'instructions' => 'required',
        ]);
        if ($validator->fails()) {
            $errors = json_decode($validator->errors());
            foreach ($errors as $error) {
                $message[] = $error[0];
            }
            $message = implode(",", $message);

            return response()->json(['message' => $message], 422);
        }
        try {
            $dataPreTest = array(
                'course_id' => $request->course_id,
                'name' => $request->name,
                'instruction' => $request->instructions,
                'number_of_questions' => 0,
                'created_at' => Carbon::now('UTC'),
            );
            $preTestId = PreTestModel::insertGetId($dataPreTest);
            if ($preTestId) {
                if (is_array($request->pretest_questions) && !empty($request->pretest_questions)) {
                    $insertQuestionAnswer = PreTestModel::preTestQuestion($preTestId, $request->pretest_questions);

                    if ($insertQuestionAnswer) {

                        return response()->json(['message' => 'Pre test created successfully.'], 200);
                    }

                    return response()->json(['message' => 'Something is wrong, try again'], 422);
                }

                return response()->json(['message' => 'Pre test created successfully.'], 200);
            } else {

                return response()->json(['message' => 'Something is wrong, try again'], 422);
            }

        } catch (Exception $ex) {

            return response()->json(['message' => $ex->getMessage()], 422);
        }
    }

    public function preTestUpdate(Request $request, $id) {
        $validator = Validator::make($request->all(), [
            'course_id' => 'required',
            'name' => 'required',
            'instructions' => 'required',
        ]);
        if ($validator->fails()) {
            $errors = json_decode($validator->errors());
            foreach ($errors as $error) {
                $message[] = $error[0];
            }
            $message = implode(",", $message);

            return response()->json(['message' => $message], 422);
        }
        try {
            $dataPreTest = array(
                'course_id' => $request->course_id,
                'name' => $request->name,
                'instruction' => $request->instructions,
                'number_of_questions' => $request->number_of_questions,
                'status' => 1,
            );
            PreTestModel::where('id', $id)->update($dataPreTest);
            PreTestModel::updatePreTestQuestion($id, $request->pretest_questions);

            return response()->json(['message' => 'Pre test updated successfully.'], 200);

        } catch (Exception $ex) {

            return response()->json(['message' => $ex->getMessage()], 422);
        }
    }

    public function preTestDelete($id) {
        try {
            $deleteQuestionAnswer = PreTestModel::deletePreTestQuestion($id);
            if ($deleteQuestionAnswer) {
                PreTestModel::where('id', $id)->delete();

                return response()->json(['message' => 'Pre test question answers deleted successfully.'], 200);
            }

            return response()->json(['message' => 'Something is wrong, try again'], 422);
        } catch (Exception $ex) {

            return response()->json(['message' => $ex->getMessage()], 422);
        }
    }

    public function readQuestionAnswer(Request $request) {
        try {

            //             print_r($_FILES);
            //             die;
            //             $row = 1;
            //             $file = $request->file('questionAnswerFile');
            //             if (($handle = fopen( $file, "r")) !== FALSE) {
            //             while (($data = fgetcsv($handle, 10000, ",")) !== FALSE) {
            //                 $num = count($data);
            //                 echo "<p> $num fields in line $row: <br /></p>\n";
            //                 $row++;
            //                 for ($c=0; $c < $num; $c++) {
            //                     echo $data[$c] . "<br />\n";
            //                 }
            //             }
            //             fclose($handle);
            //             }
            // die;

            $file = $request->file('questionAnswerFile');
            $fileData = Excel::toArray(new QuestionsImport, $file);

            //$fileData = [[["Question Text","Active Question","Answer Text","Correct Answer","Active Answer"],["This is a test question for a lesson?",1,null,null,null],[null,null,"It is a question",1,1],[null,null,"It is not a question",0,1],[null,null,"How should I know",0,1],[null,null,"Please help",0,1],["This is a true\/false test question.",1,null,null,null],[null,null,"True  ",1,1],[null,null,"False   ",0,1]]];
            $questionFormat = array();
            $i = 0;
            $j = 0;
            foreach ($fileData[0] as $key => $value) {
                if ($key == 0) {
                    if ($value[0] != 'Question Text') {

                        return response()->json(['message' => 'Spreadsheet sample is not correct.'], 422);
                    }

                    if ($value[1] != 'Active Question') {

                        return response()->json(['message' => 'Spreadsheet sample is not correct.'], 422);
                    }

                    if ($value[2] != 'Answer Text') {

                        return response()->json(['message' => 'Spreadsheet sample is not correct.'], 422);
                    }

                    if ($value[3] != 'Correct Answer') {

                        return response()->json(['message' => 'Spreadsheet sample is not correct.'], 422);
                    }

                    if ($value[4] != 'Active Answer') {

                        return response()->json(['message' => 'Spreadsheet sample is not correct.'], 422);
                    }
                } else {
                    if ($value[0] != '') {
                        $question = array(
                            "question" => $value[0],
                            "status" => $value[1],
                        );
                        $questionFormat[$i] = $question;
                        $i++;
                        $j = 0;
                    } else {
                        $answers = array();
                        $answers = array(
                            "answer" => $value[2],
                            "currect_answer" => $value[3],
                            "status" => $value[4],
                        );
                        $questionFormat[$i - 1]["answers"][$j] = $answers;
                        $j++;
                    }
                }
            }

            return response()->json([
                'message' => "File read successfully.",
                'data' => $questionFormat,
            ], 200);
        } catch (Exception $th) {

            return response()->json(['message' => $th->getMessage()], 422);
        }
    }

    public function templates() {
        return DB::table('tbl_templates')->orderby('name', 'asc')->get();
    }

    public function employeeAnswer(Request $request) {
        $validator = Validator::make($request->all(), [
            'course_id' => 'required',
            'test_id' => 'required',
            'test_type' => 'required',

        ]);
        if ($validator->fails()) {
            $errors = json_decode($validator->errors());
            foreach ($errors as $error) {
                $message[] = $error[0];
            }
            $message = implode(",", $message);

            return response()->json(['message' => $message], 422);
        }

        try {
            $result_percentage = 0;
            $user_id = Auth::user()->id;
            $response = array();
            $testData = array();
            $is_last_lesson = 0;
            if (!empty($request->is_last_lesson)) {
                $is_last_lesson = 1;
            }

            $is_test_ids = array();
            $is_test_types = array();
            $is_test_attempt = DB::table('tbl_employee_course_test_attempts')->where([
                'employee_id' => $user_id,
                'course_id' => $request->course_id,
                'status' => 0,
            ])->first();
            if ($is_test_attempt == NULL) {
                array_push($is_test_ids, $request->test_id);
                array_push($is_test_types, $request->test_type);
                $is_test_types = array();
                $course_test_attempts = array(
                    'employee_id' => $user_id,
                    'course_id' => $request->course_id,
                    'test_id' => json_encode($is_test_ids),
                    'test_type' => json_encode($is_test_types),
                    'status' => $is_last_lesson,
                    'created_at' => Carbon::now('UTC'),
                );
                $test_attempt_id = DB::table('tbl_employee_course_test_attempts')->insertGetId($course_test_attempts);
            } else {
                $test_attempt_id = $is_test_attempt->id;
                $is_test_ids = json_decode($is_test_attempt->test_id, TRUE);
                $is_test_types = json_decode($is_test_attempt->test_type, TRUE);
                array_push($is_test_ids, $request->test_id);
                array_push($is_test_types, $request->test_type);
                DB::table('tbl_employee_course_test_attempts')->where('id', $test_attempt_id)->update([
                    'test_id' => json_encode($is_test_ids),
                    'test_type' => json_encode($is_test_types),
                    'status' => $is_last_lesson,
                    'updated_at' => Carbon::now('UTC'),
                ]);
            }
            switch ($request->test_type) {
                case ($request->test_type == 'pre_test' || $request->test_type == 'survey'):
                    $question_answers = array();
                    $mailData = array();
                    $questionIds = array();
                    $answersForPretest = array();
                    $total_question = count($request->questions);
                    foreach ($request->questions as $key => $value) {
                        $answer_id = 0;
                        $answer_title = 0;
                        if (isset($value['answer_id'])) {
                            $answer_id = $value['answer_id'];
                        }
                        if (isset($value['answer'])) {
                            $answer_title = $value['answer'];
                        }
                        if ($request->test_type == 'survey') {
                            $get_validation_data = DB::table('tbl_survey_questions')->where('survey_id', $request->test_id)->where('id', $value['question_id'])->first();
                        } else {
                            $get_validation_data = DB::table('tbl_pre_test_questions')->where('pre_test_id', $request->test_id)->where('id', $value['question_id'])->first();
                        }

                        if ($get_validation_data->validation == 1) {
                            if ($answer_title == '') {
                                return response()->json(['message' => "Phone number is required."], 422);
                                exit;
                            }
                            if (!empty($answer_title)) {
                                if (CommonTrait::isValidPhone($answer_title) == FALSE) {
                                    return response()->json(['message' => "Phone number is not correct,  Phone number should be 10 digit."], 422);
                                    exit;
                                }
                            }

                        }
                        if ($get_validation_data->validation == 2) {
                            if ($answer_title == '') {
                                return response()->json(['message' => "Email is required."], 422);
                                exit;
                            }
                            if (CommonTrait::isValidEmail($answer_title) == FALSE) {
                                return response()->json(['message' => "Email is not valid, Enter correct email."], 422);
                                exit;
                            }

                        }
                        if ($get_validation_data->validation == 3 && $answer_title == '') {
                            return response()->json(['message' => "Text is required."], 422);
                            exit;
                        }
                        if ($get_validation_data->validation == 4 && $answer_title == '') {
                            return response()->json(['message' => "Date is required."], 422);
                            exit;
                        }
                        if ($get_validation_data->validation == 5) {
                            if($answer_title == '') {
                                return response()->json(['message' => "SSN is required"], 422);
                                exit;
                            }
                            if(strlen($answer_title) != 11) {
                                return response()->json(['message' => "Please enter the valid SSN number"], 422);
                                exit;
                            }
                        }

                        $question_answers[$key]['test_attempt_id'] = $test_attempt_id;
                        $question_answers[$key]['question_id'] = $value['question_id'];
                        $question_answers[$key]['answer_id'] = $answer_id;
                        $question_answers[$key]['employee_id'] = $user_id;
                        $question_answers[$key]['test_id'] = $request->test_id;
                        $question_answers[$key]['test_type'] = ($request->test_type == 'pre_test') ? 1 : 2;
                        $question_answers[$key]['question_title'] = $value['question'];
                        $question_answers[$key]['course_id'] = $request->course_id;
                        $question_answers[$key]['answer_title'] = $answer_title;
                        $mailData[$key]['question_title'] = $value['question'];
                        $mailData[$key]['answer_title'] = $answer_title;
                        $question_id = $value['question_id'];
                        $questionIds[$key] = $question_id;
                        $answersForPretest[$question_id] = $answer_title;
                    }
                    $insert = DB::table('tbl_employee_pre_test_survey_answer')->insert($question_answers);
                    $user_result = 1;
                    $allowed_attempts = 0;
                    $marks = 0;
                    $testData['user_id'] = $user_id;
                    $testData['course_id'] = $request->course_id;
                    $testData['test_type'] = $request->test_type;
                    $testData['test_id'] = $request->test_id;
                    $testData['user_result'] = $user_result;
                    $testData['allowed_attempts'] = $allowed_attempts;
                    $testData['result_percentage'] = 0;
                    $testData['is_last_lesson'] = $is_last_lesson;
                    $testData['number_of_question'] = $total_question;

                    $userAttempts = EmployeeCourseAttemptsModel::testAttempts($testData);
                    $message = "";
                    if ($request->test_type == 'survey') {
                        $course = CourseModel::getCourseSurveyDetails($request->course_id, $request->test_id);
                        $comapnyId = CompanyModel::getCompantIdByEmployeeId($user_id);
                        $company_data = CompanyModel::where('id', $comapnyId)->select('name')->first();
                        if ($company_data == NULL) {
                            $surveyData = array(
                                'username' => Auth::user()->first_name . ' ' . Auth::user()->last_name,
                                'company_name' => 'Individual',
                                'course_name' => $course->course_name,
                                'survey_name' => $course->survey_name,
                                "survey" => $mailData,
                            );
                        } else {
                            $surveyData = array(
                                'username' => Auth::user()->first_name . ' ' . Auth::user()->last_name,
                                'company_name' => $company_data->name,
                                'course_name' => $course->course_name,
                                'survey_name' => $course->survey_name,
                                "survey" => $mailData,
                            );
                        }

                        User::sendSurveyToSupport($surveyData);
                        $message = 'Survey submitted successfully.';
                    } else {
                        $pre_test_id = $request->test_id;
                        $update_data = DB::table('tbl_pre_test_questions')->where('pre_test_id', $pre_test_id)->whereIn('id', $questionIds)->get();
                        $is_update_data = array();
                        $i = 0;
                        foreach ($update_data as $value) {
                            switch ($value->is_update_employee) {
                                case $this->status['pre_test_user_field']['dob']:
                                    if ($answersForPretest[$value->id] != '') {
                                        EmployeeModel::where('id', $user_id)->update(['dob' => $answersForPretest[$value->id]]);
                                    }
                                    break;
                                case $this->status['pre_test_user_field']['social_security']:
                                    if ($answersForPretest[$value->id] != '') {
                                        EmployeeModel::where('id', $user_id)->update(['social_security' => $answersForPretest[$value->id]]);
                                    }
                                    break;
                                case $this->status['pre_test_user_field']['address1']:
                                    if ($answersForPretest[$value->id] != '') {
                                        EmployeeModel::where('id', $user_id)->update(['address' => $answersForPretest[$value->id]]);
                                    }
                                    break;
                                case $this->status['pre_test_user_field']['city']:
                                    if ($answersForPretest[$value->id] != '') {
                                        EmployeeModel::where('id', $user_id)->update(['city' => $answersForPretest[$value->id]]);
                                    }
                                    break;
                                case $this->status['pre_test_user_field']['state']:
                                    if ($answersForPretest[$value->id] != '') {
                                        EmployeeModel::where('id', $user_id)->update(['state' => $answersForPretest[$value->id]]);
                                    }
                                    break;
                                case $this->status['pre_test_user_field']['zipcode']:
                                    if ($answersForPretest[$value->id] != '') {
                                        EmployeeModel::where('id', $user_id)->update(['zipcode' => $answersForPretest[$value->id]]);
                                    }
                                    break;
                                case $this->status['pre_test_user_field']['email']:
                                    if ($answersForPretest[$value->id] != '') {
                                        $employeeData = EmployeeModel::where('id', $user_id)->first();
                                        if ($employeeData->email == "" || $employeeData->email == NULL) {
                                            EmployeeModel::where('id', $user_id)->update(['email' => $answersForPretest[$value->id]]);
                                        }
                                    }
                                    break;
                                case $this->status['pre_test_user_field']['phone_number']:
                                    if ($answersForPretest[$value->id] != '') {
                                        EmployeeModel::where('id', $user_id)->update(['phone_num' => $answersForPretest[$value->id]]);
                                    }
                                    break;
                                default:
                                    $is_update_data = array();
                                    break;
                            }
                        }
                        $course = CourseModel::getCoursePretestDetails($request->course_id, $pre_test_id);
                        $comapnyId = CompanyModel::getCompantIdByEmployeeId($user_id);
                        $company_data = CompanyModel::where('id', $comapnyId)->select('name')->first();

                        if ($company_data == NULL) {
                            $pretestData = array(
                                'username' => Auth::user()->first_name . ' ' . Auth::user()->last_name,
                                'company_name' => 'Individual',
                                'course_name' => $course->course_name,
                                'pretest_name' => $course->pretest_name,
                            );
                        } else {
                            $pretestData = array(
                                'username' => Auth::user()->first_name . ' ' . Auth::user()->last_name,
                                'company_name' => $company_data->name,
                                'course_name' => $course->course_name,
                                'pretest_name' => $course->pretest_name,
                            );
                        }

                        $pretestData['pretest'] = [];

                        foreach ($request->questions as $question) {
                            $pretestData['pretest'][] = [
                                'question_title' => $question['question'],
                                'answer_title' => $question['answer'],
                            ];
                        }

                        // User::sendPreTestToSupport($pretestData);
                        $message = 'Pre test submitted successfully.';
                    }
                    $response['message'] = $message;
                    $response['status'] = 1;
                    $response['data'] = $userAttempts;
                    break;
                case ($request->test_type == 'lesson' || $request->test_type == 'test'):

                    $total_question = count($request->questions);
                    $total_currect_answer = 0;
                    $user_result = 0;
                    if ($total_question == 0) {
                        $result_percentage == 100;
                        $user_result = 1;
                    } else {
                        $questionError = "";
                        foreach ($request->questions as $key => $value) {
                            $answer_id = 0;
                            $answer_title = 0;
                            if (isset($value['answer_id'])) {
                                $answer_id = $value['answer_id'];
                                if ($answer_id == 0) {

                                    $questionError = $value['question'];
                                    $numberOfQuestion = $key + 1;

                                    return response()->json(['message' => "<center>Please answer this question <br/>#" . $numberOfQuestion . ": " . $questionError . "</center>"], 422);
                                    exit;

                                }

                            }
                            if (isset($value['answer'])) {
                                $answer_title = $value['answer'];
                            }
                            $question_answers[$key]['test_attempt_id'] = $test_attempt_id;
                            $question_answers[$key]['question_id'] = $value['question_id'];
                            $question_answers[$key]['answer_id'] = $answer_id;
                            $question_answers[$key]['employee_id'] = $user_id;
                            $question_answers[$key]['test_id'] = $request->test_id;
                            $question_answers[$key]['test_type'] = ($request->test_type == 'lesson') ? 1 : 2;
                            $question_answers[$key]['question_title'] = $value['question'];
                            $question_answers[$key]['course_id'] = $request->course_id;
                            $question_answers[$key]['answer_title'] = $answer_title;
                            $question_answers[$key]['is_currect'] = 0;
                            if ((int)$answer_id == (int)$value['currect_answer_id']) {
                                $total_currect_answer++;
                                $question_answers[$key]['is_currect'] = 1;
                            }
                        }
                        $result_percentage = round(($total_currect_answer / $total_question) * 100, 2);
                        DB::table('tbl_employee_answers')->insert($question_answers);
                    }
                    $pass_rate = 0;
                    $allowed_attempts = 0;
                    $message = "";
                    if ($request->test_type == 'test') {
                        $test = CourseModel::where('id', $request->course_id)->first();
                        $pass_rate = $test->passing_percent;
                        $allowed_attempts = $test->allow_attempts;
                        $testData['pass_message'] = $test->pass_message;
                        $testData['fail_message'] = $test->fail_message;

                    } else {
                        $lesson = CourseLessonModel::where('id', $request->test_id)->first();
                        $pass_rate = $lesson->passing_rate;
                        $allowed_attempts = $lesson->allowed_attempts;
                    }
                    if ($result_percentage >= $pass_rate) {
                        $user_result = 1;
                    }

                    $testData['user_id'] = $user_id;
                    $testData['course_id'] = $request->course_id;
                    $testData['test_type'] = $request->test_type;
                    $testData['test_id'] = $request->test_id;
                    $testData['user_result'] = $user_result;
                    $testData['allowed_attempts'] = $allowed_attempts;
                    $testData['result_percentage'] = $result_percentage;
                    $testData['is_last_lesson'] = $is_last_lesson;
                    $testData['number_of_question'] = $total_question;
                    $userAttempts = EmployeeCourseAttemptsModel::testAttempts($testData);
                    $response['message'] = $userAttempts['message'];
                    $response['data'] = $userAttempts['courseAttempts'];
                    $response['retake'] = $userAttempts['retake'];
                    $response['status'] = $userAttempts['status'];

                    break;
                default:
                    break;
            }


            return response()->json($response, 200);

        } catch (Exception $th) {

            return response()->json(['message' => $th->getMessage()], 422);
        }

    }

    public function practiceTestAnswers(Request $request) {
        $getCorrectAnswers = CourseQuizQuestionAnswerModel::select('course_quiz_question_id as question_id', 'id as correct_answer')->whereIn('course_quiz_question_id', $request->questions)->where('course_quiz_correct_answer', 1)->get();

        return response()->json($getCorrectAnswers, 200);
    }

    public function cronToSetExpireCourses() {
        try {
            $todayDate = date('Y-m-d');
            $getcoursestoexpire = EmployeeCoursesModel::leftjoin('tbl_employee', 'tbl_employee.id', '=', 'tbl_employee_courses.employee_id')->where('employee_course_due_date', '<=', $todayDate)->where('employee_course_status', '<>', 3)->where('employee_course_status', '<>', 1)->get();
            if (count($getcoursestoexpire) > 0) {
                foreach ($getcoursestoexpire as $courseexpire) {
                    $getReassignmentExpiry = CourseModel::where('id', $courseexpire->course_id)->first();
                    $getReassignmentExpiryStatus = $getReassignmentExpiry->reassignment_expiry;
                    $getReassignmentExpiryAttempts = $getReassignmentExpiry->expiry_attempts;
                    $getManagersDaystoComplete = $getReassignmentExpiry->managers_days_to_complete;
                    $getEmployeesDaystoComplete = $getReassignmentExpiry->employees_days_to_complete;

                    if ($courseexpire->role_id == 4) {
                        $dueDate = Carbon::now('UTC')->addDays($getEmployeesDaystoComplete)->format('Y-m-d');
                    } else {
                        $dueDate = Carbon::now('UTC')->addDays($getManagersDaystoComplete)->format('Y-m-d');
                    }
                    if ($getReassignmentExpiryStatus && $getReassignmentExpiryAttempts != NULL && $getReassignmentExpiryAttempts > 0) {
                        if ($courseexpire->reassignment_expiry_attempts != $getReassignmentExpiryAttempts && $courseexpire->reassignment_expiry_attempts < $getReassignmentExpiryAttempts) {
                            $reassignment_expiry_attempt = ($courseexpire->reassignment_expiry_attempts + 1);
                            $updated = EmployeeCoursesModel::where('employee_id', $courseexpire->employee_id)->where('course_id', $courseexpire->course_id)->update([
                                'employee_course_status' => 2,
                                'employee_course_date_assigned' => $todayDate,
                                'employee_course_due_date' => $dueDate,
                                'reassignment_expiry_attempts' => $reassignment_expiry_attempt,
                            ]);
                        } else {
                            $updated = EmployeeCoursesModel::where('employee_id', $courseexpire->employee_id)->where('course_id', $courseexpire->course_id)->where('employee_course_due_date', '<=', $todayDate)->update(['employee_course_status' => 3]);
                        }
                    } else {
                        $updated = EmployeeCoursesModel::where('employee_id', $courseexpire->employee_id)->where('course_id', $courseexpire->course_id)->where('employee_course_due_date', '<=', $todayDate)->update(['employee_course_status' => 3]);
                    }
                }

                return response()->json($updated, 200);
            } else {
                return response()->json("No courses to expire..", 200);
            }
        } catch (Exception $th) {
            return response()->json(['message' => $th->getMessage()], 422);
        }
    }

    public function autoReminderCron() {
        try {
            $employee1 = EmployeeCoursesModel::select('tbl_employee.full_name as employee_name', 'tbl_employee.email as employee_email', 'tbl_employee_courses.*')->leftjoin('tbl_employee', 'tbl_employee.id', '=', 'tbl_employee_courses.employee_id')->where('employee_course_status', 2)->where('tbl_employee.status', 1)->whereDate('employee_course_due_date', '=', Carbon::today()->addDays(1))->groupby('employee_id')->get();
            $employee5 = EmployeeCoursesModel::select('tbl_employee.full_name as employee_name', 'tbl_employee.email as employee_email', 'tbl_employee_courses.*')->leftjoin('tbl_employee', 'tbl_employee.id', '=', 'tbl_employee_courses.employee_id')->where('employee_course_status', 2)->where('tbl_employee.status', 1)->whereDate('employee_course_due_date', '=', Carbon::today()->addDays(5))->groupby('employee_id')->get();
            $employee15 = EmployeeCoursesModel::select('tbl_employee.full_name as employee_name', 'tbl_employee.email as employee_email', 'tbl_employee_courses.*')->leftjoin('tbl_employee', 'tbl_employee.id', '=', 'tbl_employee_courses.employee_id')->where('employee_course_status', 2)->where('tbl_employee.status', 1)->whereDate('employee_course_due_date', '=', Carbon::today()->addDays(15))->groupby('employee_id')->get();

            if ($employee1->count() > 0) {
                foreach ($employee1 as $emp1) {
                    $company = self::getEmployeeCompany($emp1->employee_id);
                    $email = $emp1->employee_email;
                    $reminder = "1 day";
                    $courses = self::getEmployeeGettingExpiredCourses($emp1->employee_id, $remind = 1);
                    if ($email) {
                        $sendEmail = self::sendAutoReminderEmail($company, $emp1, $reminder, $courses, $email);
                    }
                }
            }
            if ($employee5->count() > 0) {
                foreach ($employee5 as $emp5) {
                    $company = self::getEmployeeCompany($emp5->employee_id);
                    $email = $emp5->employee_email;
                    $reminder = "5 days";
                    $courses = self::getEmployeeGettingExpiredCourses($emp5->employee_id, $remind = 5);
                    if ($email) {
                        $sendEmail = self::sendAutoReminderEmail($company, $emp5, $reminder, $courses, $email);
                    }
                }

            }
            if ($employee15->count() > 0) {
                foreach ($employee15 as $emp15) {
                    $company = self::getEmployeeCompany($emp15->employee_id);
                    $email = $emp15->employee_email;
                    $reminder = "15 days";
                    $courses = self::getEmployeeGettingExpiredCourses($emp15->employee_id, $remind = 15);
                    if ($email) {
                        $sendEmail = self::sendAutoReminderEmail($company, $emp15, $reminder, $courses, $email);
                    }
                }
            }

            return 1;

        } catch (Exception $th) {
            return response()->json(['message' => $th->getMessage()], 422);
        }
    }

    public function getEmployeeCompany($employee_id) {
        $getCompany = EmployeeCompanyLocationsModel::where('employee_id', $employee_id)->first();
        $company = "";
        if ($getCompany != NULL) {
            if ($getCompany->location_id == 0) {
                $company_id = $getCompany->company_id;
            } else {
                $company_id = $getCompany->location_id;
            }
            $isCompany = CompanyModel::where('id', $company_id)->first();

            $company = $isCompany->name;
        } else {
            $company = "individual";
        }

        return $company;
    }

    public function getEmployeeGettingExpiredCourses($employee_id, $remind) {
        $courses = EmployeeCoursesModel::select('tbl_course.name as course_name', 'tbl_employee_courses.*')->leftjoin('tbl_course', 'tbl_course.id', '=', 'tbl_employee_courses.course_id')->where('employee_id', $employee_id)->where('employee_course_status', 2)->whereDate('employee_course_due_date', '=', Carbon::today()->addDays($remind))->get();
        $course_names = array();
        $i = 0;
        $reminder = 0;
        if ($courses->count() > 0) {
            foreach ($courses as $course) {
                $course_names[$i]['name'] = $course->course_name;
                $i++;
            }
        }

        return $course_names;
    }

    public function sendAutoReminderEmail($company, $employee, $reminder, $courses, $email) {
        $data = array(
            'full_name' => $employee->employee_name,
            'company_name' => $company,
            'email' => $employee->employee_email,
            'courses' => $courses,
            'expire_date' => Carbon::parse($employee->employee_course_due_date)->format("m-d-Y"),
            'reminder' => $reminder,
            'userId' => Helper::maskUserId($employee->id),
        );

        if ((new UnsubscribeController())->wantToSendTheEmail($employee->id) === FALSE) {
            return FALSE;
        }

        Mail::send('auto_reminder', $data, function($message) use ($email) {
            $message->to($email)->subject('Important reminder from ' . env('SITE_NAME') . '!');
            $message->from(env('MAIL_FROM_ADDRESS'), env('MAIL_FROM_NAME'));
        });
        CommonTrait::emailLog("Course " . $reminder . " Reminder", $email, $employee->employee_id);
    }

    public function weeklyProgressreport() {
        $today = date('Y-m-d');
        $previous_date = date('Y-m-d', strtotime('-7 days'));
        $data['weekly_data'] = EmployeeCoursesModel::select('tbl_employee.first_name', 'tbl_employee.last_name', 'tbl_company.name as company_name', 'tbl_employee.address', 'tbl_employee.city', 'tbl_employee.state', 'tbl_employee.zipcode', 'tbl_employee.phone_num', 'tbl_employee.email', 'tbl_employee_courses.course_id', 'tbl_employee_courses.employee_course_due_date', 'tbl_employee_courses.employee_course_date_completed', 'tbl_course.name AS course_name')->leftjoin('tbl_employee_company_locations', 'tbl_employee_company_locations.employee_id', '=', 'tbl_employee_courses.employee_id')->leftJoin('tbl_company', 'tbl_company.id', '=', DB::Raw('(CASE WHEN `tbl_employee_company_locations`.location_id = 0 then `tbl_employee_company_locations`.company_id ELSE `tbl_employee_company_locations`.location_id END)'))->leftjoin('tbl_course', 'tbl_course.id', '=', 'tbl_employee_courses.course_id')->leftjoin('tbl_employee', 'tbl_employee.id', '=', 'tbl_employee_courses.employee_id')->where('tbl_course.is_weekly_report', '1')->where('tbl_employee_courses.employee_course_status', 1)->where('tbl_employee_courses.employee_course_date_completed', '!=', NULL)->whereBetween('tbl_employee_courses.employee_course_date_completed', [
            $previous_date,
            $today,
        ])->get();
        $email = config('mail.support');
        Mail::send('weekly_completion', $data, function($message) use ($email) {
            $message->to($email)->subject('Weekly Course Completion report ' . env('SITE_NAME') . '!');
            $message->from(env('MAIL_FROM_ADDRESS'), env('MAIL_FROM_NAME'));
        });
        CommonTrait::emailLog("Weekly Course Completion", $email, 0);
    }

    /**
     * To send the daily progress report for Seller Server Training - Texas
     */
    public function dailyReport() {

        $today = date('Y-m-d', strtotime('-1 day'));

        $data['weekly_data'] = EmployeeCoursesModel::select('tbl_employee.first_name', 'tbl_employee.last_name', 'tbl_company.name as company_name', 'tbl_employee.address', 'tbl_employee.city', 'tbl_employee.state', 'tbl_employee.zipcode', 'tbl_employee.phone_num', 'tbl_employee.email', 'tbl_employee_courses.course_id', 'tbl_employee_courses.employee_course_due_date', 'tbl_employee_courses.employee_course_date_completed', 'tbl_course.name AS course_name')->leftjoin('tbl_employee_company_locations', 'tbl_employee_company_locations.employee_id', '=', 'tbl_employee_courses.employee_id')->leftJoin('tbl_company', 'tbl_company.id', '=', DB::Raw('(CASE WHEN `tbl_employee_company_locations`.location_id = 0 then `tbl_employee_company_locations`.company_id ELSE `tbl_employee_company_locations`.location_id END)'))->leftjoin('tbl_course', 'tbl_course.id', '=', 'tbl_employee_courses.course_id')->leftjoin('tbl_employee', 'tbl_employee.id', '=', 'tbl_employee_courses.employee_id')->where('tbl_course.status', 1)->where('tbl_employee_courses.employee_course_date_completed', $today)->where('tbl_course.id', 305)->get();

        $email = config('mail.support');

        $status = Mail::send('daily_completion', $data, function($message) use ($email) {
            $message->to($email)->subject('Daily Course Completion Report ' . env('SITE_NAME') . '!');
            $message->from(env('MAIL_FROM_ADDRESS'), env('MAIL_FROM_NAME'));
        });
    }

    public function testQuestionReport(Request $request) {
        $where_data = [];
        if (!empty($request->search)) {
            $search = $request->search;
            $search = explode(" ", $search);
            foreach ($search as $key => $name) {
                $where_data[] = [
                    'tbl_course_quiz_questions.question',
                    'like',
                    '%' . $name . '%',
                ];
            }
        }
        $course_id = [];
        if (!empty($request->course_id)) {
            $course_id[] = [
                'tbl_employee_answers.course_id',
                $request->course_id,
            ];
        }
        $test_type = [];
        if (!empty($request->test_type)) {
            $test_type[] = [
                'tbl_employee_answers.test_type',
                $request->test_type,
            ];
        }

        $requestData = $request->all();
        $startFrom = "";
        $limit = "";
        if (isset($requestData['page']) && isset($requestData['per_page'])) {
            $startFrom = ($requestData['page'] == 0) ? ($requestData['page'] * $requestData['per_page']) : ($requestData['page'] - 1) * $requestData['per_page'];
            $limit = $requestData['per_page'];
        }
        $data = DB::table('tbl_employee_answers')->select('tbl_employee_answers.question_id', 'tbl_employee_answers.test_type', 'tbl_employee_answers.test_id', 'tbl_employee_answers.course_id', 'tbl_course.name AS course_name', 'tbl_course_lesson.course_lesson_name AS lesson_name', 'tbl_course_quiz_questions.question as question_title', DB::Raw('count(case when is_currect = 1 then 1 end) AS correctCount'), DB::Raw('count(case when is_currect = 0 then 0 end) AS wrongCount'))->join('tbl_course', 'tbl_course.id', '=', 'tbl_employee_answers.course_id')->leftjoin('tbl_course_lesson', 'tbl_course_lesson.id', '=', 'tbl_employee_answers.test_id')->leftjoin('tbl_course_test', 'tbl_course_test.id', '=', 'tbl_employee_answers.test_id')->leftjoin('tbl_course_quiz_questions', 'tbl_course_quiz_questions.id', '=', 'tbl_employee_answers.question_id')->where($where_data)->where($course_id)->where($test_type)->groupBy('tbl_employee_answers.question_id');


        $start_date = "";
        $end_date = "";
        if ($request->report_start_date || $request->report_end_date) {
            $start_date = Carbon::parse("$request->report_start_date 00:00:00")->format('Y-m-d H:i:s');
            $end_date = Carbon::parse("$request->report_end_date 23:59:59")->format('Y-m-d H:i:s');
            $data->whereBetween('tbl_employee_answers.updated_at', array(
                $start_date,
                $end_date,
            ));
        }
        $total = 0;
        if (!empty($data->get()->toArray())) {
            $total = count($data->get()->toArray());
        }
        $data->orderby('tbl_course.name', 'asc');
        if ($limit != '') {
            $data->skip($startFrom);
            $data->take($limit);
        }
        $records = $data->get();

        $getSheet = array();
        foreach ($records as $key => $value) {
            $getSheet[$key]['Question'] = ucfirst($value->question_title);
            $getSheet[$key]['Test Title'] = $value->test_type == 1 ? $value->lesson_name : "Test";
            $getSheet[$key]['Course Name'] = ucfirst($value->course_name);
            $getSheet[$key]['Test Type'] = $value->test_type == 1 ? "Lesson" : "Test";
            $getSheet[$key]['Correct Attempts'] = $value->correctCount;
            $getSheet[$key]['Wrong Attempts'] = $value->wrongCount;
        }


        return response()->json([
            'report' => $records,
            'download' => $getSheet,
            'total' => $total,
        ], 200);


    }


    public function surveyReport(Request $request) {
        $where_data = [];
        if (!empty($request->search)) {
            $search = $request->search;
            $search = explode(" ", $search);
            foreach ($search as $key => $name) {
                $where_data[] = [
                    'tbl_survey.name',
                    'like',
                    '%' . $name . '%',
                ];
            }
        }


        $data = DB::table('tbl_survey')->select('tbl_survey.id', 'tbl_survey.name', 'tbl_survey.created_at', DB::Raw('count(distinct(test_attempt_id)) as submissions'))->leftjoin('tbl_employee_pre_test_survey_answer', 'tbl_employee_pre_test_survey_answer.test_id', '=', 'tbl_survey.id')->where('tbl_survey.survey_type', '!=', "post-login")->where($where_data)->groupBy('tbl_employee_pre_test_survey_answer.test_id');

        $requestData = $request->all();
        $startFrom = "";
        $limit = "";
        if (isset($requestData['page']) && isset($requestData['per_page'])) {
            $startFrom = ($requestData['page'] == 0) ? ($requestData['page'] * $requestData['per_page']) : ($requestData['page'] - 1) * $requestData['per_page'];
            $limit = $requestData['per_page'];
        }
        $start_date = "";
        $end_date = "";
        if ($request->report_start_date || $request->report_end_date) {
            $start_date = Carbon::parse("$request->report_start_date 00:00:00")->format('Y-m-d H:i:s');
            $end_date = Carbon::parse("$request->report_end_date 23:59:59")->format('Y-m-d H:i:s');
            $data->whereBetween('tbl_employee_pre_test_survey_answer.updated_at', array(
                $start_date,
                $end_date,
            ));
        }
        $total = 0;
        if (!empty($data->get()->toArray())) {
            $total = count($data->get()->toArray());
        }
        $data->orderby('tbl_survey.name', 'asc');
        if ($limit != '') {
            $data->skip($startFrom);
            $data->take($limit);
        }
        $records = $data->get();
        $getSheet = array();
        foreach ($records as $key => $value) {
            $getSheet[$key]['Survey Name'] = ucfirst($value->name);
            $getSheet[$key]['Created At'] = $value->created_at;
            $getSheet[$key]['# Submissions'] = $value->submissions;

        }


        return response()->json([
            'report' => $records,
            'download' => $getSheet,
            'total' => $total,
        ], 200);


    }

    public function surveySubmissionReport(Request $request) {

        $where_data = [];
        $where_data1 = [];
        if (!empty($request->search_employee)) {
            $search = $request->search_employee;
            $search = explode(" ", $search);
            foreach ($search as $key => $name) {
                $where_data[] = [
                    'tbl_employee.full_name',
                    'like',
                    '%' . $name . '%',
                ];
            }
        }
        if (!empty($request->search_company)) {
            $search1 = $request->search_company;
            $search1 = explode(" ", $search1);
            foreach ($search1 as $key => $name) {
                $where_data1[] = [
                    'tbl_company.name',
                    'like',
                    '%' . $name . '%',
                ];
            }
        }
        $course_id = [];
        if (!empty($request->course_id)) {
            $course_id[] = [
                'tbl_employee_pre_test_survey_answer.course_id',
                $request->course_id,
            ];
        }

        $requestData = $request->all();
        $startFrom = "";
        $limit = "";
        if (isset($requestData['page']) && isset($requestData['per_page'])) {
            $startFrom = ($requestData['page'] == 0) ? ($requestData['page'] * $requestData['per_page']) : ($requestData['page'] - 1) * $requestData['per_page'];
            $limit = $requestData['per_page'];
        }

        $data = DB::table('tbl_employee_pre_test_survey_answer')->select('tbl_course.id as course_id', 'tbl_employee.id as employee_id', 'tbl_employee_pre_test_survey_answer.test_id as survey_id', 'tbl_employee.full_name as employee_name', 'tbl_company.name as company_name', 'tbl_course.name as course_name', 'tbl_employee_pre_test_survey_answer.id', 'tbl_employee_pre_test_survey_answer.updated_at')->leftjoin('tbl_employee', 'tbl_employee.id', '=', 'tbl_employee_pre_test_survey_answer.employee_id')->leftjoin('tbl_course', 'tbl_course.id', '=', 'tbl_employee_pre_test_survey_answer.course_id')->leftjoin('tbl_employee_company_locations', 'tbl_employee_company_locations.employee_id', '=', 'tbl_employee_pre_test_survey_answer.employee_id')->leftJoin('tbl_company', 'tbl_company.id', '=', DB::Raw('(CASE WHEN `tbl_employee_company_locations`.location_id = 0 then `tbl_employee_company_locations`.company_id ELSE `tbl_employee_company_locations`.location_id END)'))->where($where_data)->where($where_data1)->where($course_id)->where('tbl_employee_pre_test_survey_answer.test_id', $request->id)->groupBy('tbl_employee_pre_test_survey_answer.employee_id');

        $start_date = "";
        $end_date = "";
        if ($request->report_start_date || $request->report_end_date) {
            $start_date = Carbon::parse("$request->report_start_date 00:00:00")->format('Y-m-d H:i:s');
            $end_date = Carbon::parse("$request->report_end_date 23:59:59")->format('Y-m-d H:i:s');
            $data->whereBetween('tbl_employee_pre_test_survey_answer.updated_at', array(
                $start_date,
                $end_date,
            ));
        }
        $total = 0;
        if (!empty($data->get()->toArray())) {
            $total = count($data->get()->toArray());
        }
        if ($limit != '' && $request->has('isExcelDownload') === FALSE) {
            $data->skip($startFrom);
            $data->take($limit);
        }

        if ($request->column && $request->order) {
            switch ($request->column) {
                case 'employee':
                    $data->orderBy('tbl_employee.full_name', $request->order);
                    break;
                default:
                    $data->orderby('tbl_employee_pre_test_survey_answer.updated_at', $request->order);
                    break;
            }
        }

        $records = $data->get();
        $getSheet = array();
        foreach ($records as $key => $value) {


            $date = date('m/d/Y', strtotime($value->updated_at));

            $getSheet[$key]['Company'] = ucfirst($value->company_name);
            $getSheet[$key]['Employee'] = ucfirst($value->employee_name);
            $getSheet[$key]['Date'] = $date;
            $getSheet[$key]['Course'] = $value->course_name;
            //with the help of loop get the questions and answers from the table with this record and append those in the list
            $questionsData1 = DB::table('tbl_employee_pre_test_survey_answer')->select('tbl_employee_pre_test_survey_answer.question_title as question', 'tbl_employee_pre_test_survey_answer.answer_title')
                //  ->leftjoin('tbl_survey_questions','tbl_survey_questions.survey_id','=','tbl_employee_pre_test_survey_answer.question_id')
                ->where('tbl_employee_pre_test_survey_answer.test_id', $request->id)->where('tbl_employee_pre_test_survey_answer.employee_id', $value->employee_id)->where('tbl_employee_pre_test_survey_answer.course_id', $value->course_id)->where('tbl_employee_pre_test_survey_answer.test_type', 2)->get();
            foreach ($questionsData1 as $keys => $record) {
                $getSheet[$key]['Question' . ' ' . ++$keys] = isset($record) ? $record->question : 'NULL';
                $getSheet[$key]['Answer' . ' ' . $keys] = isset($record) ? $record->answer_title : 'NULL';
            }
            //$getSheet[$key]['Questions'] = isset($questionsData1[0]) ? $questionsData1[0]->question:'NULL' ;
            //$getSheet[$key]['Answers'] = isset($questionsData1[0]) ? $questionsData1[0]->answer_title:'NULL' ;
            //\Log::info('course_id', array($request->id ));

        }


        return response()->json([
            'report' => $records,
            'download' => $getSheet,
            'total' => $total,
        ], 200);


    }

    public function surveySubmissionReportDetails(Request $request) {
        $data = DB::table('tbl_employee_pre_test_survey_answer')->select('tbl_employee.full_name as employee_name', 'tbl_company.name as company_name', 'tbl_course.name as course_name', 'tbl_employee_pre_test_survey_answer.id', 'tbl_employee_pre_test_survey_answer.updated_at')->leftjoin('tbl_employee', 'tbl_employee.id', '=', 'tbl_employee_pre_test_survey_answer.employee_id')->leftjoin('tbl_course', 'tbl_course.id', '=', 'tbl_employee_pre_test_survey_answer.course_id')->leftjoin('tbl_employee_company_locations', 'tbl_employee_company_locations.employee_id', '=', 'tbl_employee_pre_test_survey_answer.employee_id')->leftJoin('tbl_company', 'tbl_company.id', '=', DB::Raw('(CASE WHEN `tbl_employee_company_locations`.location_id = 0 then `tbl_employee_company_locations`.company_id ELSE `tbl_employee_company_locations`.location_id END)'))->where('tbl_employee_pre_test_survey_answer.test_id', $request->test_id)->where('tbl_employee_pre_test_survey_answer.employee_id', $request->employee_id)->where('tbl_employee_pre_test_survey_answer.course_id', $request->course_id)->where('tbl_employee_pre_test_survey_answer.test_type', 2)->get();

        $questionsData = DB::table('tbl_employee_pre_test_survey_answer')->select('tbl_employee_pre_test_survey_answer.question_title as question', 'tbl_employee_pre_test_survey_answer.answer_title')
            //  ->leftjoin('tbl_survey_questions','tbl_survey_questions.survey_id','=','tbl_employee_pre_test_survey_answer.question_id')
            ->where('tbl_employee_pre_test_survey_answer.test_id', $request->test_id)->where('tbl_employee_pre_test_survey_answer.employee_id', $request->employee_id)->where('tbl_employee_pre_test_survey_answer.course_id', $request->course_id)->where('tbl_employee_pre_test_survey_answer.test_type', 2)->get();


        $total = count($questionsData);

        return response()->json([
            'report' => $data,
            'questionreport' => $questionsData,
            'total' => $total,
        ], 200);


    }

    public function proctoredExam(Request $request) {
        $user = Auth::user();

        if ($user->role_id == 1) {
            return response()->json([
                'url' => '',
                'completed' => 0,
            ]);
        }

        $getCompany = EmployeeCompanyLocationsModel::where('employee_id', $user->id)->where('company_id', '!=', 0)->first();
        if ($getCompany != NULL) {
            $company = CompanyModel::leftJoin('tbl_employee', 'tbl_employee.id', '=', 'tbl_company.employee_id')->where('tbl_company.id', $getCompany->company_id)->first();
        }

        $proctoredExam = ProctoredExam::where('user_id', $user->id)->where('completed', 0)->where('attempts', '<', 2)->where('in_progress', 1)->latest()->get()->first();
        $isProctoredExamCompleted = ProctoredExam::where('user_id', $user->id)->where('completed', 1)->first();
        if (!empty($isProctoredExamCompleted) && $isProctoredExamCompleted->completed == 1) {
            return response()->json([
                'url' => '',
                'completed' => 1,
            ]);
        }

        if (empty($proctoredExam)) {
            $externalInviteId = $user->id . time();
            $externalUserId = $user->id;
            $requestArray = [
                'externalInviteId' => "$externalInviteId",
                'externalUserId' => "$externalUserId",
                'webhookUri' => URL::to('/course/proctored-exam-callback'),
                'includeCourse' => FALSE,
                'includeExam' => TRUE,
                'certificateId' => 438,
                'userEmailAddress' => "$user->email",
                'userFirstName' => "$user->first_name",
                'userLastName' => "$user->last_name",
                'addressLine1' => "$user->address",
                'addressLine2' => "",
                'city' => "$user->city",
                'state' => "$user->state",
                'zipCode' => "$user->zipcode",
                'country' => "",
                'phoneNumber' => "$user->phone_num",
                'externalOrganization' => [
                    'externalOrganizationId' => $getCompany->company_id ?? 0,
                    'externalOrganizationName' => "$company->name",
                ],
            ];
            $proctoredExamDetails = $this->getProctoredExamLink($requestArray);

            if (isset($proctoredExamDetails['error'])) {
                return response()->json([
                    'url' => '',
                    'completed' => 0,
                    'errors' => $proctoredExamDetails['error']['details'],
                ]);
            }

            $proctoredExam1 = new ProctoredExam();
            $proctoredExam1->user_id = $user->id;
            $proctoredExam1->external_user_id = $externalUserId;
            $proctoredExam1->external_invite_id = $externalInviteId;
            $proctoredExam1->link = $proctoredExamDetails['item']['link'];
            $proctoredExam1->invite_id = $proctoredExamDetails['item']['id'];
            $proctoredExam1->course_id = $request->course_id;
            $proctoredExam1->attempts = 0;
            $proctoredExam1->completed = 0;
            $proctoredExam1->in_progress = 1;
            $proctoredExam1->save();
            $proctoredExam = $proctoredExam1;
        }

        return response()->json([
            'url' => $proctoredExam->link,
            'completed' => 0,
            'errors' => FALSE,
        ]);

    }

    public function getProctoredExamLink($requestParameters) {
        $access_token = $this->getAccessToken();
        $header = array(
            "Authorization: Bearer {$access_token}",
            "Content-Type: application/json",
        );
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => "https://alwaysfoodsafe.com/learn/api/v2.0/PartnerInvite",
            CURLOPT_SSL_VERIFYPEER => FALSE,
            CURLOPT_RETURNTRANSFER => TRUE,
            CURLOPT_POST => TRUE,
            CURLOPT_POSTFIELDS => json_encode($requestParameters),
            CURLOPT_HTTPHEADER => $header,
        ));
        $response = curl_exec($curl);
        curl_close($curl);

        return json_decode($response, TRUE);
    }

    public function getAccessToken() {
        $token_url = "https://login.ncco.com/connect/token";
        $client_id = "train321";
        $client_secret = "6K^ZzhtR64dTtXpAgtNhwi";
        $content = "grant_type=client_credentials";
        $authorization = base64_encode("$client_id:$client_secret");
        $header = array(
            "Authorization: Basic {$authorization}",
            "Content-Type: application/x-www-form-urlencoded",
        );
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => $token_url,
            CURLOPT_HTTPHEADER => $header,
            CURLOPT_SSL_VERIFYPEER => FALSE,
            CURLOPT_RETURNTRANSFER => TRUE,
            CURLOPT_POST => TRUE,
            CURLOPT_POSTFIELDS => $content,
        ));
        $response = curl_exec($curl);
        curl_close($curl);

        return json_decode($response)->access_token;
    }

    public function proctoredExamCallback(Request $request) {
        log::debug($request);
        $protoredExam = ProctoredExam::where('external_invite_id', $request->ExternalInviteId)->where('external_user_id', $request->ExternalUserId)->first();
        $protoredExam->attempts = $protoredExam->attempts + 1;
        $protoredExam->completed = $request->Passed ? 1 : 0;
        $protoredExam->score = $request->Score;
        $protoredExam->response = $request->all();
        if ($request->Passed) {
            EmployeeCoursesModel::where('course_id', $protoredExam->course_id)->where('employee_id', $protoredExam->user_id)->update([
                'employee_course_status' => '1',
                'employee_course_date_completed' => date('Y-m-d'),
            ]);
            $protoredExam->in_progress = 0;
            // assign the certificate to the user
            $employeeCertificate = new EmployeeCertificateModel();
            $employeeCertificate->certificate_no = 0;
            $employeeCertificate->certificate_name = 'ProctorU proctored exam';
            $employeeCertificate->course_id = $protoredExam->course_id;
            $employeeCertificate->employee_id = $protoredExam->user_id;
            $employeeCertificate->certificate_date = date('Y-m-d');
            $employeeCertificate->certificate_expiration_date = date('Y-m-d', strtotime($request->CertificateExpiration));
            $employeeCertificate->certificate_url = 'https://alwaysfoodsafe.com/learn/api/v2.0/ExamCertificate?externalInviteId=' . $protoredExam->external_invite_id;
            $employeeCertificate->manual = 0;
            $employeeCertificate->is_proctored_exam = 1;
            $employeeCertificate->save();
            $employeeCertificate->certificate_no = CourseTrait::getCertificateId($employeeCertificate->id);
            $employeeCertificate->save();
        }
        $protoredExam->save();

        return $protoredExam;
    }

    public function proctoredExamCertificate(Request $request) {
        $access_token = $this->getAccessToken();
        $header = array(
            "Authorization: Bearer {$access_token}",
            "Content-type:application/pdf",
        );
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => $request->certificateURL,
            CURLOPT_SSL_VERIFYPEER => FALSE,
            CURLOPT_RETURNTRANSFER => TRUE,
            CURLOPT_POST => FALSE,
            CURLOPT_HTTPHEADER => $header,
        ));
        $response = curl_exec($curl);
        curl_close($curl);

        $pdfFileName = time() . 'pdf';

        if (!file_exists(public_path('temp/'))) {
            mkdir(public_path('temp/'));
        }

        file_put_contents(public_path('temp/' . $pdfFileName), $response);

        return response()->json([
            'certificate_url' => URL::to('/temp/' . $pdfFileName),
        ]);
    }

    public function resourceMigration() {
        $oldResources = DB::table('tbl_course_resources')->whereNotNull('resource_type')->get();
        foreach ($oldResources as $oldResource) {
            $resource = new CourseResourceModel();
            $resource->id = $oldResource->id;
            $resource->name = $oldResource->course_resource_name;
            $resource->type = $oldResource->resource_type;
            if ($oldResource->resource_type == 'file') {
                $resource->url = $oldResource->course_resource;
            }
            if ($oldResource->resource_type == 'link') {
                $resource->url = $oldResource->course_resource_url;
            }
            $resource->file_name = $oldResource->resource_image;
            $resource->available_after_course_completion = $oldResource->appear_status ? 0 : 1;
            $resource->status = 1;
            $resource->created_at = $oldResource->created_at;
            $resource->updated_at = $oldResource->updated_at;
            $resource->save();
            DB::insert('insert into tbl_course_resource (course_id, resource_id) values (' . $oldResource->course_id . ',' . $oldResource->id . ' )');
        }
    }

    public function payByEmployeeSubmission(Request $request) {

        $paymentId = 0;
        $isPaymentResponse = array();
        $payment_request = $request->payment;
        if (!empty($payment_request)) {
            $invoice = CommonTrait::generateUniqueId();
            $payment_request['invoice_number'] = $invoice;
            $paymentResponse = PaymentModel::storeCardofUserForPayment($this->base_url, $this->profile_id, $this->profile_key, $payment_request);
            if ($paymentResponse['status'] == 'error') {

                return response()->json([
                    'status' => 'error',
                    'message' => $paymentResponse['data'],
                ], 422);
            }

            if ($payment_request['cardholder_city'] || $payment_request['cardholder_state']) {
                EmployeeModel::where('id', $request->employee_id)->update([
                    'address' => $payment_request['cardholder_street_address'],
                    'city' => $payment_request['cardholder_city'],
                    'state' => $payment_request['cardholder_state'],
                    'zipcode' => $payment_request['cardholder_zip'],
                ]);
            }
            $paymentId = PayByEmployeeHistoryModel::insertGetId([
                'employee_id' => $request->employee_id,
                'course_id' => $request->course_id,
                'company_id' => $request->company_id,
                'orignal_amount' => $request->orignal_amount,
                'actual_amount' => $request->actual_amount,
                'discount' => $request->pay_by_employee_dicount,
                'status' => isset($paymentResponse['data']['avs_result']) ? $paymentResponse['data']['avs_result'] : 'F',
                'transaction' => json_encode($paymentResponse),
                'invoice' => $invoice,
                'created_at' => Carbon::now('UTC'),
            ]);

            if ($paymentResponse['data']['error_code'] != '000') {

                return response()->json(['message' => $paymentResponse['data']['auth_response_text']], 422);
            }
            if (isset($paymentResponse['data']['avs_result']) && $paymentResponse['data']['avs_result'] == 'N') {
                return response()->json(['message' => $paymentResponse['data']['auth_response_text']], 422);
            }
            $isPaymentResponse = $paymentResponse;
            if ($paymentResponse['data']['error_code'] == '000') {
                PayByEmployeeHistoryModel::where('id', $paymentId)->update([
                    'accessible' => 1,
                ]);
            }
        }

        return;
    }

    public function failPassReport(Request $request) {
        try {
            $columnName = [
                0 => 'tbl_employee.first_name',
                1 => 'tbl_employee.last_name',
                2 => 'tbl_company.name',
                3 => 'tbl_course.name',
                4 => 'tbl_employee_courses.employee_course_status',
                5 => 'tbl_employee_courses.employee_course_date_completed',
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
            if ($request->course_id) {
                array_push($where_data, [
                    'tbl_course.id',
                    $request->course_id,
                ]);

            }

            $data = EmployeeCoursesModel::select('tbl_employee_courses.*', 'tbl_employee.first_name', 'tbl_employee.last_name', 'tbl_employee.user_name', 'tbl_company.name as company_name', 'tbl_course.name as course_name')->leftjoin('tbl_employee', 'tbl_employee_courses.employee_id', '=', 'tbl_employee.id')->leftjoin('tbl_course', 'tbl_employee_courses.course_id', '=', 'tbl_course.id')->leftjoin('tbl_employee_company_locations', 'tbl_employee_company_locations.employee_id', '=', 'tbl_employee_courses.employee_id')->leftJoin('tbl_company', 'tbl_company.id', '=', DB::Raw('(CASE WHEN `tbl_employee_company_locations`.location_id = 0 then `tbl_employee_company_locations`.company_id ELSE `tbl_employee_company_locations`.location_id END)'))->where($where_data)->where(function($query) {
                $query->where('tbl_employee_courses.employee_course_status', 0)->orWhere('tbl_employee_courses.employee_course_status', 1);
            });

            if ($where) {
                $data->whereIn($where, $companyIds);
            }

            if ($orderColumn != '' && $orderBy != '') {
                $data->orderBy($orderColumn, $orderBy);
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

                    if ($limit != '' && $request->has('isExcelDownload') === FALSE) {
                        $data->skip($startFrom);
                        $data->take($limit);
                    }

                    $records = $data->get();
                    foreach ($records as $key => $value) {
                        $getSheet[$key]['First Name'] = ucfirst($value->first_name);
                        $getSheet[$key]['Last Name'] = ucfirst($value->last_name);
                        $getSheet[$key]['Location'] = $value->company_name ? $value->company_name : '-';
                        $getSheet[$key]['Course'] = ucfirst($value->course_name);
                        $getSheet[$key]['Status'] = $value->employee_course_status ? 'Passed' : 'Failed';
                        $getSheet[$key]['Completion Date'] = $value->employee_course_date_completed ? $value->employee_course_date_completed : '-';

                        $employeeAnswers = DB::table('tbl_employee_answers')->where('course_id', $value->course_id)->where('employee_id', $value->employee_id)->where('test_type', 2)->get();
                        $percentage = 0;
                        if(!empty(count($employeeAnswers))) {
                            $totalCorrectAnswers = 0;
                            foreach ($employeeAnswers as $employeeAnswer) {
                                if ($employeeAnswer->is_currect == 1) {
                                    $totalCorrectAnswers++;
                                }
                            }
                            $percentage = round(($totalCorrectAnswers / count($employeeAnswers)) * 100);
                        }
                        $records[$key]['percentage'] = $percentage . '%';
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
                    $getSheet[$key]['Location'] = $value->company_name ? $value->company_name : '-';
                    $getSheet[$key]['Course'] = ucfirst($value->course_name);
                    $getSheet[$key]['Status'] = $value->employee_course_status ? 'Passed' : 'Failed';
                    $getSheet[$key]['Completion Date'] = $value->employee_course_date_completed ? $value->employee_course_date_completed : '-';
                    $employeeAnswers = DB::table('tbl_employee_answers')->where('course_id', $value->course_id)->where('employee_id', $value->employee_id)->where('test_type', 2)->get();
                    $percentage = 0;
                    if(!empty(count($employeeAnswers))) {
                        $totalCorrectAnswers = 0;
                        foreach ($employeeAnswers as $employeeAnswer) {
                            if ($employeeAnswer->is_currect == 1) {
                                $totalCorrectAnswers++;
                            }
                        }
                        $percentage = round(($totalCorrectAnswers / count($employeeAnswers)) * 100);
                    }
                    $records[$key]['percentage'] = $percentage . '%';
                }
            }

            return response()->json([
                'report' => $records,
                'download' => $records,
                'total' => $total,
            ], 200);
        } catch (Exception $ex) {
            return response()->json(['message' => $ex->getMessage()], 422);
        }
    }

    public function courseCategories() {
        $data = DB::table('tbl_course_category')->get();

        return response()->json($data, 200);
    }

    public function updateOldCourseHistoryRecord() {
        $oldRecords = EmployeeCoursesModel::
        leftjoin('tbl_course', 'tbl_course.id', '=', 'tbl_employee_courses.course_id')->where('employee_course_date_completed', '<', "2021-05-10")->where('employee_course_status', '=', 1)->where('next_course', '!=', 0)->get();
        log::debug($oldRecords);
        foreach ($oldRecords as $records) {
            $insert = new EmployeeCourseHistoryModel();
            $insert->employee_id = $records->employee_id;
            $insert->course_id = $records->course_id;
            $insert->attempt_date = $records->employee_course_date_completed;
            $insert->attempt_status = $records->employee_course_status;
            $insert->assignment_status = 0;
            $insert->is_manual = 0;
            $insert->save();
        }
        foreach ($oldRecords as $records) {

            $is_company_id = CompanyModel::getCompantIdByEmployeeId($records->employee_id);
            if ($is_company_id != 0) {
                $courseDueDate = CourseModel::select('employees_days_to_complete as due_days')->where('id', $records->course_id)->first();
                $due_days = 0;
                if ($courseDueDate != NULL) {
                    $due_days = (int)$courseDueDate->due_days;
                }
                $course_due_date = Carbon::now('UTC')->addDays($due_days)->format('Y-m-d');
                $isCourse = EmployeeCoursesModel::where([
                    [
                        'employee_id',
                        $records->employee_id,
                    ],
                    [
                        'course_id',
                        $records->course_id,
                    ],
                ])->first();
                $count = 0;
                if ($isCourse != NULL) {
                    if ($isCourse->employee_course_status !== 2) {
                        EmployeeCoursesModel::where('id', $isCourse->id)->delete();
                        EmployeeCourseAttemptsModel::where([
                            [
                                'user_id',
                                $records->employee_id,
                            ],
                            [
                                'course_id',
                                $records->course_id,
                            ],
                        ])->delete();

                    } else {
                        $count = 1;
                    }
                }
                $employee_id = $records->employee_id;
                if ($count == 0) {
                    $employeeCourse = new EmployeeCoursesModel();
                    $employeeCourse->employee_id = $records->employee_id;
                    $employeeCourse->course_id = $records->course_id;
                    $employeeCourse->company_id = $is_company_id;
                    $employeeCourse->employee_course_date_assigned = date('Y-m-d');
                    $employeeCourse->employee_course_due_date = $course_due_date;
                    $employeeCourse->save();

                    EmployeeCourseHistoryModel::where([
                        [
                            'course_id',
                            $records->course_id,
                        ],
                        [
                            'employee_id',
                            $records->employee_id,
                        ],
                        [
                            'attempt_status',
                            '1',
                        ],
                    ])->update(['assignment_status' => '1']);

                    $employee_info = EmployeeModel::join('tbl_employee_company_locations', 'tbl_employee_company_locations.employee_id', '=', 'tbl_employee.id')->where('tbl_employee.id', $records->employee_id)->get();
                } else {
                    $update = EmployeeCoursesModel::where([
                        [
                            'employee_id',
                            $records->employee_id,
                        ],
                        [
                            'course_id',
                            $records->course_id,
                        ],
                    ])->update([
                        'employee_course_due_date' => $course_due_date,
                        'employee_course_date_assigned' => date('Y-m-d'),
                    ]);
                    EmployeeCourseHistoryModel::where([
                        [
                            'course_id',
                            $records->course_id,
                        ],
                        [
                            'employee_id',
                            $records->employee_id,
                        ],
                        [
                            'attempt_status',
                            '1',
                        ],
                    ])->update(['assignment_status' => '1']);
                }
            }

        }
    }


}
