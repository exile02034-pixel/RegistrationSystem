<?php

namespace App\Services;

use App\Models\ActivityLog;
use App\Models\User;

class ActivityLogService
{
    public function log(
        string $type,
        string $description,
        ?User $performedBy = null,
        ?string $guestEmail = null,
        ?string $guestName = null,
        string $role = 'client',
        array $metadata = [],
    ): void {
        ActivityLog::create([
            'type' => $type,
            'description' => $description,
            'performed_by' => $performedBy?->id,
            'performed_by_email' => $performedBy?->email ?? $guestEmail,
            'performed_by_name' => $performedBy?->name ?? $guestName,
            'performed_by_role' => $performedBy?->role ?? $role,
            'metadata' => $metadata,
        ]);
    }
}
