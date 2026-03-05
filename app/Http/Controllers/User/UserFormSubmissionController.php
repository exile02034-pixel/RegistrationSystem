<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateFormSectionRequest;
use App\Models\FormSubmission;
use App\Services\User\UserSubmissionService;
use Illuminate\Http\RedirectResponse;

class UserFormSubmissionController extends Controller
{
    public function __construct(
        private readonly UserSubmissionService $submissionService,
    ) {}

    public function updateSection(UpdateFormSectionRequest $request, FormSubmission $submission, string $section): RedirectResponse
    {
        $this->submissionService->updateSection(
            $request->user(),
            $submission,
            $section,
            (array) $request->validated('fields', []),
        );

        return back()->with('success', 'Form updated successfully.');
    }
}
