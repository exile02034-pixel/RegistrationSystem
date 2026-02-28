<?php

namespace App\Services;

use App\Mail\SubmissionTrackingLinkMail;
use App\Models\RegistrationLink;
use App\Models\SubmissionAccessToken;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class SubmissionTrackingService
{
    private const SESSION_LINK_ID = 'tracking.registration_link_id';

    private const SESSION_EXPIRES_AT = 'tracking.expires_at';

    public function sendAccessLink(string $email): void
    {
        $link = RegistrationLink::query()
            ->where('email', $email)
            ->whereHas('formSubmission')
            ->latest('updated_at')
            ->first();

        if ($link === null) {
            return;
        }

        SubmissionAccessToken::query()
            ->where('registration_link_id', $link->id)
            ->whereNull('revoked_at')
            ->where('expires_at', '>', now())
            ->update(['revoked_at' => now()]);

        $rawToken = Str::random(80);

        SubmissionAccessToken::query()->create([
            'registration_link_id' => $link->id,
            'token_hash' => hash('sha256', $rawToken),
            'expires_at' => now()->addMinutes(30),
        ]);

        Mail::to($email)->send(new SubmissionTrackingLinkMail(
            trackingUrl: route('registration.tracking.access', ['token' => $rawToken]),
        ));
    }

    public function consumeAccessToken(Request $request, string $rawToken): ?RegistrationLink
    {
        $token = SubmissionAccessToken::query()
            ->with('registrationLink.formSubmission')
            ->where('token_hash', hash('sha256', $rawToken))
            ->whereNull('revoked_at')
            ->where('expires_at', '>', now())
            ->first();

        if ($token === null || $token->registrationLink?->formSubmission === null) {
            return null;
        }

        $token->forceFill([
            'used_at' => now(),
            'revoked_at' => now(),
        ])->save();

        $request->session()->put(self::SESSION_LINK_ID, $token->registration_link_id);
        $request->session()->put(self::SESSION_EXPIRES_AT, now()->addMinutes(30)->toIso8601String());

        return $token->registrationLink;
    }

    public function authorizedRegistrationLink(Request $request): ?RegistrationLink
    {
        $registrationLinkId = $request->session()->get(self::SESSION_LINK_ID);
        $expiresAt = $request->session()->get(self::SESSION_EXPIRES_AT);

        if (! is_string($registrationLinkId) || ! is_string($expiresAt)) {
            return null;
        }

        if (now()->greaterThan(Carbon::parse($expiresAt))) {
            $this->clearAccess($request);

            return null;
        }

        $link = RegistrationLink::query()
            ->with('formSubmission.fields')
            ->whereKey($registrationLinkId)
            ->first();

        if ($link?->formSubmission === null) {
            $this->clearAccess($request);

            return null;
        }

        return $link;
    }

    public function canEdit(RegistrationLink $link): bool
    {
        return in_array($link->status, ['pending', 'incomplete'], true);
    }

    public function clearAccess(Request $request): void
    {
        $request->session()->forget([self::SESSION_LINK_ID, self::SESSION_EXPIRES_AT]);
    }
}
