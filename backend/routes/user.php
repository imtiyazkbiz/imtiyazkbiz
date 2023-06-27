<?php

$router->post('/register', 'UserController@register');
$router->post('/login', 'UserController@login');
$router->post('/login_report', 'UserController@loginReport');
$router->post('/add_activity', 'UserController@addActivity');
$router->post('/activity_report', 'UserController@activityReport');
$router->get('/logout_time', 'UserController@logout');
$router->get('/login_check', 'UserController@loginCheck');
$router->get('/update_first_login_status', 'UserController@updateFirstLoginStatus');
$router->get('/recurring_payment_cron', 'PaymentController@recurringPaymentCron');
$router->get('/discountRules', 'UserController@discountRules');
$router->post('/lead', 'UserController@lead');
$router->post('/minLocationLead', 'UserController@minLocationLead');
$router->post('/contact', 'UserController@contact');
$router->post('/resetLink', 'UserController@sendResetPasswordLink');
$router->post('/resetPassword', 'UserController@resetPassword');
$router->post('/saveCertificatePdf', 'UserController@saveCertificatePdf');
$router->group(['middleware' => 'auth'],function () use ($router) {
$router->post('/paymentWithStoreCard', 'PaymentController@paymentWithStoreCard');
$router->post('/paymentByStoreCardId', 'PaymentController@paymentByStoreCardId');
$router->post('/course_data', 'UserController@courses_data');
  //  $router->post('previewCertificate', 'UserController@previewCertificate');

    
});

$router->get('testroute', function () {
    return 'Hello World vvvvvv';
});