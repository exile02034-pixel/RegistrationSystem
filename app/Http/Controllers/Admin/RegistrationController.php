<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\SendRegistrationPdfsEmailRequest;
use App\Http\Requests\Admin\SendRegistrationLinkRequest;
use App\Http\Requests\Admin\UpdateRegistrationStatusRequest;
use App\Models\RegistrationRequiredDocument;
use App\Models\RegistrationLink;
use App\Services\NotificationService;
use App\Services\Admin\AdminRegistrationPdfEmailService;
use App\Services\Admin\AdminRegistrationService;
use App\Services\Admin\RequiredDocumentExtractionService;
use App\Services\RegistrationWorkflowService;
use Illuminate\Http\UploadedFile;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Inertia\Inertia;
use Inertia\Response;

class RegistrationController extends Controller
{
    public function __construct(
        private readonly RegistrationWorkflowService $workflowService,
        private readonly AdminRegistrationService $adminRegistrationService,
        private readonly AdminRegistrationPdfEmailService $adminRegistrationPdfEmailService,
        private readonly RequiredDocumentExtractionService $requiredDocumentExtractionService,
        private readonly NotificationService $notificationService,
    ) {}

    public function index(): Response
    {
        $search = trim((string) request('search', ''));
        $sort = (string) request('sort', 'created_at');
        $direction = (string) request('direction', 'desc');
        $companyType = request('company_type');

        return Inertia::render('admin/registration/index', $this->adminRegistrationService->indexPageProps(
            search: $search,
            companyType: is_string($companyType) ? $companyType : null,
            sort: $sort,
            direction: $direction,
        ));
    }

    public function create(): Response
    {
        return Inertia::render('admin/registration/create', $this->adminRegistrationService->createPageProps());
    }

    public function sendLink(SendRegistrationLinkRequest $request): RedirectResponse
    {
        $email = $request->string('email')->toString();
        $companyType = $request->string('company_type')->toString();

        $this->workflowService->createRegistrationLinkAndSend(
            email: $email,
            companyType: $companyType,
        );

        $this->notificationService->notifyAdmins(
            category: 'registration_email_sent',
            title: 'Registration email sent',
            message: "A registration email was sent to {$email}.",
            actionUrl: route('admin.register.index'),
            meta: [
                'email' => $email,
                'company_type' => $companyType,
            ],
        );

        return back()->with('success', 'Registration email sent successfully.');
    }

    public function destroy(Request $request, RegistrationLink $registrationLink): RedirectResponse
    {
        $this->adminRegistrationService->deleteRegistration($registrationLink, $request->user());

        return redirect()
            ->route('admin.register.index')
            ->with('success', 'Registration deleted successfully.');
    }

    public function show(RegistrationLink $registrationLink): Response
    {
        return Inertia::render('admin/registration/show', $this->adminRegistrationService->showPageProps($registrationLink));
    }

    public function updateStatus(UpdateRegistrationStatusRequest $request, RegistrationLink $registrationLink): RedirectResponse
    {
        $status = $request->string('status')->toString();

        $this->adminRegistrationService->setStatus($registrationLink, $status);

        return back()->with('success', "Successfully set status to {$status}.");
    }

    public function sendSelectedPdfsEmail(
        SendRegistrationPdfsEmailRequest $request,
        RegistrationLink $registrationLink,
    ): RedirectResponse {
        $validated = $request->validated();

        try {
            $count = $this->adminRegistrationPdfEmailService->sendSelectedPdfs(
                registrationLink: $registrationLink,
                sections: (array) ($validated['sections'] ?? []),
                documentIds: (array) ($validated['document_ids'] ?? []),
            );
        } catch (\InvalidArgumentException $exception) {
            throw ValidationException::withMessages([
                'sections' => $exception->getMessage(),
            ]);
        }

        return back()->with('success', "Sent {$count} selected PDF(s) to {$registrationLink->email}.");
    }

    public function uploadRequiredDocument(Request $request, RegistrationLink $registrationLink): RedirectResponse
    {
        $validated = $request->validate([
            'document_type' => ['required', 'string', 'in:'.implode(',', array_keys(AdminRegistrationService::REQUIRED_DOCUMENT_TYPES))],
            'file' => ['required', 'file', 'max:10240'],
        ]);

        /** @var UploadedFile $uploadedFile */
        $uploadedFile = $validated['file'];
        $documentType = (string) $validated['document_type'];
        $documentName = AdminRegistrationService::REQUIRED_DOCUMENT_TYPES[$documentType] ?? $documentType;

        $extension = $uploadedFile->getClientOriginalExtension();
        $filename = $documentType.'_'.now()->format('Ymd_His').'_'.Str::lower(Str::random(6)).($extension !== '' ? '.'.$extension : '');
        $path = $uploadedFile->storeAs('required-documents/'.$registrationLink->id, $filename, 'local');
        $absolutePath = Storage::disk('local')->path($path);

        $extractionPayload = [];
        if (in_array($documentType, ['certificate_of_registration', 'cover_sheet', 'articles_of_corporation'], true)) {
            $extractionPayload = $this->requiredDocumentExtractionService->extractFieldsForDocument(
                documentType: $documentType,
                absolutePath: $absolutePath,
                originalFilename: $uploadedFile->getClientOriginalName(),
            );
        }

        $existing = $registrationLink->requiredDocuments()->where('document_type', $documentType)->first();
        if ($existing !== null) {
            Storage::disk('local')->delete($existing->file_path);
        }

        $registrationLink->requiredDocuments()->updateOrCreate(
            ['document_type' => $documentType],
            [
                'document_name' => $documentName,
                'original_filename' => $uploadedFile->getClientOriginalName(),
                'file_path' => $path,
                'mime_type' => $uploadedFile->getClientMimeType(),
                'extraction_payload' => $extractionPayload !== [] ? $extractionPayload : null,
                'uploaded_by' => $request->user()?->id,
            ],
        );

        return back()->with('success', "{$documentName} uploaded successfully.");
    }

    public function viewRequiredDocument(
        RegistrationLink $registrationLink,
        RegistrationRequiredDocument $requiredDocument,
    ): BinaryFileResponse {
        $this->assertRequiredDocumentOwnership($registrationLink, $requiredDocument);

        $path = Storage::disk('local')->path($requiredDocument->file_path);

        return response()->file($path, [
            'Content-Type' => $requiredDocument->mime_type ?? 'application/octet-stream',
        ]);
    }

    public function downloadRequiredDocument(
        RegistrationLink $registrationLink,
        RegistrationRequiredDocument $requiredDocument,
    ): BinaryFileResponse {
        $this->assertRequiredDocumentOwnership($registrationLink, $requiredDocument);

        $path = Storage::disk('local')->path($requiredDocument->file_path);

        return response()->download(
            $path,
            $requiredDocument->original_filename,
            ['Content-Type' => $requiredDocument->mime_type ?? 'application/octet-stream'],
        );
    }

    public function destroyRequiredDocument(
        RegistrationLink $registrationLink,
        RegistrationRequiredDocument $requiredDocument,
    ): RedirectResponse {
        $this->assertRequiredDocumentOwnership($registrationLink, $requiredDocument);

        Storage::disk('local')->delete($requiredDocument->file_path);
        $documentName = $requiredDocument->document_name;
        $requiredDocument->delete();

        return back()->with('success', "{$documentName} deleted successfully.");
    }

    private function assertRequiredDocumentOwnership(
        RegistrationLink $registrationLink,
        RegistrationRequiredDocument $requiredDocument,
    ): void {
        if ($requiredDocument->registration_link_id !== $registrationLink->id) {
            abort(404);
        }
    }
}
