<?php

namespace App\Http\Controllers;

use App\Models\EmployeeCoursesModel;
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
use App\Models\EmployeeCourseHistoryModel;
use Illuminate\Support\Facades\DB;
use App\Models\CompanyModel;
use Validator;
use Auth;

class AnalyticsController extends Controller {

    private $status;
    private $sucess;
    private $fail;

    /**
     * Construct function
     */
    public function __construct() {
        $this->status = config('constant.status');
        $this->sucess = config('constant.success');
        $this->fail = config('constant.fail');
    }


    public function recentCompletions(Request $request) {
        $where_data = [];

        $where_searchdata = [];
        if (!empty($request->search)) {
            $search = $request->search;
            $search = explode(" ", $search);
            foreach ($search as $key => $name) {
                if ($request->search != '') {
                    $where_searchdata[] = [
                        'tbl_employee.full_name',
                        'like',
                        '%' . $request->search . '%',
                    ];
                }
            }
        }
        $user = Auth::user();
        $where_data_manager = [];
        if ($user->role_id == 3) {

            $where_data_manager[] = [
                'tbl_employee.role_id',
                '!=',
                '2',
            ];
        }
        $today = date('Y-m-d');
        $previous_date = date('Y-m-d', strtotime('-7 days'));
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
        $companyIds = array_merge([$companies['isParent']], $companies['isLocations']);

        $startFrom = "";
        $limit = "";
        if (isset($request->page) && isset($request->per_page)) {
            $startFrom = ($request->page == 0) ? ($request->page * $request->per_page) : ($request->page - 1) * $request->per_page;
            $limit = $request->per_page;
        }


        if ($request->has('company_id')) {
            $company = CompanyModel::find($request->company_id);
            if ($company->parent_id == 0) {
                $where_data = 'tbl_employee_company_locations.company_id';
            } else {
                $where_data = 'tbl_employee_company_locations.location_id';
            }
            $companyIds = [$request->company_id];
        }

        $data = EmployeeCourseHistoryModel::select('tbl_employee.first_name', 'tbl_employee.last_name', 'tbl_company.name as company_name', 'tbl_course.name as course_name', 'tbl_employee_course_history.attempt_date as employee_course_date_completed')->leftjoin('tbl_employee_company_locations', 'tbl_employee_company_locations.employee_id', '=', 'tbl_employee_course_history.employee_id')->leftJoin('tbl_company', 'tbl_company.id', '=', DB::Raw('(CASE WHEN `tbl_employee_company_locations`.location_id = 0 then `tbl_employee_company_locations`.company_id ELSE `tbl_employee_company_locations`.location_id END)'))->leftjoin('tbl_employee', 'tbl_employee.id', '=', 'tbl_employee_course_history.employee_id')->leftjoin('tbl_course', 'tbl_course.id', '=', 'tbl_employee_course_history.course_id')->whereIn($where_data, $companyIds)->where($where_searchdata)->where($where_data_manager)->where('tbl_employee.status', 1)->where('tbl_employee_course_history.attempt_status', 1)->whereNotNull('tbl_employee_course_history.attempt_date')->whereBetween('tbl_employee_course_history.attempt_date', [
            $previous_date,
            $today,
        ])->groupBy('tbl_employee_company_locations.employee_id', 'tbl_employee_course_history.course_id')->orderBy('tbl_employee_course_history.attempt_date', 'desc');

        $total = $data->get()->count();

        if ($limit != '') {
            $data->skip($startFrom);
            $data->take($limit);
        }

        return [
            'data' => $data->get(),
            'total' => $total,
        ];
    }

