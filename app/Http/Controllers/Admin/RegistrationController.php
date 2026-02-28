<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\SendRegistrationLinkRequest;
use App\Http\Requests\Admin\UpdateRegistrationStatusRequest;
use App\Models\RegistrationLink;
use App\Models\User;
use App\Services\ActivityLogService;
use App\Services\NotificationService;
use App\Services\RegistrationFormService;
use App\Services\RegistrationTemplateService;
use App\Services\RegistrationWorkflowService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class RegistrationController extends Controller
{
    public function __construct(
        private readonly RegistrationTemplateService $templateService,
        private readonly RegistrationWorkflowService $workflowService,
        private readonly RegistrationFormService $registrationFormService,
        private readonly NotificationService $notificationService,
        private readonly ActivityLogService $activityLogService,
    ) {}

    public function index(): Response
    {
        $search = trim((string) request('search', ''));
        $sort = request('sort') === 'created_at' ? 'created_at' : 'created_at';
        $direction = request('direction') === 'asc' ? 'asc' : 'desc';
        $companyType = request('company_type');

        $links = RegistrationLink::query()
            ->with('formSubmission')
            ->when(in_array($companyType, ['corp', 'sole_prop', 'opc'], true), function ($query) use ($companyType) {
                $query->where('company_type', $companyType);
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

        return Inertia::render('admin/registration/index', [
            'links' => $links,
            'companyTypes' => $this->templateService->availableCompanyTypes(),
            'filters' => [
                'search' => $search,
                'sort' => $sort,
                'direction' => $direction,
                'company_type' => in_array($companyType, ['corp', 'sole_prop', 'opc'], true) ? $companyType : '',
            ],
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('admin/registration/create', [
            'companyTypes' => $this->templateService->availableCompanyTypes(),
        ]);
    }

    public function sendLink(SendRegistrationLinkRequest $request): RedirectResponse
    {
        $email = $request->string('email')->toString();
        $companyType = $request->string('company_type')->toString();

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

        return back()->with('success', 'Registration email sent successfully.');
    }

    public function destroy(Request $request, RegistrationLink $registrationLink): RedirectResponse
    {
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
            performedBy: $request->user(),
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
                performedBy: $request->user(),
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

        return redirect()
            ->route('admin.register.index')
            ->with('success', 'Registration deleted successfully.');
    }

    public function show(RegistrationLink $registrationLink): Response
    {
        $registrationLink->loadMissing('formSubmission.revisions');
        $revisionCount = $registrationLink->formSubmission?->revisions()->count() ?? 0;
        $lastRevisionAt = $registrationLink->formSubmission?->revisions()->latest('created_at')->first()?->created_at;

        return Inertia::render('admin/registration/show', [
            'registration' => [
                'id' => $registrationLink->id,
                'email' => $registrationLink->email,
                'token' => $registrationLink->token,
                'company_type' => $registrationLink->company_type,
                'company_type_label' => $this->templateService->labelFor($registrationLink->company_type),
                'status' => $registrationLink->status,
                'created_at' => $registrationLink->created_at?->toDateTimeString(),
                'form_submission' => $this->registrationFormService->getStructuredSubmission($registrationLink),
                'revision_count' => $revisionCount,
                'last_revision_at' => $lastRevisionAt?->toDateTimeString(),
            ],
        ]);
    }

    public function updateStatus(UpdateRegistrationStatusRequest $request, RegistrationLink $registrationLink): RedirectResponse
    {
        $status = $request->string('status')->toString();

        $registrationLink->update([
            'status' => $status,
        ]);

        return back()->with('success', "Successfully set status to {$status}.");
    }

    private function guessClientNameFromEmail(string $email): string
    {
        $localPart = explode('@', $email)[0] ?? $email;
        $normalized = trim(str_replace(['.', '_', '-'], ' ', $localPart));

        return $normalized !== '' ? ucwords($normalized) : $email;
    }
}
