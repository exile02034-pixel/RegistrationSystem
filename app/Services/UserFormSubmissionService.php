<?php

namespace App\Services;

use App\Models\FormSubmission;
use App\Models\RegistrationLink;
use App\Models\User;
use Illuminate\Support\Collection;

class UserFormSubmissionService
{
    public function __construct(
        private readonly RegistrationFormService $registrationFormService,
        private readonly RegistrationTemplateService $templateService,
    ) {}

    public function getSubmissionsForUser(User $user): array
    {
        return $this->getSubmissionsByEmail($user->email);
    }

    public function getSubmissionsByEmail(string $email): array
    {
        $submissions = FormSubmission::query()
            ->with('registrationLink')
            ->where('email', $email)
            ->latest()
            ->get();

        return $submissions
            ->map(function (FormSubmission $submission): array {
                $registrationLink = $submission->registrationLink;

                if (! $registrationLink instanceof RegistrationLink) {
                    return [];
                }

                return [
                    'registration_id' => $registrationLink->id,
                    'company_type' => $registrationLink->company_type,
                    'company_type_label' => $this->templateService->labelFor($registrationLink->company_type),
                    'registration_status' => $registrationLink->status,
                    'created_at' => $registrationLink->created_at?->toDateTimeString(),
                    'form_submission' => $this->registrationFormService->getStructuredSubmission($registrationLink),
                ];
            })
            ->filter()
            ->values()
            ->all();
    }

    public function getCompanyTypesForUser(User $user): Collection
    {
        return $this->getCompanyTypesByEmail($user->email);
    }

    public function getCompanyTypesByEmail(string $email): Collection
    {
        return RegistrationLink::query()
            ->where('email', $email)
            ->pluck('company_type')
            ->filter(fn ($type) => in_array($type, ['corp', 'sole_prop', 'opc'], true))
            ->unique()
            ->values()
            ->map(fn (string $type) => [
                'value' => $type,
                'label' => $this->templateService->labelFor($type),
            ])
            ->values();
    }
}
