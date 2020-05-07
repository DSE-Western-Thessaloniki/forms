<?php

use Illuminate\Support\Facades\Route;

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

Route::get('/', 'PagesController@index');
Route::get('/about', 'PagesController@about');
Route::get('/setup', 'SetupController@setupPage');
Route::post('/setup', 'SetupController@saveSetup')->name('setup');

Route::resource('forms', 'FormsController');

Auth::routes([
    'reset' => false,
    'verify' => false
]);

Route::get('/dashboard', 'DashboardController@index');

Route::get('/home', 'DashboardController@index')->name('home');
