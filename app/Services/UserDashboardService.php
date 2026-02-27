<?php

namespace App\Services;

use App\Models\FormSubmission;
use App\Models\User;

class UserDashboardService
{
    public function getStatsForUser(User $user): array
    {
        $baseQuery = FormSubmission::query()
            ->where('email', $user->email);

        $latestSubmissionAt = (clone $baseQuery)
            ->latest()
            ->value('created_at');

        return [
            'totalSubmissions' => (clone $baseQuery)->count(),
            'completedSubmissions' => (clone $baseQuery)->where('status', 'completed')->count(),
            'latestSubmissionAt' => $latestSubmissionAt?->toDateTimeString(),
        ];
    }
}
