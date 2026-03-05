<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\PrintSubmissionBatchRequest;
use App\Models\FormSubmission;
use App\Services\User\UserSubmissionPdfService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class UserFormPdfController extends Controller
{
    public function __construct(
        private readonly UserSubmissionPdfService $submissionPdfService,
    ) {}

    public function view(Request $request, FormSubmission $submission, string $section): Response
    {
        return $this->submissionPdfService->view($request->user(), $submission, $section);
    }

    public function download(Request $request, FormSubmission $submission, string $section): Response
    {
        return $this->submissionPdfService->download($request->user(), $submission, $section);
    }

    public function destroy(Request $request, FormSubmission $submission, string $section): RedirectResponse
    {
        $this->submissionPdfService->deleteSection($request->user(), $submission, $section);

        return back()->with('success', 'Form deleted successfully.');
    }

    public function printBatch(PrintSubmissionBatchRequest $request, FormSubmission $submission): Response
    {
        return $this->submissionPdfService->printBatch(
            $request->user(),
            $submission,
            (array) $request->validated('sections', []),
        );
    }
}
