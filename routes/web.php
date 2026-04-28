<?php

use App\Http\Controllers\AcademicYearController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ExtracurricularCategoryController;
use App\Http\Controllers\ExtracurricularController;
use App\Http\Controllers\ExtracurricularMembershipController;
use App\Http\Controllers\PresenceController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\ScoreController;
use App\Http\Controllers\StudentClassController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    if (Auth::check()) {
        $role = Auth::user()->role;

        return redirect()->route($role.'.dashboard');
    }

    return redirect()->route('login');
});

Route::middleware('guest')->group(function () {
    Route::get('login', [AuthController::class, 'index'])->name('login');
    Route::post('login', [AuthController::class, 'login'])->name('login.post');
});

Route::post('logout', [AuthController::class, 'logout'])->name('logout');

Route::middleware(['auth', 'check.role:kesiswaan'])->group(function () {
    Route::get('kesiswaan/dashboard', [DashboardController::class, 'index'])->name('kesiswaan.dashboard');

    Route::prefix('academic-years')
        ->name('academic-years.')
        ->group(function () {
            Route::get('/', [AcademicYearController::class, 'index'])->name('index');
            Route::get('/create', [AcademicYearController::class, 'create'])->name('create');
            Route::post('/', [AcademicYearController::class, 'store'])->name('store');
            Route::get('/{academicYear}/edit', [AcademicYearController::class, 'edit'])->name('edit');
            Route::put('/{academicYear}', [AcademicYearController::class, 'update'])->name('update');
            Route::delete('/{academicYear}', [AcademicYearController::class, 'destroy'])->name('destroy');
            Route::patch('/{academicYear}/toggle', [AcademicYearController::class, 'toggleActive'])->name('toggle');
            Route::get('/transition', [AcademicYearController::class, 'transitionForm'])->name('transition.form');
            Route::post('/transition', [AcademicYearController::class, 'processTransition'])->name('transition.process');
        });

    Route::prefix('extracurricular-category')
        ->name('extracurricular-category.')
        ->group(function () {
            Route::get('/', [ExtracurricularCategoryController::class, 'index'])->name('index');
            Route::get('/create', [ExtracurricularCategoryController::class, 'create'])->name('create');
            Route::get('/import', [ExtracurricularCategoryController::class, 'import'])->name('import');
            Route::post('/import', [ExtracurricularCategoryController::class, 'importStore'])->name('import.store');
            Route::post('/', [ExtracurricularCategoryController::class, 'store'])->name('store');
            Route::get('/{extracurricularCategory}/edit', [ExtracurricularCategoryController::class, 'edit'])->name('edit');
            Route::put('/{extracurricularCategory}', [ExtracurricularCategoryController::class, 'update'])->name('update');
            Route::delete('/{extracurricularCategory}', [ExtracurricularCategoryController::class, 'destroy'])->name('destroy');
        });

    Route::prefix('extracurricular')
        ->name('extracurricular.')
        ->group(function () {
            Route::get('/', [ExtracurricularController::class, 'index'])->name('index');
            Route::get('/detail/{extracurricular}', [ExtracurricularController::class, 'detail'])->name('detail');
            Route::get('/create', [ExtracurricularController::class, 'create'])->name('create');
            Route::get('/import', [ExtracurricularController::class, 'import'])->name('import');
            Route::post('/import', [ExtracurricularController::class, 'importStore'])->name('import.store');
            Route::post('/', [ExtracurricularController::class, 'store'])->name('store');
            Route::get('/{extracurricular}/edit', [ExtracurricularController::class, 'edit'])->name('edit');
            Route::put('/{extracurricular}', [ExtracurricularController::class, 'update'])->name('update');
            Route::delete('/{extracurricular}', [ExtracurricularController::class, 'destroy'])->name('destroy');
            Route::patch('/{extracurricular}/toggle', [ExtracurricularController::class, 'toggleActive'])->name('toggle');
            Route::get('/generate-code/{name}', [ExtracurricularController::class, 'generateCode'])->name('generateCode');
        });

    Route::prefix('student-class')
        ->name('student-class.')
        ->group(function () {
            Route::get('/', [StudentClassController::class, 'index'])->name('index');
            Route::get('/create', [StudentClassController::class, 'create'])->name('create');
            Route::get('/import', [StudentClassController::class, 'import'])->name('import');
            Route::post('/import', [StudentClassController::class, 'importStore'])->name('import.store');
            Route::post('/', [StudentClassController::class, 'store'])->name('store');
            Route::get('/{studentClass}/edit', [StudentClassController::class, 'edit'])->name('edit');
            Route::put('/{studentClass}', [StudentClassController::class, 'update'])->name('update');
            Route::delete('/{studentClass}', [StudentClassController::class, 'destroy'])->name('destroy');
            Route::patch('/{studentClass}/toggle', [StudentClassController::class, 'toggleActive'])->name('toggle');
        });

    Route::prefix('student')
        ->name('student.')
        ->group(function () {
            Route::get('/', [StudentController::class, 'index'])->name('index');
            Route::get('/create', [StudentController::class, 'create'])->name('create');
            Route::get('/import', [StudentController::class, 'import'])->name('import');
            Route::post('/import', [StudentController::class, 'importStore'])->name('import.store');
            Route::post('/', [StudentController::class, 'store'])->name('store');
            Route::get('/{student}/edit', [StudentController::class, 'edit'])->name('edit');
            Route::put('/{student}', [StudentController::class, 'update'])->name('update');
            Route::delete('/{student}', [StudentController::class, 'destroy'])->name('destroy');
        });

    Route::prefix('scores')
        ->name('scores.')
        ->group(function () {
            Route::get('/', [ScoreController::class, 'index'])->name('index');
            Route::get('/input', [ScoreController::class, 'input'])->name('input');
            Route::post('/', [ScoreController::class, 'store'])->name('store');
            Route::get('/get-students', [ScoreController::class, 'getStudentsForScore'])->name('get-students');
            Route::get('/export', [ScoreController::class, 'export'])->name('export');
        });

    Route::prefix('reports')
        ->name('reports.')
        ->group(function () {
            Route::get('/', [ReportController::class, 'index'])->name('index');
            Route::get('/preview', [ReportController::class, 'preview'])->name('preview');
            Route::post('/export/excel', [ReportController::class, 'exportExcel'])->name('export.excel');
            Route::post('/export/pdf', [ReportController::class, 'exportPdf'])->name('export.pdf');
        });

    Route::prefix('memberships')
        ->name('memberships.')
        ->group(function () {
            Route::get('/', [ExtracurricularMembershipController::class, 'index'])->name('index');
            Route::get('/create', [ExtracurricularMembershipController::class, 'create'])->name('create');
            Route::post('/', [ExtracurricularMembershipController::class, 'store'])->name('store');
            Route::get('/eligible-students', [ExtracurricularMembershipController::class, 'getEligibleStudents'])->name('eligible-students');
            Route::patch('/{membership}/status', [ExtracurricularMembershipController::class, 'updateStatus'])->name('update.status');
            Route::delete('/{membership}', [ExtracurricularMembershipController::class, 'destroy'])->name('destroy');
        });

    Route::prefix('user-management')
        ->name('user-management.')
        ->group(function () {
            Route::get('/', [UserController::class, 'index'])->name('index');
            Route::get('/create', [UserController::class, 'create'])->name('create');
            Route::get('/generate-username', [UserController::class, 'generateUsername'])->name('generate.username');
            Route::post('/', [UserController::class, 'store'])->name('store');
            Route::get('/{user}/edit', [UserController::class, 'edit'])->name('edit');
            Route::put('/{user}', [UserController::class, 'update'])->name('update');
            Route::delete('/{user}', [UserController::class, 'destroy'])->name('destroy');
            Route::patch('/{user}/toggle', [UserController::class, 'toggleActive'])->name('toggle');
        });

    Route::prefix('presence')
        ->name('presence.')
        ->group(function () {
            Route::get('/', [PresenceController::class, 'index'])->name('index');
            Route::get('/show/{extracurricular}', [PresenceController::class, 'show'])->name('show');
            Route::get('/create', [PresenceController::class, 'create'])->name('create');
            Route::post('/', [PresenceController::class, 'store'])->name('store');
            Route::get('/{presence}/edit', [PresenceController::class, 'edit'])->name('edit');
            Route::put('/{presence}', [PresenceController::class, 'update'])->name('update');
            Route::delete('/{presence}', [PresenceController::class, 'destroy'])->name('destroy');
        });
});

Route::middleware(['auth', 'check.role:pembina'])->group(function () {
    Route::get('pembina/dashboard', [DashboardController::class, 'index'])->name('pembina.dashboard');
});

Route::middleware(['auth', 'check.role:admin'])->group(function () {
    Route::get('admin/dashboard', [DashboardController::class, 'index'])->name('admin.dashboard');
});
