<?php

namespace App\Services\Admin;

use App\Models\RegistrationGeneratedDocument;
use App\Models\RegistrationLink;
use App\Models\User;
use App\Services\DocumentGenerationService;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class AdminDocumentService
{
    public function __construct(
        private readonly DocumentGenerationService $documentGenerationService,
    ) {}

    public function generate(
        RegistrationLink $registrationLink,
        string $documentType,
        array $fields,
        ?User $generatedBy,
    ): RegistrationGeneratedDocument {
        return $this->documentGenerationService->generate(
            registrationLink: $registrationLink,
            documentType: $documentType,
            fields: $fields,
            generatedBy: $generatedBy,
        );
    }

    public function documentPayload(
        RegistrationLink $registrationLink,
        RegistrationGeneratedDocument $document,
    ): array {
        $this->assertOwnership($registrationLink, $document);

        return [
            'id' => $document->id,
            'document_type' => $document->document_type,
            'document_name' => $document->document_name,
            'pdf_path' => $document->pdf_path,
            'view_url' => route('admin.register.documents.view', [$registrationLink->id, $document->id]),
            'download_url' => route('admin.register.documents.download', [$registrationLink->id, $document->id]),
        ];
    }

    public function generatedJsonResponsePayload(array $documentPayload): array
    {
        return [
            'message' => 'Document generated successfully.',
            'document' => $documentPayload,
        ];
    }

    public function resolvePdfPath(RegistrationLink $registrationLink, RegistrationGeneratedDocument $document): string
    {
        $this->assertOwnership($registrationLink, $document);

        if (! $this->documentGenerationService->ensurePdfExists($document)) {
            throw new NotFoundHttpException('Generated PDF file no longer exists on disk.');
        }

        $absolutePath = $this->resolveAbsolutePdfPath($document);

        if ($absolutePath === null) {
            throw new NotFoundHttpException('Generated PDF file no longer exists on disk.');
        }

        return $absolutePath;
    }

    public function delete(RegistrationLink $registrationLink, RegistrationGeneratedDocument $document): void
    {
        $this->assertOwnership($registrationLink, $document);
        $this->documentGenerationService->delete($document);
    }

    public function downloadFilename(RegistrationGeneratedDocument $document): string
    {
        return Str::slug($document->document_name, '-').'.pdf';
    }

    private function assertOwnership(RegistrationLink $registrationLink, RegistrationGeneratedDocument $document): void
    {
        if ($document->registration_link_id !== $registrationLink->id) {
            throw new NotFoundHttpException;
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
