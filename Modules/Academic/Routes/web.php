<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::prefix('academic')->group(function() {
    Route::get('/', 'AcademicController@index');
});

Route::group(['middleware' => 'api_auth'], function () {
    Route::prefix('academic')->group(function() {
        
        Route::get('/register-course-print/{student}', 'AcademicController@getRegisterCoursePrintPreview');
    });
});