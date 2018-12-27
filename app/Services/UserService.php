<?php


namespace App\Services;


use App\Mails\ForgotPasswordMail;
use App\Repositories\UserRepository;
use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class UserService extends Service
{
    private $repository;

    public function __construct(UserRepository $repository)
    {
        $this->repository = $repository;
    }

    function forgotPassword()
    {

    }

    function createResetPasswordToken($email)
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
            $this->logWarning('Problem pri reset passworde pre ' . $email);
        }
    }

    function checkResetPasswordToken($token)
    {
        $token = $this->repository->findResetPasswordToken($token);
        $now = Carbon::now()->addHour(2);
        if ($token && $now->greaterThanOrEqualTo($token->created_at)) {
            return true;
        }

        return false;
    }

}
