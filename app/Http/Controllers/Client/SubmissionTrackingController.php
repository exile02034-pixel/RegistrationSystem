<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Services\RegistrationFormService;
use App\Services\RegistrationTemplateService;
use App\Services\SubmissionTrackingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class SubmissionTrackingController extends Controller
{
    public function __construct(
        private readonly SubmissionTrackingService $trackingService,
        private readonly RegistrationFormService $formService,
        private readonly RegistrationTemplateService $templateService,
    ) {}

    public function lookup(Request $request): Response
    {
        return Inertia::render('Registration/TrackingLookup', [
            'statusMessage' => (string) $request->session()->get('tracking_status', ''),
            'errorMessage' => (string) $request->session()->get('tracking_error', ''),
            'requestLinkUrl' => route('registration.tracking.request-link'),
        ]);
    }

    public function requestLink(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'email'],
        ]);

        $this->trackingService->sendAccessLink((string) $validated['email']);

        return back()->with('tracking_status', 'If a submission exists for that email, we sent a secure tracking link.');
    }

    public function access(Request $request, string $token): RedirectResponse
    {
        $authorized = $this->trackingService->consumeAccessToken($request, $token);

        if ($authorized === null) {
            return redirect()
                ->route('registration.tracking.lookup')
                ->with('tracking_error', 'That tracking link is invalid or expired. Request a new one.');
        }

        return redirect()->route('registration.tracking.show');
    }

    public function show(Request $request): Response|RedirectResponse
    {
        $link = $this->trackingService->authorizedRegistrationLink($request);

        if ($link === null || $link->formSubmission === null) {
            return redirect()
                ->route('registration.tracking.lookup')
                ->with('tracking_error', 'Your tracking session expired. Request a new secure link.');
        }

        return Inertia::render('Registration/TrackingShow', [
            'email' => $link->email,
            'companyTypeLabel' => $this->templateService->labelFor($link->company_type),
            'status' => $link->status,
            'statusLabel' => $this->statusLabel($link->status),
            'submittedAt' => $link->formSubmission->submitted_at?->toDateTimeString(),
            'canEdit' => $this->trackingService->canEdit($link),
            'editUrl' => route('registration.form.show', $link->token),
            'logoutUrl' => route('registration.tracking.logout'),
            'revisionCount' => $link->formSubmission->revisions()->count(),
            'lastRevisionAt' => $link->formSubmission->revisions()->latest('created_at')->first()?->created_at?->toDateTimeString(),
            'summary' => $this->formService->getStructuredSubmission($link),
        ]);
    }

    public function logout(Request $request): RedirectResponse
    {
        $this->trackingService->clearAccess($request);

        return redirect()
            ->route('registration.tracking.lookup')
            ->with('tracking_status', 'Tracking session ended.');
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
