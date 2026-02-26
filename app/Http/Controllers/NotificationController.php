<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Support\Collection;
use Inertia\Inertia;
use Inertia\Response;

class NotificationController extends Controller
{
    public function index(Request $request): Response
    {
        $items = $request->user()
            ?->notifications()
            ->latest()
            ->limit(100)
            ->get()
            ->map(fn (DatabaseNotification $notification) => [
                'id' => $notification->id,
                'title' => (string) ($notification->data['title'] ?? 'Notification'),
                'message' => (string) ($notification->data['message'] ?? ''),
                'time' => $notification->created_at?->diffForHumans(),
                'is_read' => (bool) $notification->read_at,
                'open_url' => route('notifications.open', $notification->id),
                'target_url' => (string) ($notification->data['url'] ?? route('notifications.index')),
            ]) ?? collect();

        return Inertia::render('notifications/Index', [
            'items' => $items,
        ]);
    }

    public function open(Request $request, string $notification): RedirectResponse
    {
        $item = $request->user()
            ?->notifications()
            ->whereKey($notification)
            ->firstOrFail();

        if (! $item->read_at) {
            $item->markAsRead();
        }

        return redirect()->to((string) ($item->data['url'] ?? route('notifications.index')));
    }

    public function markAllRead(Request $request): RedirectResponse
    {
        $request->user()?->unreadNotifications->markAsRead();

        return back();
    }

    public function markRead(Request $request, string $notification): RedirectResponse
    {
        $item = $request->user()
            ?->notifications()
            ->whereKey($notification)
            ->firstOrFail();

        if (! $item->read_at) {
            $item->markAsRead();
        }

        return back();
    }

    public function destroy(Request $request, string $notification): RedirectResponse
    {
        $item = $request->user()
            ?->notifications()
            ->whereKey($notification)
            ->firstOrFail();

        $item->delete();

        return back();
    }

    public function clearAll(Request $request): RedirectResponse
    {
        $request->user()?->notifications()->delete();

        return back();
    }

    public function markSelectedRead(Request $request): RedirectResponse
    {
        $ids = $this->validatedIds($request);

        if ($ids->isEmpty()) {
            return back();
        }

        $request->user()
            ?->notifications()
            ->whereIn('id', $ids->all())
            ->whereNull('read_at')
            ->get()
            ->each
            ->markAsRead();

        return back();
    }

    public function deleteSelected(Request $request): RedirectResponse
    {
        $ids = $this->validatedIds($request);

        if ($ids->isEmpty()) {
            return back();
        }

        $request->user()
            ?->notifications()
            ->whereIn('id', $ids->all())
            ->delete();

        return back();
    }

    private function validatedIds(Request $request): Collection
    {
        $validated = $request->validate([
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['required', 'string'],
        ]);

        return collect($validated['ids'])
            ->filter(fn ($id) => is_string($id) && $id !== '')
            ->values();
    }
}
