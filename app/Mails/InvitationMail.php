<?php


namespace App\Mails;


use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class InvitationMail extends Mailable
{
    use Queueable, SerializesModels;

    public $user;

    public function __construct($userFirstName)
    {
        $this->user = $userFirstName;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->markdown('emails.invitation')
            ->subject('Pozvanka')
            ->with([
                'user' => $this->user
            ]);
    }
}