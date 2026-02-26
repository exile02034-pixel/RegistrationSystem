<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class RegistrationLinkMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * @param  array<string, array{path: string, name: string}>  $templateAttachments
     */
    public function __construct(
        public readonly string $registrationUrl,
        public readonly string $companyTypeLabel,
        public readonly array $templateAttachments,
    ) {
    }

    public function build(): self
    {
        $qrCodeUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=220x220&data='.urlencode($this->registrationUrl);

        $mail = $this
            ->subject('Complete Your Registration')
            ->view('emails.registration-link')
            ->with([
                'registrationUrl' => $this->registrationUrl,
                'companyTypeLabel' => $this->companyTypeLabel,
                'qrCodeUrl' => $qrCodeUrl,
            ]);

        foreach ($this->templateAttachments as $attachment) {
            $mail->attach(base_path($attachment['path']), [
                'as' => $attachment['name'],
                'mime' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            ]);
        }

        return $mail;
    }
}
