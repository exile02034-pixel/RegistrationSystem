<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\RegistrationLink;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;

class UserController extends Controller
{
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

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
        ]);

        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => 'user',
        ]);

        return back()->with('success', 'User created successfully');
    }
}
