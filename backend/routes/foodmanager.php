<?php

$router->group(['middleware' => 'auth'],function () use ($router) {

    $router->post('/food_manager_employees','FoodManagerController@foodManagerEmployees');
    $router->post('/reassign_food_manager','FoodManagerController@reassignFoodManager'); 
    $router->post('/reminder_food_manager','FoodManagerController@ReminderFoodManager'); 
});