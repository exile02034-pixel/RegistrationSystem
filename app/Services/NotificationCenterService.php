<?php

namespace App\Services;

use App\Models\User;
use App\Models\UserNotification;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Pagination\LengthAwarePaginator;

class NotificationCenterService
{
    public function indexPageProps(User $user, int $perPage = 12): array
    {
        return [
            'notifications' => $this->paginatedForUser($user, $perPage),
        ];
    }

    public function paginatedForUser(User $user, int $perPage = 12): LengthAwarePaginator
    {
        return UserNotification::query()
            ->forUser($user->id)
            ->latest('created_at')
            ->paginate($perPage)
            ->through(fn (UserNotification $notification) => [
                'id' => $notification->id,
                'category' => $notification->category,
                'title' => $notification->title,
                'message' => $notification->message,
                'action_url' => $notification->action_url,
                'read_at' => $notification->read_at?->toDateTimeString(),
                'created_at' => $notification->created_at?->toDateTimeString(),
            ])
            ->withQueryString();
    }

    public function markRead(User $user, UserNotification $notification): void
    {
        $this->assertOwnership($user, $notification);

        if ($notification->read_at === null) {
            $notification->forceFill(['read_at' => now()])->save();
        }
    }

    public function markAllRead(User $user): void
    {
        UserNotification::query()
            ->forUser($user->id)
            ->unread()
            ->update(['read_at' => now()]);
    }

    public function delete(User $user, UserNotification $notification): void
    {
        $this->assertOwnership($user, $notification);
        $notification->delete();
    }

    public function deleteSelected(User $user, array $ids): void
    {
        UserNotification::query()
            ->forUser($user->id)
            ->whereIn('id', $ids)
            ->delete();
    }

    public function deleteAll(User $user): void
    {
        UserNotification::query()
            ->forUser($user->id)
            ->delete();
    }

    private function assertOwnership(User $user, UserNotification $notification): void
    {
        if ($notification->user_id !== $user->id) {
            throw new AuthorizationException('This action is unauthorized.');
        }
    }
}
