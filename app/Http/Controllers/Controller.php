<?php

namespace App\Http\Controllers;

use App\Constants\ErrorMessagesConstant;
use App\Logging\Logger;
use Illuminate\Http\Request;
use Laravel\Lumen\Routing\Controller as BaseController;

class Controller extends BaseController
{
    use Logger;

    public function error($status = 400, $errorMessage = ErrorMessagesConstant::SERVER_ERROR)
    {
        return response()->json([
            'message' => $errorMessage,
            'success' => false,
        ], $status);
    }

    public function jsonResponse($responseData, $status = 200, $options = JSON_NUMERIC_CHECK)
    {
        return response()->json($responseData, $status, [], $options);
    }

    public function successResponse($status = 200)
    {
        return $this->jsonResponse([
            'success' => true,
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
