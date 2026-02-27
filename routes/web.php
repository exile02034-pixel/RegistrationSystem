<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Laravel\Fortify\Features;
use Illuminate\Http\Request;
use App\Http\Controllers\NotificationController;

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

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::patch('/notifications/{notification}/read', [NotificationController::class, 'markRead'])->name('notifications.read');
    Route::post('/notifications/read-all', [NotificationController::class, 'markAllRead'])->name('notifications.read-all');
    Route::post('/notifications/delete-selected', [NotificationController::class, 'destroySelected'])->name('notifications.delete-selected');
    Route::delete('/notifications/{notification}', [NotificationController::class, 'destroy'])->name('notifications.delete');
    Route::delete('/notifications', [NotificationController::class, 'destroyAll'])->name('notifications.delete-all');
});

require __DIR__.'/admin.php';
require __DIR__.'/user.php';
require __DIR__.'/client.php';
require __DIR__.'/settings.php';
