<?php

use App\Http\Controllers\Admin\AcceptedFiletypeController;
use App\Http\Controllers\Admin\FormCopyController;
use App\Http\Controllers\Admin\FormDataController;
use App\Http\Controllers\Admin\FormDataMissingController;
use App\Http\Controllers\Admin\FormFileDownloadController;
use App\Http\Controllers\Admin\FormFilesDownloadController;
use App\Http\Controllers\Admin\FormsController;
use App\Http\Controllers\Admin\FormToggleActiveController;
use App\Http\Controllers\Admin\OptionsController;
use App\Http\Controllers\Admin\OtherTeacherController;
use App\Http\Controllers\Admin\SchoolCategoriesController;
use App\Http\Controllers\Admin\SchoolController;
use App\Http\Controllers\Admin\SchoolImportController;
use App\Http\Controllers\Admin\SelectionListController;
use App\Http\Controllers\Admin\SelectionListCopyController;
use App\Http\Controllers\Admin\SelectionListImportController;
use App\Http\Controllers\Admin\TeacherController;
use App\Http\Controllers\Admin\TeacherImportController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\UserPasswordController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PagesController;
use App\Http\Controllers\ReportFileDownloadController;
use App\Http\Controllers\ReportRecordController;
use App\Http\Controllers\ReportsController;
use App\Http\Controllers\SetupController;
use App\Http\Middleware\EnsureCasAccountHasAccess;
use App\Models\Form;
use App\Models\School;
use App\Models\SelectionList;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
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

Route::get('/', [PagesController::class, 'index'])->name('index');
Route::get('/setup', [SetupController::class, 'index']);
Route::post('/setup', [SetupController::class, 'store'])->name('setup');
Route::get('/admin', [DashboardController::class, 'index'])
    ->middleware('auth')
    ->name('admin.index');

