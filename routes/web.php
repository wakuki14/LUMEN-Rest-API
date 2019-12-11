<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
*/

$router->group(['prefix' => 'api/'], function ($router) {
    $router->post('user/login/','UsersController@authenticate');
    $router->post('user/refresh_token/','UsersController@refreshToken');
    $router->post('user/login_social/','UsersController@loginSocial');
    $router->post('user/register/','UsersController@register');
    $router->post('user/update','UsersController@update');
    $router->put('user/update','UsersController@update');
    $router->post('user/avatar','UsersController@updateAvatar');
    $router->put('user/avatar','UsersController@updateAvatar');
    $router->post('user/forgot_password/','UsersController@forgotPassword');
    $router->post('user/reset_password/','UsersController@resetPassword');
    $router->get('user/{id}', 'UsersController@getUser');
    $router->post('country/create/','CountriesController@create');
    $router->post('country/update/','CountriesController@update');
    $router->put('country/update/','CountriesController@update');
    $router->get('country/list/','CountriesController@list');
    $router->post('city/create/','CitiesController@create');
    $router->post('city/update/','CitiesController@update');
    $router->put('city/update/','CitiesController@update');
    $router->get('city/list/','CitiesController@list');
    
});
