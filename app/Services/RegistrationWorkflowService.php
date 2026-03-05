<?php

namespace App\Services;

use App\Jobs\SendRegistrationLinkMailJob;
use App\Models\RegistrationLink;
use Illuminate\Support\Str;

class RegistrationWorkflowService
{
    public function __construct(
        private readonly RegistrationTemplateService $templateService,
        private readonly RegistrationQrCodeService $qrCodeService,
    ) {}

    public function createRegistrationLinkAndSend(string $email, string $companyType): RegistrationLink
    {
        $link = RegistrationLink::create([
            'email' => $email,
            'token' => Str::random(40),
            'company_type' => $companyType,
            'status' => 'pending',
        ]);

        $registrationUrl = route('registration.form.show', $link->token);
        $qrCodeDataUri = $this->qrCodeService->makeDataUri($registrationUrl);

        SendRegistrationLinkMailJob::dispatch(
            email: $email,
            registrationUrl: $registrationUrl,
            companyTypeLabel: $this->templateService->labelFor($companyType),
            qrCodeDataUri: $qrCodeDataUri,
        )->onQueue('mail');

        return $link;
    }
}
