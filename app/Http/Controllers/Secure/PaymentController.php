<?php


namespace App\Http\Controllers\Secure;


use App\Http\Controllers\Controller;
use App\Services\PaymentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PaymentController extends Controller
{
    private $service;

    public function __construct(PaymentService $service)
    {
        $this->service = $service;
    }

    public function uploadTransferLog(Request $request)
    {
        $log = request()->file('file')->openFile('r');
        $parsedPayments = [];
        $error = false;

        try {
            $log->fgets();
            $log->fgets();
            $log->fgets();
            $log->fgets();
            while (!$log->eof()) {
                $line = $log->fgets();
                # do same stuff with the $line
                $parsed = explode('|', $line);
                if ($parsed[4] === 'Kredit') {
                    $parsedPayments [] = [
                        'paymentNumber' => $parsed[9],
                        'iban' => $parsed[8],
                        'amount' => $parsed[2],
                        'note' => $parsed[13],
                        'date' => $parsed[1],
                    ];
                }
            }
        } catch (\Exception $e) {
            Log::error($e);
            $error = true;
        }

        $this->service->processPayments($parsedPayments);
        return response()->json([
            'processed'=> count($parsedPayments),
            'error' => $error
        ]);

    }

}