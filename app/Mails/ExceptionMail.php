<?php
/**
 * Created by PhpStorm.
 * User: sebastiansimko
 * Date: 6.3.2019
 * Time: 19:20.
 */

namespace App\Mails;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ExceptionMail extends Mailable
{
    use Queueable, SerializesModels;

    public $exception;

    public function __construct(\Exception $exception)
    {
        $this->exception = $exception;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->markdown('emails.exception')->with([
            'exceptionMessage' =>  $this->exception->getMessage(),
            'exceptionStackTrace' => $this->exception->getTraceAsString(),
        ]);
    }
}
