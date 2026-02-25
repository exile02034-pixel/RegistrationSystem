<?php

use App\Http\Controllers\UserCreateController;
use App\Http\Controllers\UserController;

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Laravel\Fortify\Features;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canRegister' => Features::enabled(Features::registration()),
    ]);
})->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard', function () {
        return Inertia::render('Dashboard');
    })->name('dashboard');


    Route::get('CreatePage/create', [UserCreateController::class, 'index'])->name('createpage.create');
    Route::get('User/Index', [UserController::class, 'index'])->name('user.index');

});

require __DIR__.'/settings.php';
