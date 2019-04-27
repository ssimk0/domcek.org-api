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


    public function processPayments($payments, $eventId)
    {

        foreach ($payments as $payment) {
            $matched = false;

            if ($payment['paymentNumber']) {
                $dbPayment = $this->repository->findByPaymentNumber($payment['paymentNumber'], $eventId);

                if ($dbPayment && intval($dbPayment->paid) < intval($payment['amount'])) {
                    try {
                        $user = $this->userRepository->findUserWithProfile($dbPayment->user_id);
                        $mail = new ConfirmPaymentMail($payment, $user);
                        Mail::to($user)
                            ->send($mail);

                        $this->repository->edit($dbPayment->user_id, $dbPayment->event_id, intval($payment['amount']));
                    } catch (\Exception $e) {
                        $this->logError('Problem pri updatovani platby: ' . json_encode($payment) . ' error: ' . $e->getMessage() . 'trace: ' . $e->getTraceAsString());
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