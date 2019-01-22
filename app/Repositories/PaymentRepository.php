<?php


namespace App\Repositories;


use App\Constants\TableConstants;
use App\Models\Payment;
use Illuminate\Support\Facades\DB;

class PaymentRepository extends Payment
{
    function create($data)
    {
        $payment = new Payment($data);
        $payment->save();

        return $payment;
    }

    private function isExistPaymentNumber($paymentNumber)
    {
        return DB::table(TableConstants::PAYMENTS)->where('payment_number', $paymentNumber)->count() > 0;
    }


    function generatePaymentNumber()
    {
        $digits = 10;
        $i = 0;
        $number = "";

        while ($i < $digits) {

            $number .= mt_rand(0, 9);
            $i++;
        }

        if ($this->isExistPaymentNumber($number)) {
            $number = $this->generatePaymentNumber();
        }

        return $number;
    }

    public function edit($userId, $eventId, $paid)
    {
        return DB::table(TableConstants::PAYMENTS)
            ->where('user_id', $userId)
            ->where('event_id', $eventId)
            ->update([
                'paid' => $paid
            ]);
    }
}
