<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Services\Settings\SettingsPageService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class NotificationPreferenceController extends Controller
{
    public function __construct(
        private readonly SettingsPageService $settingsPageService,
    ) {}

    public function edit(Request $request): Response
    {
        $user = $request->user();
        abort_unless($user !== null, 403);

        return Inertia::render('settings/Notifications', $this->settingsPageService->notificationSettingsPageProps($user));
    }

    public function update(Request $request): RedirectResponse
    {
        $user = $request->user();
        abort_unless($user !== null, 403);

        $normalized = $this->settingsPageService->normalizedNotificationPreferences(
            $user,
            $request->input('preferences', []),
        );

        $user->forceFill([
            'notification_preferences' => $normalized,
        ])->save();

        return back();
    }
}
