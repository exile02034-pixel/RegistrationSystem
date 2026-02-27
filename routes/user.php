<?php

use App\Http\Controllers\User\DashboardController as UserDashboardController;
use Illuminate\Support\Facades\Route;

Route::prefix('user')
    ->name('user.')
    ->group(function () {
        Route::get('/uploads/{upload}/view-signed', [UserDashboardController::class, 'viewSignedRawUpload'])
            ->middleware('signed')
            ->name('uploads.view-signed');
    });

Route::middleware(['auth', 'verified', 'role:user'])
    ->prefix('user')
    ->name('user.')
    ->group(function () {
        Route::get('/dashboard', [UserDashboardController::class, 'index'])->name('dashboard');
        Route::get('/files', [UserDashboardController::class, 'files'])->name('files');
        Route::get('/uploads/print-batch', [UserDashboardController::class, 'printBatch'])->name('uploads.print-batch');
        Route::get('/uploads/print-batch/file/{token}', [UserDashboardController::class, 'printBatchFile'])->name('uploads.print-batch.file');
        Route::post('/uploads/store', [UserDashboardController::class, 'storeUploads'])->name('uploads.store');
        Route::get('/uploads/{upload}/view', [UserDashboardController::class, 'viewUpload'])->name('uploads.view');
        Route::get('/uploads/{upload}/download', [UserDashboardController::class, 'downloadUpload'])->name('uploads.download');
        Route::get('/uploads/{upload}/print', [UserDashboardController::class, 'printUpload'])->name('uploads.print');
    });
