<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class NotificationSettingsController extends Controller
{
    private const DEFAULT_PREFERENCES = [
        'registration_submitted' => true,
        'registration_link_sent' => true,
        'client_created' => true,
    ];

    public function edit(Request $request): Response
    {
        $userPreferences = $request->user()?->notification_preferences ?? [];

        return Inertia::render('settings/Notifications', [
            'preferences' => [
                ...self::DEFAULT_PREFERENCES,
                ...$userPreferences,
            ],
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $keys = ['registration_submitted', 'registration_link_sent', 'client_created'];
        $currentPreferences = [
            ...self::DEFAULT_PREFERENCES,
            ...((array) ($request->user()?->notification_preferences ?? [])),
        ];

        // Strict single-toggle update path from UI.
        if ($request->has('key')) {
            $validated = $request->validate([
                'key' => ['required', 'string', 'in:'.implode(',', $keys)],
                'enabled' => ['required'],
            ]);

            $currentPreferences[$validated['key']] = $this->parseBooleanValue(
                $validated['enabled'],
                'enabled',
            );
        } else {
            // Backward-compatible bulk update path.
            $request->validate([
                'registration_submitted' => ['sometimes'],
                'registration_link_sent' => ['sometimes'],
                'client_created' => ['sometimes'],
            ]);

            foreach ($keys as $key) {
                if ($request->exists($key)) {
                    $currentPreferences[$key] = $this->parseBooleanValue(
                        $request->input($key),
                        $key,
                    );
                }
            }
        }

        $request->user()?->update([
            'notification_preferences' => $currentPreferences,
        ]);

        return back()->with('success', 'Notification preferences updated.');
    }

    /**
     * @throws ValidationException
     */
    private function parseBooleanValue(mixed $value, string $field): bool
    {
        if (is_bool($value)) {
            return $value;
        }

        if (is_int($value) && ($value === 0 || $value === 1)) {
            return (bool) $value;
        }

        if (is_string($value)) {
            $normalized = strtolower(trim($value));

            if (in_array($normalized, ['1', 'true', 'on', 'yes'], true)) {
                return true;
            }

            if (in_array($normalized, ['0', 'false', 'off', 'no'], true)) {
                return false;
            }
        }

        throw ValidationException::withMessages([
            $field => 'The '.$field.' field must be true or false.',
        ]);
    }
}
