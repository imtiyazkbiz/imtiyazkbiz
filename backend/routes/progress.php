<?php
 $router->get('/mondayProgressReport', 'ProgressReport@mondayProgressReport');
 $router->get('/send_progress', 'ProgressReport@SendProgressReport');
 $router->group(['middleware' => 'auth'],function () use ($router) {
    $router->get('/generate_report', 'ProgressReport@generateProgressReport');  
});