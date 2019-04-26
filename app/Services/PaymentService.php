<?php


namespace App\Services;


use App\Repositories\PaymentRepository;

class PaymentService extends Service
{
    private $repository;

    public function __construct(PaymentRepository $repository)
    {
        $this->repository = $repository;
    }


    public function processPayments($payments) {

    }
}