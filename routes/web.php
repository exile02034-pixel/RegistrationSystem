<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Laravel\Fortify\Features;
use Illuminate\Http\Request;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canRegister' => Features::enabled(Features::registration()),
    ]);
})->name('home');

Route::get('/dashboard', function (Request $request) {
    $user = $request->user();

    if (! $user) {
        return redirect()->route('home');
    }

    return $user->role === 'admin'
        ? redirect()->route('admin.dashboard')
        : redirect()->route('user.dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware(['auth', 'verified', 'role:user'])
    ->prefix('user')
    ->name('user.')
    ->group(function () {
        Route::get('/dashboard', fn () => Inertia::render('user/Dashboard'))->name('dashboard');
    });

require __DIR__.'/admin.php';
require __DIR__.'/client.php';
require __DIR__.'/settings.php';
