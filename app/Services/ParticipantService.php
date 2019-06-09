<?php


namespace App\Services;

use App\Mails\RegistrationMail;
use App\Repositories\EventRepository;
use App\Repositories\ParticipantRepository;
use App\Repositories\PaymentRepository;
use App\Repositories\VolunteersRepository;
use App\Repositories\GroupRepository;
use Carbon\Carbon;
use Endroid\QrCode\QrCode;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use function GuzzleHttp\json_encode;

class ParticipantService extends Service
{
    private $REGISTRATION_FEE = 5;

    private $repository;
    private $volunteersRepository;
    private $paymentRepository;
    private $eventRepository;
    private $groupRepository;

    public function __construct(ParticipantRepository $repository, 
    VolunteersRepository $volunteersRepository, 
    PaymentRepository $paymentRepository,
    EventRepository $eventRepository,
    GroupRepository $groupRepository
    )
    {
        $this->repository = $repository;
        $this->eventRepository = $eventRepository;
        $this->paymentRepository = $paymentRepository;
        $this->volunteersRepository = $volunteersRepository;
        $this->groupRepository = $groupRepository;
    }

    public function list($eventId, $filters)
    {
        return $this->repository->list($eventId, $filters);
    }

    public function registrationList($eventId)
    {
        return $this->repository->registrationList($eventId);
    }

    public function edit(array $data, $eventId, $participantId)
    {
        $userId = array_get($data, 'userId');
        $paid = array_get($data, 'paid');
        $adminNote = array_get($data, 'adminNote', '');
        $isLeader = array_get($data, 'isLeader', false);
        $volunteerTypeId = array_get($data, 'volunteerTypeId');
        $group = array_get($data, 'group_name');

        try {
            if (!empty($paid)) {
                $this->paymentRepository->edit($userId, $eventId, $paid);
            }

            if (!empty($volunteerTypeId)) {
                $data = [
                    'volunteer_type_id' => $volunteerTypeId,
                    'is_leader' => $isLeader,
                ];
                $this->volunteersRepository->editByUserAndEventId($data, $userId, $eventId);
            } else {
                $this->volunteersRepository->deleteIfExist($userId, $eventId);
            }

            if (!empty($group)) {
                $this->groupRepository->editGroupByParticipantAndEventId($group, $participantId, $eventId);
            }

            $this->repository->edit($participantId, $this->userId(), [
                'admin_note' => $adminNote
            ]);

            return true;

        } catch (\Exception $e) {
            $this->logError("Problem with editing participant with error: " . $e);
        }
    }

    public function userEvents()
    {
        try {
            return $this->repository->userEvents($this->userId());
        } catch (\Exception $e) {
            $this->logError("Problem with getting participant detail with error: " . $e);
        }

        return [];
    }

    public function create(array $data, $eventId)
    {
        $volunteerTypeId = array_get($data, 'volunteerTypeId');

        try {
            $event = $this->eventRepository->detail($eventId);
            $now = Carbon::now();

            if ($now >= Carbon::parse($event->start_date)) {
                return false;
            }

            $this->createParticipant($data, $eventId);

            if ($volunteerTypeId && $now <= Carbon::parse($event->end_volunteer_registration)) {
                $this->createVolunteer($volunteerTypeId, $eventId);
            }

            $needPay = $event->need_pay;

            if ($now > Carbon::parse($event->end_registration)) {
                // if you register after end of registration you need pay 5 euro fee
                $needPay += $this->REGISTRATION_FEE;
            }
            $paymentNumber = $this->createPayment($needPay, $eventId);

            $user = Auth::user();
            $profile = $user->profile()->first();
            $qrCodePath = "/tmp/".Str::random().".png";
            $this->generateQrCode($eventId, $user->id, $qrCodePath);
    
            Mail::to($user->email)->send(new RegistrationMail(
                $event->deposit,
                $profile->first_name,
                $profile->birth_date,
                $paymentNumber,
                $event->name,
                "https://domcek.org/login?next=/user/registrations",
                $qrCodePath
            ));

            return true;
        } catch (\Exception $e) {
            $this->logError("Problem with creating participant with error: " . $e);
        }

        return false;
    }

    public function unsubscribe($eventId)
    {
        try {
            $this->repository->unsubscribeToEvent($eventId, $this->userId());
        } catch (\Exception $e) {
            $this->logError("Problem with unsubscribe to event with error: " . $e);
            return false;
        }

        return true;
    }

    public function subscribe($eventId)
    {
        try {
            $this->repository->resubscribeToEvent($eventId, $this->userId());
        } catch (Exception $e) {
            $this->logError("Problem with subscribe to event with error: " . $e);
            return false;
        }

        return true;
    }

