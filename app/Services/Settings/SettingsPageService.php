<?php

namespace App\Services\Settings;

use App\Models\User;
use App\Services\NotificationService;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Laravel\Fortify\Features;

class SettingsPageService
{
    public function __construct(
        private readonly NotificationService $notificationService,
    ) {}

    public function profilePageProps(?User $user, mixed $status): array
    {
        return [
            'mustVerifyEmail' => $user instanceof MustVerifyEmail,
            'status' => $status,
        ];
    }

    public function twoFactorPageProps(User $user): array
    {
        return [
            'twoFactorEnabled' => $user->hasEnabledTwoFactorAuthentication(),
            'requiresConfirmation' => Features::optionEnabled(Features::twoFactorAuthentication(), 'confirm'),
        ];
    }

    public function notificationSettingsPageProps(User $user): array
    {
        return [
            'preferences' => $this->notificationService->mergedPreferencesForUser($user),
            'labels' => $this->notificationService->availablePreferenceLabels(),
        ];
    }

    public function normalizedNotificationPreferences(User $user, mixed $incoming): array
    {
        $defaults = $this->notificationService->defaultPreferences();

        return collect($defaults)->mapWithKeys(function (bool $defaultEnabled, string $key) use ($incoming) {
            return [$key => (bool) data_get($incoming, $key, $defaultEnabled)];
        })->all();
    }
}
