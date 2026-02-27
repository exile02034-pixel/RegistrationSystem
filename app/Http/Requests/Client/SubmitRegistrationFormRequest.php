<?php

namespace App\Http\Requests\Client;

use App\Models\RegistrationLink;
use App\Services\RegistrationFormService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class SubmitRegistrationFormRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $link = RegistrationLink::query()->where('token', $this->route('token'))->first();

        if ($link === null) {
            return [
                'sections' => ['required', 'array'],
            ];
        }

        $schema = app(RegistrationFormService::class)->getSchemaForCompanyType($link->company_type);
        $rules = [
            'sections' => ['required', 'array'],
        ];

        foreach ($schema as $section) {
            $sectionName = $section['name'];
            $rules["sections.{$sectionName}"] = ['required', 'array'];
            foreach ($section['fields'] as $field) {
                $fieldRules = $this->fieldRules($field);
                $rules["sections.{$sectionName}.{$field['name']}"] = $fieldRules;
            }
        }

        return $rules;
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $link = RegistrationLink::query()->where('token', $this->route('token'))->first();

            if ($link === null) {
                return;
            }

            $schema = app(RegistrationFormService::class)->getSchemaForCompanyType($link->company_type);
            $allowedSections = [];

            foreach ($schema as $section) {
                $allowedSections[$section['name']] = array_map(
                    fn (array $field): string => $field['name'],
                    $section['fields'] ?? [],
                );
            }

            $sections = $this->input('sections', []);

            foreach ($sections as $sectionName => $fields) {
                if (! array_key_exists($sectionName, $allowedSections)) {
                    $validator->errors()->add("sections.{$sectionName}", 'Invalid section.');

                    continue;
                }

                foreach ((array) $fields as $fieldName => $value) {
                    if (! in_array($fieldName, $allowedSections[$sectionName], true)) {
                        $validator->errors()->add("sections.{$sectionName}.{$fieldName}", 'Invalid field.');
                    }
                }
            }
        });
    }

    private function fieldRules(array $field): array
    {
        $required = (bool) ($field['required'] ?? false);
        $rules = [$required ? 'required' : 'nullable'];

        $fieldName = (string) ($field['name'] ?? '');
        $type = $field['type'] ?? 'text';

        if ($type === 'date' || str_contains($fieldName, 'date')) {
            $rules[] = 'date';

            return $rules;
        }

        if ($this->isNumericField($fieldName, $type)) {
            $rules[] = 'regex:/^[0-9]+$/';

            if (str_contains($fieldName, 'tin')) {
                $rules[] = 'digits_between:9,15';
            }

            if ($this->isContactNumberField($fieldName)) {
                $rules[] = 'digits_between:7,15';
            }

            return $rules;
        }

        $rules[] = 'string';
        $rules[] = 'max:1000';

        if ($type === 'email' || str_contains($fieldName, 'email')) {
            $rules[] = 'email';
        }

        if ($type === 'select' && ! empty($field['options'])) {
            $rules[] = Rule::in(array_map(
                fn (array $option): string => (string) $option['value'],
                $field['options'],
            ));
        }

        return $rules;
    }

    private function isNumericField(string $fieldName, string $type): bool
    {
        if ($type === 'number') {
            return true;
        }

        return (bool) preg_match('/(tin|mobile|phone|contact_number|_number)$/', $fieldName);
    }

    private function isContactNumberField(string $fieldName): bool
    {
        return (bool) preg_match('/(mobile|phone|contact_number)/', $fieldName);
    }
}
