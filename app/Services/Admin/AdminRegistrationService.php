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

class AdminRegistrationService
{
    public function __construct(
        private readonly RegistrationTemplateService $templateService,
        private readonly RegistrationFormService $registrationFormService,
        private readonly DocumentGenerationService $documentGenerationService,
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
        $registrationLink->loadMissing('formSubmission.revisions', 'generatedDocuments.generatedBy');
        $revisionCount = $registrationLink->formSubmission?->revisions()->count() ?? 0;
        $lastRevisionAt = $registrationLink->formSubmission?->revisions()->latest('created_at')->first()?->created_at;

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
            'revision_count' => $revisionCount,
            'last_revision_at' => $lastRevisionAt?->toDateTimeString(),
        ];
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
