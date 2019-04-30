<?php


namespace App\Http\Controllers\Secure;


use App\Constants\ErrorMessagesConstant;
use App\Http\Controllers\Controller;
use App\Services\UserService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

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
            'oldPassword' => 'required',
            'password' => 'required|string|confirmed|min:6'
        ]);

        if (!Hash::check($data['oldPassword'], $request->user()->password ))  {
            return ErrorMessagesConstant::error(400, ErrorMessagesConstant::WRONG_CREDENTIALS);
        }

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

    function list(Request $request) {
        $data = $this->validate($request, [
            'size' => 'integer',
            'filter' => 'string'
        ]);

        $list = $this->service->list(
            array_get($data, 'size', 10),
            array_get($data, 'filter', '%')
        );

        return $this->jsonResponse($list);
    }

    function editUserAdmin(Request $request, $userId) {
        $data = $this->validate($request, [
            'firstName' => 'string',
            'lastName' => 'string',
            'city' => 'string',
            'isAdmin' => 'boolean',
            'isEditor' => 'boolean',
            'phone' => 'string',
            'email' => 'string',
        ]);

        $result = $this->service->editUser($data, $userId);

        if ($result) {
            return $this->successResponse();
        }

        return ErrorMessagesConstant::badAttempt();
    }

    function adminUserDetail($userId)
    {
        $user = $this->service->findUser($userId);
        $detail = $this->service->userDetail($user);

        return $this->jsonResponse($detail);
    }

    function resetPassword($userId) {

        $result = $this->service->generateNewPassword($userId);

        if ($result) {
            return $this->successResponse();
        }

        return ErrorMessagesConstant::badAttempt();
    }


}
