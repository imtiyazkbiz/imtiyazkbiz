<?php

namespace App\Http\Controllers;


use App\Helpers\Helper;
use App\Models\CompanyModel;
use App\Models\CourseModel;
use App\Models\EmployeeCertificateModel;
use App\Models\EmployeeCourseAttemptsModel;
use App\Models\EmployeeCoursesModel;
use App\Models\EmployeeModel;
use App\Models\UserActivityLogModel;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class TestingDebuggingController extends Controller {
    public static function logging($name, $data = '') {
        Log::info($name . '------------------------');
        if (empty($data)) {
            return '';
        }
        if (is_object($data)) {
            Log::info(print_r($data->toArray(), TRUE));
        } else if (is_array($data)) {
            Log::info(print_r($data, TRUE));
        } else {
            Log::info($data);
        }
    }


    public function test(Request $request) {
        //$this->emailTemplateTest($request);

        try {

            $where_data = [];

            $where = "";
            $companyIds = [];


            if(empty($request->company_id)) {
                return 'Please pass the company_id';
            }

            $companyId = $request->company_id;


            if ($companyId != '' && $companyId != 0) {
                if (is_array($companyId)) {
                    $companyIds = $companyId;
                } else {
                    $companyIds = array($companyId);
                }
                $companies = CompanyModel::where('id', $companyIds)->first();
                if ($companies->parent_id != 0) {
                    $where = 'tbl_employee_company_locations.location_id';
                } else {
                    $where = 'tbl_employee_company_locations.company_id';
                    array_push($where_data, [
                        'tbl_employee_company_locations.location_id',
                        0,
                    ]);
                }
            }

            $data = UserActivityLogModel::select('tbl_activity_log.*', 'tbl_course.name', 'tbl_employee.first_name', 'tbl_employee.last_name', 'tbl_employee.user_name', 'tbl_company.name as company_name')
                ->leftjoin('tbl_course', 'tbl_activity_log.course_id', '=', 'tbl_course.id')
                ->leftjoin('tbl_employee', 'tbl_activity_log.user_id', '=', 'tbl_employee.id')
                ->leftjoin('tbl_employee_company_locations', 'tbl_employee_company_locations.employee_id', '=', 'tbl_activity_log.user_id')->leftJoin('tbl_company', 'tbl_company.id', '=', DB::Raw('(CASE WHEN `tbl_employee_company_locations`.location_id = 0 then `tbl_employee_company_locations`.company_id ELSE `tbl_employee_company_locations`.location_id END)'))
                ->where($where_data);
            if ($where) {
                $data->whereIn($where, $companyIds);
            }

            $data->orderBy('tbl_activity_log.created_at', 'DESC');

            $data->groupBy('tbl_activity_log.user_id', 'created_at');



            $records = array();
            $getSheet = array();
            $records = $data->get();

            $entriesToSkip = [];

            $companyName = $companies->name;

            foreach ($records as $key => $value) {
                if(empty($value->name)) {
                    continue;
                }

                $needle = $value->user_name . '-' . $value->name;

                if(in_array($needle, $entriesToSkip)) {
                    continue;
                }

                $entriesToSkip[] = $needle;

                $getSheet[$key]['First Name'] = ucfirst($value->first_name);
                $getSheet[$key]['Last Name'] = ucfirst($value->last_name);
                $getSheet[$key]['User Name'] = $value->user_name;
                $getSheet[$key]['Location'] = $value->company_name ? $value->company_name : '-';
                $getSheet[$key]['IP'] = $value->ip;
                $getSheet[$key]['Course'] = $value->name ? $value->name : '-';
                $getSheet[$key]['Total Time Spent'] = $value->total_time_spent ? gmdate("H:i:s", $value->total_time_spent) : '-';
                $getSheet[$key]['Activity Performed'] = $value->event;
                $getSheet[$key]['Date'] = Carbon::parse($value->created_at)->format('m-d-Y');
            }

            array_multisort(array_column($getSheet, 'First Name'), SORT_ASC, SORT_STRING, $getSheet);

            // return $getSheet;

            $fp = fopen('php://output', 'w');

            header('Content-type: application/csv');
            header('Content-Disposition: attachment; filename=' . $companyName . '.csv');
            fputcsv($fp, [
                'First Name',
                'Last Name',
                'User Name',
                'Location',
                'IP',
                'Course',
                'Total Time Spent',
                'Activity Performed',
                'Date',
            ]);
            
            foreach($getSheet as $getS) {
                fputcsv($fp, $getS);
            }

            exit();


        } catch (Exception $ex) {
            return response()->json(['message' => $ex->getMessage()], 422);
        }

    }

    public function emailTemplateTest($request) {
        $emailAddress = 'rishabhg@chetu.com';
        switch ($request->case) {
            case 1:
                $data = array(
                    'full_name' => 'Rishabh Gupta',
                    'company_name' => 'Chetu',
                    'email' => 'rishabhg@chetu.com',
                    'courses' => [
                        [
                            'name' => 'Course 1',
                        ],
                        [
                            'name' => 'Course 2',
                        ],
                        [
                            'name' => 'Course 3',
                        ],
                    ],
                    'expire_date' => date('m/d/Y'),
                    'reminder' => '1 day',
                    'userId' => Helper::maskUserId(1),
                );

                if ($request->onlyHTML) {
                    echo view('auto_reminder', $data);
                    break;
                }

                Mail::send('auto_reminder', $data, function($message) use ($emailAddress) {
                    $message->to($emailAddress)->subject('Urgent Reminder – Training Due');
                    $message->from(env('MAIL_FROM_ADDRESS'), env('MAIL_FROM_NAME'));
                });
                break;
            case 2:
                $data = array(
                    "type" => "employee",
                    "location_name" => 'Noida',
                    "company_name" => 'Chetu',
                    "status" => "added",
                    "maxUserCount" => 1,
                    "currentUserCount" => 2,
                    "employeeDetails" => [
                        'firstName' => "Rishabh",
                        'lastName' => "Gupta",
                        'email' => "rishabhg@chetu.com",
                    ],
                );

                if ($request->onlyHTML) {
                    echo view('charge_company', $data);
                    break;
                }

                Mail::send('charge_company', $data, function($message) use ($emailAddress) {
                    $message->to($emailAddress)->subject(env('SITE_NAME') . ' - Location Added');
                    $message->from(env('MAIL_FROM_ADDRESS'), env('MAIL_FROM_NAME'));
                });
                break;
            case 3:
                $data = array(
                    'courses' => [
                        'Course 1',
                        'Course 2',
                        'Course 3',
                    ],
                    'content' => 'You have selected to view the following course(s):',
                    'first_name' => 'Rishabh',
                    'last_name' => 'Gupta',
                    'userId' => Helper::maskUserId(1),
                );

                if ($request->onlyHTML) {
                    echo view('course_assigned', $data);
                    break;
                }

                Mail::send('course_assigned', $data, function($message) use ($emailAddress) {
                    $message->to($emailAddress)->subject(env('SITE_NAME') . ' - Course(s) Now Available!');
                    $message->from(env('MAIL_FROM_ADDRESS'), env('MAIL_FROM_NAME'));
                });
                break;
            case 4:
                $data = array(
                    'first_name' => 'Rishabh',
                    'last_name' => 'Gupta',
                    'company_name' => 'Chetu',
                    'email' => 'rishabhg@chetu.com',
                    'courses' => [
                        [
                            'name' => 'Course 1',
                            'dus_days' => 1,
                        ],
                        [
                            'name' => 'Course 2',
                            'dus_days' => 2,
                        ],
                        [
                            'name' => 'Course 3',
                            'dus_days' => 3,
                        ],
                    ],
                    'userId' => Helper::maskUserId(1),
                );

                if ($request->onlyHTML) {
                    echo view('expired_mail', $data);
                    break;
                }

                Mail::send('expired_mail', $data, function($message) use ($emailAddress) {
                    $message->to($emailAddress)->subject('Urgent – Courses Overdue!');
                    $message->from(env('MAIL_FROM_ADDRESS'), env('MAIL_FROM_NAME'));
                });
                break;
            case 5:
                $data = array(
                    'full_name' => 'Rishabh Gupta',
                    'email' => 'rishabhg@chetu.com',
                    'course' => 'Food Manager',
                    'course_status' => 'Open',
                    'due_date' => date('m/d/Y'),
                );

                if ($request->onlyHTML) {
                    echo view('food_manager_reminder', $data);
                    break;
                }

                Mail::send('food_manager_reminder', $data, function($message) use ($emailAddress) {
                    $message->to($emailAddress)->subject('Important reminder from ' . env('SITE_NAME') . '!');
                    $message->from(env('MAIL_FROM_ADDRESS'), env('MAIL_FROM_NAME'));
                });
                break;
            case 6:
                $data = [
                    'email' => 'rishabhg@chetu.com',
                    'link' => env('LMS_URL') . '/#/reset_password?link=' . encrypt('rishabhgupta54'),
                    'first_name' => 'Rishabh',
                    'last_name' => 'Gupta',
                    'userId' => Helper::maskUserId(1),
                ];

                if ($request->onlyHTML) {
                    echo view('forget_password_reset', $data);
                    break;
                }

                Mail::send('forget_password_reset', $data, function($message) use ($emailAddress) {
                    $message->to($emailAddress)->subject('Forgot Password Link - ' . env('SITE_NAME'));
                    $message->from(env('MAIL_FROM_ADDRESS'), env('MAIL_FROM_NAME'));
                });
                break;
            case 7:
                $data = array(
                    'full_name' => 'Rishabh Gupta',
                    'file_name' => 'Test File',
                    'file' => 'test_file.pdf',
                    'company_name' => 'Chetu',
                    'userId' => Helper::maskUserId(1),
                );

                if ($request->onlyHTML) {
                    echo view('hrform_notify', $data);
                    break;
                }

                Mail::send('hrform_notify', $data, function($message) use ($emailAddress) {
                    $message->to($emailAddress)->subject(env('SITE_NAME') . ' - Company Form Notification');
                    $message->from(env('MAIL_FROM_ADDRESS'), env('MAIL_FROM_NAME'));
                });
                break;
            case 8:
                $data = array(
                    'first_name' => 'Rishabh',
                    'last_name' => 'Gupta',
                    'company_name' => 'Chetu',
                    'email' => 'rishabhg@chetu.com',
                    'courses' => [
                        [
                            'name' => 'Course 1',
                            'dus_days' => 1,
                        ],
                        [
                            'name' => 'Course 2',
                            'dus_days' => 2,
                        ],
                        [
                            'name' => 'Course 3',
                            'dus_days' => 3,
                        ],
                    ],
                    'userId' => Helper::maskUserId(1),
                );

                if ($request->onlyHTML) {
                    echo view('mail', $data);
                    break;
                }

                Mail::send('mail', $data, function($message) use ($emailAddress) {
                    $message->to($emailAddress)->subject('Important Reminder – Training Course Due!');
                    $message->from(env('MAIL_FROM_ADDRESS'), env('MAIL_FROM_NAME'));
                });
                break;
            case 9:
                $data = array(
                    'date' => date('m/d/Y'),
                    'interface' => 'Individual',
                    'name' => 'Chetu',
                    'already_member' => 'Yes',
                    'member_id' => 123,
                    'first_name' => 'Rishabh',
                    'last_name' => 'Gupta',
                    'address' => '2066 Heritage Road, Visalia, California, 93291',
                    'street_address' => '2066 Heritage Road, Visalia, California, 93291',
                    'zip_code' => '93291',
                    'phone_no' => '(123) 456-7890',
                    'email' => 'rishabhg@chetu.com',
                    'courses' => [
                        [
                            'name' => 'Course 1',
                        ],
                        [
                            'name' => 'Course 2',
                        ],
                        [
                            'name' => 'Course 3',
                        ],
                    ],
                    'course_folders' => [
                        [
                            'name' => 'Course Folder 1',
                        ],
                        [
                            'name' => 'Course Folder 2',
                        ],
                        [
                            'name' => 'Course Folder 3',
                        ],
                    ],
                    'plan_detail' => 'Monthly',
                    'credit_card' => str_replace('"', '', '1234'),
                    'expiry' => '01/23',
                    'fee' => 100,
                    'promo_code' => '',
                    'course_cost' => '',
                    'discounted_cost' => '',
                    'location_count' => 1,
                    'user_count' => 10,
                );

                if ($request->onlyHTML) {
                    echo view('new_signup', $data)->render();
                    break;
                }

                Mail::send('new_signup', $data, function($message) use ($emailAddress) {
                    $message->to($emailAddress)->subject('New Individual Signup - ' . env('SITE_NAME') . '!');
                    $message->from(env('MAIL_FROM_ADDRESS'), env('MAIL_FROM_NAME'));
                });
                break;
            case 10:
                $data = [
                    'recent_completions' => EmployeeCoursesModel::select('tbl_employee.id as employee_id', 'tbl_employee.full_name', 'tbl_course.name', 'tbl_company.name as company_name', 'tbl_employee_courses.employee_course_date_completed')->leftjoin('tbl_employee', 'tbl_employee.id', '=', 'tbl_employee_courses.employee_id')->leftjoin('tbl_course', 'tbl_course.id', '=', 'tbl_employee_courses.course_id')->leftjoin('tbl_company', 'tbl_company.id', '=', 'tbl_employee_courses.company_id')->where('tbl_employee_courses.employee_course_status', 1)->whereBetween('employee_course_date_completed', [
                        '2020-01-01',
                        '2023-02-01',
                    ])->whereNotNull('tbl_employee_courses.employee_course_date_completed')->orderBy('tbl_employee_courses.employee_course_date_completed', 'desc')->limit(10)->get()->toArray(),
                    'employee_course_due' => EmployeeCoursesModel::select('tbl_employee.id as employee_id', 'tbl_employee.full_name', 'tbl_course.name', 'tbl_company.name as company_name', 'tbl_employee_courses.employee_course_due_date')->leftjoin('tbl_employee', 'tbl_employee.id', '=', 'tbl_employee_courses.employee_id')->leftjoin('tbl_course', 'tbl_course.id', '=', 'tbl_employee_courses.course_id')->leftjoin('tbl_company', 'tbl_company.id', '=', 'tbl_employee_courses.company_id')->whereBetween('employee_course_due_date', [
                        '2020-01-01',
                        '2023-02-01',
                    ])->where('tbl_employee_courses.employee_course_status', '!=', 1)->limit(10)->get()->toArray(),
                ];

                if ($request->onlyHTML) {
                    echo view('progress_report', $data);
                    break;
                }

                Mail::send('progress_report', $data, function($message) use ($emailAddress) {
                    $message->to($emailAddress)->subject('Progress Report');
                    $message->from(env('MAIL_FROM_ADDRESS'), env('MAIL_FROM_NAME'));
                });
                break;
            case 11:
                $data = [
                    'messagedata' => "Thank you for subscribing!",
                ];

                if ($request->onlyHTML) {
                    echo view('subscribe_mail', $data);
                    break;
                }

                Mail::send('subscribe_mail', $data, function($message) use ($emailAddress) {
                    $message->to($emailAddress)->subject(env('SITE_NAME') . 'Subscribed');
                    $message->from(env('MAIL_FROM_ADDRESS'), env('MAIL_FROM_NAME'));
                });
                break;
            case 12:
                $data = array(
                    'personName' => 'Rishabh Gupta',
                    'personEmail' => 'rishabhg@chetu.com',
                    'personQuery' => 'I am unable to send the email',
                );

                if ($request->onlyHTML) {
                    echo view('support_mail', $data);
                    break;
                }

                Mail::send('support_mail', $data, function($message) use ($emailAddress) {
                    $message->to($emailAddress)->subject(env('SITE_NAME') . ' Email Support');
                    $message->from(env('MAIL_FROM_ADDRESS'), env('MAIL_FROM_NAME'));
                });
                break;
            case 13:
                $data = array(
                    'full_name' => 'Testing',
                    'tutorial_link' => 'https://google.com',
                    'userId' => Helper::maskUserId(1),
                );

                if ($request->onlyHTML) {
                    echo view('tutorial_link', $data);
                    break;
                }

                Mail::send('tutorial_link', $data, function($message) use ($emailAddress) {
                    $message->to($emailAddress)->subject(env('SITE_NAME') . ' - Tutorial Link');
                    $message->from(env('MAIL_FROM_ADDRESS'), env('MAIL_FROM_NAME'));
                });
                break;
            case 14:
                $data = [
                    'first_name' => 'Rishabh',
                    'last_name' => 'Gupta',
                    'email' => 'rishabhg@chetu.com',
                    'phone_num' => '(123) 456-7890',
                    'company_name' => 'Chetu',
                    'number_of_locations' => 1,
                    'number_of_employees' => 2,
                    'type' => 'individual',
                    'user_type' => 'individual',
                    'courses' => CourseModel::whereIn('id', [
                        336,
                        338,
                        339,
                        341,
                    ])->select('id', 'name', 'description', 'cost', 'course_type')->get(),
                    'course_folders' => [
                        [
                            'name' => 'Course Folder 1',
                        ],
                        [
                            'name' => 'Course Folder 2',
                        ],
                        [
                            'name' => 'Course Folder 3',
                        ],
                    ],
                    'special_courses' => [],
                    'perYearCost' => 100,
                    'discount' => 0,
                    'sub_total' => 100,
                    'discount_value' => 0,
                    'total_discounted' => 0,
                    'per_location' => 50,
                    'per_employee' => 50,
                    'user_id' => 1,
                    'promo_code' => '',
                    'course_cost' => 100,
                    'discounted_cost' => '',
                    'original_cost' => 100,
                    'costPerMonth' => 100,
                    'costPerYear' => 100,
                    'costPerUser' => 100,
                    'onlySpecialCourse' => 0,
                    'already_member ' => 'Yes',
                ];

                if ($request->onlyHTML) {
                    echo view('user_leads', $data);
                    break;
                }

                Mail::send('user_leads', $data, function($message) use ($emailAddress) {
                    $message->to($emailAddress)->subject(env('SITE_NAME') . ' - New Lead');
                    $message->from(env('MAIL_FROM_ADDRESS'), env('MAIL_FROM_NAME'));
                });
                break;
            case 15:
                $data = array(
                    'username' => 'rishabhgupta54',
                    'company_name' => 'Chetu',
                    'course_name' => 'Course 1',
                    'pretest_name' => 'Pre Test',
                    'pretest' => [
                        [
                            'question_title' => 'Question 1',
                            'answer_title' => 'Answer 1',
                        ],
                        [
                            'question_title' => 'Question 2',
                            'answer_title' => 'Answer 2',
                        ],
                        [
                            'question_title' => 'Question 3',
                            'answer_title' => 'Answer 3',
                        ],
                    ],
                );

                if ($request->onlyHTML) {
                    echo view('user_pretest', $data);
                    break;
                }

                Mail::send('user_pretest', $data, function($message) use ($emailAddress) {
                    $message->to($emailAddress)->subject(env('SITE_NAME') . ' - User Pre Test');
                    $message->from(env('MAIL_FROM_ADDRESS'), env('MAIL_FROM_NAME'));
                });
                break;
            case 16:
                $data['weekly_data'] = EmployeeCoursesModel::select('tbl_employee.first_name', 'tbl_employee.last_name', 'tbl_company.name as company_name', 'tbl_employee.address', 'tbl_employee.city', 'tbl_employee.state', 'tbl_employee.zipcode', 'tbl_employee.phone_num', 'tbl_employee.email', 'tbl_employee_courses.course_id', 'tbl_employee_courses.employee_course_due_date', 'tbl_employee_courses.employee_course_date_completed', 'tbl_course.name AS course_name')->leftjoin('tbl_employee_company_locations', 'tbl_employee_company_locations.employee_id', '=', 'tbl_employee_courses.employee_id')->leftJoin('tbl_company', 'tbl_company.id', '=', DB::Raw('(CASE WHEN `tbl_employee_company_locations`.location_id = 0 then `tbl_employee_company_locations`.company_id ELSE `tbl_employee_company_locations`.location_id END)'))->leftjoin('tbl_course', 'tbl_course.id', '=', 'tbl_employee_courses.course_id')->leftjoin('tbl_employee', 'tbl_employee.id', '=', 'tbl_employee_courses.employee_id')->where('tbl_employee_courses.employee_course_status', 1)->where('tbl_employee_courses.employee_course_date_completed', '!=', NULL)->whereBetween('tbl_employee_courses.employee_course_date_completed', [
                    '2022-01-01',
                    date('Y-m-d'),
                ])->limit(10)->get();

                if ($request->onlyHTML) {
                    echo view('weekly_completion', $data);
                    break;
                }

                Mail::send('weekly_completion', $data, function($message) use ($emailAddress) {
                    $message->to($emailAddress)->subject('Weekly Course Completion report ' . env('SITE_NAME') . '!');
                    $message->from(env('MAIL_FROM_ADDRESS'), env('MAIL_FROM_NAME'));
                });
                break;
            case 17:
                $data = [
                    'first_name' => 'Rishabh',
                    'last_name' => 'Gupta',
                    'company_name' => 'Chetu',
                    'email' => 'rishabhg@chetu.com',
                    'access_code' => '123456',
                ];

                if ($request->onlyHTML) {
                    echo view('welcome_company', $data);
                    break;
                }

                Mail::send('welcome_company', $data, function($message) use ($emailAddress) {
                    $message->to($emailAddress)->subject('Welcome to ' . env('SITE_NAME') . ' Online Training');
                    $message->from(env('MAIL_FROM_ADDRESS'), env('MAIL_FROM_NAME'));
                });
                break;
            case 18:
                $data = [
                    'first_name' => 'Rishabh',
                    'last_name' => 'Gupta',
                    'company_name' => 'Chetu',
                    'email' => 'rishabhg@chetu.com',
                    'access_code' => '123456',
                    'user_type' => 'employee',
                    'user_name' => 'rishabhgupta54',
                ];

                if ($request->onlyHTML) {
                    echo view('welcome_employee', $data);
                    break;
                }

                Mail::send('welcome_employee', $data, function($message) use ($emailAddress) {
                    $message->to($emailAddress)->subject('Welcome to ' . env('SITE_NAME') . ' Online Training');
                    $message->from(env('MAIL_FROM_ADDRESS'), env('MAIL_FROM_NAME'));
                });
                break;
            case 19:
                $data = [
                    'first_name' => 'Rishabh',
                    'last_name' => 'Gupta',
                    'company_name' => 'Chetu',
                    'email' => 'rishabhg@chetu.com',
                    'access_code' => '123456',
                    'user_type' => 'employee',
                    'user_name' => 'rishabhgupta54',
                ];

                if ($request->onlyHTML) {
                    echo view('welcome_employee_indivisual', $data);
                    break;
                }

                Mail::send('welcome_employee_indivisual', $data, function($message) use ($emailAddress) {
                    $message->to($emailAddress)->subject('Welcome to ' . env('SITE_NAME') . ' Online Training');
                    $message->from(env('MAIL_FROM_ADDRESS'), env('MAIL_FROM_NAME'));
                });
                break;
        }
    }

    public function cronJobTest() {
        $data = [
            'date' => date('Y-m-d H:i:s'),
        ];
        Mail::send('test', $data, function($message) {
            $message->to('rishabhg@chetu.com')->subject('CRON Job Testing ' . env('SITE_NAME'));
            $message->from(env('MAIL_FROM_ADDRESS'), env('MAIL_FROM_NAME'));
        });
    }

    private function changeUserEmailAddress() {
        $users = EmployeeModel::all();
        foreach ($users as $user) {
            if (strpos($user->user_name, '@')) {
                $user->user_name = explode('@', $user->user_name)[0] . '@yopmail.com';
            }
            if (!empty($user->email)) {
                $user->email = explode('@', $user->email)[0] . '@yopmail.com';
            }
            $user->save();
        }
    }
}
