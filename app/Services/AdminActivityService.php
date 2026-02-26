<?php

namespace App\Services;

use App\Models\AdminActivity;
use App\Models\User;

class AdminActivityService
{
    public function log(
        ?User $admin,
        string $action,
        string $title,
        ?string $description = null,
        ?string $url = null,
        array $metadata = [],
    ): void {
        AdminActivity::query()->create([
            'user_id' => $admin?->id,
            'action' => $action,
            'title' => $title,
            'description' => $description,
            'url' => $url,
            'metadata' => $metadata === [] ? null : $metadata,
        ]);
    }
}
