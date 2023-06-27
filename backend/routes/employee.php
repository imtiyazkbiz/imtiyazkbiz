<?php

$router->post('/register', 'EmployeeController@register');
$router->post('/welcome_individual_email','EmployeeController@welcomeIndividualEmail');
$router->group(['middleware' => 'auth'],function () use ($router) {
    $router->get('/get/{id}', 'EmployeeController@get');
    $router->post('/list', 'EmployeeController@list');
    $router->post('/bulk_user', 'EmployeeController@bulkUser');
    $router->put('/update_status/{id}','EmployeeController@updateStatus');
    $router->post('/delete_employee','EmployeeController@deleteEmployee');
    $router->post('/manager/all','EmployeeController@managerList');
    $router->post('/all_list','EmployeeController@allList');
    $router->get('/become_manager/{id}','EmployeeController@becomeManager');
    $router->post('/course_reminder_email','EmployeeController@courseReminderEmail');
    $router->post('/course_expire_reminder_email','EmployeeController@courseExpireReminderEmail');
    $router->post('/course_expiring_reminder_email','EmployeeController@courseExpiringReminderEmail');
    $router->post('/send_tutorial_link','EmployeeController@sendTutorialLink');
    $router->post('/welcome_email','EmployeeController@welcomeEmail');
    $router->put('/update/{id}','EmployeeController@update');
    $router->post('/user_data', 'EmployeeController@userData');
    $router->post('/add_manager', 'EmployeeController@addManager');
    $router->get('/reset_access_code/{id}','EmployeeController@resetCode');
    $router->post('/company_employee','EmployeeController@companyEmployee');
    $router->post('/job_title','EmployeeController@jobtitle');
    $router->get('/stats/{id}','EmployeeController@stats');
    $router->post('/courses','EmployeeController@courses');
    $router->post('/resources','EmployeeController@resources');
    $router->post('/update_course','EmployeeController@updateCourse');
    $router->post('/unassign_course','EmployeeController@unassignCourse');
    $router->post('/reassign_location','EmployeeController@reassignLocation');
     $router->post('/reassign_multiple_location','EmployeeController@reassignMultipleLocation');
    $router->get('/tutorial_video','EmployeeController@tutorialVideos');
   
    $router->post('/tutorial_video','EmployeeController@filteredTutorialVideos');
    $router->put('/update_video_status/{id}','EmployeeController@updateVideoStatus');
    $router->get('/tutorialVideos/{id}','EmployeeController@tutorialVideo');
    $router->put('/tutorialVideo/{id}','EmployeeController@updateTutorialVideo');
    $router->post('/addtutorialVideo','EmployeeController@addTutorialVideo');

    $router->post('/getDemoTourVideo','EmployeeController@getDemoTourVideo');
    $router->post('/addTourVideo','EmployeeController@addTourVideo');
    $router->get('/getTourVideo/{id}','EmployeeController@getTourVideo');
    $router->put('/updateTourVideo/{id}','EmployeeController@updateTourVideo');
    $router->post('/destroyTourVideo','EmployeeController@destroyTourVideo');
    $router->post('/updateTourVideoOrder','EmployeeController@updateTourVideoOrder');
    $router->post('/updateTutorialVideoOrder','EmployeeController@updateTutorialVideoOrder');
    $router->post('/deleteTutorialVideo','EmployeeController@deleteTutorialVideo');
    $router->post('/tutorialVideo','EmployeeController@tutorialVideoWithRole');
 
    $router->post('/getDemoTourVideo','EmployeeController@getDemoTourVideo');
    $router->post('/addTourVideo','EmployeeController@addTourVideo');
    $router->get('/getTourVideo/{id}','EmployeeController@getTourVideo');
    $router->put('/updateTourVideo/{id}','EmployeeController@updateTourVideo');
    $router->post('/destroyTourVideo','EmployeeController@destroyTourVideo');

    $router->post('/certificates','EmployeeController@certificates');
    $router->post('/additionalCertificates','EmployeeController@additionalCertificates');
    $router->get('/certificates/{id}','EmployeeController@getCertificates');
    $router->post('/upload_certificate','EmployeeController@uploadCertificate');
    $router->post('/documents','EmployeeController@employeeDocuments');
    $router->post('/delete_document','EmployeeController@deleteDocument');
    $router->post('/upload_document','EmployeeController@uploadDocument');
    $router->post('/course_due','EmployeeController@courseDue');
    $router->post('/non_compliance','EmployeeController@nonComplianceEmployee');
    $router->post('/report','EmployeeController@report');
    $router->post('/non_compliant_reminder_email_list','EmployeeController@nonComplianceEmailList');
    $router->post('/password_reset','EmployeeController@resetPassword');
    $router->post('/employee_list','EmployeeController@employeeList');
    $router->get('/download/{file}', 'EmployeeController@downloadFile');
    $router->post('/reset_attempts', 'EmployeeController@resetAttempts');
    $router->post('/userPassRating', 'EmployeeController@userPassRating');
    $router->get('/jobTitles', 'EmployeeController@jobTitles');
    //$router->get('/sampleofUsers', 'EmployeeController@sampleofUsers');
    $router->post('/employee_documents', 'EmployeeController@allemployeeDocuments');
    $router->get('/not_purchased_courses', 'EmployeeController@notPurchasedCourses');
    $router->post('/purchase_course', 'EmployeeController@purchaseCourse');
    $router->get('/employeeCourseFolderProgress', 'EmployeeController@employeeCourseFolderProgress');
    $router->post('/updateCourseManually', 'EmployeeController@updateCourseManually'); 
    
});
