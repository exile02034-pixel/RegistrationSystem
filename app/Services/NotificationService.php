<?php

namespace App\Services;

use App\Models\User;
use App\Models\UserNotification;
use Illuminate\Support\Arr;

class NotificationService
{
    public function notifyAdmins(
        string $category,
        string $title,
        ?string $message = null,
        ?string $actionUrl = null,
        array $meta = []
    ): void {
        User::query()
            ->where('role', 'admin')
            ->get()
            ->each(function (User $admin) use ($category, $title, $message, $actionUrl, $meta) {
                $this->notifyUser($admin, $category, $title, $message, $actionUrl, $meta);
            });
    }

    public function notifyUser(
        User $user,
        string $category,
        string $title,
        ?string $message = null,
        ?string $actionUrl = null,
        array $meta = []
    ): ?UserNotification {
        if (! $this->isEnabledForUser($user, $category)) {
            return null;
        }

        return UserNotification::create([
            'user_id' => $user->id,
            'category' => $category,
            'title' => $title,
            'message' => $message,
            'action_url' => $actionUrl,
            'meta' => $meta,
        ]);
    }

    public function defaultPreferences(): array
    {
        $defaults = config('notifications.preferences', []);

        return is_array($defaults) ? $defaults : [];
    }

    public function availablePreferenceLabels(): array
    {
        $labels = config('notifications.labels', []);
        $defaults = $this->defaultPreferences();

        if (! is_array($labels)) {
            $labels = [];
        }

        return collect($defaults)->mapWithKeys(function (bool $enabled, string $key) use ($labels) {
            return [$key => (string) Arr::get($labels, $key, $key)];
        })->all();
    }

    public function mergedPreferencesForUser(User $user): array
    {
        $defaults = $this->defaultPreferences();
        $stored = is_array($user->notification_preferences) ? $user->notification_preferences : [];

        return collect($defaults)->mapWithKeys(function (bool $defaultEnabled, string $key) use ($stored) {
            return [$key => (bool) Arr::get($stored, $key, $defaultEnabled)];
        })->all();
    }

    private function isEnabledForUser(User $user, string $category): bool
    {
        return (bool) Arr::get($this->mergedPreferencesForUser($user), $category, true);
    }
}
