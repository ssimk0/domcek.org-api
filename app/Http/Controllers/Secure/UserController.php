<?php


namespace App\Http\Controllers\Secure;


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


}
