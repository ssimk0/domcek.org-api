<?php

namespace App\Http\Controllers;

use App\Constants\ErrorMessagesConstant;
use App\Logging\Logger;
use Laravel\Lumen\Routing\Controller as BaseController;
use Illuminate\Http\Request;

class Controller extends BaseController
{
    use Logger;

    public function error($status = 400, $errorMessage = ErrorMessagesConstant::SERVER_ERROR)
    {
        return response()->json([
            'message' => $errorMessage,
            'success' => false
        ], $status);
    }

    public function jsonResponse($responseData, $status = 200)
    {
        return response()->json($responseData, $status, [], JSON_NUMERIC_CHECK);
    }

    public function successResponse($status = 200)
    {
        return $this->jsonResponse([
            'success' => true
        ], $status);
    }

    protected function validateWithCaptcha(Request $request, $rules)
    {
        if (env('APP_ENV') !== 'testing') {
            $this->validate($request, ['recaptcha' => 'required|captcha']);
        }

        return $this->validate($request, $rules);
    }
}
