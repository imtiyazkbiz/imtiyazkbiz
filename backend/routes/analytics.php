<?php

$router->group(['middleware' => 'auth'],function () use ($router) {
    $router->post('/employee_due', 'AnalyticsController@getEmployeeDue');
    $router->post('/non_complaint', 'AnalyticsController@getnoncomplaint');
    $router->post('/recent_completions', 'AnalyticsController@recentCompletions');
    $router->post('/get_course_completions_byMonth', 'AnalyticsController@getCourseCompletionsByMonth');
    $router->post('/pofe_courses', 'AnalyticsController@pofeCourses');
    $router->get('/compliance_count', 'AnalyticsController@complianceCount');
    
});

