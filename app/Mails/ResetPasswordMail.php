<?php
/**
 * Created by PhpStorm.
 * User: sebastiansimko
 * Date: 16.2.2019
 * Time: 15:24
 */

namespace App\Mails;


use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ResetPasswordMail extends Mailable
{
    use Queueable, SerializesModels;

    public $password;

    public function __construct($password)
    {
        $this->password = $password;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->markdown('emails.reset-password')
            ->subject('Tvoje heslo bolo restovane')
            ->with([
                'password' =>  $this->password
            ]);
    }
}