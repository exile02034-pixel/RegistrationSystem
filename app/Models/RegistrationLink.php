<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class RegistrationLink extends Model
{
    use HasFactory;

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
}
