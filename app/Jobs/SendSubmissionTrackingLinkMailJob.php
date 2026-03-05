<?php

namespace App\Jobs;

use App\Mail\SubmissionTrackingLinkMail;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class SendSubmissionTrackingLinkMailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public readonly string $email,
        public readonly string $trackingUrl,
    ) {}

    public function handle(): void
    {
        Mail::to($this->email)->send(new SubmissionTrackingLinkMail(
            trackingUrl: $this->trackingUrl,
        ));
    }
}
