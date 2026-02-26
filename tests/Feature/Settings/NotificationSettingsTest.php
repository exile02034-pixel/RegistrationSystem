<?php

namespace Tests\Feature\Settings;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NotificationSettingsTest extends TestCase
{
    use RefreshDatabase;

    public function test_single_notification_toggle_is_persisted(): void
    {
        $user = User::factory()->create([
            'notification_preferences' => [
                'registration_submitted' => true,
                'registration_link_sent' => true,
                'client_created' => true,
            ],
        ]);

        $response = $this
            ->actingAs($user)
            ->patch(route('notifications.settings.update'), [
                'key' => 'registration_submitted',
                'enabled' => false,
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect();

        $user->refresh();

        $this->assertFalse((bool) ($user->notification_preferences['registration_submitted'] ?? true));
        $this->assertTrue((bool) ($user->notification_preferences['registration_link_sent'] ?? false));
        $this->assertTrue((bool) ($user->notification_preferences['client_created'] ?? false));
    }

    public function test_toggle_remains_after_page_reload(): void
    {
        $user = User::factory()->create([
            'notification_preferences' => [
                'registration_submitted' => false,
                'registration_link_sent' => true,
                'client_created' => true,
            ],
        ]);

        $this
            ->actingAs($user)
            ->get(route('notifications.settings.edit'))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('settings/Notifications')
                ->where('preferences.registration_submitted', false)
                ->where('preferences.registration_link_sent', true)
                ->where('preferences.client_created', true));
    }
}
