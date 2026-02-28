<?php

use App\Http\Controllers\Client\RegistrationFormController;
use App\Http\Controllers\Client\SubmissionTrackingController;
use Illuminate\Support\Facades\Route;

Route::get('/register/success', [RegistrationFormController::class, 'success'])
    ->name('registration.form.success');

Route::get('/register/track', [SubmissionTrackingController::class, 'lookup'])
    ->name('registration.tracking.lookup');

Route::post('/register/track/request-link', [SubmissionTrackingController::class, 'requestLink'])
    ->middleware('throttle:5,1')
    ->name('registration.tracking.request-link');

Route::get('/register/track/access/{token}', [SubmissionTrackingController::class, 'access'])
    ->middleware('throttle:20,1')
    ->name('registration.tracking.access');

Route::get('/register/track/submission', [SubmissionTrackingController::class, 'show'])
    ->name('registration.tracking.show');

Route::post('/register/track/logout', [SubmissionTrackingController::class, 'logout'])
    ->name('registration.tracking.logout');

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
