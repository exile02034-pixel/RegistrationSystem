<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RegistrationUpload extends Model
{
    use HasFactory;

    protected $fillable = [
        'registration_link_id',
        'original_name',
        'stored_name',
        'storage_path',
        'mime_type',
        'size_bytes',
        'extracted_text',
    ];

    public function registrationLink(): BelongsTo
    {
        return $this->belongsTo(RegistrationLink::class);
    }
}
