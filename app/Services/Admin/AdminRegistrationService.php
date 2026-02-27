<?php

namespace App\Services\Admin;

use App\Http\Resources\Admin\AdminRegistrationResource;
use App\Models\RegistrationLink;
use App\Services\NotificationService;
use App\Services\RegistrationFormService;
use App\Services\RegistrationTemplateService;
use App\Services\RegistrationWorkflowService;
use Illuminate\Pagination\LengthAwarePaginator;

class AdminRegistrationService
{
    public function __construct(
        private readonly RegistrationTemplateService $templateService,
        private readonly RegistrationWorkflowService $workflowService,
        private readonly RegistrationFormService $registrationFormService,
        private readonly NotificationService $notificationService,
        private readonly AdminFileService $fileService,
    ) {}

    public function listLinks(array $input): array
    {
        $search = trim((string) ($input['search'] ?? ''));
        $sort = 'created_at';
        $direction = ($input['direction'] ?? 'desc') === 'asc' ? 'asc' : 'desc';
        $companyType = $input['company_type'] ?? null;
        $allowedType = in_array($companyType, ['corp', 'sole_prop', 'opc'], true) ? $companyType : '';

        $links = RegistrationLink::query()
            ->with('formSubmission')
            ->when($allowedType !== '', function ($query) use ($allowedType) {
                $query->where('company_type', $allowedType);
            })
            ->when($search !== '', function ($query) use ($search) {
                $innerSearch = strtolower($search);
                $searchType = match (true) {
                    str_contains($innerSearch, 'opc') => 'opc',
                    str_contains($innerSearch, 'sole') || str_contains($innerSearch, 'prop') || str_contains($innerSearch, 'proprietorship') => 'sole_prop',
                    str_contains($innerSearch, 'corp') || str_contains($innerSearch, 'corporation') || str_contains($innerSearch, 'regular') => 'corp',
                    default => null,
                };

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
            ->orderBy($sort, $direction)
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

        $this->transformPaginator($links, AdminRegistrationResource::class);

        return [
            'links' => $links,
            'companyTypes' => $this->templateService->availableCompanyTypes(),
            'filters' => [
                'search' => $search,
                'sort' => $sort,
                'direction' => $direction,
                'company_type' => $allowedType,
            ],
        ];
    }

    public function companyTypes(): array
    {
        return $this->templateService->availableCompanyTypes();
    }

    public function sendRegistrationLink(string $email, string $companyType): void
    {
        $this->workflowService->createRegistrationLinkAndSend(
            email: $email,
            companyType: $companyType,
        );

        $this->notificationService->notifyAdmins(
            category: 'registration_email_sent',
            title: 'Registration email sent',
            message: "A registration email was sent to {$email}.",
            actionUrl: route('admin.register.index'),
            meta: [
                'email' => $email,
                'company_type' => $companyType,
            ],
        );
    }

    public function deleteRegistration(RegistrationLink $registrationLink): void
    {
        $email = $registrationLink->email;
        $registrationLink->delete();

        $this->notificationService->notifyAdmins(
            category: 'registration_deleted',
            title: 'Registration deleted',
            message: "Registration record for {$email} was deleted.",
            actionUrl: route('admin.register.index'),
            meta: ['email' => $email],
        );
    }

    public function registrationDetail(RegistrationLink $registrationLink): array
    {
        $registrationLink->load('uploads');

        return AdminRegistrationResource::make([
            'id' => $registrationLink->id,
            'email' => $registrationLink->email,
            'token' => $registrationLink->token,
            'company_type_label' => $this->templateService->labelFor($registrationLink->company_type),
            'status' => $registrationLink->status,
            'created_at' => $registrationLink->created_at?->toDateTimeString(),
            'uploads' => $this->fileService->mapUploadsForRegistration($registrationLink),
            'form_submission' => $this->registrationFormService->getStructuredSubmission($registrationLink),
        ])->resolve();
    }

    private function transformPaginator(LengthAwarePaginator $paginator, string $resourceClass): void
    {
        $paginator->setCollection($resourceClass::collection($paginator->getCollection())->resolve());
    }
}
