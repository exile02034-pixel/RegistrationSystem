<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\SendRegistrationLinkRequest;
use App\Models\RegistrationLink;
use App\Models\RegistrationUpload;
use App\Services\DocumentConversionService;
use App\Services\RegistrationTemplateService;
use App\Services\RegistrationWorkflowService;
use Illuminate\Http\Request;
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
        private readonly DocumentConversionService $conversionService,
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
            'companyTypes' => $this->templateService->availableCompanyTypes(),
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

    public function destroy(RegistrationLink $registrationLink): RedirectResponse
    {
        $registrationLink->delete();

        return redirect()
            ->route('admin.register.index')
            ->with('success', 'Registration deleted successfully.');
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
                    'download_pdf_url' => route('admin.register.uploads.download', [$registrationLink->id, $upload->id]).'?format=pdf',
                    'delete_url' => route('admin.register.uploads.destroy', [$registrationLink->id, $upload->id]),
                    'can_convert_pdf' => in_array(strtolower(pathinfo($upload->original_name, PATHINFO_EXTENSION)), ['doc', 'docx'], true),
                ]),
            ],
        ]);
    }

    public function downloadUpload(Request $request, RegistrationLink $registrationLink, RegistrationUpload $upload): BinaryFileResponse
    {
        abort_unless($upload->registration_link_id === $registrationLink->id, 404);

        $sourcePath = Storage::disk('public')->path($upload->storage_path);
        $extension = strtolower(pathinfo($upload->original_name, PATHINFO_EXTENSION));
        $wantsPdf = $request->query('format') === 'pdf';

        if ($wantsPdf && in_array($extension, ['doc', 'docx'], true)) {
            $pdf = $this->conversionService->convertToPdf($sourcePath, $upload->original_name);

            if ($pdf !== null) {
                return response()->download($pdf['path'], $pdf['name'])->deleteFileAfterSend(true);
            }
        }

        return response()->download($sourcePath, $upload->original_name);
    }

    public function viewUpload(Request $request, RegistrationLink $registrationLink, RegistrationUpload $upload): BinaryFileResponse
    {
        abort_unless($upload->registration_link_id === $registrationLink->id, 404);

        $sourcePath = Storage::disk('public')->path($upload->storage_path);
        $extension = strtolower(pathinfo($upload->original_name, PATHINFO_EXTENSION));
        $format = $request->query('format', 'raw');
        $strict = (bool) $request->boolean('strict');

        if ($format === 'pdf' && in_array($extension, ['doc', 'docx'], true)) {
            $pdf = $this->conversionService->convertToPdf($sourcePath, $upload->original_name);

            if ($pdf !== null) {
                return response()->file($pdf['path'])->deleteFileAfterSend(true);
            }

            abort_if($strict, 422, 'PDF preview is unavailable for this document.');
        }

        if ($format === 'pdf' && $extension === 'pdf') {
            return response()->file($sourcePath);
        }

        if ($format === 'pdf') {
            abort_if($strict, 422, 'PDF preview is unavailable for this file type.');
        }

        return response()->file($sourcePath);
    }

    public function destroyUpload(RegistrationLink $registrationLink, RegistrationUpload $upload): RedirectResponse
    {
        abort_unless($upload->registration_link_id === $registrationLink->id, 404);

        Storage::disk('public')->delete($upload->storage_path);
        $upload->delete();

        return back()->with('success', 'File deleted successfully.');
    }
}
