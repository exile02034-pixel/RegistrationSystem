<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\RegistrationLink;
use App\Models\User;
use App\Services\AdminActivityService;
use App\Services\AdminNotificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;

class UserController extends Controller
{
    public function __construct(
        private readonly AdminNotificationService $adminNotificationService,
        private readonly AdminActivityService $adminActivityService,
    ) {
    }

    public function index()
    {
        $users = User::query()
            ->where('role', 'user')
            ->latest()
            ->get();

        $latestLinkByEmail = RegistrationLink::query()
            ->whereIn('email', $users->pluck('email')->filter()->values())
            ->latest()
            ->get()
            ->unique('email')
            ->keyBy('email');

        return Inertia::render('admin/users/index', [
            'users' => $users->map(function (User $user) use ($latestLinkByEmail) {
                $registrationLink = $latestLinkByEmail->get($user->email);

                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'created_at' => $user->created_at?->toDateTimeString(),
                    'company_type' => $registrationLink?->company_type,
                    'registration_show_url' => $registrationLink
                        ? route('admin.register.show', $registrationLink->id)
                        : null,
                ];
            }),
        ]);
    }

    public function create()
    {
        return Inertia::render('admin/users/create');
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $createdUser = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => 'user',
        ]);

        $this->adminActivityService->log(
            admin: $request->user(),
            action: 'client_created',
            title: 'Client account created',
            description: 'Created client account for '.$createdUser->email.'.',
            url: route('admin.user.index'),
            metadata: ['email' => $createdUser->email],
        );

        $this->adminNotificationService->notifyClientCreated($request->string('email')->toString());

        return back()->with('success', 'User created successfully');
    }

    public function destroy(User $user): RedirectResponse
    {
        if ($user->role !== 'user') {
            return back()->with('error', 'Only client users can be deleted.');
        }

        $deletedEmail = $user->email;
        $user->delete();

        $this->adminActivityService->log(
            admin: request()->user(),
            action: 'client_deleted',
            title: 'Client account deleted',
            description: 'Deleted client account for '.$deletedEmail.'.',
            url: route('admin.user.index'),
            metadata: ['email' => $deletedEmail],
        );

        return back()->with('success', 'User deleted successfully');
    }
}