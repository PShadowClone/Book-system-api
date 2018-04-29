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


Route::group(['middleware' => 'auth:api'], function () {

    Route::get('/ads/{ads_id?}', 'Advertisements\Controller@show');
    Route::get('/libraries/{library?}', 'Library\Controller@show');
    Route::get('/categories/{category_id?}', 'Category\Controller@show');
    Route::get('/books/{book_id?}', 'Book\Controller@show');
    Route::post('/request', 'Request\Controller@store');
    Route::get('/cities/{id?}', 'City\Controller@show');
    Route::get('/quarters/{id?}', 'Quarter\Controller@show');

});

