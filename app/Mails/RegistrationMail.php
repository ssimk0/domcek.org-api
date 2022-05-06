<?php

namespace App\Mails;

use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class RegistrationMail extends Mailable
{
    use Queueable, SerializesModels;

    public $deposit;
    public $userName;
    public $price;
    public $paymentNumber;
    public $url;
    private $eventName;
    private $birthDate;
    private $qrCodePath;
    private $isVolunteer;

    public function __construct($deposit, $price, $userName, $birthDate, $paymentNumber, $eventName, $url, $qrCodePath, $isVolunteer)
    {
        $this->deposit = $deposit;
        $this->price = $price;
        $this->userName = $userName;
        $this->birthDate = $birthDate;
        $this->paymentNumber = $paymentNumber;
        $this->url = $url;
        $this->eventName = $eventName;
        $this->qrCodePath = $qrCodePath;
        $this->isVolunteer = $isVolunteer;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $template = $this->isVolunteer ? 'emails.registrationVolunteer' : 'emails.registration';
        $min = Carbon::now()->subYears(18);

        if ($min->lt(Carbon::parse($this->birthDate))) {
            return $this->markdown($template)
                ->subject('Potvrdenie Prihlasenia')
                ->with([
                    'url' => $this->url,
                    'deposit' => $this->deposit,
                    'price' => $this->price,
                    'paymentNumber' => $this->paymentNumber,
                    'userName' => $this->userName,
                    'eventName' => $this->eventName,
                ])
                ->attach($this->qrCodePath)
                ->attach('https://s3.nl-ams.scw.cloud/org.domcek/docs/Potvrdenie%20rodic%CC%8Ca.pdf');
        } else {
            return $this->markdown($template)
                ->subject('Potvrdenie Prihlasenia')
                ->with([
                    'url' => $this->url,
                    'deposit' => $this->deposit,
                    'price' => $this->price,
                    'paymentNumber' => $this->paymentNumber,
                    'userName' => $this->userName,
                    'eventName' => $this->eventName,
                ])
                ->attach($this->qrCodePath);
        }
    }
}
