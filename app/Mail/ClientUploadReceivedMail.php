<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ClientUploadReceivedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly string $companyTypeLabel,
        public readonly int $filesCount,
    ) {
    }

    public function build(): self
    {
        return $this
            ->subject('We received your files')
            ->view('emails.registration.received')
            ->with([
                'companyTypeLabel' => $this->companyTypeLabel,
                'filesCount' => $this->filesCount,
            ]);
    }
}
