<?php

namespace App\Services;

use App\Models\RegistrationUpload;
use App\Models\User;

class UserDashboardService
{
    public function getStatsForUser(User $user): array
    {
        $baseQuery = RegistrationUpload::query()
            ->whereHas('registrationLink', fn ($query) => $query->where('email', $user->email));

        $latestSubmissionAt = (clone $baseQuery)
            ->latest()
            ->value('created_at');

        return [
            'totalUploads' => (clone $baseQuery)->count(),
            'pdfUploads' => (clone $baseQuery)
                ->where(fn ($query) => $query
                    ->where('mime_type', 'application/pdf')
                    ->orWhere('original_name', 'like', '%.pdf'))
                ->count(),
            'latestSubmissionAt' => $latestSubmissionAt?->toDateTimeString(),
        ];
    }
}
