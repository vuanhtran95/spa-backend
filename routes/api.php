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

Route::get('/public', function (Request $request) {
    return "Public";
});

Route::get('/private', function (Request $request) {
    return "Private";
})->middleware('auth:api');

// Create User
Route::post('/v1/users', 'UserController@create')->middleware('auth:api');
// Get All User
Route::get('/v1/users', 'UserController@get')->middleware('auth:api');
// Get One User By Id
Route::get('/v1/users/{id}', 'UserController@getOneById')->middleware('auth:api');
// Update One User By Id
Route::put('/v1/users/{id}', 'UserController@update')->middleware('auth:api');
