<?php

use App\Http\Controllers\Admin\RegistrationController;
use App\Http\Controllers\Admin\UserController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::middleware(['auth', 'verified', 'role:admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::get('/dashboard', fn () => Inertia::render('admin/Dashboard'))->name('dashboard');

        Route::get('/user', [UserController::class, 'index'])->name('user.index');
        Route::get('/user/create', [UserController::class, 'create'])->name('user.create');
        Route::post('/user', [UserController::class, 'store'])->name('user.store');

        Route::get('/registration', [RegistrationController::class, 'index'])->name('register.index');
        Route::post('/registration/send', [RegistrationController::class, 'sendLink'])->name('register.send');
        Route::get('/registration/{registrationLink}', [RegistrationController::class, 'show'])->name('register.show');
        Route::get('/registration/{registrationLink}/uploads/{upload}/download', [RegistrationController::class, 'downloadUpload'])
            ->name('register.uploads.download');
    });
