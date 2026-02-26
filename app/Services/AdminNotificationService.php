<?php

namespace App\Services;

use App\Models\RegistrationLink;
use App\Models\User;
use App\Notifications\AdminActivityNotification;

class AdminNotificationService
{
    private const DEFAULT_PREFERENCES = [
        'registration_submitted' => true,
        'registration_link_sent' => true,
        'client_created' => true,
    ];

    private function adminUsers()
    {
        return User::query()->where('role', 'admin')->get();
    }

    public function notifyRegistrationSubmitted(RegistrationLink $link, int $filesCount): void
    {
        $notification = new AdminActivityNotification([
            'title' => 'New registration submitted',
            'message' => $link->email.' submitted '.$filesCount.' file(s).',
            'url' => route('admin.register.show', $link->id),
            'type' => 'registration_submitted',
        ]);

        foreach ($this->adminUsers() as $admin) {
            if (! $this->wantsNotification($admin, 'registration_submitted')) {
                continue;
            }
            $admin->notify($notification);
        }
    }

    public function notifyRegistrationLinkSent(RegistrationLink $link): void
    {
        $notification = new AdminActivityNotification([
            'title' => 'Registration link sent',
            'message' => 'Registration link sent to '.$link->email.'.',
            'url' => route('admin.register.show', $link->id),
            'type' => 'registration_link_sent',
        ]);

        foreach ($this->adminUsers() as $admin) {
            if (! $this->wantsNotification($admin, 'registration_link_sent')) {
                continue;
            }
            $admin->notify($notification);
        }
    }

    public function notifyClientCreated(string $email): void
    {
        $notification = new AdminActivityNotification([
            'title' => 'Client account created',
            'message' => 'New client user created: '.$email,
            'url' => route('admin.user.index'),
            'type' => 'client_created',
        ]);

        foreach ($this->adminUsers() as $admin) {
            if (! $this->wantsNotification($admin, 'client_created')) {
                continue;
            }
            $admin->notify($notification);
        }
    }

    private function wantsNotification(User $user, string $type): bool
    {
        $preferences = [
            ...self::DEFAULT_PREFERENCES,
            ...((array) ($user->notification_preferences ?? [])),
        ];

        return (bool) ($preferences[$type] ?? true);
    }
}
