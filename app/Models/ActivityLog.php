<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use RuntimeException;

class ActivityLog extends Model
{
    use HasUuids;

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'type',
        'description',
        'performed_by',
        'performed_by_email',
        'performed_by_name',
        'performed_by_role',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function performedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'performed_by');
    }

    public function delete(): ?bool
    {
        throw new RuntimeException('Activity logs are append-only and cannot be deleted.');
    }

    public function forceDelete(): ?bool
    {
        throw new RuntimeException('Activity logs are append-only and cannot be deleted.');
    }

    public static function record(
        string $type,
        string $description,
        ?User $performedBy = null,
        ?string $performedByEmail = null,
        ?string $performedByName = null,
        ?string $performedByRole = null,
        array $metadata = [],
    ): self {
        return self::query()->create([
            'type' => $type,
            'description' => $description,
            'performed_by' => $performedBy?->id,
            'performed_by_email' => $performedBy?->email ?? $performedByEmail,
            'performed_by_name' => $performedBy?->name ?? $performedByName,
            'performed_by_role' => $performedByRole,
            'metadata' => $metadata,
        ]);
    }
}
