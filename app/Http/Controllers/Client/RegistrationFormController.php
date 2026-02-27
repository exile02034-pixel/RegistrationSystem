<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Http\Requests\Client\SubmitRegistrationFormRequest;
use App\Models\RegistrationLink;
use App\Services\NotificationService;
use App\Services\RegistrationFormService;
use App\Services\RegistrationQrCodeService;
use App\Services\RegistrationTemplateService;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class RegistrationFormController extends Controller
{
    public function __construct(
        private readonly RegistrationFormService $formService,
        private readonly RegistrationTemplateService $templateService,
        private readonly NotificationService $notificationService,
        private readonly RegistrationQrCodeService $qrCodeService,
    ) {}

    public function show(string $token): Response
    {
        $link = RegistrationLink::query()
            ->where('token', $token)
            ->where('status', 'pending')
            ->firstOrFail();

        $formSchema = $this->formService->getSchemaForCompanyType($link->company_type);
        $formUrl = route('registration.form.show', $token);

        return Inertia::render('Registration/Form', [
            'token' => $token,
            'email' => $link->email,
            'companyType' => $link->company_type,
            'companyTypeLabel' => $this->templateService->labelFor($link->company_type),
            'formSchema' => $formSchema,
            'submitUrl' => route('registration.form.submit', $token),
            'qrCodeDataUri' => $this->qrCodeService->makeDataUri($formUrl),
        ]);
    }

    public function submit(string $token, SubmitRegistrationFormRequest $request): RedirectResponse
    {
        $link = RegistrationLink::query()->where('token', $token)->firstOrFail();

        $this->formService->saveSubmission($link, $request->validated());

        $this->notificationService->notifyAdmins(
            category: 'client_files_submitted',
            title: 'Client registration form submitted',
            message: "{$link->email} submitted the online registration form.",
            actionUrl: route('admin.register.show', $link->id),
            meta: [
                'email' => $link->email,
                'registration_link_id' => $link->id,
                'company_type' => $link->company_type,
            ],
        );

        return redirect()->route('registration.form.success');
    }

    public function success(): Response
    {
        return Inertia::render('Registration/Success');
    }
}
