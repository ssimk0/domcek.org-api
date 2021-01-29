<?php

namespace App\Mails;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ConfirmPaymentMail extends Mailable
{
    use Queueable, SerializesModels;

    public $amount;
    public $user;

    public function __construct($amount, $userFirstName)
    {
        $this->amount = $amount;
        $this->user = $userFirstName;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->markdown('emails.confirmPayment')
            ->subject('Potvrdenie Platby')
            ->with([
                'amount' => $this->amount,
                'user' => $this->user,
            ]);
    }
}
