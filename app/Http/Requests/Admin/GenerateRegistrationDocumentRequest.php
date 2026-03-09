<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class GenerateRegistrationDocumentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $documentType = (string) $this->route('documentType');

        $rules = [
            'fields' => ['required', 'array'],
            'fields_json' => ['nullable', 'string'],
        ];

        if ($documentType === 'secretary_certificate') {
            return array_merge($rules, [
                'fields.secretary_name' => ['required', 'string', 'max:255'],
                'fields.secretary_address' => ['required', 'string', 'max:1000'],
                'fields.secretary_signature_data_uri' => ['nullable', 'string', 'max:1048576'],
                'fields.signatureDataUri' => ['nullable', 'string', 'max:1048576'],
                'fields.corporation_name' => ['required', 'string', 'max:255'],
                'fields.corporation_address' => ['required', 'string', 'max:1000'],
                'fields.authorized_person_name' => ['required', 'string', 'max:255'],
                'fields.signing_date' => ['required', 'date'],
                'fields.tin' => ['required', 'string', 'max:50', 'regex:/^\d{3}-\d{3}-\d{3}(?:-\d{3,4})?$/'],
                'fields.doc_no' => ['nullable', 'digits_between:1,20'],
                'fields.page_no' => ['nullable', 'digits_between:1,20'],
                'fields.book_no' => ['nullable', 'digits_between:1,20'],
                'fields.series' => ['nullable', 'digits_between:1,20'],
            ]);
        }

        if ($documentType === 'appointment_form_opc') {
            return $rules;
        }

        if ($documentType === 'gis_stock_corporation') {
            return $rules;
        }

        return [
            'document' => ['required', 'in:secretary_certificate,appointment_form_opc,gis_stock_corporation'],
        ];
    }
}
