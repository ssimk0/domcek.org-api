<?php


namespace App\Mails;


use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ForgotPasswordMail extends Mailable
{
    use Queueable, SerializesModels;

    public $token;

    public function __construct($token)
    {
        $this->token = $token;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->markdown('emails.forgot-password')
            ->subject('Zabudnute Heslo')
            ->with([
                'url' => env('APP_URL') . '/reset-password?token=' . $this->token
            ]);
    }
}
