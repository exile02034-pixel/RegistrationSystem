<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Admin\AdminActivityLogService;
use Inertia\Inertia;
use Inertia\Response;

class ActivityLogController extends Controller
{
    public function __construct(
        private readonly AdminActivityLogService $activityLogService,
    ) {
    }

    public function index(): Response
    {
        return Inertia::render('admin/activity-logs/index', [
            'logs' => $this->activityLogService->paginated(10),
        ]);
    }
}
