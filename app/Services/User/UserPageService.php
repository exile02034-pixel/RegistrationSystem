<?php

namespace App\Services\User;

use App\Models\User;
use App\Services\UserDashboardService;
use App\Services\UserFormSubmissionService;

class UserPageService
{
    public function __construct(
        private readonly UserDashboardService $dashboardService,
        private readonly UserFormSubmissionService $formSubmissionService,
    ) {}

    public function dashboardPageProps(User $user): array
    {
        return [
            'stats' => $this->dashboardService->getStatsForUser($user),
        ];
    }

    public function filesPageProps(User $user): array
    {
        return [
            'submissions' => $this->formSubmissionService->getSubmissionsForUser($user),
            'clientInfo' => [
                'name' => $user->name,
                'email' => $user->email,
                'company_types' => $this->formSubmissionService->getCompanyTypesForUser($user)->all(),
            ],
        ];
    }
}
