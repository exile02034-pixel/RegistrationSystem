<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

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
        ];

        if ($documentType === 'secretary_certificate') {
            return array_merge($rules, [
                'fields.secretary_name' => ['required', 'string', 'max:255'],
                'fields.secretary_address' => ['required', 'string', 'max:1000'],
                'fields.corporation_name' => ['required', 'string', 'max:255'],
                'fields.corporation_address' => ['required', 'string', 'max:1000'],
                'fields.authorized_person_name' => ['required', 'string', 'max:255'],
                'fields.signing_date' => ['required', 'date'],
                'fields.tin' => ['required', 'string', 'max:50', 'regex:/^\d{3}-\d{3}-\d{3}(?:-\d{3,4})?$/'],
                'fields.doc_no' => ['required', 'digits_between:1,20'],
                'fields.page_no' => ['required', 'digits_between:1,20'],
                'fields.book_no' => ['required', 'digits_between:1,20'],
                'fields.series' => ['required', 'digits_between:1,20'],
            ]);
        }

        if ($documentType === 'appointment_form_opc') {
            return array_merge($rules, [
                'fields.corporate_name' => ['required', 'string', 'max:255'],
                'fields.trade_name' => ['nullable', 'string', 'max:255'],
                'fields.sec_registration_number' => ['required', 'string', 'max:255'],
                'fields.date_of_registration' => ['required', 'date'],
                'fields.fiscal_year_end' => ['required', 'date'],
                'fields.complete_business_address' => ['required', 'string', 'max:1000'],
                'fields.email_address' => ['required', 'email', 'max:255'],
                'fields.telephone_number' => ['required', 'string', 'max:50', 'regex:/^\+?[0-9()\-.\s]{6,20}$/'],
                'fields.corporate_tin' => ['required', 'string', 'max:50', 'regex:/^\d{3}-\d{3}-\d{3}(?:-\d{3,4})?$/'],
                'fields.primary_purpose_activity' => ['required', 'string', 'max:1000'],
                'fields.officers' => ['required', 'array', 'size:3'],
                'fields.officers.*.role' => ['required', Rule::in(['President', 'Treasurer', 'Corporate Secretary'])],
                'fields.officers.*.name_and_residential_address' => ['required', 'string', 'max:1000'],
                'fields.officers.*.nationality' => ['required', 'string', 'max:100'],
                'fields.officers.*.gender' => ['required', 'string', 'max:50'],
                'fields.officers.*.tin' => ['required', 'string', 'max:50', 'regex:/^\d{3}-\d{3}-\d{3}(?:-\d{3,4})?$/'],
                'fields.certifier_name' => ['required', 'string', 'max:255'],
                'fields.certifier_tin' => ['required', 'string', 'max:50', 'regex:/^\d{3}-\d{3}-\d{3}(?:-\d{3,4})?$/'],
            ]);
        }

        if ($documentType === 'gis_stock_corporation') {
            return $rules;
        }

        return [
            'document' => ['required', 'in:secretary_certificate,appointment_form_opc,gis_stock_corporation'],
        ];
    }
}