    public function userEdit($data, $eventId)
    {
        try {
            $this->repository->userEdit([
                'note' => array_get($data, 'note'),
                'transport_in' => array_get($data, 'transportIn'),
                'transport_out' => array_get($data, 'transportOut')
            ], $this->userId(), $eventId);
        } catch (Exception $e) {
            $this->logError("Problem with user edit participant detail with error: " . $e);
            return false;
        }

        return true;
    }

    public function adminDetail($eventId, $userId)
    {
        try {
            return $this->repository->detail($eventId, $userId);
        } catch (Exception $e) {
            $this->logError("Problem with getting participant detail with error: " . $e);
        }

        return false;
    }

    public function getUserPaymentNumber($eventId, $userId)
    {
        return $this->paymentRepository->findByUserIdAndEventId($eventId, $userId)->payment_number;
    }

    public function generateQrCode($eventId, $userId, $path)
    {
        $paymentNumber = $this->getUserPaymentNumber($eventId, $userId);
        $qrCode = new QrCode(base64_encode($paymentNumber));
        $qrCode->setEncoding('UTF-8');
        $qrCode->setWriterByName('png');
        $qrCode->setSize(300);

        $qrCode->writeFile($path);
    }

    public function detailedRegistrationList($eventId)
    {
        $participants = $this->repository->detailedRegistrationList($eventId);
        $notMatchedPayments = $this->paymentRepository->getNotMatchedPaymentForEvent($eventId);

        return [
            "participants" => $participants,
            "wrong-payments" => $notMatchedPayments
        ];
    }

    function sync($data, $eventId) {
        $event = $this->eventRepository->detail($eventId);
        foreach(array_get($data, 'participants', []) as $user) {
            if (array_get($user, 'was_on_event', null)) {
                try {
                    $transport = array_get($user, 'transport_out');
                    $userId = $user['user_id'];
                    $payedOnRegistration = $user['on_registration'];
                    // registered before event
                    if (array_get($user, 'payment_number', false)) {
                    $this->repository->registerUser($userId, $eventId, $transport, $payedOnRegistration);
                    } else {
                        $exist = $this->repository->participantExist($eventId, $userId);
                        if (!$exist) {
                            // register on event
                            $this->createParticipant([
                                'user_id' => $userId,
                                'transportOut' => $transport,
                                'note' => 'Prihlasený na púti'
                            ], $eventId, true);
                            $this->createPayment($event->need_pay + $this->REGISTRATION_FEE, $eventId, $payedOnRegistration, $userId);
                        } else {
                            $this->repository->registerUser($userId, $eventId, $transport, $payedOnRegistration);
                        }
                    }
                } catch(\Exception $e) {
                    $this->logError('Problem with register user ' + json_encode($user));
                }
            }
        }

        foreach(array_get($data, 'wrong-payments', []) as $payment) {
            $userId = array_get($payment, 'user_id', false);
            if ($userId) { 
                try {
                    $this->paymentRepository->edit($userId, $eventId, $payment['amount']);
                    $this->paymentRepository->deleteWrongPaymentById($payment['id']);
                } catch(\Exception $e) {
                    $this->logError('Problem with edit payment ' + $payment['id'] + 'for user ' + $payment['user_id']);
                }
            }
        }

        return true;
    }

    private function createParticipant($data, $eventId, $wasOnEvent=false) {
        $this->repository->create([
            'admin_note' => array_get($data, 'note', ''),
            'note' => '',
            'transport_in' => array_get($data, 'transportIn'),
            'transport_out' => array_get($data, 'transportOut'),
            'user_id' => array_get($data, 'user_id', false) ? $data['user_id'] : $this->userId(),
            'event_id' => $eventId,
            'was_on_event' => $wasOnEvent
        ]);
    }

    private function createVolunteer($volunteerTypeId, $eventId, $wasOnEvent=false) {
        $this->volunteersRepository->create([
            'volunteer_type_id' => $volunteerTypeId,
            'event_id' => $eventId,
            'user_id' => $this->userId(),
            'was_on_event' => $wasOnEvent
        ]);
    }

    private function createPayment($needPay, $eventId, $onReg = 0, $userId=false) {
        $paymentNumber = $this->paymentRepository->generatePaymentNumber();
     
        $this->paymentRepository->create([
            'user_id' => $userId ? $userId : $this->userId(),
            'payment_number' => $paymentNumber,
            'paid' => 0,
            'on_registration' => $onReg,
            'need_pay' => $needPay,
            'event_id' => $eventId,
        ]);

        return $paymentNumber;
    }

}
