<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class RegistrationLinkMail extends Mailable
{
    use Queueable, SerializesModels;

    public $link; // the registration link

    public function __construct(string $link)
    {
        $this->link = $link;
    }

    public function build()
    {
        return $this->subject('Complete Your Registration')
                    ->view('emails.registration-link'); 
    }
}