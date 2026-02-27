<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\FormSubmission;
use App\Services\FormPdfService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use InvalidArgumentException;
use Symfony\Component\HttpFoundation\Response;

class UserFormPdfController extends Controller
{
    public function __construct(
        private readonly FormPdfService $formPdfService,
    ) {}

    public function view(Request $request, FormSubmission $submission, string $section): Response
    {
        abort_unless($submission->email === $request->user()?->email, 403);

        try {
            return $this->formPdfService->streamPdf($submission, $section);
        } catch (InvalidArgumentException) {
            abort(404);
        }
    }

    public function download(Request $request, FormSubmission $submission, string $section): Response
    {
        abort_unless($submission->email === $request->user()?->email, 403);

        try {
            return $this->formPdfService->downloadPdf($submission, $section);
        } catch (InvalidArgumentException) {
            abort(404);
        }
    }

    public function destroy(Request $request, FormSubmission $submission, string $section): RedirectResponse
    {
        abort_unless($submission->email === $request->user()?->email, 403);

        try {
            $this->formPdfService->deleteSection($submission, $section);
        } catch (InvalidArgumentException) {
            abort(404);
        }

        return back()->with('success', 'Form deleted successfully.');
    }

    public function printBatch(Request $request, FormSubmission $submission): Response
    {
        abort_unless($submission->email === $request->user()?->email, 403);

        $validated = $request->validate([
            'sections' => ['required', 'array', 'min:1'],
            'sections.*' => ['required', 'string', 'in:client_information,treasurer_details,opc_details,proprietorship,regular_corporation'],
        ]);

        try {
            return $this->formPdfService->mergeToPdf($submission, $validated['sections']);
        } catch (InvalidArgumentException) {
            abort(404);
        }
    }
}
