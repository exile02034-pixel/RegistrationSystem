<?php

namespace App\Services\User;

use App\Models\FormSubmission;
use App\Models\User;
use App\Services\RegistrationFormService;
use InvalidArgumentException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\HttpException;

class UserSubmissionService
{
    public function __construct(
        private readonly RegistrationFormService $registrationFormService,
    ) {}

    public function updateSection(?User $user, FormSubmission $submission, string $section, array $fields): void
    {
        $this->assertOwnership($user, $submission);

        try {
            $this->registrationFormService->updateSection(
                submission: $submission,
                section: $section,
                fields: $fields,
                updatedBy: $user,
            );
        } catch (InvalidArgumentException) {
            throw new NotFoundHttpException;
        }
    }

    public function assertOwnership(?User $user, FormSubmission $submission): void
    {
        if ($user === null || $submission->email !== $user->email) {
            throw new HttpException(403, 'Unauthorized action.');
        }
    }
}
