<?php

use App\Http\Controllers\Client\RegistrationFormController;
use Illuminate\Support\Facades\Route;

Route::get('/register/success', [RegistrationFormController::class, 'success'])
    ->name('registration.form.success');

Route::get('/register/{token}', [RegistrationFormController::class, 'show'])
    ->name('registration.form.show');

Route::post('/register/{token}', [RegistrationFormController::class, 'submit'])
    ->name('registration.form.submit');

// Keep legacy URL paths alive for links that were already sent.
Route::get('/client-registration/{token}', [RegistrationFormController::class, 'show'])
    ->name('client.registration.show');

Route::post('/client-registration/{token}', [RegistrationFormController::class, 'submit'])
    ->name('client.registration.submit');

Route::get('/client-registration/{token}/thank-you', [RegistrationFormController::class, 'success'])
    ->name('client.registration.thank-you');
