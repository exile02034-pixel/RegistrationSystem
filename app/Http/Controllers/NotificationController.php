<?php

namespace App\Http\Controllers;

use App\Models\UserNotification;
use App\Services\NotificationCenterService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class NotificationController extends Controller
{
    public function __construct(
        private readonly NotificationCenterService $notificationCenterService,
    ) {}

    public function index(Request $request): Response
    {
        $user = $request->user();

        abort_unless($user !== null, 403);

        return Inertia::render('notifications/Index', $this->notificationCenterService->indexPageProps($user));
    }

    public function markRead(Request $request, UserNotification $notification): RedirectResponse
    {
        $user = $request->user();
        abort_unless($user !== null, 403);

        $this->notificationCenterService->markRead($user, $notification);

        return back();
    }

    public function markAllRead(Request $request): RedirectResponse
    {
        $user = $request->user();
        abort_unless($user !== null, 403);

        $this->notificationCenterService->markAllRead($user);

        return back();
    }

    public function destroy(Request $request, UserNotification $notification): RedirectResponse
    {
        $user = $request->user();
        abort_unless($user !== null, 403);

        $this->notificationCenterService->delete($user, $notification);

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

        $this->notificationCenterService->deleteSelected($user, $validated['ids']);

        return back();
    }

    public function destroyAll(Request $request): RedirectResponse
    {
        $user = $request->user();
        abort_unless($user !== null, 403);

        $this->notificationCenterService->deleteAll($user);

        return back();
    }
}
