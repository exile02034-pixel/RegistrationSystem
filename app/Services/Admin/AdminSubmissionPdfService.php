<?php

namespace App\Services\Admin;

use App\Models\FormSubmission;
use App\Services\FormPdfService;
use InvalidArgumentException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class AdminSubmissionPdfService
{
    public function __construct(
        private readonly FormPdfService $formPdfService,
    ) {}

    public function view(FormSubmission $submission, string $section): Response
    {
        try {
            return $this->formPdfService->streamPdf($submission, $section);
        } catch (InvalidArgumentException) {
            throw new NotFoundHttpException;
        }
    }

    public function download(FormSubmission $submission, string $section): Response
    {
        try {
            return $this->formPdfService->downloadPdf($submission, $section);
        } catch (InvalidArgumentException) {
            throw new NotFoundHttpException;
        }
    }

    public function deleteSection(FormSubmission $submission, string $section): void
    {
        try {
            $this->formPdfService->deleteSection($submission, $section);
        } catch (InvalidArgumentException) {
            throw new NotFoundHttpException;
        }
    }

    public function printBatch(FormSubmission $submission, array $sections): Response
    {
        try {
            return $this->formPdfService->mergeToPdf($submission, $sections);
        } catch (InvalidArgumentException) {
            throw new NotFoundHttpException;
        }
    }
}
