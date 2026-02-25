<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Models\RegistrationLink;

class ClientRegistrationLink extends Mailable
{
    use Queueable, SerializesModels;

    public $link;

    public function __construct(RegistrationLink $link)
    {
        $this->link = $link;
    }

    public function build()
    {
        return $this->subject('Complete Your Registration')
                    ->markdown('emails.registration.link');
    }
}