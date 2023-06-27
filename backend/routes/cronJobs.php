<?php
$router->get('/set-due-course-to-expire-course','CronJobsController@setDueCourseToExpireCourse');
$router->get('/re-assign-course-on-certificate-expire','CronJobsController@reAssignCourseOnCertificateExpire');