<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class RegistrationLink extends Model
{
    use HasFactory, HasUuids;

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'email',
        'token',
        'company_type',
        'status',
    ];

    public function formSubmission(): HasOne
    {
        return $this->hasOne(FormSubmission::class);
    }

    public function submissionAccessTokens(): HasMany
    {
        return $this->hasMany(SubmissionAccessToken::class);
    }
}