Route::prefix('admin')
    ->name('admin.')
    ->group(
        function (): void {
            Route::get('/form/{form}/missing/csv', FormDataMissingController::class)
                ->name('form.missing.csv')
                ->middleware('auth')
                ->defaults('format', 'csv');
            Route::get('/form/{form}/missing/xlsx', FormDataMissingController::class)
                ->name('form.missing.xlsx')
                ->middleware('auth')
                ->defaults('format', 'xlsx');
            Route::get('/form/{form}/missing', FormDataMissingController::class)
                ->name('form.missing')
                ->middleware('auth');
            Route::get('/form/{form}/data/csv', FormDataController::class)
                ->name('form.data.csv')
                ->middleware('auth')
                ->defaults('format', 'csv');
            Route::get('/form/{form}/data/xlsx', FormDataController::class)
                ->name('form.data.xlsx')
                ->middleware('auth')
                ->defaults('format', 'xlsx');
            Route::get('/form/{form}/data', FormDataController::class)
                ->name('form.data')
                ->middleware('auth');
            Route::get('/form/{form}/copy', FormCopyController::class)
                ->name('form.copy')
                ->middleware('auth', 'can:create,form');
            Route::get('/form/{form}/confirmDelete', function (Form $form): View {
                return view('admin.form.confirm_delete')->with('form', $form);
            })
                ->name('form.confirmDelete')
                ->middleware('auth');
            Route::get('/form/{form}/active/toggle', FormToggleActiveController::class)
                ->name('form.active.toggle')
                ->middleware('auth', 'can:update,form');
            Route::resource('form', FormsController::class)
                ->middleware('auth');
            Auth::routes([
                'reset' => false,
                'verify' => false,
                'register' => false,
            ]);
            Route::prefix('user')
                ->name('user.')
                ->middleware(['auth', 'can:delete,user'])
                ->group(
                    function (): void {
                        Route::get(
                            '/{user}/confirmDelete',
                            fn (User $user) => view('admin.user.confirm_delete')->with('user', $user)
                        )->name('confirmDelete');
                    }
                );
            Route::prefix('user')
                ->name('user.')
                ->middleware(['auth', 'can:update,user'])
                ->group(
                    function (): void {
                        Route::get('/{user}/password', [UserPasswordController::class, 'edit'])->name('password');
                        Route::put('/{user}/password', [UserPasswordController::class, 'update'])->name('change_password');
                    }
                );
            Route::resource('user', UserController::class)
                ->middleware('auth');
            Route::prefix('school')
                ->name('school.')
                ->middleware(['auth', 'can:delete,school'])
                ->group(
                    function (): void {
                        Route::get(
                            '/{school}/confirmDelete',
                            fn (School $school) => view('admin.school.confirm_delete')->with('school', $school)
                        )->name('confirmDelete');
                    }
                );
            Route::prefix('school')
                ->middleware(['auth', 'can:create,App\Models\School'])
                ->name('school.')
                ->group(
                    function (): void {
                        Route::get('/import', [SchoolImportController::class, 'edit'])->name('show_import');
                        Route::put('/import', [SchoolImportController::class, 'update'])->name('import');
                    }
                );
            Route::prefix('school')
                ->name('school.')
                ->group(
                    function (): void {
                        Route::resource('schoolcategory', SchoolCategoriesController::class)
                            ->middleware('auth');
                    }
                );
            Route::resource('school', SchoolController::class)
                ->middleware('auth');

            Route::prefix('teacher')
                ->name('teacher.')
                ->middleware(['auth', 'can:create,App\Models\Teacher'])
                ->group(function (): void {
                    Route::get('/import', [TeacherImportController::class, 'edit'])
                        ->name('show_import');
                    Route::put('/import', [TeacherImportController::class, 'update'])
                        ->name('import');
                    Route::get(
                        '/{teacher}/confirmDelete',
                        fn (Teacher $teacher) => view('admin.teacher.confirm_delete')->with('teacher', $teacher)
                    )->name('confirmDelete');
                });

            Route::resource('teacher', TeacherController::class)
                ->except('show')
                ->middleware('auth');

            Route::resource('other_teacher', OtherTeacherController::class)
                ->only(['index'])
                ->middleware('auth');

            Route::prefix('options')
                ->name('options.')
                ->middleware('auth')
                ->group(
                    function (): void {
                        Route::get('/', [OptionsController::class, 'index'])->name('index');
                        Route::post('/', [OptionsController::class, 'store'])->name('store');
                    }
                );
            Route::prefix('selection_list')
                ->name('list.')
                ->middleware(['auth', 'can:create,App\Models\SelectionList'])
                ->group(
                    function (): void {
                        Route::get('/import', [SelectionListImportController::class, 'edit'])->name('show_import');
                        Route::put('/import', [SelectionListImportController::class, 'update'])->name('import');
                        Route::get(
                            '/{selection_list}/confirmDelete',
                            fn (SelectionList $selectionList) => view('admin.list.confirm_delete')->with('list', $selectionList)
                        )->name('confirmDelete');
                        Route::post('/{selection_list}/copy', SelectionListCopyController::class)->name('copy');
                    }
                );
            Route::resource('selection_list', SelectionListController::class)
                ->except('show')
                ->names('list')
                ->middleware('auth');
            Route::get('/download/{form}/{category}/{categoryId}/{record}/{fieldId}', FormFileDownloadController::class)
                ->name('report.download')
                ->middleware('auth');
            Route::get('/download_all/{form}', FormFilesDownloadController::class)
                ->name('report.downloadAllFiles')
                ->middleware('auth');
            Route::resource('accepted_filetype', AcceptedFiletypeController::class)
                ->except('show')
                ->names('accepted_filetype')
                ->middleware('auth');
        }
    );

Route::get('/login', fn () => Redirect::route('report.index'))->name('login');

Route::get('/logout', function (): void {
    cas()->logout();
})->middleware('cas.auth')->name('logout');

Route::middleware(['cas.auth', EnsureCasAccountHasAccess::class])
    ->group(function (): void {
        Route::delete('/report/{report}/record/{record}', [ReportRecordController::class, 'destroy'])->name('report.record.destroy');
        Route::put('/report/{report}/edit/{record}/update/{next}', [ReportRecordController::class, 'update'])->name('report.edit.record.update');
        Route::get('/report/{report}/edit/{record}', [ReportRecordController::class, 'edit'])->name('report.edit.record');
        Route::get('/report/{report}/record/{record}', [ReportRecordController::class, 'show'])->name('report.show.record');
        Route::get('/download/{report}/{fieldId}/{record}', ReportFileDownloadController::class)->name('report.download');
        Route::resource('report', ReportsController::class)
            ->missing(fn (Request $request) => Redirect::route('report.index'));
    });

Route::get('/home', [DashboardController::class, 'index'])
    ->middleware('auth')
    ->name('home');
