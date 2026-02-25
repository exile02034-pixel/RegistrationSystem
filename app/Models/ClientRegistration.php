<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class ClientRegistration extends Model
{
    protected $fillable = [
        'name',
        'email',
        'phone',
        'address',
    ];

    // Specify that the primary key is a UUID column
    protected $primaryKey = 'uuid'; // change this to your actual UUID column name
    public $incrementing = false;   // UUIDs are not auto-increment
    protected $keyType = 'string';  // UUIDs are strings

    // Automatically generate UUID when creating a new record
    protected static function booted()
    {
        static::creating(function ($model) {
            if (empty($model->{$model->getKeyName()})) {
                $model->{$model->getKeyName()} = (string) Str::uuid();
            }
        });
    }
}