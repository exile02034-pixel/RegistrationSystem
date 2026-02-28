<?php

namespace App\Services;

use App\Models\FormSubmission;
use App\Models\FormSubmissionRevision;
use App\Models\RegistrationLink;
use App\Models\User;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use InvalidArgumentException;

class RegistrationFormService
{
    public function __construct(
        private readonly ActivityLogService $activityLogService,
    ) {}

    public function getSchemaForCompanyType(string $companyType): array
    {
        $sectionsConfig = config('registration_forms.sections', []);
        $companyTypeSectionMap = config('registration_forms.company_type_sections', []);

        $alwaysIncluded = ['client_information', 'treasurer_details'];
        $companySpecific = $companyTypeSectionMap[$companyType] ?? null;
        $sectionNames = array_values(array_filter(array_merge($alwaysIncluded, [$companySpecific])));

        return array_values(array_map(function (string $sectionName) use ($sectionsConfig): array {
            $section = $sectionsConfig[$sectionName] ?? ['label' => Str::title(str_replace('_', ' ', $sectionName)), 'fields' => []];

            return [
                'name' => $sectionName,
                'label' => $section['label'] ?? $sectionName,
                'fields' => array_values($section['fields'] ?? []),
            ];
        }, $sectionNames));
    }

    public function saveSubmission(RegistrationLink $link, array $data): FormSubmission
    {
        $schema = $this->getSchemaForCompanyType($link->company_type);
        $allowedFieldsBySection = $this->allowedFieldsBySection($schema);
        $submittedSections = Arr::get($data, 'sections', []);

        $submission = FormSubmission::query()->updateOrCreate(
            ['registration_link_id' => $link->id],
            [
                'email' => $link->email,
                'company_type' => $link->company_type,
                'status' => 'completed',
                'submitted_at' => now(),
            ],
        );
        $isResubmission = ! $submission->wasRecentlyCreated;

        $payload = [];

        foreach ($allowedFieldsBySection as $section => $fields) {
            $values = Arr::get($submittedSections, $section, []);

            foreach ($fields as $fieldName) {
                $value = Arr::get($values, $fieldName);

                $payload[] = [
                    'id' => (string) Str::uuid(),
                    'form_submission_id' => $submission->id,
                    'section' => $section,
                    'field_name' => $fieldName,
                    'field_value' => $this->normalizeFieldValue($value),
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
        }

        $submission->fields()->delete();
        $submission->fields()->insert($payload);
        $submission->refresh()->load('fields');
        $revisionNumber = $this->captureRevision(
            submission: $submission,
            event: $isResubmission ? 'resubmitted' : 'submitted',
            actorType: 'client',
            actorIdentifier: $link->email,
        );

        $this->activityLogService->log(
            type: $isResubmission ? 'client.form.resubmitted' : 'client.form.submitted',
            description: $isResubmission
                ? "Client {$link->email} revised and resubmitted their registration form"
                : "Client {$link->email} submitted their registration form",
            guestEmail: $link->email,
            guestName: $this->displayNameFromEmail($link->email),
            role: 'client',
            metadata: [
                'registration_id' => $link->id,
                'submission_id' => $submission->id,
                'company_type' => $link->company_type,
                'revision_number' => $revisionNumber,
                'sections_count' => count($allowedFieldsBySection),
            ],
        );

        return $submission;
    }

    public function getSubmission(RegistrationLink $link): ?FormSubmission
    {
        return FormSubmission::query()
            ->with('fields')
            ->where('registration_link_id', $link->id)
            ->first();
    }

    public function getStructuredSubmission(RegistrationLink $link): ?array
    {
        $submission = $this->getSubmission($link);

        if ($submission === null) {
            return null;
        }

        $schema = $this->getSchemaForCompanyType($link->company_type);
        $values = $submission->fields
            ->mapWithKeys(fn ($field) => ["{$field->section}.{$field->field_name}" => $field->field_value])
            ->all();

        $sections = array_map(function (array $section) use ($values): array {
            $sectionName = $section['name'];
            $fields = array_map(function (array $field) use ($sectionName, $values): array {
                return [
                    'name' => $field['name'],
                    'label' => $field['label'],
                    'type' => $field['type'] ?? 'text',
                    'required' => (bool) ($field['required'] ?? false),
                    'options' => array_values($field['options'] ?? []),
                    'value' => Arr::get($values, "{$sectionName}.{$field['name']}"),
                ];
            }, $section['fields']);

            return [
                'name' => $sectionName,
                'label' => $section['label'],
                'fields' => $fields,
            ];
        }, $schema);

        return [
            'id' => $submission->id,
            'email' => $submission->email,
            'status' => $submission->status,
            'submitted_at' => $submission->submitted_at?->toDateTimeString(),
            'sections' => $sections,
        ];
    }

    public function updateSection(FormSubmission $submission, string $section, array $fields, User $updatedBy): void
    {
        $schema = $this->getSchemaForCompanyType($submission->company_type);
        $allowedFieldsBySection = $this->allowedFieldsBySection($schema);
        $allowedFields = $allowedFieldsBySection[$section] ?? null;

        if (! is_array($allowedFields)) {
            throw new InvalidArgumentException("Unknown section: {$section}");
        }

        $updates = Arr::only($fields, $allowedFields);
        $updatedFieldNames = [];

        foreach ($updates as $fieldName => $fieldValue) {
            $normalizedValue = $this->normalizeFieldValue($fieldValue);

            $existing = $submission->fields()
                ->where('section', $section)
                ->where('field_name', $fieldName)
                ->first();

            if ($existing !== null && $existing->field_value === $normalizedValue) {
                continue;
            }

            $submission->fields()->updateOrCreate(
                [
                    'form_submission_id' => $submission->id,
                    'section' => $section,
                    'field_name' => $fieldName,
                ],
                ['field_value' => $normalizedValue],
            );

            $updatedFieldNames[] = $fieldName;
        }

        if ($updatedFieldNames === []) {
            return;
        }

        $sectionLabel = collect($schema)->firstWhere('name', $section)['label'] ?? Str::title(str_replace('_', ' ', $section));
        $clientEmail = $submission->registrationLink?->email ?? $submission->email;

        $description = $updatedBy->role === 'admin'
            ? "Admin {$updatedBy->name} ({$updatedBy->email}) updated the {$sectionLabel} form for client {$clientEmail}"
            : "User {$updatedBy->name} ({$updatedBy->email}) updated their {$sectionLabel} form";

        $revisionNumber = $this->captureRevision(
            submission: $submission->refresh()->load('fields'),
            event: 'section_updated',
            actorType: $updatedBy->role,
            actorIdentifier: $updatedBy->email,
        );

        $this->activityLogService->log(
            type: 'form.section.updated',
            description: $description,
            performedBy: $updatedBy,
            metadata: [
                'submission_id' => $submission->id,
                'section' => $section,
                'section_label' => $sectionLabel,
                'updated_fields' => $updatedFieldNames,
                'client_email' => $clientEmail,
                'revision_number' => $revisionNumber,
            ],
        );
    }

    private function allowedFieldsBySection(array $schema): array
    {
        $result = [];

        foreach ($schema as $section) {
            $result[$section['name']] = array_map(
                fn (array $field): string => $field['name'],
                $section['fields'] ?? [],
            );
        }

        return $result;
    }

    private function normalizeFieldValue(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        if (is_bool($value)) {
            return $value ? '1' : '0';
        }

        if (is_scalar($value)) {
            $trimmed = trim((string) $value);

            return $trimmed === '' ? null : $trimmed;
        }

        return json_encode($value);
    }

    private function captureRevision(
        FormSubmission $submission,
        string $event,
        string $actorType,
        ?string $actorIdentifier = null,
    ): int {
        $nextRevision = ((int) $submission->revisions()->max('revision_number')) + 1;

        $snapshot = [
            'status' => $submission->status,
            'submitted_at' => $submission->submitted_at?->toDateTimeString(),
            'sections' => $submission->fields
                ->groupBy('section')
                ->map(fn ($rows, $section) => [
                    'name' => $section,
                    'fields' => $rows->map(fn ($row) => [
                        'name' => $row->field_name,
                        'value' => $row->field_value,
                    ])->values()->all(),
                ])
                ->values()
                ->all(),
        ];

        FormSubmissionRevision::query()->create([
            'form_submission_id' => $submission->id,
            'revision_number' => $nextRevision,
            'event' => $event,
            'actor_type' => $actorType,
            'actor_identifier' => $actorIdentifier,
            'snapshot' => $snapshot,
        ]);

        return $nextRevision;
    }

    private function displayNameFromEmail(string $email): string
    {
        $localPart = explode('@', $email)[0] ?? $email;
        $normalized = trim(str_replace(['.', '_', '-'], ' ', $localPart));

        return $normalized !== '' ? ucwords($normalized) : $email;
    }
}
