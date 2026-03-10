<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RegistrationRequiredDocument extends Model
{
    use HasFactory, HasUuids;

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'registration_link_id',
        'document_type',
        'document_name',
        'original_filename',
        'file_path',
        'mime_type',
        'extraction_payload',
        'uploaded_by',
    ];

    protected $casts = [
        'extraction_payload' => 'array',
    ];

    public function registrationLink(): BelongsTo
    {
        return $this->belongsTo(RegistrationLink::class);
    }

    public function uploadedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}
