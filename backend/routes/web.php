<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
*/
$router->get('/', function() {
    return view('email_sample.password_reset');

});

$router->get('/template', function() {

    $data = [
        'employee_first_name' => 'emp9',
        'employee_last_name' => 'last',
        'name_of_course' => 'Train 321 Certificate',
        'date_of_birth' => '2020-12-08',
        'student_home_address' => 'A186, Sector 3, Noida, UP India 201301',
        'city_state_zip' => 'Noida/UP/201301',
        'current_employer' => 'Child 5 Technologies Pvt Ltd',
        'completion_date' => '12-10-2020',
        'expiration_date' => '12-10-2020',
        'certificate_no' => '2020-100426',
        'signature_title_1' => 'Eric Mickelson',
        'signature_title_2' => 'Jennifer Mickelson, President
    Name of School:  HOT NYS, Inc.
    School Certificate of Approval No: AT0005',

        'text' => 'SCHOOL CERTIFICATION: I CERTIFY THAT I AM THE DIRECTOR OF THE SCHOOL DESCRIBED ABOVE AND THAT THE ABOVE STUDENT SUCCESSFULLY COMPLETED THE ENTIRE PROGRAM.
    
    STUDENT CERTIFICATION: BY MARKING THE CERTIFICATION BOX ONLINE I CERTIFY THAT I COMPLETED ALL LESSONS, QUIZZES AND FINAL EXAM REQUIRED TO DEMONSTRATE MASTERY OF ALL MATERIAL.  MY CERTIFICATION TO THAT FACT IF NOT TRUE MAY CONSTITUTE FILING A FALSE INSTRUMENT, MAY SUBJECT MY EMPLOYER TO DISCIPLINARY ACTION BY THE STATE LIQUOR AUTHORITY, AND WILL SUBJECT THIS CERTIFICATE TO BE REVOKED. SCHOOL CERTIFICATION: I CERTIFY THAT I AM THE DIRECTOR OF THE SCHOOL DESCRIBED ABOVE AND THAT THE ABOVE STUDENT SUCCESSFULLY COMPLETED THE ENTIRE PROGRAM.',
    ];

    return view('templates/ny_atap', $data);

});


$router->post('/register', 'UserController@register');
$router->post('/login', 'UserController@login');
$router->get('/downloadCourseCertificate/{type}/{course_id}/{user_id}/{employee_certifiicate_id}', 'UserController@downloadCourseCertificate');
$router->get('/downloadCourseCertificate/{type}/{course_id}/{user_id}/{employee_certifiicate_id}/{is_coursefolder}', 'UserController@downloadCourseCertificate');
$router->get('/downloadAllCourseCertificate/single/{course_id}/{user_id}', 'UserController@downloadAllCourseCertificate');
$router->get('/downloadAllCourseCertificate/multiple/{course_id}/{user_id}', 'UserController@downloadAllMultipleUserCourseCertificate');
$router->get('previewCertificate/{certificate_id}', 'UserController@previewCertificate');
$router->get('downloadFoodManagerCertificate/{user_id}', 'FoodManagerController@downloadBulkFoodManagerCertificate');
$router->get('/downloadCourseFoldersCertificate/{user_id}', 'UserController@downloadCourseFolderCertificates');


$router->group([
    'prefix' => 'api',
    'middleware' => 'auth',
], function() use (&$router) {

    $router->get('/profile', 'UserController@profile');
});

$router->group(['prefix' => 'super_admin'], function() use ($router) {
    //        require "courses.php";
    $router->post('/update_password', 'CompanyController@updatePasswordSuperAdmin');
});
$router->group(['prefix' => 'course'], function() use ($router) {
    require "courses.php";
});
$router->group(['prefix' => 'user'], function() use ($router) {
    require "user.php";
});
$router->group(['prefix' => 'company'], function() use ($router) {
    require "company.php";
});
$router->group(['prefix' => 'employees'], function() use ($router) {
    require "employee.php";
});
$router->group(['prefix' => 'analytics'], function() use ($router) {
    require "analytics.php";
});
$router->group(['prefix' => 'location'], function() use ($router) {
    require "location.php";
});
$router->group(['prefix' => 'twilio'], function() use ($router) {
    require "twilio.php";
});
$router->group(['prefix' => 'progress'], function() use ($router) {
    require "progress.php";
});
$router->group(['prefix' => 'resources'], function() use ($router) {
    require "resources.php";
});
$router->group(['prefix' => 'subadmin'], function() use ($router) {
    require "subadmin.php";
});
$router->group(['prefix' => 'prismhr'], function() use ($router) {
    require "prismhr.php";
});
$router->group(['prefix' => 'foodmanager'], function() use ($router) {
    require "foodmanager.php";
});
$router->group(['prefix' => 'promocode'], function() use ($router) {
    require "promocode.php";
});

$router->group(['prefix' => 'cronJobs'], function() use ($router) {
    require "cronJobs.php";
});

$router->post('/front-end/contact-us', 'ContactUsController@sendMail');

// post login survey

$router->get('/getPostLoginSurvey', 'SurveyController@getPostLoginSurvey');
$router->get('/getPostLoginSurveyEmployees/{id}', 'SurveyController@getPostLoginSurveyEmployees');
$router->get('/editPostLoginSurvey/{id}', 'SurveyController@editPostLoginSurvey');
$router->post('/deletePostLoginSurvey', 'SurveyController@deletePostLoginSurvey');
$router->post('/updateSurvey', 'SurveyController@updatePostLoginSurvey');
$router->post('/assignPostLoginSurvey', 'SurveyController@assignPostLoginSurvey');
$router->post('/getAssignedPostSurveyEmployees/{id}', 'SurveyController@getAssignedPostSurveyEmployees');
$router->get('/getEmployeePostLoginSurvey', 'SurveyController@getEmployeePostLoginSurvey');
$router->post('/postLoginSurveySubmissions', 'SurveyController@postLoginSurveySubmissions');
$router->get('/getPostLoginSurveySubmissions/{survey_id}/{employee_id}', 'SurveyController@getPostLoginSurveySubmissions');

// tour

$router->get('/tour', 'TourController@index');
$router->get('/editTour/{id:[0-9]+}', 'TourController@editTour');
$router->post('/tourUpdate/{id}', 'TourController@tourUpdate');
$router->get('/destroy/{id}', 'TourController@destroy');
$router->post('/tourStore', 'TourController@tourStore');

$router->get('/tour/demovideos', 'TourController@demoVideo');
$router->get('/get_tour_data/{id}', 'TourController@getTourData');

$router->post('/marketing/subscribe_newsLetter', 'SubscribeController@newsLetterSubscribe');
$router->post('/marketing/sendQuerySupport', 'EmailSupportController@sendQuerySupport');

$router->get('/unsubscribe', 'UnsubscribeController@store');

$router->get('/test', 'TestingDebuggingController@test');