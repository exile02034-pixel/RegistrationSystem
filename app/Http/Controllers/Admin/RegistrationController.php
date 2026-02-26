<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\SendRegistrationLinkRequest;
use App\Models\RegistrationLink;
use App\Models\RegistrationUpload;
use App\Services\RegistrationTemplateService;
use App\Services\RegistrationWorkflowService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class RegistrationController extends Controller
{
    public function __construct(
        private readonly RegistrationTemplateService $templateService,
        private readonly RegistrationWorkflowService $workflowService,
    ) {
    }

    public function index(): Response
    {
        $links = RegistrationLink::query()
            ->withCount('uploads')
            ->latest()
            ->get()
            ->map(fn (RegistrationLink $link) => [
                'id' => $link->id,
                'email' => $link->email,
                'company_type' => $link->company_type,
                'company_type_label' => $this->templateService->labelFor($link->company_type),
                'status' => $link->status,
                'token' => $link->token,
                'uploads_count' => $link->uploads_count,
                'created_at' => $link->created_at?->toDateTimeString(),
                'client_url' => route('client.registration.show', $link->token),
                'show_url' => route('admin.register.show', $link->id),
            ]);

        return Inertia::render('admin/registration/index', [
            'links' => $links,
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
        $this->workflowService->createRegistrationLinkAndSend(
            email: $request->string('email')->toString(),
            companyType: $request->string('company_type')->toString(),
        );

        return back()->with('success', 'Registration email sent successfully.');
    }

    public function show(RegistrationLink $registrationLink): Response
    {
        $registrationLink->load('uploads');

        return Inertia::render('admin/registration/show', [
            'registration' => [
                'id' => $registrationLink->id,
                'email' => $registrationLink->email,
                'token' => $registrationLink->token,
                'company_type_label' => $this->templateService->labelFor($registrationLink->company_type),
                'status' => $registrationLink->status,
                'created_at' => $registrationLink->created_at?->toDateTimeString(),
                'uploads' => $registrationLink->uploads->map(fn (RegistrationUpload $upload) => [
                    'id' => $upload->id,
                    'original_name' => $upload->original_name,
                    'mime_type' => $upload->mime_type,
                    'size_bytes' => $upload->size_bytes,
                    'created_at' => $upload->created_at?->toDateTimeString(),
                    'download_url' => route('admin.register.uploads.download', [$registrationLink->id, $upload->id]),
                ]),
            ],
        ]);
    }

    public function downloadUpload(RegistrationLink $registrationLink, RegistrationUpload $upload): BinaryFileResponse
    {
        abort_unless($upload->registration_link_id === $registrationLink->id, 404);

        return response()->download(
            Storage::disk('public')->path($upload->storage_path),
            $upload->original_name
        );
    }
}
