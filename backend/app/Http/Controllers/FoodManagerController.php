<?php

namespace App\Http\Controllers;

use App\Models\CourseModel;
use App\Models\EmployeeCoursesModel;
use Illuminate\Http\Request;
use App\Http\Traits\CommonTrait;
use App\Models\EmployeeCourseAttemptsModel;
use App\Models\CourseCertificateModel;
use App\Models\EmployeeCertificateModel;
use App\Models\EmployeeModel;
use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\File;
use ZipArchive;
use Barryvdh\DomPDF\Facade as PDF;

class FoodManagerController extends Controller
{
    use CommonTrait;
    private $food_manager_course;
    /**
     * Construct function
     */
    public function __construct()
    {

        $this->food_manager_course = config('constant.food_manager_course');
    }


    public function foodManagerEmployees(Request $request)
    {
        try {
            $getSheet = array();
            $getFMEmployee = EmployeeCoursesModel::select('tbl_employee.id as employee_id', 'tbl_employee_courses.employee_course_status', 'tbl_employee.first_name', 'tbl_employee.last_name', 'tbl_employee_certificates.id', 'tbl_employee_certificates.certificate_url')
                ->leftjoin('tbl_employee', 'tbl_employee_courses.employee_id', '=', 'tbl_employee.id')
                ->leftjoin('tbl_employee_company_locations', 'tbl_employee_company_locations.employee_id', '=', 'tbl_employee_courses.employee_id')
                ->leftJoin('tbl_employee_certificates', function ($query) {
                    $query->on('tbl_employee_certificates.course_id', '=', 'tbl_employee_courses.course_id');
                    $query->on('tbl_employee_certificates.employee_id', '=', 'tbl_employee_courses.employee_id');
                })
                ->where('tbl_employee.status', 1)
                ->where('tbl_employee_courses.course_id', $this->food_manager_course)
                ->where('tbl_employee_company_locations.company_id', $request->company_id)
                ->whereYear('tbl_employee_courses.employee_course_date_assigned', $request->year)
                ->groupby('tbl_employee_courses.employee_id', 'tbl_employee_courses.course_id')->get();

            $status =  "";
            foreach ($getFMEmployee as $key => $value) {
                if ($value->employee_course_status == 1) {
                    $status = "Passed";
                } else if ($value->employee_course_status == 2) {
                    $status = "Open";
                } else if ($value->employee_course_status == 0) {
                    $status = "Failed";
                } else if ($value->employee_course_status == 3) {
                    $status = "Expired";
                }

                $getSheet[$key]['First Name'] = ucfirst($value->first_name);
                $getSheet[$key]['Last Name'] = ucfirst($value->last_name);
                $getSheet[$key]['Status'] = $status;
            }
            return response()->json(['report' => $getFMEmployee, 'download' => $getSheet], 200);
        } catch (Exception $ex) {
            return response()->json(['message' => $ex->getMessage()], 422);
        }
    }
    public function reassignFoodManager(Request $request)
    {
        $courseDueDate = CourseModel::select('employees_days_to_complete as due_days')->where('id', $this->food_manager_course)->first();
        $due_days = 0;
        if ($courseDueDate != null) {
            $due_days = (int)$courseDueDate->due_days;
        }
        $course_due_date = Carbon::now('UTC')->addDays($due_days)->format('Y-m-d');
        EmployeeCoursesModel::whereIn('employee_id', $request->employee_id)
            ->where('course_id', $this->food_manager_course)->update([
                'employee_course_status' => 2,
                'employee_course_date_completed' => '',
                'employee_course_date_assigned' => Carbon::now('UTC'),
                'employee_course_due_date' => $course_due_date
            ]);

        EmployeeCourseAttemptsModel::whereIn('user_id', $request->employee_id)
            ->where('course_id', $this->food_manager_course)->delete();
    }

    public function ReminderFoodManager(Request $request)
    {
        foreach ($request->employee_id as $employee_id) {
            $getEmployeeData = EmployeeModel::where('id', $employee_id)->first();
            $getCourseData = EmployeeCoursesModel::where('employee_id', $employee_id)
                ->where('course_id', $this->food_manager_course)->first();
            $email = $getEmployeeData->email;
            $today = Carbon::now('UTC');
            $courseStatus = "";
            if ($getCourseData->employee_course_due_date > $today) {
                $courseStatus = "Open";
            } else {
                $courseStatus = "Expired";
            }
            $data = array(
                'full_name' => $getEmployeeData->full_name,
                'email' => $getEmployeeData->employee_email,
                'course' => 'Food Manager',
                'course_status' => $courseStatus,
                'due_date' => Carbon::parse($getCourseData->employee_course_due_date)->format("m-d-Y"),
            );

            Mail::send('food_manager_reminder', $data, function ($message) use ($email) {
                $message->to($email)->subject('Important reminder from ' . env('SITE_NAME') . '!');
                $message->from(env('MAIL_FROM_ADDRESS'), env('MAIL_FROM_NAME'));
            });
            CommonTrait::emailLog("Food Manager Reminder", $email, $getEmployeeData->employee_id);
        }
    }
    public function downloadBulkFoodManagerCertificate($user_id)
    {
        try {


        } catch (Exception $th) {

            echo $th->getMessage();
            exit;
        }
    }
}
