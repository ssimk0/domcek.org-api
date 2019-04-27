<?php


namespace App\Mails;


use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ConfirmPaymentMail extends Mailable
{
    use Queueable, SerializesModels;

    public $paymentsDetails;
    public $user;

    public function __construct($paymentsDetails, $user)
    {
        $this->paymentsDetails = $paymentsDetails;
        $this->user = $user;
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
                'details' => $this->paymentsDetails,
                'user' => $this->user
            ]);
    }
}