<?php

namespace App\Mail;

use App\Models\RegistrationLink;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class RegistrationSelectedPdfsMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * @var array<int, array{name: string, content: string}>
     */
    private readonly array $pdfAttachments;

    /**
     * @param array<int, array{name: string, content: string}> $attachments
     * @param array<int, string> $documentNames
     */
    public function __construct(
        public readonly RegistrationLink $registrationLink,
        public readonly array $documentNames,
        array $attachments,
    ) {
        $this->pdfAttachments = $attachments;
    }

    public function build(): self
    {
        $mail = $this
            ->subject('Selected registration PDFs')
            ->view('emails.registration.selected-pdfs', [
                'registrationLink' => $this->registrationLink,
                'documentNames' => $this->documentNames,
            ]);

        foreach ($this->pdfAttachments as $attachment) {
            $mail->attachData(
                $attachment['content'],
                $attachment['name'],
                ['mime' => 'application/pdf'],
            );
        }

        return $mail;
    }
}
