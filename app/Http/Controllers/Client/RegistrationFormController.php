<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Http\Requests\Client\SubmitRegistrationFormRequest;
use App\Models\RegistrationLink;
use App\Services\Client\ClientRegistrationFormPageService;
use App\Services\RegistrationFormService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class RegistrationFormController extends Controller
{
    public function __construct(
        private readonly RegistrationFormService $formService,
        private readonly ClientRegistrationFormPageService $pageService,
    ) {}

    public function show(Request $request, string $token): Response
    {
        $link = RegistrationLink::query()
            ->where('token', $token)
            ->whereIn('status', ['pending', 'incomplete'])
            ->firstOrFail();

        return Inertia::render('Registration/Form', $this->pageService->showProps($request, $link, $token));
    }

    public function submit(string $token, SubmitRegistrationFormRequest $request): RedirectResponse
    {
        $link = RegistrationLink::query()
            ->where('token', $token)
            ->whereIn('status', ['pending', 'incomplete'])
            ->firstOrFail();

        $payload = $this->pageService->normalizedSubmitPayload($request, $link, $request->validated());

        $this->formService->saveSubmission($link, $payload);
        $this->pageService->handleAfterSubmit($link);

        return redirect()->route('registration.form.success');
    }

    public function success(): Response
    {
        return Inertia::render('Registration/Success', $this->pageService->successProps());
    }
}
