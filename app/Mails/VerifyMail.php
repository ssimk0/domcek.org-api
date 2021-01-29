<?php

namespace App\Mails;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class VerifyMail extends Mailable
{
    use Queueable, SerializesModels;

    public $token;
    public $email;

    public function __construct($token, $email)
    {
        $this->token = $token;
        $this->email = $email;
    }

    public function build()
    {
        return $this->markdown('emails.verify-user')
            ->subject('Overenie emailu')
            ->with([
                'url' => env('APP_URL')."/verify-email?token=$this->token&email=$this->email",
            ]);
    }
}
