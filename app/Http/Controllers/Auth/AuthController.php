<?php


namespace App\Http\Controllers\Auth;


use App\Constants\ErrorMessagesConstant;
use App\Http\Controllers\Controller;
use App\Services\UserService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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
            $data = $this->validate($request, [
                'username' => 'required',
                'password' => 'required',
                'recaptcha' => 'required|captcha'
            ]);
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
        $data = $this->validate($request, [
            'email' => 'required|email',
            'recaptcha' => 'required|captcha'
        ]);

        $this->service->forgotPassword($data['email']);

        return $this->successResponse();
    }

    function resetPassword(Request $request)
    {
        $data = $this->validate($request, [
            'token' => 'required|string',
            'password' => 'required|string|confirmed|min:6',
            'recaptcha' => 'required|captcha'
        ]);

        $result = $this->service->resetPassword($data['token'], $data['password']);

        if ($result) {
            return $this->successResponse();
        }

        return ErrorMessagesConstant::badAttempt();
    }
//         'email' => $data['email'],
//                'avatar' => $data['avatar'],
//                'password' =>  Hash::make($data['password']),
//            ];
//            $user = $this->repository->createUser($userData);
//
//            $profileData = [
//                'first_name' => $data['firstName'],
//                'last_name' => $data['lastName'],
//                'city' => $data['city'],
//                'phone' => $data['phone'],
//                'user_id' => $user->id
    function registerUser(Request $request)
    {
        $data = $this->validate($request, [
            'avatar' => 'url',
            'password' => 'required|string|confirmed|min:6',
            'firstName' => 'required|string',
            'lastName' => 'required|string',
            'city' => 'required|string',
            'phone' => 'required|string',
            'email' => 'required|email',
            'recaptcha' => 'required|captcha'
        ]);

        $result = $this->service->createUser($data);

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
