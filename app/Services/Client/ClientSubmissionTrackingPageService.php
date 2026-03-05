<?php

namespace App\Services\Client;

use App\Models\RegistrationLink;
use App\Services\NotificationService;
use App\Services\RegistrationFormService;
use App\Services\RegistrationTemplateService;
use App\Services\SubmissionTrackingService;
use Illuminate\Http\Request;

class ClientSubmissionTrackingPageService
{
    public function __construct(
        private readonly SubmissionTrackingService $trackingService,
        private readonly RegistrationFormService $formService,
        private readonly RegistrationTemplateService $templateService,
        private readonly NotificationService $notificationService,
    ) {}

    public function lookupProps(Request $request): array
    {
        return [
            'statusMessage' => (string) $request->session()->get('tracking_status', ''),
            'errorMessage' => (string) $request->session()->get('tracking_error', ''),
            'requestLinkUrl' => route('registration.tracking.request-link'),
        ];
    }

    public function showProps(Request $request, RegistrationLink $link): array
    {
        return [
            'email' => $link->email,
            'companyTypeLabel' => $this->templateService->labelFor($link->company_type),
            'status' => $link->status,
            'statusLabel' => $this->statusLabel($link->status),
            'submittedAt' => $link->formSubmission->submitted_at?->toDateTimeString(),
            'canEdit' => $this->trackingService->canEdit($link),
            'editableSections' => $this->trackingService->editableSections($link),
            'statusMessage' => (string) $request->session()->get('tracking_status', ''),
            'errorMessage' => (string) $request->session()->get('tracking_error', ''),
            'editUrl' => route('registration.form.show', $link->token),
            'requestEditPermissionUrl' => route('registration.tracking.request-edit-permission'),
            'logoutUrl' => route('registration.tracking.logout'),
            'revisionCount' => $link->formSubmission->revisions()->count(),
            'lastRevisionAt' => $link->formSubmission->revisions()->latest('created_at')->first()?->created_at?->toDateTimeString(),
            'summary' => $this->formService->getStructuredSubmission($link),
        ];
    }

    public function sendEditPermissionRequestNotification(RegistrationLink $link): void
    {
        $this->notificationService->notifyAdmins(
            category: 'client_edit_permission_requested',
            title: 'Client requested edit permission',
            message: "{$link->email} requested permission to edit their submitted registration.",
            actionUrl: route('admin.register.show', $link->id),
            meta: [
                'email' => $link->email,
                'registration_link_id' => $link->id,
                'company_type' => $link->company_type,
                'status' => $link->status,
            ],
        );
    }

    private function statusLabel(string $status): string
    {
        return match ($status) {
            'pending' => 'Submitted',
            'incomplete' => 'Needs Changes',
            'completed' => 'Under Review',
            default => ucfirst($status),
        };
    }
}
