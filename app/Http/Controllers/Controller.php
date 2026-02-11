<?php

namespace App\Http\Controllers;

use App\Constants\ErrorMessagesConstant;
use App\Logging\Logger;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

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
        if (!app()->environment('testing')) {
            $token = $request->input('g-recaptcha-response') ?? $request->input('recaptcha');

            if (empty($token)) {
                $this->logDebug('Recaptcha: no token in request. Input keys: '.implode(',', array_keys($request->all())));
                throw ValidationException::withMessages([
                    'recaptcha' => ['The recaptcha field is required.'],
                ]);
            }

            $secret = config('services.recaptcha.secret');
            $this->logDebug('Recaptcha: secret exists: '.(!empty($secret) ? 'yes' : 'no'));

            $response = Http::asForm()->post('https://www.google.com/recaptcha/api/siteverify', [
                'secret' => $secret,
                'response' => $token,
                'remoteip' => $request->ip(),
            ]);

            $result = $response->json();
            $this->logDebug('Recaptcha: google response: '.json_encode($result));

            if (!($result['success'] ?? false) || ($result['score'] ?? 0) < 0.5) {
                throw ValidationException::withMessages([
                    'recaptcha' => ['Recaptcha verification failed.'],
                ]);
            }
        }

        return $request->validate($rules);
    }
}
