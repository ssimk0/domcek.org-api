<?php


namespace App\Services;


use App\Mails\ForgotPasswordMail;
use App\Repositories\UserRepository;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class UserService extends Service
{
    private $repository;

    public function __construct(UserRepository $repository)
    {
        $this->repository = $repository;
    }

    function forgotPassword($email)
    {
        $token = Str::random(32);
        try {
            $user = $this->repository->findUserByEmail($email);
            if ($user) {

                $result = $this->repository->saveResetPasswordToken($token, $email);

                if ($result) {
                    Mail::to($email)->send(new ForgotPasswordMail($token));
                } else {
                    $this->logError('Nepodarilo sa ulozit token do databazy pre email ' . $email);
                }
            }
        } catch (\Exception $e) {
            $this->logWarning('Problem pri reset passworde pre ' . $email . 's error ' . $e);
        }
    }

    function resetPassword($token, $password)
    {
        try {
            $token = $this->repository->findResetPasswordToken($token);
            $now = Carbon::now()->addHour(2);
            if ($token && $now->greaterThanOrEqualTo($token->created_at)) {
                $this->repository->updateUser([
                    'password' => Hash::make($password)
                ], request()->user()->id);

                return true;
            }
        } catch (\Exception $e) {
            $this->logWarning('Problem pri updatovani použivateľovho hesla pre token' . $token);
        }

        return false;
    }

    function userDetail($user)
    {
        return [
            'admin' => $user->is_admin,
            'editor' => $user->is_writer,
            'registration' => $user->is_registration,
            'avatar' => $user->avatar,
            'profile' => $this->repository->getUserProfile($user->id)
        ];
    }

    function checkPermission($perm, $user)
    {
        $admin = $user->is_admin === 1;

        if ($perm === 'editor') {
            return $user->is_writer === 1 || $admin;
        } else if ($perm === 'registration') {
            return $user->is_registration === 1 || $admin;
        } else {
            return $admin;
        }
    }

}
