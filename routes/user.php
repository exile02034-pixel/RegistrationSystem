<?php

use App\Http\Controllers\User\DashboardController as UserDashboardController;
use App\Http\Controllers\User\UserFormPdfController;
use App\Http\Controllers\User\UserFormSubmissionController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified', 'role:user'])
    ->prefix('user')
    ->name('user.')
    ->group(function () {
        Route::get('/dashboard', [UserDashboardController::class, 'index'])->name('dashboard');
        Route::get('/about-me', [UserDashboardController::class, 'aboutMe'])->name('about-me');
        Route::get('/files', [UserDashboardController::class, 'aboutMe'])->name('files');
        Route::get('/submissions/{submission}/pdf/{section}/view', [UserFormPdfController::class, 'view'])->name('submissions.pdf.view');
        Route::get('/submissions/{submission}/pdf/{section}/download', [UserFormPdfController::class, 'download'])->name('submissions.pdf.download');
        Route::get('/submissions/{submission}/pdf/print-batch', [UserFormPdfController::class, 'printBatch'])->name('submissions.pdf.print-batch');
        Route::delete('/submissions/{submission}/pdf/{section}', [UserFormPdfController::class, 'destroy'])->name('submissions.pdf.destroy');
        Route::patch('/submissions/{submission}/section/{section}', [UserFormSubmissionController::class, 'updateSection'])->name('submissions.section.update');
    });
