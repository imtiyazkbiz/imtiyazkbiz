
<?php
  $router->get('/rights', 'SubAdminController@getRights');
  $router->post('/save_subadmin', 'SubAdminController@saveSubAdmin');
  $router->put('/edit/{id}', 'SubAdminController@edit');
  $router->post('/update_subadmin', 'SubAdminController@updateSubAdmin');
  $router->get('/subadmin_rights/{type}', 'SubAdminController@subadminRights');
  
?>