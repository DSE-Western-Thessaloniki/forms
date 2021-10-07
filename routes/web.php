<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\PagesController;
use App\Http\Controllers\SetupController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ReportsController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;

use App\Http\Controllers\Admin\FormsController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\SchoolsController;
use App\Http\Controllers\Admin\SchoolCategoriesController;

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

Route::get('/', [PagesController::class, 'index'])->name('index');
Route::get('/setup', [SetupController::class, 'setupPage']);
Route::post('/setup', [SetupController::class, 'saveSetup'])->name('setup');
Route::get('/admin', [DashboardController::class, 'index']);

Route::prefix('admin')
    ->name('admin.')
    ->group(
        function () {
            Route::get('/form/{form}/data/csv', [FormsController::class, 'formDataCSV'])
                ->name('form.data.csv')
                ->middleware('auth');
            Route::get('/form/{form}/data/xlsx', [FormsController::class, 'formDataXLSX'])
                ->name('form.data.xlsx')
                ->middleware('auth');
            Route::get('/form/{form}/data', [FormsController::class, 'formData'])
                ->name('form.data')
                ->middleware('auth');
            Route::resource('form', FormsController::class)
                ->middleware('auth');
            Auth::routes([
                'reset' => false,
                'verify' => false,
                'register' => false
            ]);
            Route::prefix('user')->name('user.')->group(
                function () {
                    Route::get('/{user}/password', [UserController::class, 'password'])->name('password');
                    Route::post('/{user}/password', [UserController::class, 'changePassword'])->name('change_password');
                }
            );
            Route::resource('user', UserController::class)
                ->middleware('auth');
            Route::prefix('school')
                ->name('school.')
                ->group(
                    function () {
                        Route::resource('schoolcategory', SchoolCategoriesController::class)
                            ->middleware('auth');
                    }
                );
            Route::resource('school', SchoolsController::class)
            ->middleware('auth');
        }
    );

Route::get('/login', function() {
    return Redirect::route('report.index');
})->name('login');

//Route::get('/login', [PagesController::class, 'login'])->name('login');
//Route::post('/login', [PagesController::class, 'checkLogin'])->name('checkLogin');
//Route::post('/logout', [PagesController::class, 'logout'])->name('logout');

Route::get('/logout', function() {
    cas()->logout();
})->middleware('cas.auth')->name('logout');

Route::middleware('cas.auth')
    ->group(function () {
        Route::put('/report/{report}/edit/{record}/update/{next}', [ReportsController::class, 'updateRecord'])->name('report.edit.record.update');
        Route::get('/report/{report}/edit/{record}', [ReportsController::class, 'editRecord'])->name('report.edit.record');
        Route::resource('report', ReportsController::class)
            ->missing(function (Request $request) {
                return Redirect::route('report.index');
            });
    });


Route::get('/home', [DashboardController::class, 'index'])->name('home');
