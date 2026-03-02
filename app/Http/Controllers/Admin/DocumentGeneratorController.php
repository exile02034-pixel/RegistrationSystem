<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\GenerateRegistrationDocumentRequest;
use App\Models\RegistrationGeneratedDocument;
use App\Models\RegistrationLink;
use App\Services\DocumentGenerationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class DocumentGeneratorController extends Controller
{
    public function __construct(
        private readonly DocumentGenerationService $documentGenerationService,
    ) {}

    public function generate(
        GenerateRegistrationDocumentRequest $request,
        RegistrationLink $registrationLink,
        string $documentType,
    ): JsonResponse|RedirectResponse {
        try {
            $document = $this->documentGenerationService->generate(
                registrationLink: $registrationLink,
                documentType: $documentType,
                fields: (array) $request->input('fields', []),
                generatedBy: $request->user(),
            );
        } catch (\Throwable $e) {
            throw ValidationException::withMessages([
                'document' => $e->getMessage(),
            ]);
        }

        $payload = [
            'id' => $document->id,
            'document_type' => $document->document_type,
            'document_name' => $document->document_name,
            'pdf_path' => $document->pdf_path,
            'view_url' => route('admin.register.documents.view', [$registrationLink->id, $document->id]),
            'download_url' => route('admin.register.documents.download', [$registrationLink->id, $document->id]),
        ];

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Document generated successfully.',
                'document' => $payload,
            ]);
        }

        return back()->with('success', 'Document generated successfully.');
    }

    public function view(RegistrationLink $registrationLink, RegistrationGeneratedDocument $document): BinaryFileResponse
    {
        $this->assertOwnership($registrationLink, $document);

        if (! $this->documentGenerationService->ensurePdfExists($document)) {
            abort(404, 'Generated PDF file no longer exists on disk.');
        }

        $absolutePath = $this->resolveAbsolutePdfPath($document);

        if ($absolutePath === null) {
            abort(404, 'Generated PDF file no longer exists on disk.');
        }

        return response()->file(
            $absolutePath,
            ['Content-Type' => 'application/pdf'],
        );
    }

    public function download(RegistrationLink $registrationLink, RegistrationGeneratedDocument $document): BinaryFileResponse
    {
        $this->assertOwnership($registrationLink, $document);

        if (! $this->documentGenerationService->ensurePdfExists($document)) {
            abort(404, 'Generated PDF file no longer exists on disk.');
        }

        $absolutePath = $this->resolveAbsolutePdfPath($document);

        if ($absolutePath === null) {
            abort(404, 'Generated PDF file no longer exists on disk.');
        }

        return response()->download(
            $absolutePath,
            Str::slug($document->document_name, '-').'.pdf',
            ['Content-Type' => 'application/pdf'],
        );
    }

    public function destroy(RegistrationLink $registrationLink, RegistrationGeneratedDocument $document): RedirectResponse
    {
        $this->assertOwnership($registrationLink, $document);

        $this->documentGenerationService->delete($document);

        return back()->with('success', 'Generated document deleted successfully.');
    }

    private function assertOwnership(RegistrationLink $registrationLink, RegistrationGeneratedDocument $document): void
    {
        if ($document->registration_link_id !== $registrationLink->id) {
            abort(404);
        }
    }

    private function resolveAbsolutePdfPath(RegistrationGeneratedDocument $document): ?string
    {
        $localPath = Storage::disk('local')->path($document->pdf_path);
        if (is_file($localPath)) {
            return $localPath;
        }

        // Backward compatibility for rows generated when files were stored under storage/app.
        $legacyPath = storage_path('app/'.$document->pdf_path);

        return is_file($legacyPath) ? $legacyPath : null;
    }
}
