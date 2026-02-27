<?php

namespace App\Services\Admin;

use App\Models\ActivityLog;
use Illuminate\Pagination\LengthAwarePaginator;

class AdminActivityLogService
{
    public function recent(int $limit = 5): array
    {
        return ActivityLog::query()
            ->latest()
            ->limit($limit)
            ->get()
            ->map(fn (ActivityLog $log) => $this->mapLog($log))
            ->all();
    }

    public function paginated(int $perPage = 10): LengthAwarePaginator
    {
        $logs = ActivityLog::query()
            ->latest()
            ->paginate($perPage)
            ->withQueryString();

        $logs->setCollection($logs->getCollection()->map(fn (ActivityLog $log) => $this->mapLog($log)));

        return $logs;
    }

    private function mapLog(ActivityLog $log): array
    {
        $metadata = is_array($log->metadata) ? $log->metadata : [];

        return [
            'id' => $log->id,
            'type' => $log->type,
            'description' => $log->description,
            'performed_by_name' => $log->performed_by_name,
            'performed_by_email' => $log->performed_by_email,
            'performed_by_role' => $log->performed_by_role,
            'company_type' => $metadata['company_type'] ?? null,
            'metadata' => $metadata,
            'created_at' => $log->created_at?->toIso8601String(),
        ];
    }
}
