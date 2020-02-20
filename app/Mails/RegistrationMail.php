<?php
/**
 * Created by PhpStorm.
 * User: sebastiansimko
 * Date: 16.2.2019
 * Time: 15:24
 */

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
        $min = Carbon::now()->subYear(18);

        if ($min->lt(Carbon::parse($this->birthDate))) {
            return $this->markdown($template)
                ->subject('Potvrdenie Prihlasenia')
                ->with([
                    'url' => $this->url,
                    'deposit' => $this->deposit,
                    'price' => $this->price,
                    'paymentNumber' => $this->paymentNumber,
                    'userName' => $this->userName,
                    'eventName' => $this->eventName
                ])
                ->attach($this->qrCodePath)
                ->attach('https://s3.nl-ams.scw.cloud/org.domcek/docs/Pre+%C3%BA%C4%8Dastn%C3%ADkov+mlad%C5%A1%C3%ADch+ako+18+rokov.docx');
        } else {
            return $this->markdown($template)
                ->subject('Potvrdenie Prihlasenia')
                ->with([
                    'url' => $this->url,
                    'deposit' => $this->deposit,
                    'price' => $this->price,
                    'paymentNumber' => $this->paymentNumber,
                    'userName' => $this->userName,
                    'eventName' => $this->eventName
                ])
                ->attach($this->qrCodePath);
        }
    }
}
