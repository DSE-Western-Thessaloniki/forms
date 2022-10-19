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
Route::get('/admin', [DashboardController::class, 'index'])
    ->middleware('auth')
    ->name('admin.index');

Route::prefix('admin')
    ->name('admin.')
    ->group(
        function () {
            Route::get('/form/{form}/missing/csv', [FormsController::class, 'missingCSV'])
                ->name('form.missing.csv')
                ->middleware('auth');
            Route::get('/form/{form}/missing/xlsx', [FormsController::class, 'missingXLSX'])
                ->name('form.missing.xlsx')
                ->middleware('auth');
            Route::get('/form/{form}/missing', [FormsController::class, 'missing'])
                ->name('form.missing')
                ->middleware('auth');
            Route::get('/form/{form}/data/csv', [FormsController::class, 'formDataCSV'])
                ->name('form.data.csv')
                ->middleware('auth');
            Route::get('/form/{form}/data/xlsx', [FormsController::class, 'formDataXLSX'])
                ->name('form.data.xlsx')
                ->middleware('auth');
            Route::get('/form/{form}/data', [FormsController::class, 'formData'])
                ->name('form.data')
                ->middleware('auth');
            Route::get('/form/{form}/copy', [FormsController::class, 'copyForm'])
                ->name('form.copy')
                ->middleware('auth','can:create,form');
            Route::get('/form/{form}/confirmDelete', [FormsController::class, 'confirmDelete'])
                ->name('form.confirmDelete')
                ->middleware('auth');
            Route::get('/form/{form}/active/set/{state}', [FormsController::class, 'setActive'])
                ->name('form.active.set')
                ->middleware('auth','can:update,form');
            Route::get('/form/{form}/active/toggle', [FormsController::class, 'toggleActive'])
                ->name('form.active.toggle')
                ->middleware('auth','can:update,form');
            Route::resource('form', FormsController::class)
                ->middleware('auth');
            Auth::routes([
                'reset' => false,
                'verify' => false,
                'register' => false
            ]);
            Route::prefix('user')
                ->name('user.')
                ->middleware('auth','can:delete,user')
                ->group(
                function () {
                    Route::get('/{user}/confirmDelete', [UserController::class, 'confirmDelete'])->name('confirmDelete');
                }
            );
            Route::prefix('user')
                ->name('user.')
                ->middleware('auth','can:update,user')
                ->group(
                function () {
                    Route::get('/{user}/password', [UserController::class, 'password'])->name('password');
                    Route::post('/{user}/password', [UserController::class, 'changePassword'])->name('change_password');
                }
            );
            Route::resource('user', UserController::class)
                ->middleware('auth');
            Route::prefix('school')
                ->name('school.')
                ->middleware('auth', 'can:delete,school')
                ->group(
                    function() {
                        Route::get('/{school}/confirmDelete', [SchoolsController::class, 'confirmDelete'])->name('confirmDelete');
                    }
                );
            Route::prefix('school')
                ->middleware('auth', 'can:create,App\Models\School')
                ->name('school.')
                ->group(
                function() {
                    Route::get('/import', [SchoolsController::class, 'showImport'])->name('show_import');
                    Route::post('/import', [SchoolsController::class, 'import'])->name('import');
                }
            );
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

Route::get('/logout', function() {
    cas()->logout();
})->middleware('cas.auth')->name('logout');

Route::middleware('cas.auth')
    ->group(function () {
        Route::put('/report/{report}/edit/{record}/update/{next}', [ReportsController::class, 'updateRecord'])->name('report.edit.record.update');
        Route::get('/report/{report}/edit/{record}', [ReportsController::class, 'editRecord'])->name('report.edit.record');
        Route::get('/report/{report}/record/{record}', [ReportsController::class, 'showRecord'])->name('report.show.record');
        Route::resource('report', ReportsController::class)
            ->missing(function (Request $request) {
                return Redirect::route('report.index');
            });
    });


Route::get('/home', [DashboardController::class, 'index'])
    ->middleware('auth')
    ->name('home');
