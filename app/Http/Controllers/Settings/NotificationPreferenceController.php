<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Services\NotificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class NotificationPreferenceController extends Controller
{
    public function __construct(
        private readonly NotificationService $notificationService
    ) {
    }

    public function edit(Request $request): Response
    {
        $user = $request->user();
        abort_unless($user !== null, 403);

        return Inertia::render('settings/Notifications', [
            'preferences' => $this->notificationService->mergedPreferencesForUser($user),
            'labels' => $this->notificationService->availablePreferenceLabels(),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $user = $request->user();
        abort_unless($user !== null, 403);

        $defaults = $this->notificationService->defaultPreferences();
        $incoming = $request->input('preferences', []);

        $normalized = collect($defaults)->mapWithKeys(function (bool $defaultEnabled, string $key) use ($incoming) {
            return [$key => (bool) data_get($incoming, $key, $defaultEnabled)];
        })->all();

        $user->forceFill([
            'notification_preferences' => $normalized,
        ])->save();

        return back();
    }
}

