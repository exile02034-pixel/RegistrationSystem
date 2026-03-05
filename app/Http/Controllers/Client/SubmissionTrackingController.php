<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Services\Client\ClientSubmissionTrackingPageService;
use App\Services\SubmissionTrackingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class SubmissionTrackingController extends Controller
{
    public function __construct(
        private readonly SubmissionTrackingService $trackingService,
        private readonly ClientSubmissionTrackingPageService $pageService,
    ) {}

    public function lookup(Request $request): Response
    {
        return Inertia::render('Registration/TrackingLookup', $this->pageService->lookupProps($request));
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

        return Inertia::render('Registration/TrackingShow', $this->pageService->showProps($request, $link));
    }

    public function requestEditPermission(Request $request): RedirectResponse
    {
        $link = $this->trackingService->authorizedRegistrationLink($request);

        if ($link === null || $link->formSubmission === null) {
            return redirect()
                ->route('registration.tracking.lookup')
                ->with('tracking_error', 'Your tracking session expired. Request a new secure link.');
        }

        if ($this->trackingService->canEdit($link)) {
            return back()->with('tracking_status', 'Editing is already enabled for your submission.');
        }

        $this->pageService->sendEditPermissionRequestNotification($link);

        return back()->with('tracking_status', 'Your request has been sent. An admin will review it.');
    }

    public function logout(Request $request): RedirectResponse
    {
        $this->trackingService->clearAccess($request);

        return redirect()
            ->route('registration.tracking.lookup')
            ->with('tracking_status', 'Tracking session ended.');
    }
}
