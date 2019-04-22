<?php


namespace App\Services;


use App\Constants\ErrorMessagesConstant;
use App\Exceptions\MultipleOldAccounts;
use App\Mails\ExceptionMail;
use App\Mails\ForgotPasswordMail;
use App\Mails\ResetPasswordMail;
use App\Repositories\OldWebIntegrationRepository;
use App\Repositories\ParticipantRepository;
use App\Repositories\PaymentRepository;
use App\Repositories\UserRepository;
use App\Repositories\VolunteersRepository;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class UserService extends Service
{
    protected $repository;
    protected $participantRepository;
    protected $volunteersRepository;
    protected $oldWebIntegrationRepository;
    protected $paymentRepository;

    public function __construct(
        UserRepository $repository,
        ParticipantRepository $participantRepository,
        PaymentRepository $paymentRepository,
        VolunteersRepository $volunteersRepository,
        OldWebIntegrationRepository $oldWebIntegrationRepository
    ) {
        $this->repository = $repository;
        $this->participantRepository = $participantRepository;
        $this->volunteersRepository = $volunteersRepository;
        $this->paymentRepository = $paymentRepository;
        $this->oldWebIntegrationRepository = $oldWebIntegrationRepository;
    }

    function forgotPassword($email)
    {
        $token = Str::random(32);
        try {
            $user = $this->repository->findUserByEmail($email);
            if ($user) {

                $result = $this->repository->saveResetPasswordToken($token,
                    $email);

                if ($result) {
                    Mail::to($email)->send(new ForgotPasswordMail($token));
                } else {
                    $this->logError('Nepodarilo sa ulozit token do databazy pre email '
                        .$email);
                }
            }
        } catch (\Exception $e) {
            $this->logWarning('Problem pri reset passworde pre '.$email
                .'s error '.$e);
        }
    }

    function resetPassword($token, $password)
    {
        try {
            $tokenResult = $this->repository->findResetPasswordToken($token);
            $now = Carbon::now()->addHour(2);
            if ($tokenResult
                && $now->greaterThanOrEqualTo($tokenResult->created_at)
            ) {
                $user = $this->repository->findUserByEmail($tokenResult->email);
                $this->updateUserPassword($password, $user->id);

                return true;
            }
        } catch (\Exception $e) {
            $this->logWarning('Problem pri updatovani použivateľovho hesla pre token'
                .$token.'s errorom '.$e);
        }

        return false;
    }

    function userDetail($user)
    {
        return [
            'email'        => $user->email,
            'admin'        => $user->is_admin,
            'editor'       => $user->is_writer,
            'avatar'       => $user->avatar,
            'profile'      => $this->repository->getUserProfile($user->id),
        ];
    }

    function findUser($userId)
    {
        return $this->repository->findUser($userId);
    }

    function checkPermission($perm, $user)
    {
        $admin = $user->is_admin == 1;

        if ($perm === 'editor') {
            return $user->is_writer == 1 || $admin;
        } else {
            if ($perm === 'registration') {
                return $user->is_registration == 1 || $admin;
            } else {
                return $admin;
            }
        }
    }

    function updateUserProfile($data)
    {
        try {
            $mappingProfile = [
                'phone'    => 'phone',
                'last_name' => 'lastName',
                'city'     => 'city',
            ];

            $mappingUser = [
                'email'  => 'email',
                'avatar' => 'avatar',
            ];

            $this->repository->updateUser($this->parseExistingData($data,
                $mappingUser), $this->userId());
            $this->repository->updateUserProfile($this->parseExistingData($data,
                $mappingProfile), $this->userId());

            return true;
        } catch (\Exception $e) {
            $this->logWarning('Problem pri updatovani použivateľovho hesla s errorom'
                .$e);
        }

        return false;
    }

    function updateUserPassword($password, $userId = null)
    {
        $userId = $userId ?: $this->userId();
        try {
            $this->repository->updateUser([
                'password' => Hash::make($password),
            ], $userId);

            return true;
        } catch (\Exception $e) {
            $this->logWarning('Problem pri updatovani použivateľovho hesla s errorom'
                .$e);
        }

        return false;
    }

    function createUser($data)
    {
        try {
            $userData = [
                'email'    => $data['email'],
                'avatar'   => array_get($data, 'avatar', null),
                'password' => Hash::make($data['password']),
            ];

            $user = $this->repository->findUserByEmail($data['email']);

            if (isset($user)) {
                return ErrorMessagesConstant::USER_ALREADY_EXIST;
            }

            $user = $this->repository->createUser($userData);

            $profileData = [
                'first_name' => ucfirst($data['firstName']),
                'last_name'  => ucfirst($data['lastName']),
                'city'       => ucfirst($data['city']),
                'phone'      => $data['phone'],
                'user_id'    => $user->id,
                'birth_date' => $data['birthDate'],
                'date_approved_term_and_condition' => Carbon::now()
            ];
            $this->repository->createUserProfile($profileData);
            if (array_get($data, 'newsletter', false)) {
                $this->repository->registerToNewsLetter($data['email']);
            }
            $this->migrateDataFromOldDatabase($user->email, $user->id);

            return true;
        } catch (\Exception $e) {
            $this->logWarning('Problem pri vytvarani použivateľa s errorom '
                .$e);
        }

        return false;
    }

    private function migrateDataFromOldDatabase($email, $userId) {
        try {
            $oldUsers = $this->oldWebIntegrationRepository->findOldUser($email);

            if (empty($oldUsers)) {
                return;
            }

            if (count($oldUsers) > 1) {
                throw new MultipleOldAccounts("User s emailom: $email ma viac uctou");
            }
            $this->logWarning(print_r($oldUsers));
            $oldUserId = $oldUsers[0];

            $oldEvents = $this->oldWebIntegrationRepository->findOldEventRegistration($oldUserId);
            if (!empty($oldEvents) && count($oldEvents) > 1) {
                foreach ($oldEvents as $event) {
                    // create record about event registration
                    $this->participantRepository->create([
                        'note'          => $event->note,
                        'transport_in'  => '',
                        'transport_out' => '',
                        'user_id'       => $userId,
                        'event_id'      => $event->action_id,
                        'was_on_event' => $event->was_on_act === 'true'
                    ]);

                    // create record about event volunteer registration
                    if (!empty($event->role) || !empty($event->real_role)) {
                        $role = !empty($event->real_role) ? $event->real_role : $event->role;

                        $typeId = $this->volunteersRepository->typeByName($role)->id;
                        $this->registerVolunteer($typeId, $userId, $event->action_id, $event->was_on_act === 'true');
                    }
                    // create record about event payment registration
                    $paymentNumber = $this->paymentRepository->generatePaymentNumber();

                    $this->paymentRepository->create([
                        'user_id' => $userId,
                        'payment_number' => $paymentNumber,
                        'paid' => $event->payedDeposit + $event->payedReg,
                        'need_pay' => $event->payedDeposit + $event->payedReg,
                        'event_id' => $event->action_id,
                    ]);
                }
            }
        } catch(MultipleOldAccounts $e) {
            Mail::to('simko22@gmail.com')->send(new ExceptionMail($e));
        } catch (\Exception $e) {
            Mail::to('simko22@gmail.com')->send(new ExceptionMail($e));
        }
    }

    public function userEvents()
    {
        return $this->participantRepository->userEvents($this->userId());
    }

    public function list($size, $filter)
    {
        return $this->repository->list($size, $filter);
    }

    public function editUser(array $data, $userId)
    {
        try {
            $mappingProfile = [
                'phone'    => 'phone',
                'last_name' => 'lastName',
                'first_name' => 'firstName',
                'city'     => 'city',
            ];

            $mappingUser = [
                'email'  => 'email',
                'is_writer' => 'isEditor',
                'is_admin' => 'isAdmin',
            ];

            $this->repository->updateUser($this->parseExistingData($data,
                $mappingUser), $userId);
            $this->repository->updateUserProfile($this->parseExistingData($data,
                $mappingProfile), $userId);

            return true;
        } catch (\Exception $e) {
            $this->logWarning('Problem pri updatovani použivateľovho hesla s errorom'
                .$e);
        }

        return false;
    }

    public function generateNewPassword($userId)
    {
        $user = $this->repository->findUser($userId);
        $password = Str::random(12);

        Mail::to($user->email)->send(new ResetPasswordMail($password));
        return $this->updateUserPassword($password, $userId);
    }

    private function registerVolunteer(
        $typeId,
        $userId,
        $eventId,
        $wasOnEvent
    ) {
        $this->volunteersRepository->create([
            'volunteer_type_id' => $typeId,
            'event_id' => $eventId,
            'user_id' => $userId,
            'was_on_event' => $wasOnEvent
        ]);
    }
}
