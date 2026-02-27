<?php

namespace App\Http\Requests\Client;

use App\Models\RegistrationLink;
use App\Services\RegistrationTemplateService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StoreClientUploadsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'files' => ['required', 'array', 'min:1'],
            'files.*' => ['required', 'file', 'max:10240', 'mimes:docx'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $token = (string) $this->route('token');
            $registrationLink = RegistrationLink::query()->where('token', $token)->first();

            if ($registrationLink === null) {
                return;
            }

            $files = $this->file('files') ?? [];
            $expectedNames = collect(app(RegistrationTemplateService::class)->templatesFor($registrationLink->company_type))
                ->pluck('name')
                ->all();

            $uploadedNames = collect($files)
                ->map(fn ($file) => $file->getClientOriginalName())
                ->values()
                ->all();

            if (count(array_unique($uploadedNames)) !== count($uploadedNames)) {
                $validator->errors()->add('files', 'Please upload each template file only once.');
                return;
            }

            $invalidNames = array_values(array_diff($uploadedNames, $expectedNames));

            if ($invalidNames !== []) {
                $validator->errors()->add('files', 'Please upload only the official template files without renaming them.');
            }
        });
    }
}
