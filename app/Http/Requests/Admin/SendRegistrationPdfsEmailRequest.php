<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class SendRegistrationPdfsEmailRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'sections' => ['nullable', 'array'],
            'sections.*' => ['required', 'string', 'in:client_information,treasurer_details,opc_details,proprietorship,regular_corporation'],
            'document_ids' => ['nullable', 'array'],
            'document_ids.*' => ['required', 'string', 'uuid'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator): void {
            $sections = array_filter((array) $this->input('sections', []));
            $documentIds = array_filter((array) $this->input('document_ids', []));

            if ($sections === [] && $documentIds === []) {
                $validator->errors()->add('sections', 'Select at least one PDF document to email.');
            }
        });
    }
}
