<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\GenerateRegistrationDocumentRequest;
use App\Models\RegistrationGeneratedDocument;
use App\Models\RegistrationLink;
use App\Services\Admin\AdminDocumentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class DocumentGeneratorController extends Controller
{
    public function __construct(
        private readonly AdminDocumentService $adminDocumentService,
    ) {}

    public function generate(
        GenerateRegistrationDocumentRequest $request,
        RegistrationLink $registrationLink,
        string $documentType,
    ): JsonResponse|RedirectResponse {
        try {
            $document = $this->adminDocumentService->generate(
                registrationLink: $registrationLink,
                documentType: $documentType,
                fields: $this->resolvedFields($request),
                generatedBy: $request->user(),
            );
        } catch (\Throwable $e) {
            throw ValidationException::withMessages([
                'document' => $e->getMessage(),
            ]);
        }

        $payload = $this->adminDocumentService->documentPayload($registrationLink, $document);

        if ($request->expectsJson()) {
            return response()->json($this->adminDocumentService->generatedJsonResponsePayload($payload));
        }

        return back()->with('success', 'Document generated successfully.');
    }

    private function resolvedFields(Request $request): array
    {
        $json = $request->input('fields_json');
        if (is_string($json) && trim($json) !== '') {
            $decoded = json_decode($json, true);

            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                return $decoded;
            }
        }

        return (array) $request->input('fields', []);
    }

    public function view(RegistrationLink $registrationLink, RegistrationGeneratedDocument $document): BinaryFileResponse
    {
        $absolutePath = $this->adminDocumentService->resolvePdfPath($registrationLink, $document);

        return response()->file(
            $absolutePath,
            ['Content-Type' => 'application/pdf'],
        );
    }

    public function download(RegistrationLink $registrationLink, RegistrationGeneratedDocument $document): BinaryFileResponse
    {
        $absolutePath = $this->adminDocumentService->resolvePdfPath($registrationLink, $document);

        return response()->download(
            $absolutePath,
            $this->adminDocumentService->downloadFilename($document),
            ['Content-Type' => 'application/pdf'],
        );
    }

    public function destroy(RegistrationLink $registrationLink, RegistrationGeneratedDocument $document): RedirectResponse
    {
        $this->adminDocumentService->delete($registrationLink, $document);

        return back()->with('success', 'Generated document deleted successfully.');
    }
}
