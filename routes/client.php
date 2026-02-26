<?php

use App\Http\Controllers\Client\RegistrationController;
use Illuminate\Support\Facades\Route;

Route::get('/client-registration/{token}', [RegistrationController::class, 'show'])
    ->name('client.registration.show');

Route::get('/client-registration/{token}/templates/{templateKey}', [RegistrationController::class, 'downloadTemplate'])
    ->name('client.registration.templates.download');

Route::post('/client-registration/{token}/uploads', [RegistrationController::class, 'storeUploads'])
    ->name('client.registration.uploads.store');
