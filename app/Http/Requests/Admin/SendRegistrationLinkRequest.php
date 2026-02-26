<?php

namespace App\Http\Requests\Admin;

use App\Services\RegistrationTemplateService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SendRegistrationLinkRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'email' => ['required', 'email', 'max:255'],
            'company_type' => [
                'required',
                Rule::in([
                    RegistrationTemplateService::TYPE_CORP,
                    RegistrationTemplateService::TYPE_SOLE_PROP,
                    RegistrationTemplateService::TYPE_OPC,
                ]),
            ],
        ];
    }
}
