<?php

namespace App\Http\Resources\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AdminUserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => data_get($this->resource, 'id'),
            'name' => data_get($this->resource, 'name'),
            'email' => data_get($this->resource, 'email'),
            'status' => data_get($this->resource, 'status'),
            'created_at' => data_get($this->resource, 'created_at'),
            'company_types' => data_get($this->resource, 'company_types', []),
            'company_type_values' => data_get($this->resource, 'company_type_values', []),
            'uploads_count' => data_get($this->resource, 'uploads_count', 0),
            'show_url' => data_get($this->resource, 'show_url'),
        ];
    }
}
