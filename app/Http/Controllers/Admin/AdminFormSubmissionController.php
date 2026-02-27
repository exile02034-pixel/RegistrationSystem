<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateFormSectionRequest;
use App\Models\FormSubmission;
use App\Services\RegistrationFormService;
use Illuminate\Http\RedirectResponse;
use InvalidArgumentException;

class AdminFormSubmissionController extends Controller
{
    public function __construct(
        private readonly RegistrationFormService $registrationFormService,
    ) {}

    public function updateSection(UpdateFormSectionRequest $request, FormSubmission $submission, string $section): RedirectResponse
    {
        try {
            $this->registrationFormService->updateSection(
                submission: $submission,
                section: $section,
                fields: (array) $request->validated('fields', []),
                updatedBy: $request->user(),
            );
        } catch (InvalidArgumentException) {
            abort(404);
        }

        return back()->with('success', 'Form updated successfully.');
    }
}
