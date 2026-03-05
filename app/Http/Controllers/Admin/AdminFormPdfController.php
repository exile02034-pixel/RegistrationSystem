<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\PrintSubmissionBatchRequest;
use App\Models\FormSubmission;
use App\Services\Admin\AdminSubmissionPdfService;
use Illuminate\Http\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;

class AdminFormPdfController extends Controller
{
    public function __construct(
        private readonly AdminSubmissionPdfService $submissionPdfService,
    ) {}

    public function view(FormSubmission $submission, string $section): Response
    {
        return $this->submissionPdfService->view($submission, $section);
    }

    public function download(FormSubmission $submission, string $section): Response
    {
        return $this->submissionPdfService->download($submission, $section);
    }

    public function destroy(FormSubmission $submission, string $section): RedirectResponse
    {
        $this->submissionPdfService->deleteSection($submission, $section);

        return back()->with('success', 'Form deleted successfully.');
    }

    public function printBatch(PrintSubmissionBatchRequest $request, FormSubmission $submission): Response
    {
        return $this->submissionPdfService->printBatch(
            $submission,
            (array) $request->validated('sections', []),
        );
    }
}
