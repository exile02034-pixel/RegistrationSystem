<?php

namespace App\Services\Admin;

use App\Mail\RegistrationSelectedPdfsMail;
use App\Models\RegistrationGeneratedDocument;
use App\Models\RegistrationLink;
use App\Services\FormPdfService;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use InvalidArgumentException;

class AdminRegistrationPdfEmailService
{
    public function __construct(
        private readonly FormPdfService $formPdfService,
        private readonly AdminDocumentService $adminDocumentService,
    ) {}

    /**
     * @param array<int, string> $sections
     * @param array<int, string> $documentIds
     */
    public function sendSelectedPdfs(RegistrationLink $registrationLink, array $sections, array $documentIds): int
    {
        $sections = array_values(array_unique(array_filter($sections)));
        $documentIds = array_values(array_unique(array_filter($documentIds)));

        $attachments = [];
        $documentNames = [];

        if ($sections !== []) {
            $submission = $registrationLink->formSubmission;

            if ($submission === null) {
                throw new InvalidArgumentException('No form submission is available for this registration.');
            }

            foreach ($sections as $section) {
                if (! $this->formPdfService->hasSectionData($submission, $section)) {
                    continue;
                }

                $pdf = $this->formPdfService->pdfPayload($submission, $section);
                $filename = $this->ensurePdfExtension((string) ($pdf['name'] ?? $section.'.pdf'));

                $attachments[] = [
                    'name' => $this->uniqueAttachmentName($filename, $attachments),
                    'content' => (string) ($pdf['binary'] ?? ''),
                ];

                $documentNames[] = Str::headline(str_replace('_', ' ', $section));
            }
        }

        if ($documentIds !== []) {
            $documents = $registrationLink->generatedDocuments()
                ->whereIn('id', $documentIds)
                ->get();

            if ($documents->count() !== count($documentIds)) {
                throw new InvalidArgumentException('Some selected generated documents are invalid.');
            }

            foreach ($documents as $document) {
                $attachments[] = $this->buildGeneratedDocumentAttachment($registrationLink, $document, $attachments);
                $documentNames[] = $document->document_name;
            }
        }

        if ($attachments === []) {
            throw new InvalidArgumentException('No valid PDF files were selected.');
        }

        Mail::to($registrationLink->email)->send(new RegistrationSelectedPdfsMail(
            registrationLink: $registrationLink,
            attachments: $attachments,
            documentNames: $documentNames,
        ));

        return count($attachments);
    }

    /**
     * @param array<int, array{name: string, content: string}> $existingAttachments
     * @return array{name: string, content: string}
     */
    private function buildGeneratedDocumentAttachment(
        RegistrationLink $registrationLink,
        RegistrationGeneratedDocument $document,
        array $existingAttachments,
    ): array {
        $path = $this->adminDocumentService->resolvePdfPath($registrationLink, $document);
        $content = file_get_contents($path);

        if ($content === false) {
            throw new InvalidArgumentException("Unable to read generated PDF: {$document->document_name}.");
        }

        $filename = $this->adminDocumentService->downloadFilename($document);

        return [
            'name' => $this->uniqueAttachmentName($filename, $existingAttachments),
            'content' => $content,
        ];
    }

    /**
     * @param array<int, array{name: string, content: string}> $existingAttachments
     */
    private function uniqueAttachmentName(string $filename, array $existingAttachments): string
    {
        $existingNames = collect($existingAttachments)->pluck('name')->all();

        if (! in_array($filename, $existingNames, true)) {
            return $filename;
        }

        $baseName = pathinfo($filename, PATHINFO_FILENAME);
        $extension = pathinfo($filename, PATHINFO_EXTENSION);
        $counter = 2;

        do {
            $candidate = $extension !== ''
                ? "{$baseName}-{$counter}.{$extension}"
                : "{$baseName}-{$counter}";
            $counter++;
        } while (in_array($candidate, $existingNames, true));

        return $candidate;
    }

    private function ensurePdfExtension(string $filename): string
    {
        return str_ends_with(strtolower($filename), '.pdf')
            ? $filename
            : $filename.'.pdf';
    }
}