    public function pofeCourses(Request $request) {
    $validator = Validator::make($request->all(), [
        'company_id' => 'required',
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
        $dateRange = self::getStartAndEndDate($request->dateRange);
        $startDate = $dateRange['startDate'];
        $endDate = $dateRange['endDate'];

        $companies = CompanyModel::getCompaniesByAdminUser(Auth::user()->id);
        $companyIds = array_merge([$companies['isParent']], $companies['isLocations']);

        if ($request->has('company_id')) {
            $companyIds = [$request->company_id];
        }

        $passedCoursesCount = EmployeeCoursesModel::leftJoin('tbl_employee', 'tbl_employee_courses.employee_id', 'tbl_employee.id')->leftJoin('tbl_course', 'tbl_employee_courses.course_id', 'tbl_course.id')->whereIn('tbl_employee_courses.company_id', $companyIds)->where('tbl_employee.status', 1)->where('tbl_course.status', 1)->where('employee_course_status', 1)->whereBetween('tbl_employee_courses.employee_course_date_completed', [
            $startDate,
            $endDate,
        ])->get()->count();

        $openedCoursesCount = EmployeeCoursesModel::leftJoin('tbl_employee', 'tbl_employee_courses.employee_id', 'tbl_employee.id')->leftJoin('tbl_course', 'tbl_employee_courses.course_id', 'tbl_course.id')->whereIn('tbl_employee_courses.company_id', $companyIds)->where('tbl_employee.status', 1)->where('tbl_course.status', 1)->where('employee_course_status', 2)->whereBetween('tbl_employee_courses.employee_course_date_assigned', [
            $startDate,
            $endDate,
        ])->get()->count();

        $expiredCoursesCount = EmployeeCoursesModel::leftJoin('tbl_employee', 'tbl_employee_courses.employee_id', 'tbl_employee.id')->leftJoin('tbl_course', 'tbl_employee_courses.course_id', 'tbl_course.id')->whereIn('tbl_employee_courses.company_id', $companyIds)->where('tbl_employee.status', 1)->where('tbl_course.status', 1)->where('employee_course_status', 3)->whereBetween('tbl_employee_courses.updated_at', [
            $startDate . ' 00:00:00',
            $endDate . ' 23:59:59',
        ])->get()->count();


        $failedCoursesCount = EmployeeCoursesModel::leftJoin('tbl_employee', 'tbl_employee_courses.employee_id', 'tbl_employee.id')->leftJoin('tbl_course', 'tbl_employee_courses.course_id', 'tbl_course.id')->whereIn('tbl_employee_courses.company_id', $companyIds)->where('tbl_employee.status', 1)->where('tbl_course.status', 1)->where('employee_course_status', 0)->whereBetween('tbl_employee_courses.updated_at', [
            $startDate . ' 00:00:00',
            $endDate . ' 23:59:59',
        ])->get();

        $failedCoursesCount = $failedCoursesCount->count();


        $dataVals = [
            'pass' => $passedCoursesCount,
            'open' => $openedCoursesCount,
            'fail' => $expiredCoursesCount + $failedCoursesCount,
        ];


        return response()->json($dataVals, 200);

    } catch (Exception $th) {

        return response()->json(['message' => $th->getMessage()], 422);
    }
}

    public function complianceCount() {
        try {
            $user = Auth::user();
            $where_data = [];
            if ($user->role_id == 3) {

                $where_data[] = [
                    'tbl_employee.role_id',
                    '!=',
                    '2',
                ];
            }
            $getResult = EmployeeCoursesModel::leftJoin('tbl_employee_company_locations', 'tbl_employee_courses.employee_id', '=', 'tbl_employee_company_locations.employee_id');
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
                $getResult->whereIn('tbl_employee_company_locations.company_id', $company_id);
            } else {
                $company_id = $companies['isLocations'];
                $getResult->whereIn('tbl_employee_company_locations.location_id', $company_id);

            }
            $data = $getResult->select('tbl_employee_courses.employee_id')->leftjoin('tbl_employee', 'tbl_employee.id', '=', 'tbl_employee_courses.employee_id')->whereNotIn('tbl_employee_courses.employee_id', DB::table('tbl_employee_courses')->select('tbl_employee_courses.employee_id')->where('employee_course_status', '=', 0)->orWhere('employee_course_status', '=', 3))->where($where_data)->where('tbl_employee.status', 1)->groupBy('tbl_employee_courses.employee_id')->get();

            return response()->json($data, 200);

        } catch (Exception $th) {

            return response()->json(['message' => $th->getMessage()], 422);
        }
    }

    private function prepareMonthlyResponseData() {
        $response = [];
        for ($i = 0; $i <= 5; $i++) {
            $newDate = Carbon::now('UTC')->subMonth(5)->firstOfMonth();
            $response[$i]['month'] = $newDate->addMonth($i)->toDateString();
            $response[$i]['count'] = 0;
        }

        return $response;
    }

