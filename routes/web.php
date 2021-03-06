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
    $router->post('register-'.env("REGISTER_SECRET_URL"), 'Admin\AuthController@register');
    $router->post('auth/login', 'Admin\AuthController@login');
	$router->get('auth/me', 'Admin\AuthController@me');

    //===================academies================================
	$router->post('academies/payment-success-'.env("MIDTRANS_PAYMENT_SECRET_URL"), 'Admin\AcademyController@successPayment');
	$router->get('academies/page_data','Admin\AcademyController@page_data');
	$router->get('experts/page_data','Admin\ExpertController@page_data');

	//===================customer================================
	$router->get('customers/email/{email}', 'Admin\CustomerController@showByEmail');
});

$router->group(['prefix'=>'api', 'middleware'=>'auth'], function () use ($router){
	//===================users================================
	$router->get('profile', 'Admin\UserController@profile');
    $router->get('users/{id}', 'Admin\UserController@show');
    $router->get('users', 'Admin\UserController@index');

    //===================academies================================
	$router->get('academies/list', 'Admin\AcademyController@list');
	$router->post('academies/customer', 'Admin\AcademyController@customerStore');
	$router->get('academies/customer', 'Admin\AcademyController@customerShow');
	$router->post('academies/payment', 'Admin\AcademyController@paymentStore');
	$router->delete('academies/customer/{id}', 'Admin\AcademyController@customerDestroy');
	$router->get('academies', 'Admin\AcademyController@index');
	$router->post('academies', 'Admin\AcademyController@store');
	$router->post('academies/{id}', 'Admin\AcademyController@update');
	$router->delete('academies/{id}', 'Admin\AcademyController@destroy');

	//===================academy period================================
	$router->get('academy-periods', 'Admin\AcademyPeriodController@index');
	$router->post('academy-periods', 'Admin\AcademyPeriodController@store');
	$router->put('academy-periods/{id}', 'Admin\AcademyPeriodController@update');
	$router->delete('academy-periods/{id}', 'Admin\AcademyPeriodController@destroy');

	//===================mentor================================
	$router->get('mentors/list', 'Admin\MentorController@list');
	$router->get('mentors', 'Admin\MentorController@index');
	$router->post('mentors', 'Admin\MentorController@store');
	$router->post('mentors/{id}', 'Admin\MentorController@update');
	$router->delete('mentors/{id}', 'Admin\MentorController@destroy');

	//===================expert================================
	//$router->get('experts/list', 'Admin\ExpertController@list');
	$router->get('experts', 'Admin\ExpertController@index');
	$router->post('experts', 'Admin\ExpertController@store');
	$router->put('experts/{id}', 'Admin\ExpertController@update');
	$router->delete('experts/{id}', 'Admin\ExpertController@destroy');

	//=================== Payment ================================
	$router->get('payments/{id}', 'Admin\PaymentController@show');
});
