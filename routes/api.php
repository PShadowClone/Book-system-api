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
Route::post('register', 'Authentication\RegisterController@create');
Route::post('login', ['as' => 'login', 'uses' => 'Authentication\LoginController@login']);

//Route::get('/books/{book_id?}', 'Book\Controller@show')->middleware('auth:library');

Route::group(['middleware' => 'auth:api'], function () {

    Route::get('/ads/{ads_id?}', 'Advertisements\Controller@show');
    Route::get('/libraries/{library?}', 'Library\Controller@show')->where(['library' => '[0-9]+']);

    Route::get('/libraries/sales', 'Library\Controller@sales');
    Route::get('/libraries/sales/details', 'Library\Controller@salesDetails');

    Route::get('/categories/{category_id?}', 'Category\Controller@show');
    Route::get('/books/{book_id?}', 'Book\Controller@show');
    Route::post('/books', 'Book\Controller@store');
    Route::post('/request', 'Request\Controller@store');
    Route::get('/cities/{id?}', 'City\Controller@show');
    Route::get('/quarters/{id?}', 'Quarter\Controller@show');
    Route::get('/cart', 'Cart\Controller@show');
    Route::get('/user', 'User\Controller@edit');
    Route::put('/user', 'User\Controller@update');
    Route::post('/user/evaluation', 'User\Controller@evaluate');
    Route::get('/requests/{id?}', 'Request\Controller@show');
    Route::post('/book/evaluation', 'Book\Controller@evaluate');
    Route::get('/book/evaluation/{id?}', 'Book\Controller@showEvaluations');
    Route::get('/request/confirming', 'Request\Controller@confirming');

});

