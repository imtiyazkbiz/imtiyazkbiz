<?php
$router->get('/autoReminderSmsCron', 'TwillioService@autoReminderSmsCron');
$router->group(['middleware' => 'auth'],function () use ($router) {
    $router->post('/index', 'TwillioService@index');
    $router->post('/sendOTP', 'TwillioService@sendOTP');
    $router->post('/VerifysendOTP', 'TwillioService@VerifysendOTP');
});