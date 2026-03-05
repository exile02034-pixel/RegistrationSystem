<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\SendRegistrationPdfsEmailRequest;
use App\Http\Requests\Admin\SendRegistrationLinkRequest;
use App\Http\Requests\Admin\UpdateRegistrationStatusRequest;
use App\Models\RegistrationLink;
use App\Services\NotificationService;
use App\Services\Admin\AdminRegistrationPdfEmailService;
use App\Services\Admin\AdminRegistrationService;
use App\Services\RegistrationWorkflowService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class RegistrationController extends Controller
{
    public function __construct(
        private readonly RegistrationWorkflowService $workflowService,
        private readonly AdminRegistrationService $adminRegistrationService,
        private readonly AdminRegistrationPdfEmailService $adminRegistrationPdfEmailService,
        private readonly NotificationService $notificationService,
    ) {}

    public function index(): Response
    {
        $search = trim((string) request('search', ''));
        $sort = (string) request('sort', 'created_at');
        $direction = (string) request('direction', 'desc');
        $companyType = request('company_type');

        return Inertia::render('admin/registration/index', $this->adminRegistrationService->indexPageProps(
            search: $search,
            companyType: is_string($companyType) ? $companyType : null,
            sort: $sort,
            direction: $direction,
        ));
    }

    public function create(): Response
    {
        return Inertia::render('admin/registration/create', $this->adminRegistrationService->createPageProps());
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
        $this->adminRegistrationService->deleteRegistration($registrationLink, $request->user());

        return redirect()
            ->route('admin.register.index')
            ->with('success', 'Registration deleted successfully.');
    }

    public function show(RegistrationLink $registrationLink): Response
    {
        return Inertia::render('admin/registration/show', $this->adminRegistrationService->showPageProps($registrationLink));
    }

    public function updateStatus(UpdateRegistrationStatusRequest $request, RegistrationLink $registrationLink): RedirectResponse
    {
        $status = $request->string('status')->toString();

        $this->adminRegistrationService->setStatus($registrationLink, $status);

        return back()->with('success', "Successfully set status to {$status}.");
    }

    public function sendSelectedPdfsEmail(
        SendRegistrationPdfsEmailRequest $request,
        RegistrationLink $registrationLink,
    ): RedirectResponse {
        $validated = $request->validated();

        try {
            $count = $this->adminRegistrationPdfEmailService->sendSelectedPdfs(
                registrationLink: $registrationLink,
                sections: (array) ($validated['sections'] ?? []),
                documentIds: (array) ($validated['document_ids'] ?? []),
            );
        } catch (\InvalidArgumentException $exception) {
            throw ValidationException::withMessages([
                'sections' => $exception->getMessage(),
            ]);
        }

        return back()->with('success', "Sent {$count} selected PDF(s) to {$registrationLink->email}.");
    }
}