    public function getCourseCompletionsByMonth(Request $request) {
        $result = [];

        $companies = CompanyModel::getCompaniesByAdminUser(Auth::user()->id);
        $companyIds = array_merge([$companies['isParent']], $companies['isLocations']);

        if ($request->has('company_id')) {
            $companyIds = [$request->company_id];
        }

        $dateRange = self::getStartAndEndDate($request->dateRange);
        $startDate = $dateRange['startDate'];
        $endDate = $dateRange['endDate'];

        $datePeriod = new \DatePeriod(new \DateTime($startDate), \DateInterval::createFromDateString('1 month'), new \DateTime($endDate));
        $months = [];
        foreach ($datePeriod as $dt) {
            $months[$dt->format('Y-m-01')] = 0;
        }

        $employeeCoursesModel = EmployeeCoursesModel::leftJoin('tbl_employee', 'tbl_employee_courses.employee_id', 'tbl_employee.id')->leftJoin('tbl_course', 'tbl_employee_courses.course_id', 'tbl_course.id')->whereIn('tbl_employee_courses.company_id', $companyIds)->where('tbl_employee.status', 1)->where('tbl_course.status', 1)->whereBetween('tbl_employee_courses.employee_course_date_completed', [
            $startDate,
            $endDate,
        ])->get([
            'tbl_employee_courses.id',
            'tbl_employee_courses.employee_course_date_completed',
        ]);

        if (!empty($employeeCoursesModel)) {
            foreach ($employeeCoursesModel as $item) {
                $months[date('Y-m-01', strtotime($item->employee_course_date_completed))] = $months[date('Y-m-01', strtotime($item->employee_course_date_completed))] + 1;
            }

            foreach ($months as $key => $item) {
                $result[] = [
                    'month' => $key,
                    'count' => $item,
                ];
            }
        }

        return $result;
    }

    public function getEmployeeDue(Request $request) {
        $where_data = [];
        // if (!empty($request->company_id)){
        //     $company_id=$request->company_id;
        //     array_push($where_data,['company_id',$company_id]);
        // }
        $user = Auth::user();
        $companyIds = CompanyModel::getAllCompanyIdsOfUsersAccoringUserRole($user->id, $user->role_id);

        $today = date('Y-m-d');
        $next_date = date('Y-m-d', strtotime('+7 days'));
        $data = [];
        $data1 = [];
        $employees = EmployeeCoursesModel::leftJoin('tbl_company', 'tbl_company.id', '=', 'tbl_employee_courses.company_id')->select('tbl_employee_courses.employee_id', 'tbl_company.name as company_name')->whereIn('tbl_employee_courses.company_id', $companyIds)->whereBetween('employee_course_due_date', [
            $today,
            $next_date,
        ])->where('employee_course_status', '!=', 1)->groupBy('employee_id')->get();
        foreach ($employees as $employee) {
            $id = $employee->employee_id;
            $employee_info = EmployeeModel::with('courses_due', 'location')->withCount('course_pass', 'course_fail', 'course_open')->where('id', $id)->get();
            if (!empty($employee_info[0])) {
                for ($i = 0; $i < count($employee_info); $i++) {
                    $employee_id = $employee_info[$i]->id;
                    $employee_info[$i]['company_name'] = $employee->company_name;
                    $courses = $employee_info[$i]->courses_due;
                    for ($j = 0; $j < count($courses); $j++) {
                        if (!empty($courses[$j])) {
                            $course_id = $courses[$j]->id;
                            $course_info = EmployeeCoursesModel::where([
                                [
                                    'employee_id',
                                    $employee_id,
                                ],
                                [
                                    'course_id',
                                    $course_id,
                                ],
                            ])->whereBetween('employee_course_due_date', [
                                $today,
                                $next_date,
                            ])->get();
                            $employee_info[$i]->courses_due[$j]->employee_course_due_date = $course_info[0]->employee_course_due_date;
                            $employee_info[$i]->courses_due[$j]->employee_course_status = $course_info[0]->employee_course_status;
                            $employee_info[$i]->courses_due[$j]->employee_course_date_assigned = $course_info[0]->employee_course_date_assigned;
                            $employee_info[$i]->courses_due[$j]->employee_course_date_completed = $course_info[0]->employee_course_date_completed;
                        }
                    }
                }
                $data1[] = $employee_info[0];
            }
        }
        $total = EmployeeCoursesModel::select('employee_id')->whereIn('tbl_employee_courses.company_id', $companyIds)->whereBetween('employee_course_due_date', [
            $today,
            $next_date,
        ])->groupBy('employee_id')->get();
        $total = count($total);
        $data['total'] = $total;
        $data['employee'] = $data1;

        return $data;
    }

