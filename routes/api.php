<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/
/*
Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});
*/


//Authenticate
Route::post('register', 'Users\AuthenticateController@register');
Route::post('login', 'Users\AuthenticateController@login');
Route::get('logout', 'Users\AuthenticateController@logout');
Route::post('recover', 'Users\AuthenticateController@recover');

//usuarios
Route::resource('users', 'Users\UsersController', ['only' => ['show', 'update']]);
Route::resource('tasks', 'Tasks\TasksController', ['except' => ['create', 'edit']]);
Route::get('tasks/detail/{task}', 'Tasks\TasksController@detail');
Route::get('tasks/{order}/{campo}', 'Tasks\TasksController@index');

