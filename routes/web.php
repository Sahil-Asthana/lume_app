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
use Illuminate\Support\Facades\DB;

$router->get('/', function () use ($router) {

    return $router->app->version();
   // dd(DB::getPDO());
});




$router->group(['middleware' => ['auth', 'verified']], function () use ($router) {

  $router->post('/logout', 'AuthController@logout');
  $router->patch('update-me', ['uses' => 'UserController@updateByUser']);
  $router->post('/email/request-verification', ['as' => 'email.request.verification', 'uses' => 'AuthController@emailRequestVerification']);
  $router->post('/refresh', 'AuthController@refresh');
  $router->get('/me',['uses' => 'AuthController@me']);
  
});

$router->group(['prefix' => 'admin','middleware' => ['auth.role']], function () use ($router){
  $router->get('users',  ['uses' => 'UserController@showAllUsers']);
  $router->get('users/{id}', ['uses' => 'UserController@showOneUser']);
  $router->post('create',['uses' => 'UserController@create']);
  $router->delete('delete-user/{id}', ['uses' => 'UserController@delete']);
  $router->patch('update-user/{id}', ['uses' => 'UserController@update']);

});
$router->group([], function () use ($router) {
  $router->post('signup',['uses' => 'SignUpController@create']);
  $router->post('/login', 'AuthController@login');
  $router->post('/password/reset-request', 'RequestPasswordController@sendResetLinkEmail');
  $router->post('/password/reset', [ 'as' => 'password.reset', 'uses' => 'ResetPasswordController@reset' ]);
  $router->post('/email/verify', ['as' => 'email.verify', 'uses' => 'AuthController@emailVerify']);

});



