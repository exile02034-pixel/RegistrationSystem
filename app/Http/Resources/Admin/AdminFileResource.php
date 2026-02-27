<?php

namespace App\Http\Resources\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AdminFileResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => data_get($this->resource, 'id'),
            'registration_link_id' => data_get($this->resource, 'registration_link_id'),
            'company_type' => data_get($this->resource, 'company_type'),
            'company_type_label' => data_get($this->resource, 'company_type_label'),
            'original_name' => data_get($this->resource, 'original_name'),
            'mime_type' => data_get($this->resource, 'mime_type'),
            'size_bytes' => data_get($this->resource, 'size_bytes'),
            'created_at' => data_get($this->resource, 'created_at'),
            'view_url' => data_get($this->resource, 'view_url'),
            'download_url' => data_get($this->resource, 'download_url'),
            'download_pdf_url' => data_get($this->resource, 'download_pdf_url'),
            'delete_url' => data_get($this->resource, 'delete_url'),
            'can_convert_pdf' => (bool) data_get($this->resource, 'can_convert_pdf', false),
        ];
    }
}
