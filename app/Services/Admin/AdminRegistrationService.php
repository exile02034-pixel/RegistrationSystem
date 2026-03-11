<?php

namespace App\Services\Admin;

use App\Models\RegistrationLink;
use App\Models\User;
use App\Services\ActivityLogService;
use App\Services\DocumentGenerationService;
use App\Services\NotificationService;
use App\Services\RegistrationFormService;
use App\Services\RegistrationTemplateService;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class AdminRegistrationService
{
    public const REQUIRED_DOCUMENT_TYPES = [
        'articles_of_corporation' => 'Articles of Corporation',
        'certificate_of_corporation' => 'Certificate of Corporation',
        'cover_sheet' => 'Cover Sheet',
        'certificate_of_registration' => 'Certificate of Registration',
    ];

    public function __construct(
        private readonly RegistrationTemplateService $templateService,
        private readonly RegistrationFormService $registrationFormService,
        private readonly DocumentGenerationService $documentGenerationService,
        private readonly RequiredDocumentExtractionService $requiredDocumentExtractionService,
        private readonly NotificationService $notificationService,
        private readonly ActivityLogService $activityLogService,
    ) {}

    public function paginatedForIndex(
        string $search,
        ?string $companyType,
        string $sort = 'created_at',
        string $direction = 'desc',
    ): LengthAwarePaginator {
        $normalizedSort = $sort === 'created_at' ? 'created_at' : 'created_at';
        $normalizedDirection = $direction === 'asc' ? 'asc' : 'desc';
        $normalizedCompanyType = in_array($companyType, ['corp', 'sole_prop', 'opc'], true) ? $companyType : null;

        $links = RegistrationLink::query()
            ->with('formSubmission')
            ->when($normalizedCompanyType !== null, function ($query) use ($normalizedCompanyType) {
                $query->where('company_type', $normalizedCompanyType);
            })
            ->when($search !== '', function ($query) use ($search) {
                $searchType = $this->companyTypeFromSearch($search);

                $query->where(function ($inner) use ($search, $searchType) {
                    $inner
                        ->where('email', 'like', "%{$search}%")
                        ->orWhere('token', 'like', "%{$search}%")
                        ->orWhere('company_type', 'like', "%{$search}%");

                    if ($searchType !== null) {
                        $inner->orWhere('company_type', $searchType);
                    }
                });
            })
            ->orderBy($normalizedSort, $normalizedDirection)
            ->paginate(10)
            ->withQueryString();

        $links->setCollection($links->getCollection()->map(fn (RegistrationLink $link) => [
            'id' => $link->id,
            'email' => $link->email,
            'company_type' => $link->company_type,
            'company_type_label' => $this->templateService->labelFor($link->company_type),
            'status' => $link->status,
            'token' => $link->token,
            'form_submitted' => $link->formSubmission !== null,
            'created_at' => $link->created_at?->toDateTimeString(),
            'client_url' => route('registration.form.show', $link->token),
            'show_url' => route('admin.register.show', $link->id),
        ]));

        return $links;
    }

    public function indexPageProps(
        string $search,
        string $sort,
        string $direction,
        ?string $companyType,
    ): array {
        return [
            'links' => $this->paginatedForIndex($search, $companyType, $sort, $direction),
            'companyTypes' => $this->templateService->availableCompanyTypes(),
            'filters' => [
                'search' => $search,
                'sort' => $sort,
                'direction' => $direction,
                'company_type' => in_array($companyType, ['corp', 'sole_prop', 'opc'], true) ? $companyType : '',
            ],
        ];
    }

    public function createPageProps(): array
    {
        return [
            'companyTypes' => $this->templateService->availableCompanyTypes(),
        ];
    }

    public function showPageProps(RegistrationLink $registrationLink): array
    {
        return [
            'registration' => $this->detailsForShow($registrationLink),
        ];
    }

    public function detailsForShow(RegistrationLink $registrationLink): array
    {
        $registrationLink->loadMissing('formSubmission.revisions', 'generatedDocuments.generatedBy', 'requiredDocuments.uploadedBy');
        $revisionCount = $registrationLink->formSubmission?->revisions()->count() ?? 0;
        $lastRevisionAt = $registrationLink->formSubmission?->revisions()->latest('created_at')->first()?->created_at;
        $this->ensureRequiredDocumentExtraction($registrationLink, 'certificate_of_registration', ['business_trade_name', 'date_registered', 'business_address', 'corporate_tin']);
        $this->ensureRequiredDocumentExtraction($registrationLink, 'cover_sheet', ['sec_registration_number', 'industry_classification', 'corporate_name', 'principal_office_address', 'email', 'official_mobile', 'alternate_email', 'alternate_mobile']);
        $this->ensureRequiredDocumentExtraction($registrationLink, 'articles_of_corporation', ['primary_purpose', 'gis_step_3']);
        $registrationLink->load('requiredDocuments.uploadedBy');
        $requiredDocumentsByType = $registrationLink->requiredDocuments->keyBy('document_type');
        $certificateDocument = $requiredDocumentsByType->get('certificate_of_registration');
        $coverSheetDocument = $requiredDocumentsByType->get('cover_sheet');
        $articlesDocument = $requiredDocumentsByType->get('articles_of_corporation');

        $certificatePayload = is_array($certificateDocument?->extraction_payload)
            ? $certificateDocument->extraction_payload
            : [];
        $coverSheetPayload = is_array($coverSheetDocument?->extraction_payload)
            ? $coverSheetDocument->extraction_payload
            : [];
        $articlesPayload = is_array($articlesDocument?->extraction_payload)
            ? $articlesDocument->extraction_payload
            : [];

        $gisAutofill = [
            'corporate_tin' => (string) ($certificatePayload['corporate_tin'] ?? ''),
            'branch_code' => (string) ($certificatePayload['branch_code'] ?? ''),
            'business_address' => (string) ($certificatePayload['business_address'] ?? ''),
            'business_trade_name' => (string) ($certificatePayload['business_trade_name'] ?? ''),
            'date_registered' => (string) ($certificatePayload['date_registered'] ?? ''),
            'sec_registration_number' => (string) ($coverSheetPayload['sec_registration_number'] ?? $certificatePayload['sec_registration_number'] ?? ''),
            'industry_classification' => (string) ($coverSheetPayload['industry_classification'] ?? ''),
            'corporate_name' => (string) ($coverSheetPayload['corporate_name'] ?? ''),
            'principal_office_address' => (string) ($coverSheetPayload['principal_office_address'] ?? ''),
            'email' => (string) ($coverSheetPayload['email'] ?? ''),
            'official_mobile' => (string) ($coverSheetPayload['official_mobile'] ?? ''),
            'alternate_email' => (string) ($coverSheetPayload['alternate_email'] ?? ''),
            'alternate_mobile' => (string) ($coverSheetPayload['alternate_mobile'] ?? ''),
            'primary_purpose' => (string) ($articlesPayload['primary_purpose'] ?? ''),
            'step_3' => is_array($articlesPayload['gis_step_3'] ?? null) ? $articlesPayload['gis_step_3'] : [],
            'aoi_capital_stock_available' => $articlesDocument !== null,
        ];
        $appointmentAutofill = [
            'corporate_tin' => (string) ($certificatePayload['corporate_tin'] ?? ''),
            'complete_business_address' => (string) ($certificatePayload['business_address'] ?? ''),
            'business_trade_name' => (string) ($certificatePayload['business_trade_name'] ?? ''),
            'date_of_registration' => (string) ($certificatePayload['date_registered'] ?? ''),
            'sec_registration_number' => (string) ($coverSheetPayload['sec_registration_number'] ?? ''),
            'corporate_name' => (string) ($coverSheetPayload['corporate_name'] ?? ''),
            'email_address' => (string) ($coverSheetPayload['email'] ?? ''),
            'primary_purpose_activity' => (string) ($articlesPayload['primary_purpose'] ?? ''),
        ];
        $targetFields = [
            'corporate_tin',
            'business_address',
            'business_trade_name',
            'date_registered',
            'sec_registration_number',
            'industry_classification',
            'corporate_name',
            'principal_office_address',
            'email',
            'official_mobile',
            'alternate_email',
            'alternate_mobile',
            'primary_purpose',
        ];
        $missingFields = collect($targetFields)
            ->filter(fn (string $field): bool => trim((string) ($gisAutofill[$field] ?? '')) === '')
            ->values()
            ->all();
        $appointmentTargetFields = [
            'corporate_tin',
            'complete_business_address',
            'business_trade_name',
            'date_of_registration',
            'sec_registration_number',
            'corporate_name',
            'email_address',
            'primary_purpose_activity',
        ];
        $appointmentMissingFields = collect($appointmentTargetFields)
            ->filter(fn (string $field): bool => trim((string) ($appointmentAutofill[$field] ?? '')) === '')
            ->values()
            ->all();
        $hasUploadedSources = collect(['certificate_of_registration', 'cover_sheet', 'articles_of_corporation'])
            ->contains(fn (string $type): bool => $requiredDocumentsByType->get($type) !== null);

        return [
            'id' => $registrationLink->id,
            'email' => $registrationLink->email,
            'token' => $registrationLink->token,
            'company_type' => $registrationLink->company_type,
            'company_type_label' => $this->templateService->labelFor($registrationLink->company_type),
            'status' => $registrationLink->status,
            'created_at' => $registrationLink->created_at?->toDateTimeString(),
            'form_submission' => $this->registrationFormService->getStructuredSubmission($registrationLink),
            'generated_documents' => $registrationLink->generatedDocuments
                ->sortByDesc('created_at')
                ->values()
                ->map(fn ($document) => [
                    'id' => $document->id,
                    'document_type' => $document->document_type,
                    'document_name' => $document->document_name,
                    'created_at' => $document->created_at?->toDateTimeString(),
                    'generated_by' => $document->generatedBy?->name,
                    'view_url' => route('admin.register.documents.view', [$registrationLink->id, $document->id]),
                    'download_url' => route('admin.register.documents.download', [$registrationLink->id, $document->id]),
                    'delete_url' => route('admin.register.documents.destroy', [$registrationLink->id, $document->id]),
                ])
                ->all(),
            'document_forms' => $this->documentGenerationService->availableDocuments(),
            'gis_autofill' => array_merge($gisAutofill, [
                'has_uploaded_sources' => $hasUploadedSources,
                'missing_fields' => $missingFields,
            ]),
            'appointment_autofill' => array_merge($appointmentAutofill, [
                'has_uploaded_sources' => $hasUploadedSources,
                'missing_fields' => $appointmentMissingFields,
            ]),
            'required_documents' => collect(self::REQUIRED_DOCUMENT_TYPES)
                ->map(function (string $label, string $type) use ($registrationLink, $requiredDocumentsByType): array {
                    $uploaded = $requiredDocumentsByType->get($type);

                    return [
                        'type' => $type,
                        'name' => $label,
                        'is_uploaded' => $uploaded !== null,
                        'original_filename' => $uploaded?->original_filename,
                        'uploaded_at' => $uploaded?->created_at?->toDateTimeString(),
                        'uploaded_by' => $uploaded?->uploadedBy?->name,
                        'upload_url' => route('admin.register.required-documents.upload', $registrationLink->id),
                        'view_url' => $uploaded !== null ? route('admin.register.required-documents.view', [$registrationLink->id, $uploaded->id]) : null,
                        'download_url' => $uploaded !== null ? route('admin.register.required-documents.download', [$registrationLink->id, $uploaded->id]) : null,
                        'delete_url' => $uploaded !== null ? route('admin.register.required-documents.destroy', [$registrationLink->id, $uploaded->id]) : null,
                    ];
                })
                ->values()
                ->all(),
            'revision_count' => $revisionCount,
            'last_revision_at' => $lastRevisionAt?->toDateTimeString(),
        ];
    }

    /**
     * @param array<int, string> $requiredKeys
     */
    private function ensureRequiredDocumentExtraction(
        RegistrationLink $registrationLink,
        string $documentType,
        array $requiredKeys,
    ): void
    {
        $document = $registrationLink->requiredDocuments()
            ->where('document_type', $documentType)
            ->first();

        if ($document === null) {
            return;
        }

        $existingPayload = is_array($document->extraction_payload)
            ? $document->extraction_payload
            : [];
        $missingRequiredValues = collect($requiredKeys)->some(
            fn (string $key): bool => $this->isExtractionValueMissing($existingPayload[$key] ?? null)
        );

        if ($existingPayload !== [] && $missingRequiredValues === false) {
            return;
        }

        if (! Storage::disk('local')->exists($document->file_path)) {
            return;
        }

        $absolutePath = Storage::disk('local')->path($document->file_path);
        $payload = $this->requiredDocumentExtractionService->extractFieldsForDocument(
            documentType: $documentType,
            absolutePath: $absolutePath,
            originalFilename: $document->original_filename,
        );

        if ($payload === []) {
            return;
        }

        $document->update([
            'extraction_payload' => array_merge($existingPayload, $payload),
        ]);
    }

    private function isExtractionValueMissing(mixed $value): bool
    {
        if ($value === null) {
            return true;
        }

        if (is_string($value)) {
            return trim($value) === '';
        }

        if (is_array($value)) {
            return $value === [];
        }

        return false;
    }

    public function deleteRegistration(RegistrationLink $registrationLink, ?User $performedBy): void
    {
        DB::transaction(function () use ($registrationLink, $performedBy): void {
            $email = $registrationLink->email;
            $companyType = $registrationLink->company_type;
            $companyTypeLabel = $this->templateService->labelFor($companyType);
            $registrationId = $registrationLink->id;
            $clientName = $this->guessClientNameFromEmail($email);
            $linkedUser = User::query()
                ->where('role', 'user')
                ->where('email', $email)
                ->first();

            $registrationLink->delete();

            $this->notificationService->notifyAdmins(
                category: 'registration_deleted',
                title: 'Registration deleted',
                message: "Registration record for {$email} was deleted.",
                actionUrl: route('admin.register.index'),
                meta: ['email' => $email],
            );

            $this->activityLogService->log(
                type: 'admin.registration.deleted',
                description: "Admin deleted registration of {$clientName} ({$email}) - {$companyTypeLabel}",
                performedBy: $performedBy,
                metadata: [
                    'registration_id' => $registrationId,
                    'email' => $email,
                    'company_type' => $companyType,
                    'company_type_label' => $companyTypeLabel,
                ],
            );

            if ($linkedUser && in_array($companyType, ['corp', 'sole_prop', 'opc'], true)) {
                $this->activityLogService->log(
                    type: 'admin.user.company_type.removed',
                    description: "Admin removed {$companyTypeLabel} from {$linkedUser->name} ({$linkedUser->email})",
                    performedBy: $performedBy,
                    metadata: [
                        'user_id' => $linkedUser->id,
                        'user_email' => $linkedUser->email,
                        'user_name' => $linkedUser->name,
                        'company_type' => $companyType,
                        'company_type_label' => $companyTypeLabel,
                        'registration_id' => $registrationId,
                    ],
                );
            }
        });
    }

    public function setStatus(RegistrationLink $registrationLink, string $status): void
    {
        $registrationLink->update(['status' => $status]);
    }

    private function companyTypeFromSearch(string $search): ?string
    {
        $innerSearch = strtolower($search);

        return match (true) {
            str_contains($innerSearch, 'opc') => 'opc',
            str_contains($innerSearch, 'sole') || str_contains($innerSearch, 'prop') || str_contains($innerSearch, 'proprietorship') => 'sole_prop',
            str_contains($innerSearch, 'corp') || str_contains($innerSearch, 'corporation') || str_contains($innerSearch, 'regular') => 'corp',
            default => null,
        };
    }

    private function guessClientNameFromEmail(string $email): string
    {
        $localPart = explode('@', $email)[0] ?? $email;
        $normalized = trim(str_replace(['.', '_', '-'], ' ', $localPart));

        return $normalized !== '' ? ucwords($normalized) : $email;
    }
}
