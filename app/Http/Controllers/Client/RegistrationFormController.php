<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Http\Requests\Client\SubmitRegistrationFormRequest;
use App\Models\RegistrationLink;
use App\Services\NotificationService;
use App\Services\RegistrationFormService;
use App\Services\RegistrationQrCodeService;
use App\Services\RegistrationTemplateService;
use App\Services\SubmissionTrackingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class RegistrationFormController extends Controller
{
    public function __construct(
        private readonly RegistrationFormService $formService,
        private readonly RegistrationTemplateService $templateService,
        private readonly NotificationService $notificationService,
        private readonly RegistrationQrCodeService $qrCodeService,
        private readonly SubmissionTrackingService $trackingService,
    ) {}

    public function show(Request $request, string $token): Response
    {
        $link = RegistrationLink::query()
            ->where('token', $token)
            ->whereIn('status', ['pending', 'incomplete'])
            ->firstOrFail();

        $formSchema = $this->formService->getSchemaForCompanyType($link->company_type);
        $formUrl = route('registration.form.show', $token);
        $existingSubmission = $this->formService->getStructuredSubmission($link);
        $allowedSections = array_map(
            fn (array $section): string => (string) ($section['name'] ?? ''),
            $formSchema,
        );
        $focusSection = $request->query('section');
        $focusSection = is_string($focusSection) && in_array($focusSection, $allowedSections, true)
            ? $focusSection
            : null;

        return Inertia::render('Registration/Form', [
            'token' => $token,
            'email' => $link->email,
            'companyType' => $link->company_type,
            'companyTypeLabel' => $this->templateService->labelFor($link->company_type),
            'formSchema' => $formSchema,
            'submitUrl' => route('registration.form.submit', $token),
            'qrCodeDataUri' => $this->qrCodeService->makeDataUri($formUrl),
            'initialSections' => $this->structuredSectionsToInput($existingSubmission['sections'] ?? []),
            'isEditing' => $existingSubmission !== null,
            'focusSection' => $focusSection,
        ]);
    }

    public function submit(string $token, SubmitRegistrationFormRequest $request): RedirectResponse
    {
        $link = RegistrationLink::query()
            ->where('token', $token)
            ->whereIn('status', ['pending', 'incomplete'])
            ->firstOrFail();

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

        $this->trackingService->sendAccessLink($link->email);

        return redirect()->route('registration.form.success');
    }

    public function success(): Response
    {
        return Inertia::render('Registration/Success', [
            'trackingLookupUrl' => route('registration.tracking.lookup'),
        ]);
    }

    private function structuredSectionsToInput(array $sections): array
    {
        $result = [];

        foreach ($sections as $section) {
            $sectionName = (string) ($section['name'] ?? '');

            if ($sectionName === '') {
                continue;
            }

            $result[$sectionName] = [];

            foreach ((array) ($section['fields'] ?? []) as $field) {
                $fieldName = (string) ($field['name'] ?? '');

                if ($fieldName === '') {
                    continue;
                }

                $result[$sectionName][$fieldName] = (string) ($field['value'] ?? '');
            }
        }

        return $result;
    }
}
