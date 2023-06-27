<?php
$router->post('/apply_promocode', 'PromoCodeController@applyPromocode');
$router->group(['middleware' => 'auth'], function () use ($router) {

    $router->post('/get_promo_codes', 'PromoCodeController@getPromoCodes');
    $router->post('/get_promo_code_reports', 'PromoCodeController@getPromoCodeReports');
    
    $router->put('/update_status/{id}', 'PromoCodeController@updateStatus');
    $router->delete('/delete_promo_code/{id}', 'PromoCodeController@deletePromoCode');
    $router->post('/save_promo_code', 'PromoCodeController@savePromoCode');
    $router->get('/edit_promo_code/{id}', 'PromoCodeController@editPromoCode');
    $router->put('/update_promo_code/{id}', 'PromoCodeController@updatePromoCode');
});
