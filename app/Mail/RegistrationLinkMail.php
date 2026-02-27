<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class RegistrationLinkMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly string $registrationUrl,
        public readonly string $companyTypeLabel,
        public readonly string $qrCodeDataUri,
    ) {}

    public function build(): self
    {
        return $this
            ->subject('Complete Your Registration')
            ->view('emails.registration-link')
            ->with([
                'registrationUrl' => $this->registrationUrl,
                'companyTypeLabel' => $this->companyTypeLabel,
                'qrCodeDataUri' => $this->qrCodeDataUri,
            ]);
    }
}