    public function getnoncomplaint(Request $request) {
        $where_data = [];
        $or_where_data = [];
        //0 for fail
        $user = Auth::user();
        $companyIds = CompanyModel::getAllCompanyIdsOfUsersAccoringUserRole($user->id, $user->role_id);
        array_push($where_data, [
            'employee_course_status',
            '0',
        ]);
        array_push($or_where_data, [
            'employee_course_due_date',
            '<',
            date('Y-m-d'),
        ]);
        $data1 = [];
        // if ($request->company_id) {
        //     $company_id = $request->company_id;
        //     array_push($where_data,['company_id', $company_id]);
        //     array_push($or_where_data,['company_id', $company_id]);
        // }
        $employees = EmployeeCoursesModel::leftJoin('tbl_company', 'tbl_company.id', '=', 'tbl_employee_courses.company_id')->select('tbl_employee_courses.employee_id', 'tbl_company.name as company_name')->whereIn('tbl_employee_courses.company_id', $companyIds)->where(function($query) {
            $query->where('employee_course_status', '0');
            $query->orWhere('employee_course_due_date', '<', date('Y-m-d'));
        })->groupBy('employee_id')->get();
        foreach ($employees as $employee) {
            $id = $employee->employee_id;
            $employee_info = EmployeeModel::with('courses', 'location')->withCount('course_pass', 'course_fail', 'course_open')->where('id', $id)->get();
            if (!empty($employee_info[0])) {
                for ($i = 0; $i < count($employee_info); $i++) {
                    $employee_id = $employee_info[$i]->id;
                    $employee_info[$i]['company_name'] = $employee->company_name;
                    $courses = $employee_info[$i]->courses;
                    for ($j = 0; $j < count($courses); $j++) {
                        if (!empty($courses[$j])) {
                            $course_id = $courses[$j]->id;
                            $course_info = EmployeeCoursesModel::where([
                                [
                                    'employee_id',
                                    $employee_id,
                                ],
                                [
                                    'course_id',
                                    $course_id,
                                ],
                            ])->get();
                            $employee_info[$i]->courses[$j]->employee_course_due_date = $course_info[0]->employee_course_due_date;
                            $employee_info[$i]->courses[$j]->employee_course_status = $course_info[0]->employee_course_status;
                            $employee_info[$i]->courses[$j]->employee_course_date_assigned = $course_info[0]->employee_course_date_assigned;
                            $employee_info[$i]->courses[$j]->employee_course_date_completed = $course_info[0]->employee_course_date_completed;
                        }
                    }
                }
                $data1[] = $employee_info[0];
            }
        }
        if (!empty($request->company_id)) {
            $company_id = $request->company_id;
            $total = EmployeeCoursesModel::select('employee_id')->where([
                [
                    'employee_course_status',
                    0,
                ],
                [
                    'company_id',
                    $company_id,
                ],
            ])->orWhere('employee_course_due_date', '<', date('Y-m-d'))->groupBy('employee_id')->get();
            $total = count($total);
        } else {
            $total = EmployeeCoursesModel::select('employee_id')->where('employee_course_status', 0)->groupBy('employee_id')->orWhere('employee_course_due_date', '<', date('Y-m-d'))->get();
            $total = count($total);
        }
        $data['total'] = $total;
        $data['employee'] = $data1;

        return $data;
    }

    /**
     * To get the start and end date for the search filters
     *
     * @param $dateRange
     *
     * @return array containing start and end date of the date range
     */
    public static function getStartAndEndDate($dateRange) {
        $startDate = date('Y-m-01', strtotime('-1 month'));
        if ($dateRange) {
            $startDate = date('Y-m-01', strtotime('-' . $dateRange . ' month'));
        }
        $endDate = date('Y-m-d');

        return [
            'startDate' => $startDate,
            'endDate' => $endDate,
        ];
    }
}
