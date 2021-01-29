<?php

namespace App\Http\Controllers\Secure;

use App\Constants\ErrorMessagesConstant;
use App\Http\Controllers\Controller;
use App\Services\UserService;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    private $service;

    public function __construct(UserService $service)
    {
        $this->service = $service;
    }

    public function userDetail(Request $request)
    {
        $user = $request->user();
        $detail = $this->service->userDetail($user);

        return $this->jsonResponse($detail, 200, 0);
    }

    public function changePassword(Request $request)
    {
        $data = $request->validate([
            'oldPassword' => 'required',
            'password' => 'required|string|confirmed|min:6',
        ]);

        if (! Hash::check($data['oldPassword'], $request->user()->password)) {
            return ErrorMessagesConstant::error(400, ErrorMessagesConstant::WRONG_CREDENTIALS);
        }

        $result = $this->service->updateUserPassword($data['password']);

        if ($result) {
            $this->successResponse();
        }

        ErrorMessagesConstant::badAttempt();
    }

    public function updateProfile(Request $request)
    {
        $data = $request->validate([
            'city' => 'required|string',
            'phone' => 'required|string',
            'lastName' => 'required|string',
            'nick' => 'nullable|string',
            'avatar' => 'url',
        ]);

        $result = $this->service->updateUserProfile($data);

        if ($result) {
            $this->successResponse();
        }

        ErrorMessagesConstant::badAttempt();
    }

    public function list(Request $request)
    {
        $data = $request->validate([
            'size' => 'nullable|integer',
            'filter' => 'nullable|string',
        ]);

        $list = $this->service->list(
            Arr::get($data, 'size', 10),
            Arr::get($data, 'filter', '%')
        );

        return $this->jsonResponse($list, 200, 0);
    }

    public function editUserAdmin(Request $request, $userId)
    {
        $data = $request->validate([
            'firstName' => 'nullable|string',
            'lastName' => 'nullable|string',
            'city' => 'nullable|string',
            'isAdmin' => 'boolean|required',
            'isEditor' => 'boolean|required',
            'phone' => 'nullable|string',
            'email' => 'string|required',
            'note' => 'nullable|string',
        ]);

        $result = $this->service->editUser($data, $userId);

        if ($result) {
            return $this->successResponse();
        }

        return ErrorMessagesConstant::badAttempt();
    }

    public function adminUserDetail($userId)
    {
        $user = $this->service->findUser($userId);
        $detail = $this->service->userDetail($user);

        return $this->jsonResponse($detail, 200, 0);
    }

    public function resetPassword($userId)
    {
        $result = $this->service->generateNewPassword($userId);

        if ($result) {
            return $this->successResponse();
        }

        return ErrorMessagesConstant::badAttempt();
    }
}
