<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateFormSectionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'section' => $this->route('section'),
        ]);
    }

    public function rules(): array
    {
        return [
            'fields' => ['required', 'array'],
            'fields.*' => ['nullable', 'string'],
            'section' => [
                'required',
                'string',
                'in:client_information,treasurer_details,opc_details,proprietorship,regular_corporation',
            ],
        ];
    }
}
