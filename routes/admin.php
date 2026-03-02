<?php

use App\Http\Controllers\Admin\ActivityLogController;
use App\Http\Controllers\Admin\AdminFormPdfController;
use App\Http\Controllers\Admin\AdminFormSubmissionController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\RegistrationController;
use App\Http\Controllers\Admin\UserController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified', 'role:admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');
        Route::get('/activity-logs', [ActivityLogController::class, 'index'])->name('activity-logs.index');

        Route::get('/user', [UserController::class, 'index'])->name('user.index');
        Route::get('/user/create', [UserController::class, 'create'])->name('user.create');
        Route::post('/user', [UserController::class, 'store'])->name('user.store');
        Route::get('/users/{user}', [UserController::class, 'show'])->name('user.show');
        Route::delete('/user/{user}', [UserController::class, 'destroy'])->name('user.destroy');

        Route::get('/registration', [RegistrationController::class, 'index'])->name('register.index');
        Route::get('/registration/create', [RegistrationController::class, 'create'])->name('register.create');
        Route::post('/registration/send', [RegistrationController::class, 'sendLink'])->name('register.send');
        Route::get('/registration/{registrationLink}', [RegistrationController::class, 'show'])->name('register.show');
        Route::patch('/registration/{registrationLink}/status', [RegistrationController::class, 'updateStatus'])->name('register.status.update');
        Route::delete('/registrations/{registrationLink}', [RegistrationController::class, 'destroy'])->name('register.destroy');
        Route::get('/submissions/{submission}/pdf/{section}/view', [AdminFormPdfController::class, 'view'])->name('submissions.pdf.view');
        Route::get('/submissions/{submission}/pdf/{section}/download', [AdminFormPdfController::class, 'download'])->name('submissions.pdf.download');
        Route::get('/submissions/{submission}/pdf/print-batch', [AdminFormPdfController::class, 'printBatch'])->name('submissions.pdf.print-batch');
        Route::delete('/submissions/{submission}/pdf/{section}', [AdminFormPdfController::class, 'destroy'])->name('submissions.pdf.destroy');
        Route::patch('/submissions/{submission}/section/{section}', [AdminFormSubmissionController::class, 'updateSection'])->name('submissions.section.update');
    });
