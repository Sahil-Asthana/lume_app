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

    //return $router->app->version();
    dd(DB::getPDO());
});

$router->group(['prefix' => 'api','middleware' => 'auth'], function () use ($router) {
  $router->get('users',  ['uses' => 'UserController@showAllUsers']);

  $router->get('users/{id}', ['uses' => 'UserController@showOneUser']);

  $router->post('users', ['uses' => 'UserController@create']);

  $router->delete('users/{id}', ['uses' => 'UserController@delete']);

  $router->patch('users/{id}', ['uses' => 'UserController@update']);
});