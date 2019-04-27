<?php


namespace App\Services;


use App\Mails\ConfirmPaymentMail;
use App\Repositories\PaymentRepository;
use App\Repositories\UserRepository;
use Illuminate\Support\Facades\Mail;

class PaymentService extends Service
{
    private $repository;
    private $userRepository;

    public function __construct(PaymentRepository $repository, UserRepository $userRepository)
    {
        $this->repository = $repository;
        $this->userRepository = $userRepository;
    }


    public function processPayments($payments, $eventId) {

        foreach($payments as $i=>$payment) {
            $matched = false;

            if ($payment['paymentNumber']) {
                $dbPayment = $this->repository->findByPaymentNumber($payment['paymentNumber'], $eventId);
                if ($dbPayment && $dbPayment->paid < $payment['amount']) {
                    try {
                        $this->repository->edit($dbPayment->user_id, $dbPayment->event_id, intval($payment['amount']));
                        $user = $this->userRepository->findUserWithProfile($dbPayment->user_id);
                        // Don't send too much emails same time
                        $when = now()->addMinutes($i);
                        Mail::to($user->email)
                            ->later($when, new ConfirmPaymentMail($payment, $user));
                    } catch (\Exception $e) {
                        $this->logError('Problem pri updatovani platby: ' . json_encode($payment) . ' error: '.$e);
                    }
                    $matched = true;
                }
            }

            if (!$matched) {
                $this->repository->addNotMatchedPayment($payment, $eventId);
            }
        }
    }
}