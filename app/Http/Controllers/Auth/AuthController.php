<?php


namespace App\Http\Controllers\Auth;


use App\Constants\ErrorMessagesConstant;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    function authenticate(Request $request) {
        $errMessage = ErrorMessagesConstant::WRONG_CREDENTIALS;
        try {
            $data = $this->validate($request, [
                'username' => 'required',
                'password' => 'required',
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
