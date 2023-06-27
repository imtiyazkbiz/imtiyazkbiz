<?php

namespace App\Models;

use App\Http\Controllers\TestingDebuggingController;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class EmployeeCourseAttemptsModel extends Model {
    protected $table = "tbl_employee_course_attempts";


    /**
     * The attributes that are mass assignable.
     * @var array
     */

    /**
     * The attributes excluded from the model's JSON form.
     * @var array
     */

    public static function testAttemptsBACKUP($testData) {

        $user_id = $testData['user_id'];
        $course_id = $testData['course_id'];
        $test_type = $testData['test_type'];
        $test_id = $testData['test_id'];
        $pass = $testData['user_result'];
        $total_attempt = $testData['allowed_attempts'];
        $marks = $testData['result_percentage'];
        $is_last_lesson = $testData['is_last_lesson'];
        $number_of_question = $testData['number_of_question'];
        $isUserAttempts = EmployeeCourseAttemptsModel::where([
            'user_id' => $user_id,
            'course_id' => $course_id,
            'lesson_test' => $test_type,
            'lesson_test_id' => $test_id,
        ])->first();
        $message = "";
        $retake = 0;
        $status = 0;
        if ($pass == 0) {
            $message = "<b>Sorry!</b> </br> You have answered incorrectly, Please try again.";
            if ($test_type == 'lesson' || $test_type == 'test') {
                if (isset($testData['fail_message']) && $testData['fail_message'] != '') {
                    $message1 = ucfirst($testData['fail_message']);
                    $message2 = ". Your score is " . $marks . "%";
                    $message = ($number_of_question > 0) ? $message1 . $message2 : $message1;
                } else {
                    $message1 = "<b>Sorry!</b> </br> You have answered incorrectly";
                    $message3 = ", Please try again";
                    $message2 = ". Your score is " . $marks . "%";
                    $message = ($number_of_question > 0) ? $message1 . $message3 . $message2 : $message1 . $message3;
                }
            }
        } else {
            if ($test_type == 'lesson') {
                $message = "<b>Congratulations!</b> </br> You have passed this lesson.";
                $message = ($number_of_question > 0) ? $message . " Your score is " . $marks . "%." : $message;
            }
            if ($test_type == 'test') {
                if (isset($testData['pass_message']) && $testData['pass_message'] != '') {
                    $message = ucfirst($testData['pass_message']);
                    $message = ($number_of_question > 0) ? $message . " Your score is " . $marks . "%." : $message;
                } else {
                    $message = "<b>Congratulations!</b> </br> You have passed this test.";
                    $message = ($number_of_question > 0) ? $message . " Your score is " . $marks . "%." : $message;
                }

            }
            $status = 1;
        }
        if ($isUserAttempts) {
            $user_attempts = $isUserAttempts->attempts + 1;
            if ($user_attempts >= $total_attempt && $pass == 0) {
                $pass = 0;
                $user_attempts = 0;
                $retake = 2;
                if ($test_type == 'lesson') {
                    $message1 = "<b>Sorry!</b> </br> You have failed this lesson";
                    $message2 = ". Your score is " . $marks . "%";
                    $message3 = ", Please retake prior to its expiration";
                    $message = ($number_of_question > 0) ? $message1 . $message3 . $message2 : $message1 . $message3;
                }
                if ($test_type == 'test') {
                    if (isset($testData['fail_message']) && $testData['fail_message'] != '') {
                        $message1 = ucfirst($testData['fail_message']);
                        $message2 = ". Your score is " . $marks . "%";

                        $message = ($number_of_question > 0) ? $message1 . $message2 : $message1;
                    } else {
                        $message1 = "<b>Sorry!</b> </br> You have failed this test";
                        $message2 = ". Your score is " . $marks . "%";
                        $message3 = ", Please retake prior to its expiration";
                        $message = ($number_of_question > 0) ? $message1 . $message3 . $message2 : $message1 . $message3;
                    }

                    EmployeeCourseAttemptsModel::where([
                        'user_id' => $user_id,
                        'course_id' => $course_id,
                    ])->delete();

                    return [
                        'message' => $message,
                        'status' => $status,
                        'retake' => $retake,
                        'courseAttempts' => [],
                    ];
                }

            }
            EmployeeCourseAttemptsModel::where('id', $isUserAttempts->id)->update([
                    'attempts' => $user_attempts,
                    'pass_fail' => $pass,
                    'marks' => $marks,
                    'updated_at' => Carbon::now('UTC'),
                ]);
        } else {
            EmployeeCourseAttemptsModel::insert([
                'user_id' => $user_id,
                'course_id' => $course_id,
                'lesson_test' => $test_type,
                'lesson_test_id' => $test_id,
                'pass_fail' => $pass,
                'attempts' => 1,
                'total_attempts' => $total_attempt,
                'marks' => $marks,
                'created_at' => Carbon::now('UTC'),
            ]);
        }
        if ($test_type == 'test' || $test_type == 'lesson') {
            if ($is_last_lesson == 1) {
                EmployeeCoursesModel::where([
                    'employee_id' => $user_id,
                    'course_id' => $course_id,
                ])->update([
                        'employee_course_status' => $pass,
                        'employee_course_date_completed' => Carbon::now('UTC')->format('Y-m-d'),
                        'reassignment_expiry_attempts' => 0,
                    ]);
                $historyId = EmployeeCourseHistoryModel::insertGetId([
                    'employee_id' => $user_id,
                    'course_id' => $course_id,
                    'attempt_date' => Carbon::now('UTC')->format('Y-m-d'),
                    'attempt_status' => $pass,
                    'created_at' => Carbon::now('UTC'),
                ]);
                if ($pass == 0) {
                    PayByEmployeeHistoryModel::where([
                        'employee_id' => $user_id,
                        'course_id' => $course_id,
                        'accessible' => 1,
                    ])->update([
                            'accessible' => 0,
                            'accessibility_change_on' => Carbon::now('UTC')->format('Y-m-d'),

                        ]);
                }
                if ($pass == 1) {
                    $testCourse = CourseModel::leftJoin('tbl_course_certificate', 'tbl_course_certificate.course_id', '=', 'tbl_course.id')->leftJoin('tbl_certificate', 'tbl_certificate.id', '=', 'tbl_course_certificate.certificate_id')->select('tbl_certificate.name as certificate_name', 'tbl_course.certificate_validity', 'tbl_course.certificate_available')->where('tbl_course.id', $course_id)->first();
                    $availableCertificateDays = (int)$testCourse->certificate_validity;
                    if ($is_last_lesson == 1) {
                        if ($testCourse->certificate_available) {
                            $certificateId = EmployeeCertificateModel::insertGetId([
                                'certificate_no' => '0',
                                'certificate_name' => $testCourse->certificate_name,
                                'course_id' => $course_id,
                                'employee_id' => $user_id,
                                'certificate_date' => Carbon::now('UTC')->format('Y-m-d'),
                                'certificate_expiration_date' => Carbon::now('UTC')->addDays($availableCertificateDays)->format('Y-m-d'),
                                'created_at' => Carbon::now('UTC'),
                            ]);
                            $certificateNo = '2020-100' . $certificateId;
                            EmployeeCertificateModel::where('id', $certificateId)->update(['certificate_no' => $certificateNo]);
                            EmployeeCourseHistoryModel::where('id', $historyId)->update(['certificate_id' => $certificateId]);
                        }
                    }
                }
            }
        }
        $courseAttempts = EmployeeCourseAttemptsModel::where([
            'user_id' => $user_id,
            'course_id' => $course_id,
            'lesson_test' => $test_type,
            'lesson_test_id' => $test_id,
        ])->first();

        return [
            'message' => $message,
            'status' => $status,
            'retake' => $retake,
            'courseAttempts' => $courseAttempts,
        ];
    }

    public static function testAttempts($testData) {

        $user_id = $testData['user_id'];
        $course_id = $testData['course_id'];
        $test_type = $testData['test_type'];
        $test_id = $testData['test_id'];
        $pass = $testData['user_result'];
        $total_attempt = $testData['allowed_attempts'];
        $marks = $testData['result_percentage'];
        $is_last_lesson = $testData['is_last_lesson'];
        $number_of_question = $testData['number_of_question'];
        $isUserAttempts = EmployeeCourseAttemptsModel::where([
            'user_id' => $user_id,
            'course_id' => $course_id,
            'lesson_test' => $test_type,
            'lesson_test_id' => $test_id,
        ])->first();

        $message = "";
        $retake = 0;
        $status = 0;
        if ($pass == 0) {
            $message = "<b>Sorry!</b> </br> You have answered incorrectly, Please try again.";
            if ($test_type == 'lesson' || $test_type == 'test') {
                if (isset($testData['fail_message']) && $testData['fail_message'] != '') {
                    $message1 = ucfirst($testData['fail_message']);
                    $message2 = ". Your score is " . $marks . "%";
                    $message = ($number_of_question > 0) ? $message1 . $message2 : $message1;
                } else {
                    $message1 = "<b>Sorry!</b> </br> You have answered incorrectly";
                    $message3 = ", Please try again";
                    $message2 = ". Your score is " . $marks . "%";
                    $message = ($number_of_question > 0) ? $message1 . $message3 . $message2 : $message1 . $message3;
                }
            }
        } else {
            if ($test_type == 'lesson') {
                $message = "<b>Congratulations!</b> </br> You have passed this lesson.";
                $message = ($number_of_question > 0) ? $message . " Your score is " . $marks . "%." : $message;
            }
            if ($test_type == 'test') {
                if (isset($testData['pass_message']) && $testData['pass_message'] != '') {
                    $message = ucfirst($testData['pass_message']);
                    $message = ($number_of_question > 0) ? $message . " Your score is " . $marks . "%." : $message;
                } else {
                    $message = "<b>Congratulations!</b> </br> You have passed this test.";
                    $message = ($number_of_question > 0) ? $message . " Your score is " . $marks . "%." : $message;
                }

            }
            $status = 1;
        }
        if ($isUserAttempts) {
            $user_attempts = $isUserAttempts->attempts + 1;
            if ($user_attempts >= $total_attempt && $pass == 0) {
                // recently added
                EmployeeCoursesModel::where([
                    'employee_id' => $user_id,
                    'course_id' => $course_id,
                ])->update([
                    'employee_course_status' => $pass,
                    'employee_course_date_completed' => Carbon::now('UTC')->format('Y-m-d'),
                    'reassignment_expiry_attempts' => 0,
                ]);
                $historyId = EmployeeCourseHistoryModel::insertGetId([
                    'employee_id' => $user_id,
                    'course_id' => $course_id,
                    'attempt_date' => Carbon::now('UTC')->format('Y-m-d'),
                    'attempt_status' => $pass,
                    'created_at' => Carbon::now('UTC'),
                ]);
                if ($pass == 0) {
                    PayByEmployeeHistoryModel::where([
                        'employee_id' => $user_id,
                        'course_id' => $course_id,
                        'accessible' => 1,
                    ])->update([
                        'accessible' => 0,
                        'accessibility_change_on' => Carbon::now('UTC')->format('Y-m-d'),
                    ]);
                }
                // recently added closed
                $pass = 0;
                $user_attempts = 0;
                $retake = 2;
                if ($test_type == 'lesson') {
                    $message1 = "<b>Sorry!</b> </br> You have failed this lesson";
                    $message2 = ". Your score is " . $marks . "%";
                    $message3 = ", Please retake prior to its expiration";
                    $message = ($number_of_question > 0) ? $message1 . $message3 . $message2 : $message1 . $message3;
                }
                if ($test_type == 'test') {
                    if (isset($testData['fail_message']) && $testData['fail_message'] != '') {
                        $message1 = ucfirst($testData['fail_message']);
                        $message2 = ". Your score is " . $marks . "%";

                        $message = ($number_of_question > 0) ? $message1 . $message2 : $message1;
                    } else {
                        $message1 = "<b>Sorry!</b> </br> You have failed this test";
                        $message2 = ". Your score is " . $marks . "%";
                        $message3 = ", Please retake prior to its expiration";
                        $message = ($number_of_question > 0) ? $message1 . $message3 . $message2 : $message1 . $message3;
                    }

                    EmployeeCourseAttemptsModel::where([
                        'user_id' => $user_id,
                        'course_id' => $course_id,
                    ])->delete();

                    return [
                        'message' => $message,
                        'status' => $status,
                        'retake' => $retake,
                        'courseAttempts' => [],
                    ];
                }

            }
            EmployeeCourseAttemptsModel::where('id', $isUserAttempts->id)->update([
                'attempts' => $user_attempts,
                'pass_fail' => $pass,
                'marks' => $marks,
                'updated_at' => Carbon::now('UTC'),
            ]);
        } else {
            EmployeeCourseAttemptsModel::insert([
                'user_id' => $user_id,
                'course_id' => $course_id,
                'lesson_test' => $test_type,
                'lesson_test_id' => $test_id,
                'pass_fail' => $pass,
                'attempts' => 1,
                'total_attempts' => $total_attempt,
                'marks' => $marks,
                'created_at' => Carbon::now('UTC'),
            ]);
        }
        if ($test_type == 'test' || $test_type == 'lesson') {
            if ($is_last_lesson == 1) {
                if ($pass != 0) {
                    EmployeeCoursesModel::where([
                        'employee_id' => $user_id,
                        'course_id' => $course_id,
                    ])->update([
                        'employee_course_status' => $pass,
                        'employee_course_date_completed' => Carbon::now('UTC')->format('Y-m-d'),
                        'reassignment_expiry_attempts' => 0,
                    ]);
                    $historyId = EmployeeCourseHistoryModel::insertGetId([
                        'employee_id' => $user_id,
                        'course_id' => $course_id,
                        'attempt_date' => Carbon::now('UTC')->format('Y-m-d'),
                        'attempt_status' => $pass,
                        'created_at' => Carbon::now('UTC'),
                    ]);
                }
                if ($pass == 1) {
                    $testCourse1 = CourseModel::rightJoin('tbl_course_certificate', 'tbl_course_certificate.course_id', '=', 'tbl_course.id')->leftJoin('tbl_certificate', 'tbl_certificate.id', '=', 'tbl_course_certificate.certificate_id')->select('tbl_certificate.name as certificate_name', 'tbl_course.certificate_validity', 'tbl_course.certificate_available')->where('tbl_course.id', $course_id)->first();
                    if ($testCourse1) {
                        $availableCertificateDays = (int)$testCourse1->certificate_validity;
                        if ($is_last_lesson == 1) {
                            if ($testCourse1->certificate_available) {
                                log::debug("Certificate Avaiable");
                                $certificateId = EmployeeCertificateModel::insertGetId([
                                    'certificate_no' => '0',
                                    'certificate_name' => $testCourse1->certificate_name,
                                    'course_id' => $course_id,
                                    'employee_id' => $user_id,
                                    'certificate_date' => Carbon::now('UTC')->format('Y-m-d'),
                                    'certificate_expiration_date' => Carbon::now('UTC')->addDays($availableCertificateDays)->format('Y-m-d'),
                                    'created_at' => Carbon::now('UTC'),
                                ]);
                                $certificateNo = '2020-100' . $certificateId;
                                EmployeeCertificateModel::where('id', $certificateId)->update(['certificate_no' => $certificateNo]);
                                EmployeeCourseHistoryModel::where('id', $historyId)->update(['certificate_id' => $certificateId]);
                            }
                        }
                    }
                    /*$CourseBadge = CourseModel::leftJoin('tbl_course_badge', 'tbl_course_badge.course_id', '=', 'tbl_course.id')->where('tbl_course.id', $course_id)->first();

                    if ($is_last_lesson == 1) {
                        if ($CourseBadge) {
                            log::debug("Badge Avaiable");
                            $certificateId = DB::table('tbl_employee_badges')->insertGetId([
                                'employee_id' => $user_id,
                                'badge_id' => $CourseBadge->badge_id,
                                'course_id' => $course_id,
                                'created_at' => Carbon::now('UTC'),
                            ]);

                        }
                    }*/
                    $courseExistInCourseFolders = CourseFolderAssignModel::select('tbl_employee_coursefolders.folder_id as course_folder_id')->leftjoin('tbl_employee_coursefolders', 'tbl_employee_coursefolders.folder_id', '=', 'tbl_course_coursefolder.course_folder_id')->where('course_id', $course_id)->where('employee_id', $user_id)->groupby('tbl_employee_coursefolders.folder_id')->get();

                    TestingDebuggingController::logging('$courseExistInCourseFolders', $courseExistInCourseFolders);

                    if ($courseExistInCourseFolders) {

                        foreach ($courseExistInCourseFolders as $courseExistInCourseFolder) {

                            if ($courseExistInCourseFolder) {

                                $course_folder_id = $courseExistInCourseFolder->course_folder_id;
                                $getAllCourses = CourseFolderAssignModel::where('course_folder_id', $course_folder_id)->get();
                                $courses = array();
                                foreach ($getAllCourses as $course_id_test) {
                                    array_push($courses, $course_id_test->course_id);
                                }

                                $checkIfEmployeeAssignedCourses = EmployeeCoursesModel::whereIn('course_id', $courses)->where('employee_id', $user_id)->get();

                                if (count($checkIfEmployeeAssignedCourses) == count($courses)) {

                                    $checkIfEmployeeCompletedCourseFolder = EmployeeCoursesModel::whereIn('course_id', $courses)->where('employee_id', $user_id)->where('employee_course_status', '!=', '1');

                                    if ($checkIfEmployeeCompletedCourseFolder->count() == 0) {

                                        $testCourse = CourseFolderModel::rightJoin('tbl_course_folder_certificate', 'tbl_course_folder_certificate.folder_id', '=', 'tbl_course_folder.id')->leftJoin('tbl_certificate', 'tbl_certificate.id', '=', 'tbl_course_folder_certificate.certificate_id')->select('tbl_certificate.name as certificate_name', 'tbl_course_folder_certificate.expiry')->where('tbl_course_folder.id', $course_folder_id)->first();
                                        if ($testCourse) {
                                            if ($testCourse->expiry) {
                                                $availableCertificateDays = (int)$testCourse->expiry;
                                            } else {
                                                $availableCertificateDays = 0;
                                            }
                                            $certificateId = EmployeeCertificateModel::insertGetId([
                                                'certificate_no' => '0',
                                                'certificate_name' => $testCourse->certificate_name,
                                                'course_id' => $course_folder_id,
                                                'employee_id' => $user_id,
                                                'certificate_date' => Carbon::now('UTC')->format('Y-m-d'),
                                                'is_coursefolder_certificate' => 1,
                                                'certificate_expiration_date' => Carbon::now('UTC')->addDays($availableCertificateDays)->format('Y-m-d'),
                                                'created_at' => Carbon::now('UTC'),
                                            ]);
                                            $certificateNo = '2020-100' . $certificateId;
                                            EmployeeCertificateModel::where('id', $certificateId)->update(['certificate_no' => $certificateNo]);
                                            EmployeeCourseHistoryModel::where('id', $historyId)->update(['certificate_id' => $certificateId]);
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
        $courseAttempts = EmployeeCourseAttemptsModel::where([
            'user_id' => $user_id,
            'course_id' => $course_id,
            'lesson_test' => $test_type,
            'lesson_test_id' => $test_id,
        ])->first();

        return [
            'message' => $message,
            'status' => $status,
            'retake' => $retake,
            'courseAttempts' => $courseAttempts,
        ];
    }
}
