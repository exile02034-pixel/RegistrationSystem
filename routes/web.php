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

    return match ($user->role) {
        'admin' => redirect()->route('admin.dashboard'),
        'user' => redirect()->route('user.dashboard'),
        default => abort(403, 'Unauthorized role.'),
    };
})->middleware(['auth', 'verified'])->name('dashboard');

require __DIR__.'/admin.php';
require __DIR__.'/user.php';
require __DIR__.'/client.php';
require __DIR__.'/settings.php';
