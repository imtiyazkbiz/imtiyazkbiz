<?php

namespace App\Http\Controllers;


use App\Http\Traits\CourseTrait;
use App\Models\CourseModel;
use App\Models\EmployeeCertificateModel;
use App\Models\EmployeeCompanyLocationsModel;
use App\Models\EmployeeCourseAttemptsModel;
use App\Models\EmployeeCourseHistoryModel;
use App\Models\EmployeeCoursesModel;
use App\Models\EmployeeModel;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class CronJobsController extends Controller {
    use CourseTrait;

    /**
     * To update the course status to expire course when due date is passed
     */
    public function setDueCourseToExpireCourse() {
        EmployeeCoursesModel::where('employee_course_status', 2)->where('employee_course_due_date', '<', date('Y-m-d'))->update([
            'employee_course_status' => 3,
        ]);
    }

    /*
     * Assign the course back to the user if the course certificate is expired
     */
    public function reAssignCourseOnCertificateExpire() {
       $employeeCertificates = EmployeeCertificateModel::where('certificate_expiration_date', '<', date('Y-m-d'))->where('employee_id', 7719)->get();

        //return $employeeCertificates = EmployeeCertificateModel::where('certificate_expiration_date', '<', date('Y-m-d'))->get();
        if (!empty($employeeCertificates)) {
            $employeeCourses = []; # key = employee id, value = array of course ids whose certificates are expired for that employee
            foreach ($employeeCertificates as $employeeCertificate) {
                $employeeCourses[$employeeCertificate->employee_id][] = $employeeCertificate->course_id;
            }
        }
        if (!empty($employeeCourses)) {
            foreach ($employeeCourses as $employeeId => $employeeCourse) {
                $employee = EmployeeModel::where('id', $employeeId)->where('status', 1)->first();
                if (is_null($employee)) {
                    continue;
                }

                $courses = CourseModel::whereIn('id', $employeeCertificates->pluck('course_id'))->whereNotNull('category')->get();
                if(!empty($courses)) {
                    EmployeeCoursesModel::where('employee_id', $employeeId)->where('employee_course_status', '!=', '1')->whereIn('course_id', $courses->pluck('id'))->delete();
                }

                /**
                 * If any open course belongs to the category then remove all the open courses
                 */

                EmployeeCourseAttemptsModel::where('user_id', $employeeId)->whereIn('course_id', $employeeCourse)->delete();
                EmployeeCourseHistoryModel::where('employee_id', $employeeId)->whereIn('course_id', $employeeCourse)->delete();
                DB::table('tbl_employee_course_test_attempts')->where('employee_id', $employeeId)->whereIn('course_id', $employeeCourse)->delete();
                EmployeeCoursesModel::where('employee_id', $employeeId)->whereIn('course_id', $employeeCourse)->delete();
                EmployeeCertificateModel::where('employee_id', $employeeId)->whereIn('course_id', $employeeCourse)->delete();

                $coursesToReAssign = CourseModel::whereIn('id', $employeeCourse)->whereRaw('id NOT IN (SELECT next_course from tbl_course where id IN (' . implode(', ', $employeeCourse) . '))')->get()->pluck('id');
                $employeeCompany = EmployeeCompanyLocationsModel::where('employee_id', $employeeId)->first();

                if (!empty($coursesToReAssign)) {
                    foreach ($coursesToReAssign as $courseId) {
                        $course = CourseModel::where('id', $courseId)->where('status', 1)->first();
                        if (is_null($course) === TRUE) {
                            continue;
                        }

                        $due_days = (int)$course->managers_days_to_complete;
                        if ($employee->type === 'employee') {
                            $due_days = (int)$course->employees_days_to_complete;
                        }

                        $employeeCourse = new EmployeeCoursesModel();
                        $employeeCourse->employee_id = $employeeId;
                        $employeeCourse->course_id = $courseId;
                        $employeeCourse->company_id = $employeeCompany->company_id;
                        $employeeCourse->employee_course_date_assigned = date('Y-m-d');
                        $employeeCourse->employee_course_due_date = Carbon::now('UTC')->addDays($due_days)->format('Y-m-d');
                        $employeeCourse->save();
                    }
                }
            }
        }
    }
}
