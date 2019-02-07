<?php


namespace App\Services;


use App\Mails\ForgotPasswordMail;
use App\Repositories\EventRepository;
use App\Repositories\ParticipantRepository;
use App\Repositories\UserRepository;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class UserService extends Service
{
    private $repository;
    private $participantRepository;

    public function __construct(UserRepository $repository, ParticipantRepository $participantRepository)
    {
        $this->repository = $repository;
        $this->participantRepository = $participantRepository;
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
            $tokenResult = $this->repository->findResetPasswordToken($token);
            $now = Carbon::now()->addHour(2);
            if ($tokenResult && $now->greaterThanOrEqualTo($tokenResult->created_at)) {
                $user = $this->repository->findUserByEmail($tokenResult->email);
                $this->repository->updateUser([
                    'password' => Hash::make($password)
                ], $user->id);

                return true;
            }
        } catch (\Exception $e) {
            $this->logWarning('Problem pri updatovani použivateľovho hesla pre token' . $token . 's errorom ' . $e);
        }

        return false;
    }

    function userDetail($user)
    {
        return [
            'email' => $user->email,
            'admin' => $user->is_admin,
            'editor' => $user->is_writer,
            'registration' => $user->is_registration,
            'avatar' => $user->avatar,
            'profile' => $this->repository->getUserProfile($user->id)
        ];
    }

    function checkPermission($perm, $user)
    {
        $admin = $user->is_admin == 1;

        if ($perm === 'editor') {
            return $user->is_writer == 1 || $admin;
        } else if ($perm === 'registration') {
            return $user->is_registration == 1 || $admin;
        } else {
            return $admin;
        }
    }

    function updateUserProfile($data)
    {
        try {
            $mappingProfile = [
                'phone' => 'phone',
                'lastName' => 'last_name',
                'city' => 'city',
            ];

            $mappingUser = [
                'email' => 'email',
                'avatar' => 'avatar'
            ];

            $this->repository->updateUser($this->parseExistingData($data, $mappingUser), $this->userId());
            $this->repository->updateUserProfile($this->parseExistingData($data, $mappingProfile), $this->userId());
            return true;
        } catch (\Exception $e) {
            $this->logWarning('Problem pri updatovani použivateľovho hesla s errorom' . $e);
        }

        return false;
    }

    function updateUserPassword($password)
    {
        try {
            $this->repository->updateUser([
                'password' => Hash::make($password)
            ], $this->userId());
            return true;
        } catch (\Exception $e) {
            $this->logWarning('Problem pri updatovani použivateľovho hesla s errorom' . $e);
        }

        return false;
    }

    function createUser($data)
    {
        try {
            $userData = [
                'email' => $data['email'],
                'avatar' => array_get($data, 'avatar', null),
                'password' => Hash::make($data['password']),
            ];
            Log::info(implode($userData, ','));
            $user = $this->repository->createUser($userData);

            $profileData = [
                'first_name' => ucfirst($data['firstName']),
                'last_name' => ucfirst($data['lastName']),
                'city' => ucfirst($data['city']),
                'phone' => $data['phone'],
                'user_id' => $user->id,
                'birth_date' => $data['birthDate'],
            ];
            $this->repository->createUserProfile($profileData);
            return true;
        } catch (\Exception $e) {
            $this->logWarning('Problem pri vytvarani použivateľa s errorom ' . $e);
        }

        return false;
    }

    public function userEvents()
    {
        return $this->participantRepository->userActiveEvents($this->userId());
    }

}
