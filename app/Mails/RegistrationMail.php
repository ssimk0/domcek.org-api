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

class RegistrationMail extends Mailable
{
    use Queueable, SerializesModels;

    public $deposit;
    public $userName;
    public $paymentNumber;
    public $url;
    private $eventName;

    public function __construct($deposit, $userName, $paymentNumber, $eventName, $url)
    {
        $this->deposit = $deposit;
        $this->userName = $userName;
        $this->paymentNumber = $paymentNumber;
        $this->url = $url;
        $this->eventName = $eventName;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->markdown('emails.registration')->with([
            'url' =>  $this->url,
            'deposit' =>  $this->deposit,
            'paymentNumber' =>  $this->paymentNumber,
            'userName' =>  $this->userName,
            'eventName' => $this->eventName
        ]);
    }
}