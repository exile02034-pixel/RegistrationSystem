<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class SubmissionTrackingLinkMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly string $trackingUrl,
    ) {}

    public function build(): self
    {
        return $this
            ->subject('Your Submission Tracking Link')
            ->view('emails.submission-tracking-link')
            ->with([
                'trackingUrl' => $this->trackingUrl,
            ]);
    }
}
