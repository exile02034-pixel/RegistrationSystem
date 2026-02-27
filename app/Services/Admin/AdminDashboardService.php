<?php

namespace App\Services\Admin;

use App\Models\FormSubmission;
use App\Models\RegistrationLink;
use App\Models\User;

class AdminDashboardService
{
    public function __construct(
        private readonly AdminActivityLogService $activityLogService,
    ) {
    }

    public function dashboardData(): array
    {
        return [
            'stats' => [
                'totalUsers' => User::where('role', 'user')->count(),
                'pendingUsers' => RegistrationLink::where('status', 'pending')->count(),
                'acceptedUsers' => RegistrationLink::where('status', 'completed')->count(),
                'totalSubmissions' => FormSubmission::count(),
            ],
            'recentActivities' => $this->activityLogService->recent(5),
        ];
    }
}
