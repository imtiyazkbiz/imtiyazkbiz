<?php

namespace App\Console;

use App\Http\Controllers\TestingDebuggingController;
use Illuminate\Console\Scheduling\Schedule;
use Laravel\Lumen\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel {
    /**
     * The Artisan commands provided by your application.
     * @var array
     */
    protected $commands = [//
    ];
    protected $middleware = [
        \App\Http\Middleware\Cors::class,
    ];

    /**
     * Define the application's command schedule.
     *
     * @param \Illuminate\Console\Scheduling\Schedule $schedule
     *
     * @return void
     */
    protected function schedule(Schedule $schedule) {
        $schedule->call('App\Http\Controllers\CourseController@cronForNextCourseAssignment')->timezone('America/New_York')->daily();
        //  $schedule->call('App\Http\Controllers\PaymentController@recurringPaymentCron')->timezone('America/New_York')->daily();
        $schedule->call('App\Http\Controllers\ProgressReport@mondayProgressReport')->timezone('America/New_York')->mondays()->at('09:00');
        $schedule->call('App\Http\Controllers\CourseController@weeklyProgressreport')->timezone('America/New_York')->mondays()->at('09:10');
        $schedule->call('App\Http\Controllers\CourseController@dailyReport')->timezone('America/New_York')->dailyAt('09:15');
        $schedule->call('App\Http\Controllers\CourseController@dailyProgressreport')->timezone('America/New_York')->daily();
        $schedule->call('App\Http\Controllers\CourseController@cronToSetExpireCourses')->timezone('America/New_York')->daily();
        $schedule->call('App\Http\Controllers\CourseController@autoReminderCron')->timezone('America/New_York')->dailyAt('09:20');
        $schedule->call('App\Http\Controllers\TwillioService@autoReminderSmsCron')->timezone('America/New_York')->dailyAt('13:00');
        $schedule->call('App\Http\Controllers\CronJobsController@setDueCourseToExpireCourse')->timezone('America/New_York')->daily();
    }
}
