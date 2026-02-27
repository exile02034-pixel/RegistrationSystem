<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class MissingDocumentsFollowUpMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * @param  array<int, string>  $missingDocuments
     * @param  array<int, array{path: string, name: string}>  $missingAttachments
     */
    public function __construct(
        public readonly string $companyTypeLabel,
        public readonly string $uploadUrl,
        public readonly array $missingDocuments,
        public readonly array $missingAttachments,
    ) {
    }

    public function build(): self
    {
        $mail = $this
            ->subject('Follow-up: Missing Registration Documents')
            ->view('emails.registration.missing-documents-follow-up')
            ->with([
                'companyTypeLabel' => $this->companyTypeLabel,
                'uploadUrl' => $this->uploadUrl,
                'missingDocuments' => $this->missingDocuments,
            ]);

        foreach ($this->missingAttachments as $attachment) {
            $mail->attach(base_path($attachment['path']), [
                'as' => $attachment['name'],
                'mime' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            ]);
        }

        return $mail;
    }
}
