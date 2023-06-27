<?php

$router->group(['middleware' => 'auth'],function () use ($router) {
    $router->post('/register', 'LocationController@register');
    $router->post('/all', 'LocationController@allLocations');
     $router->post('/all_company_location', 'LocationController@allCompanyLocation');
    $router->get('/edit/{id}', 'LocationController@edit');
    $router->post('/list','LocationController@listLocations');
    $router->post('/parent_list','LocationController@parentList');
    $router->post('/bulk_location','LocationController@bulkLocation');
    $router->get('/employee_list/{id}','LocationController@employeeList');
    $router->put('/update_status/{id}','LocationController@updateStatus');
    $router->put('/update/{id}','LocationController@update');
    $router->get('/courses/{id?}','LocationController@locationCourse');
    $router->post('/courses','LocationController@locationCourses');
    $router->post('/pass_employees','LocationController@passEmployees');
    $router->post('/unassign_course','LocationController@unassignCourse');
    $router->get('/getName/{id}','LocationController@getLocationName');
    
});
