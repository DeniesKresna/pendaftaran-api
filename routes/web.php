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

$router->get('/', function () use ($router) {
    return $router->app->version();
});

$router->group(['prefix' => 'api'], function () use ($router) {
    $router->post('register', 'Admin\AuthController@register');
     $router->post('login', 'Admin\AuthController@login');

    //===================academies================================
	$router->get('academies/list', 'Admin\AcademyController@list');
	$router->post('academies/customer', 'Admin\AcademyController@customerStore');

	//===================customer================================
	$router->get('customers/email/{email}', 'Admin\CustomerController@showByEmail');
});

$router->group(['prefix'=>'api', 'middleware'=>'auth'], function () use ($router){


	//===================users================================
	$router->get('profile', 'Admin\UserController@profile');
    $router->get('users/{id}', 'Admin\UserController@show');
    $router->get('users', 'Admin\UserController@index');
});