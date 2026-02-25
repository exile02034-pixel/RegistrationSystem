<?php
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\Admin\RegistrationController as AdminRegistrationController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Laravel\Fortify\Features;
use Illuminate\Support\Facades\Mail;
use App\Mail\TestEmail;
Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canRegister' => Features::enabled(Features::registration()),
    ]);
})->name('home');

Route::get('/registration/thankyou', function () {
    return view('emails.registration.thankyou'); 
})->name('client.registration.thankyou');

Route::get('/client-registration/{token}', [AdminRegistrationController::class, 'create'])
    ->name('client.registration.create');

Route::post('/client-registration/{token}', [AdminRegistrationController::class, 'store'])
    ->name('client.registration.store');
Route::middleware(['auth', 'verified', 'role:admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {

        Route::get('/dashboard', function () {
            return Inertia::render('admin/Dashboard');
        })->name('dashboard');
           
        // USER ROUTES
        Route::get('/user', [AdminUserController::class, 'index'])->name('user.index');
        Route::get('/user/create', [AdminUserController::class, 'create'])->name('user.create');
        Route::post('/user', [AdminUserController::class, 'store'])->name('user.store');

        // REGISTRATION ROUTES
        Route::get('/registration', [AdminRegistrationController::class, 'index'])->name('register.index');

        // Send registration link via email
        Route::post('/registration/send', [AdminRegistrationController::class, 'sendLink'])->name('register.send');

        
    });

Route::middleware(['auth', 'verified', 'role:user'])
    ->prefix('user')
    ->name('user.')
    ->group(function () {
        Route::get('/dashboard', fn () => Inertia::render('user/Dashboard'))->name('dashboard');
    });

require __DIR__.'/settings.php';