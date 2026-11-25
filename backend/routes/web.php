<?php

use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\ClinicSubmissionController as AdminClinicSubmissionController;
use App\Http\Controllers\Admin\ContactInquiryController as AdminContactInquiryController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\ExportController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Middleware\SetAdminLocale;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::prefix('admin')->name('admin.')->middleware(SetAdminLocale::class)->group(function (): void {
    Route::middleware('guest')->group(function (): void {
        Route::get('/login', [AuthController::class, 'create'])->name('login');
        Route::post('/login', [AuthController::class, 'store'])
            ->middleware('throttle:5,1')
            ->name('login.store');
    });

    Route::post('/language', [AuthController::class, 'updateLocale'])->name('language');

    Route::middleware(['auth', 'admin.active', 'role:super-admin|admin'])->group(function (): void {
        Route::post('/logout', [AuthController::class, 'destroy'])->name('logout');
        Route::get('/password', [AuthController::class, 'editPassword'])->name('password.edit');
        Route::put('/password', [AuthController::class, 'updatePassword'])->name('password.update');

        Route::middleware('admin.password.changed')->group(function (): void {
            Route::get('/', DashboardController::class)
                ->middleware('permission:admin.access')
                ->name('dashboard');

            Route::get('/clinic-submissions', [AdminClinicSubmissionController::class, 'index'])
                ->middleware('permission:clinic-submissions.view')
                ->name('clinic-submissions.index');
            Route::get('/clinic-submissions/export', [ExportController::class, 'clinicSubmissions'])
                ->middleware('permission:clinic-submissions.view')
                ->name('clinic-submissions.export');
            Route::get('/clinic-submissions/status/{status}', [AdminClinicSubmissionController::class, 'index'])
                ->whereIn('status', ['pending', 'accepted', 'refused', 'meeting'])
                ->middleware('permission:clinic-submissions.view')
                ->name('clinic-submissions.status-list');
            Route::get('/clinic-submissions/create', [AdminClinicSubmissionController::class, 'create'])
                ->middleware('permission:clinic-submissions.create')
                ->name('clinic-submissions.create');
            Route::post('/clinic-submissions', [AdminClinicSubmissionController::class, 'store'])
                ->middleware('permission:clinic-submissions.create')
                ->name('clinic-submissions.store');
            Route::get('/clinic-submissions/{clinicSubmission}', [AdminClinicSubmissionController::class, 'show'])
                ->middleware('permission:clinic-submissions.view')
                ->name('clinic-submissions.show');
            Route::get('/clinic-submissions/{clinicSubmission}/edit', [AdminClinicSubmissionController::class, 'edit'])
                ->middleware('permission:clinic-submissions.update')
                ->name('clinic-submissions.edit');
            Route::put('/clinic-submissions/{clinicSubmission}', [AdminClinicSubmissionController::class, 'update'])
                ->middleware('permission:clinic-submissions.update')
                ->name('clinic-submissions.update');
            Route::patch('/clinic-submissions/{clinicSubmission}/status', [AdminClinicSubmissionController::class, 'updateStatus'])
                ->middleware('role:super-admin')
                ->name('clinic-submissions.status');
            Route::delete('/clinic-submissions/{clinicSubmission}', [AdminClinicSubmissionController::class, 'destroy'])
                ->middleware('permission:clinic-submissions.delete')
                ->name('clinic-submissions.destroy');

            Route::get('/contact-inquiries', [AdminContactInquiryController::class, 'index'])
                ->middleware('permission:contact-inquiries.view')
                ->name('contact-inquiries.index');
            Route::get('/contact-inquiries/export', [ExportController::class, 'contactInquiries'])
                ->middleware('permission:contact-inquiries.view')
                ->name('contact-inquiries.export');
            Route::get('/contact-inquiries/create', [AdminContactInquiryController::class, 'create'])
                ->middleware('permission:contact-inquiries.create')
                ->name('contact-inquiries.create');
            Route::post('/contact-inquiries', [AdminContactInquiryController::class, 'store'])
                ->middleware('permission:contact-inquiries.create')
                ->name('contact-inquiries.store');
            Route::get('/contact-inquiries/{contactInquiry}', [AdminContactInquiryController::class, 'show'])
                ->middleware('permission:contact-inquiries.view')
                ->name('contact-inquiries.show');
            Route::get('/contact-inquiries/{contactInquiry}/edit', [AdminContactInquiryController::class, 'edit'])
                ->middleware('permission:contact-inquiries.update')
                ->name('contact-inquiries.edit');
            Route::put('/contact-inquiries/{contactInquiry}', [AdminContactInquiryController::class, 'update'])
                ->middleware('permission:contact-inquiries.update')
                ->name('contact-inquiries.update');
            Route::delete('/contact-inquiries/{contactInquiry}', [AdminContactInquiryController::class, 'destroy'])
                ->middleware('permission:contact-inquiries.delete')
                ->name('contact-inquiries.destroy');

            Route::middleware(['permission:admin-users.manage'])->group(function (): void {
                Route::get('/users', [UserController::class, 'index'])->name('users.index');
                Route::get('/users/create', [UserController::class, 'create'])->name('users.create');
                Route::post('/users', [UserController::class, 'store'])->name('users.store');
                Route::get('/users/{user}/edit', [UserController::class, 'edit'])->name('users.edit');
                Route::put('/users/{user}', [UserController::class, 'update'])->name('users.update');
                Route::post('/users/{user}/reset-password', [UserController::class, 'resetPassword'])
                    ->name('users.reset-password');
            });
        });
    });
});
