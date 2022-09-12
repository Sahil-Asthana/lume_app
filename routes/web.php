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
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\DB;

$router->get('/', function () use ($router) {

    return $router->app->version();
   // dd(DB::getPDO());
});




$router->group(['middleware' => ['auth', 'verified']], function () use ($router) {

  $router->post('/logout', 'AuthController@logout');
  $router->put('/update-me', ['uses' => 'UserController@updateByUser']);
  $router->post('/email/request-verification', ['as' => 'email.request.verification', 'uses' => 'AuthController@emailRequestVerification']);
  $router->post('/refresh', 'AuthController@refresh');
  $router->get('/me',['uses' => 'AuthController@me']);
  $router->get('/tasks',  ['uses' => 'TaskController@showMyTasks']); //for showing current users task
  $router->get('/today_tasks',  ['uses' => 'TaskController@getTodaysTask']); // for todays tasks
  $router->post('/create-task',['uses' => 'TaskController@create']); // for creating task
  $router->put('/status/{id}',  ['uses' => 'TaskController@updateStatus']); //for updating status 
  $router->put('/edit-task/{id}',  ['uses' => 'TaskController@editTask']); // for editing task by creator
  $router->delete('/delete-task/{id}',  ['uses' => 'TaskController@deleteTask']); // for deleting task by creator
  $router->get('/listNotifs',  ['uses' => 'NotificationController@listNotification']); //get all notification for a user
  $router->delete('/notif/{id}',  ['uses' => 'NotificationController@deleteNotification']); // delete a notification
  $router->delete('/clear-notif',  ['uses' => 'NotificationController@clearNotification']); // clear notification
});

$router->group(['middleware' => ['auth.role']], function () use ($router){
  $router->get('users',  ['uses' => 'UserController@showAllUsers']);
  $router->get('users/{id}', ['uses' => 'UserController@showOneUser']);
  $router->delete('delete-user/{id}', ['uses' => 'UserController@delete']);
  $router->get('all-tasks',  ['uses' => 'TaskController@showAllTasks']); //for showing everyone tasks
  $router->put('update-user/{id}', ['uses' => 'UserController@update']);
  $router->post('/create',['uses' => 'UserController@create']); 
  $router->get('/tasks/{id}',['uses'=>'TaskController@getTaskForUser']); //get task for user;
});

$router->group([], function () use ($router) {
  $router->post('signup',['uses' => 'SignUpController@create']);
  $router->post('/login', 'AuthController@login');
  $router->post('/password/reset-request', 'RequestPasswordController@sendResetLinkEmail');
  $router->post('/password/reset', [ 'as' => 'password.reset', 'uses' => 'ResetPasswordController@reset' ]);
  $router->post('/email/verify', ['as' => 'email.verify', 'uses' => 'AuthController@emailVerify']);

});

// $router->post('/mailable/{id}', function($id) {
//   $user = App\User::findOrFail($id);
//   return Mail::to($user)->send(new App\Mail\DailyEmail($user));
// });



