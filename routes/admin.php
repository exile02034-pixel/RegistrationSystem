<?php

use App\Http\Controllers\Admin\RegistrationController;
use App\Http\Controllers\Admin\UserController;
use App\Models\AdminActivity;
use App\Models\RegistrationLink;
use App\Models\RegistrationUpload;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::middleware(['auth', 'verified', 'role:admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::get('/dashboard', function (Request $request) {
            $recentActivities = AdminActivity::query()
                ->with('user:id,name,email')
                ->latest()
                ->limit(6)
                ->get()
                ->map(fn (AdminActivity $activity) => [
                    'id' => (string) $activity->id,
                    'title' => $activity->title,
                    'message' => $activity->description ?? 'No details provided.',
                    'time' => $activity->created_at?->diffForHumans(),
                    'url' => $activity->url ?? route('notifications.index'),
                    'actor' => $activity->user?->name ?? $activity->user?->email ?? 'System',
                ])
                ->values()
                ->all();

            return Inertia::render('admin/Dashboard', [
                'stats' => [
                    'totalUsers' => User::where('role', 'user')->count(),
                    'pendingUsers' => RegistrationLink::where('status', 'pending')->count(),
                    'acceptedUsers' => RegistrationLink::where('status', 'completed')->count(),
                    'totalUploads' => RegistrationUpload::count(),
                ],
                'recentActivities' => $recentActivities,
            ]);
        })->name('dashboard');

        Route::get('/user', [UserController::class, 'index'])->name('user.index');
        Route::get('/user/create', [UserController::class, 'create'])->name('user.create');
        Route::post('/user', [UserController::class, 'store'])->name('user.store');
        Route::delete('/user/{user}', [UserController::class, 'destroy'])->name('user.destroy');

        Route::get('/registration', [RegistrationController::class, 'index'])->name('register.index');
        Route::get('/registration/create', [RegistrationController::class, 'create'])->name('register.create');
        Route::post('/registration/send', [RegistrationController::class, 'sendLink'])->name('register.send');
        Route::get('/registration/{registrationLink}', [RegistrationController::class, 'show'])->name('register.show');
        Route::get('/registration/{registrationLink}/uploads/{upload}/download', [RegistrationController::class, 'downloadUpload'])
            ->name('register.uploads.download');
    });
