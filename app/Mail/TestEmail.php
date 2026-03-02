<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class TestEmail extends Mailable
{
    use Queueable, SerializesModels;

    public $messageText;

    public function __construct($messageText)
    {
        $this->messageText = $messageText;
    }

    public function build()
    {
        return $this->subject('Laravel Email Test')
            ->view('emails.test'); // We'll create this view next
    }
}
