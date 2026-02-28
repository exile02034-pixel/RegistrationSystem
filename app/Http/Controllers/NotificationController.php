<?php

namespace App\Http\Controllers;

use App\Models\UserNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class NotificationController extends Controller
{
    public function index(Request $request): Response
    {
        $user = $request->user();

        abort_unless($user !== null, 403);

        $notifications = UserNotification::query()
            ->forUser($user->id)
            ->latest('created_at')
            ->paginate(12)
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

        return Inertia::render('notifications/Index', [
            'notifications' => $notifications,
        ]);
    }

    public function markRead(Request $request, UserNotification $notification): RedirectResponse
    {
        abort_unless($request->user()?->id === $notification->user_id, 403);

        if ($notification->read_at === null) {
            $notification->forceFill(['read_at' => now()])->save();
        }

        return back();
    }

    public function markAllRead(Request $request): RedirectResponse
    {
        $user = $request->user();
        abort_unless($user !== null, 403);

        UserNotification::query()
            ->forUser($user->id)
            ->unread()
            ->update(['read_at' => now()]);

        return back();
    }

    public function destroy(Request $request, UserNotification $notification): RedirectResponse
    {
        abort_unless($request->user()?->id === $notification->user_id, 403);

        $notification->delete();

        return back();
    }

    public function destroySelected(Request $request): RedirectResponse
    {
        $user = $request->user();
        abort_unless($user !== null, 403);

        $validated = $request->validate([
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['uuid'],
        ]);

        UserNotification::query()
            ->forUser($user->id)
            ->whereIn('id', $validated['ids'])
            ->delete();

        return back();
    }

    public function destroyAll(Request $request): RedirectResponse
    {
        $user = $request->user();
        abort_unless($user !== null, 403);

        UserNotification::query()
            ->forUser($user->id)
            ->delete();

        return back();
    }
}
