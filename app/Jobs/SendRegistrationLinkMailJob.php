<?php

namespace App\Jobs;

use App\Mail\RegistrationLinkMail;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class SendRegistrationLinkMailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public readonly string $email,
        public readonly string $registrationUrl,
        public readonly string $companyTypeLabel,
        public readonly string $qrCodeDataUri,
    ) {}

    public function handle(): void
    {
        Mail::to($this->email)->send(new RegistrationLinkMail(
            registrationUrl: $this->registrationUrl,
            companyTypeLabel: $this->companyTypeLabel,
            qrCodeDataUri: $this->qrCodeDataUri,
        ));
    }
}
