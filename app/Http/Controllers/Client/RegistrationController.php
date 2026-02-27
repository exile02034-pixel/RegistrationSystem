<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Http\Requests\Client\StoreClientUploadsRequest;
use App\Models\RegistrationLink;
use App\Services\DocumentConversionService;
use App\Services\NotificationService;
use App\Services\RegistrationTemplateService;
use App\Services\RegistrationWorkflowService;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class RegistrationController extends Controller
{
    public function __construct(
        private readonly RegistrationTemplateService $templateService,
        private readonly RegistrationWorkflowService $workflowService,
        private readonly DocumentConversionService $conversionService,
        private readonly NotificationService $notificationService,
    ) {
    }

    public function show(string $token): Response
    {
        $link = RegistrationLink::where('token', $token)->firstOrFail();

        $templates = collect($this->templateService->templatesFor($link->company_type))
            ->map(fn (array $template, string $key) => [
                'key' => $key,
                'name' => $template['name'],
                'download_url' => route('client.registration.templates.download', [$token, $key]),
            ])
            ->values();

        return Inertia::render('client/registration/Show', [
            'token' => $link->token,
            'email' => $link->email,
            'status' => $link->status,
            'companyTypeLabel' => $this->templateService->labelFor($link->company_type),
            'templates' => $templates,
            'uploadUrl' => route('client.registration.uploads.store', $link->token),
            'qrCodeUrl' => 'https://api.qrserver.com/v1/create-qr-code/?size=220x220&data='.urlencode(route('client.registration.show', $link->token)),
        ]);
    }

    public function downloadTemplate(string $token, string $templateKey): BinaryFileResponse
    {
        $link = RegistrationLink::where('token', $token)->firstOrFail();
        $template = $this->templateService->templateByKey($link->company_type, $templateKey);
        $sourcePath = base_path($template['path']);
        $pdf = $this->conversionService->convertToPdf($sourcePath, $template['name']);

        if ($pdf !== null) {
            return response()->download($pdf['path'], $pdf['name'])->deleteFileAfterSend(true);
        }

        return response()->download($sourcePath, $template['name']);
    }

    public function storeUploads(StoreClientUploadsRequest $request, string $token): RedirectResponse
    {
        $link = RegistrationLink::where('token', $token)->firstOrFail();
        $uploadedFiles = $request->file('files') ?? [];

        $this->workflowService->storeClientUploads($link, $uploadedFiles);

        $this->notificationService->notifyAdmins(
            category: 'client_files_submitted',
            title: 'Client files submitted',
            message: "{$link->email} submitted ".count($uploadedFiles).' file(s).',
            actionUrl: route('admin.register.show', $link->id),
            meta: [
                'email' => $link->email,
                'registration_link_id' => $link->id,
                'files_count' => count($uploadedFiles),
            ],
        );

        return redirect()
            ->route('client.registration.thank-you', $link->token)
            ->with('success', 'Files uploaded successfully.');
    }

    public function thankYou(string $token): Response
    {
        $link = RegistrationLink::where('token', $token)->firstOrFail();

        return Inertia::render('client/registration/ThankYou', [
            'email' => $link->email,
        ]);
    }
}
