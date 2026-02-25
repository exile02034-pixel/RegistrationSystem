<?php

namespace App\Http\Responses;

use Laravel\Fortify\Contracts\LoginResponse as LoginResponseContract;

class LoginResponse implements LoginResponseContract
{
     public function toResponse($request)
    {
        $user = $request->user();

        $redirectTo = match($user->role) {
            'admin' => '/admin/dashboard',
            default => '/user/dashboard',
        };

        return $request->wantsJson()
            ? response()->json(['two_factor' => false])
            : redirect()->intended($redirectTo);
    }
}