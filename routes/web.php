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
            Route::resource('school', SchoolsController::class)
                ->middleware('auth');
        }
    );

// Route::get('/login', function() {
//     cas()->authenticate();
// });

Route::get('/login', [PagesController::class, 'login'])->name('login');
Route::post('/login', [PagesController::class, 'checkLogin'])->name('checkLogin');
Route::post('/logout', [PagesController::class, 'logout'])->name('logout');

Route::resource('reports', ReportsController::class)
    ->middleware('sch.test')
    ->missing(function (Request $request) {
        return Redirect::route('reports.index');
    });


Route::get('/home', [DashboardController::class, 'index'])->name('home');
