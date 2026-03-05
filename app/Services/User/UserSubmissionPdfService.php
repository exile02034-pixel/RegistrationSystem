<?php

namespace App\Services\User;

use App\Models\FormSubmission;
use App\Models\User;
use App\Services\FormPdfService;
use InvalidArgumentException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\HttpException;

class UserSubmissionPdfService
{
    public function __construct(
        private readonly FormPdfService $formPdfService,
    ) {}

    public function view(?User $user, FormSubmission $submission, string $section): Response
    {
        $this->assertOwnership($user, $submission);

        try {
            return $this->formPdfService->streamPdf($submission, $section);
        } catch (InvalidArgumentException) {
            throw new NotFoundHttpException;
        }
    }

    public function download(?User $user, FormSubmission $submission, string $section): Response
    {
        $this->assertOwnership($user, $submission);

        try {
            return $this->formPdfService->downloadPdf($submission, $section);
        } catch (InvalidArgumentException) {
            throw new NotFoundHttpException;
        }
    }

    public function deleteSection(?User $user, FormSubmission $submission, string $section): void
    {
        $this->assertOwnership($user, $submission);

        try {
            $this->formPdfService->deleteSection($submission, $section);
        } catch (InvalidArgumentException) {
            throw new NotFoundHttpException;
        }
    }

    public function printBatch(?User $user, FormSubmission $submission, array $sections): Response
    {
        $this->assertOwnership($user, $submission);

        try {
            return $this->formPdfService->mergeToPdf($submission, $sections);
        } catch (InvalidArgumentException) {
            throw new NotFoundHttpException;
        }
    }

    private function assertOwnership(?User $user, FormSubmission $submission): void
    {
        if ($user === null || $submission->email !== $user->email) {
            throw new HttpException(403, 'Unauthorized action.');
        }
    }
}
