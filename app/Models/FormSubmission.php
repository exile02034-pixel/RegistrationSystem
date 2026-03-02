<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FormSubmission extends Model
{
    use HasFactory, HasUuids;

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'registration_link_id',
        'email',
        'company_type',
        'status',
        'submitted_at',
    ];

    protected $casts = [
        'submitted_at' => 'datetime',
    ];

    public function registrationLink(): BelongsTo
    {
        return $this->belongsTo(RegistrationLink::class);
    }

    public function fields(): HasMany
    {
        return $this->hasMany(FormSubmissionField::class);
    }

    public function revisions(): HasMany
    {
        return $this->hasMany(FormSubmissionRevision::class);
    }
}
