<?php

namespace App\Repositories;

use App\Constants\TableConstants;
use App\Models\Payment;
use Illuminate\Support\Facades\DB;

class PaymentRepository extends Payment
{
    public function create($data)
    {
        $payment = new Payment($data);
        $payment->save();

        return $payment;
    }

    private function isExistPaymentNumber($paymentNumber)
    {
        return DB::table(TableConstants::PAYMENTS)->where('payment_number', $paymentNumber)->count() > 0;
    }

    public function generatePaymentNumber()
    {
        $digits = 10;
        $i = 0;
        $number = '';

        while ($i < $digits) {
            $number .= mt_rand(0, 9);
            $i++;
        }

        if ($this->isExistPaymentNumber($number)) {
            $number = $this->generatePaymentNumber();
        }

        return $number;
    }

    public function findByPaymentNumber($paymentNumber, $eventId)
    {
        return DB::table(TableConstants::PAYMENTS)
            ->where('payment_number', $paymentNumber)
            ->where('event_id', $eventId)
            ->first();
    }

    public function edit($userId, $eventId, $paid)
    {
        return DB::table(TableConstants::PAYMENTS)
            ->where('user_id', $userId)
            ->where('event_id', $eventId)
            ->update([
                'paid' => $paid,
            ]);
    }

    public function addNotMatchedPayment($payment, $eventId)
    {
        $exist = DB::table(TableConstants::WRONG_PAYMENTS)
            ->where('event_id', $eventId)
            ->where('iban', $payment['iban'])
            ->where('payment_number', $payment['paymentNumber'])
            ->exists();
        if (! $exist) {
            DB::table(TableConstants::WRONG_PAYMENTS)
                ->insert([
                    'event_id' => $eventId,
                    'payment_number' => $payment['paymentNumber'],
                    'payment_note' => $payment['note'],
                    'amount' => $payment['amount'],
                    'iban' => $payment['iban'],
                    'transaction_date' => $payment['date'],
                ]);
        }
    }

    public function findByUserIdAndEventId($eventId, $userId)
    {
        return DB::table(TableConstants::PAYMENTS)
            ->where('user_id', $userId)
            ->where('event_id', $eventId)
            ->first();
    }

    public function getNotMatchedPaymentForEvent($eventId)
    {
        return DB::table(TableConstants::WRONG_PAYMENTS)
            ->where('event_id', $eventId)
            ->get();
    }

    public function deleteWrongPaymentById($id)
    {
        return DB::table(TableConstants::WRONG_PAYMENTS)
        ->where('id', $id)
        ->delete();
    }
}
