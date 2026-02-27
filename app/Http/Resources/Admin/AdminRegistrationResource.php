<?php

namespace App\Http\Resources\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AdminRegistrationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => data_get($this->resource, 'id'),
            'email' => data_get($this->resource, 'email'),
            'company_type' => data_get($this->resource, 'company_type'),
            'company_type_label' => data_get($this->resource, 'company_type_label'),
            'status' => data_get($this->resource, 'status'),
            'token' => data_get($this->resource, 'token'),
            'form_submitted' => data_get($this->resource, 'form_submitted', false),
            'created_at' => data_get($this->resource, 'created_at'),
            'client_url' => data_get($this->resource, 'client_url'),
            'show_url' => data_get($this->resource, 'show_url'),
            'uploads' => data_get($this->resource, 'uploads', []),
            'form_submission' => data_get($this->resource, 'form_submission'),
        ];
    }
}
