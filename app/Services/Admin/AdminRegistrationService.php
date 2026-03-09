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
        $this->ensureCertificateExtraction($registrationLink);
        $registrationLink->loadMissing('requiredDocuments.uploadedBy');
        $requiredDocumentsByType = $registrationLink->requiredDocuments->keyBy('document_type');
        $certificateOfRegistration = $requiredDocumentsByType->get('certificate_of_registration');
        $gisAutofill = is_array($certificateOfRegistration?->extraction_payload)
            ? $certificateOfRegistration->extraction_payload
            : [];

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
            'gis_autofill' => [
                'business_trade_name' => (string) ($gisAutofill['business_trade_name'] ?? ''),
                'sec_registration_number' => (string) ($gisAutofill['sec_registration_number'] ?? ''),
                'date_registered' => (string) ($gisAutofill['date_registered'] ?? ''),
                'registered_address' => (string) ($gisAutofill['registered_address'] ?? ''),
                'corporate_tin' => (string) ($gisAutofill['corporate_tin'] ?? ''),
                'branch_code' => (string) ($gisAutofill['branch_code'] ?? ''),
            ],
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
                    ];
                })
                ->values()
                ->all(),
            'revision_count' => $revisionCount,
            'last_revision_at' => $lastRevisionAt?->toDateTimeString(),
        ];
    }

    private function ensureCertificateExtraction(RegistrationLink $registrationLink): void
    {
        $certificateDocument = $registrationLink->requiredDocuments()
            ->where('document_type', 'certificate_of_registration')
            ->first();

        if ($certificateDocument === null) {
            return;
        }

        $existingPayload = is_array($certificateDocument->extraction_payload)
            ? $certificateDocument->extraction_payload
            : [];
        $requiredKeys = [
            'business_trade_name',
            'sec_registration_number',
            'date_registered',
            'registered_address',
            'corporate_tin',
        ];
        $missingRequiredValues = collect($requiredKeys)->some(
            fn (string $key): bool => trim((string) ($existingPayload[$key] ?? '')) === ''
        );

        if ($existingPayload !== [] && $missingRequiredValues === false) {
            return;
        }

        if (! Storage::disk('local')->exists($certificateDocument->file_path)) {
            return;
        }

        $absolutePath = Storage::disk('local')->path($certificateDocument->file_path);
        $payload = $this->requiredDocumentExtractionService->extractCertificateOfRegistrationFields(
            absolutePath: $absolutePath,
            originalFilename: $certificateDocument->original_filename,
        );

        if ($payload === []) {
            return;
        }

        $certificateDocument->update([
            'extraction_payload' => array_merge($existingPayload, $payload),
        ]);
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
