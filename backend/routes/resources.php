<?php
$router->get('/', 'ResourceController@index');
$router->post('/', 'ResourceController@store');
$router->get('/{resource}/courses', 'ResourceController@resourceCourses');
$router->put('/update-status/{id}', 'ResourceController@updateStatus');
$router->delete('/delete_resources/{id}', 'ResourceController@deleteResources');


//start hr form route
$router->get('/company_type_employee','HrformController@companyTypeEmployee');
$router->get('/all_company_hr_forms','HrformController@allCompanyHrForms');
$router->post('/delete_hr_form','HrformController@deleteHrForm');
$router->post('/save_hrform_data','HrformController@saveHrFormData');
$router->post('/update_hrform_data','HrformController@updateHrFormData');
$router->post('/assign_hrform','HrformController@assignHrForm');

$router->get('/all_employee_hr_forms','HrformController@allEmployeeHrForms');
$router->post('/upload_filled_hrform','HrformController@uploadFilledHrForm');

$router->post('/hr_form_report','HrformController@hrFormReport');
$router->get('/download/{file}', 'HrformController@downloadFile');
$router->get('/download_uploads/{file}', 'HrformController@downloadUploadFile');

$router->post('/download-hr-forms', 'HrformController@downlodDocs');
$router->get('/documents-report', 'HrformController@documentsReport');
$router->get('/download-documents-report', 'HrformController@downloadDocumentsReport');
