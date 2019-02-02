<?php


namespace App\Http\Controllers\Secure;


use App\Constants\ErrorMessagesConstant;
use App\Http\Controllers\Controller;
use App\Services\UserService;
use Illuminate\Http\Request;

class UserController extends Controller
{
    private $service;

    public function __construct(UserService $service)
    {
        $this->service = $service;
    }

    function userDetail(Request $request)
    {
        $user = $request->user();
        $detail = $this->service->userDetail($user);

        return $this->jsonResponse($detail);
    }

    function changePassword(Request $request)
    {
        $data = $this->validate($request, [
            'password' => 'required|string|confirmed|min:6'
        ]);

        $result = $this->service->updateUserPassword($data['password']);

        if ($result) {
            $this->successResponse();
        }

        ErrorMessagesConstant::badAttempt();
    }

    function updateProfile(Request $request)
    {
        $data = $this->validate($request, [
            'city' => 'required|string',
            'phone' => 'required|string',
            'email' => 'required|string',
            'lastName' => 'required|string',
            'avatar' => 'url'
        ]);

        $result = $this->service->updateUserProfile($data);

        if ($result) {
            $this->successResponse();
        }

        ErrorMessagesConstant::badAttempt();
    }
}
