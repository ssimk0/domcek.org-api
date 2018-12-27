<?php


namespace App\Http\Controllers\Secure;


use App\Http\Controllers\Controller;
use App\Models\Profile;
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
        return $this->jsonResponse([
            'admin' => $user->is_admin,
            'editor' => $user->is_writer,
            'registration' => $user->is_registration,
            'profile' => Profile::where('user_id', $user->id)->first()
        ]);
    }


}
