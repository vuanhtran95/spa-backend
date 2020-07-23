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


// Get All Roles
Route::get('/v1/roles', 'RoleController@get')->middleware('auth:api');

// User
Route::get('/v1/userinfo', 'UserController@getUserInfo')->middleware('auth:api');
Route::post('/v1/users', 'UserController@create')->middleware('auth:api');
Route::get('/v1/users', 'UserController@get')->middleware('auth:api');
Route::get('/v1/users/{id}', 'UserController@getOneById')->middleware('auth:api');
Route::put('/v1/users/{id}', 'UserController@update')->middleware('auth:api');
Route::delete('/v1/users/{id}', 'UserController@delete')->middleware('auth:api');

// Intake form
Route::post('/v1/intakes', 'IntakeController@create')->middleware('auth:api');
Route::put('/v1/intakes/{id}', 'IntakeController@update')->middleware('auth:api');
Route::get('/v1/intakes', 'IntakeController@get')->middleware('auth:api');
Route::get('/v1/intakes/{id}', 'IntakeController@getOneById')->middleware('auth:api');

// Service
Route::get('/v1/services', 'ServiceController@get')->middleware('auth:api');
Route::post('/v1/services', 'ServiceController@create')->middleware('auth:api');

// Service Category
Route::get('/v1/service-categories', 'ServiceCategoryController@get')->middleware('auth:api');
Route::post('/v1/service-categories', 'ServiceCategoryController@create')->middleware('auth:api');


// Customer
Route::post('/v1/customers', 'CustomerController@create')->middleware('auth:api');
Route::get('/v1/customers', 'CustomerController@get')->middleware('auth:api');
Route::get('/v1/customers/{id}', 'CustomerController@getOneById')->middleware('auth:api');


// Combo
Route::post('/v1/combos', 'ComboController@create')->middleware('auth:api');
Route::get('/v1/combos', 'ComboController@get')->middleware('auth:api');
Route::put('/v1/combos/{id}', 'ComboController@update')->middleware('auth:api');
