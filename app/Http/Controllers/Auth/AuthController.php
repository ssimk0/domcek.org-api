<?php


namespace App\Http\Controllers\Auth;


use App\Constants\ErrorMessagesConstant;
use App\Http\Controllers\Controller;
use App\Services\UserService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class AuthController extends Controller
{
    private $service;

    public function __construct(UserService $service)
    {
        $this->service = $service;
    }

    function authenticate(Request $request)
    {
        $errMessage = ErrorMessagesConstant::WRONG_CREDENTIALS;
        try {
            if(env('APP_DEBUG')) {
                $data = $this->validate($request, [
                    'username' => 'required',
                    'password' => 'required'
                ]);
            } else {
                $data = $this->validateWithCaptcha($request, [
                    'username' => 'required',
                    'password' => 'required'
                ]);
            }

        } catch (\Exception $e) {
            $this->logDebug("Validation exception for login user: " . $e->getMessage());

            return $this->error(401, $errMessage);
        }

        $token = Auth::attempt([
            'email' => $data['username'],
            'password' => $data['password'],
        ]);

        if ($token) {
            return $this->respondWithToken($token);
        } else {
            $this->logDebug("Validation exception for login user: no token");

        }

        return $this->error(401, $errMessage);
    }

    function refresh()
    {
        try {
            return $this->respondWithToken(Auth::refresh());
        } catch (\Exception $e) {
            return ErrorMessagesConstant::badRequest();
        }
    }

    function logout()
    {
        try {
            Auth::logout();
        } catch (\Exception $e) {
        }
        return $this->successResponse();
    }

    function forgotPassword(Request $request)
    {
        $data = $this->validateWithCaptcha($request, [
            'email' => 'required|email'
        ]);

        $this->service->forgotPassword($data['email']);

        return $this->successResponse();
    }

    function resetPassword(Request $request)
    {
        $data = $this->validateWithCaptcha($request, [
            'token' => 'required|string',
            'password' => 'required|string|confirmed|min:6'
        ]);

        $result = $this->service->resetPassword($data['token'], $data['password']);

        if ($result) {
            return $this->successResponse();
        }

        return ErrorMessagesConstant::badAttempt();
    }

    function registerUser(Request $request)
    {
        $data = $this->validateWithCaptcha($request, [
            'avatar' => 'url',
            'password' => 'required|string|confirmed|min:6',
            'firstName' => 'required|string',
            'lastName' => 'required|string',
            'nick' => 'string',
            'birthDate' => 'required|date_format:Y-m-d',
            'city' => 'required|string',
            'phone' => 'required|string',
            'email' => 'required|email',
            'terms_and_condition' => 'required|accepted',
            'newsletter' => 'boolean',
            'sex' => ['required', Rule::in(['f', 'm'])],
        ]);

        $result = $this->service->createUser($data);
        if ($result === ErrorMessagesConstant::USER_ALREADY_EXIST) {
            return ErrorMessagesConstant::error(400, ErrorMessagesConstant::USER_ALREADY_EXIST);
        }

        if ($result) {
            return $this->successResponse();
        }

        return ErrorMessagesConstant::badAttempt();
    }

    protected function respondWithToken($token)
    {
        return response()->json([
            'success' => true,
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => Auth::factory()->getTTL() * 60,
        ]);
    }
}
