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
            }

            if (!empty($group)) {
                $this->groupRepository->editGroupByParticipantAndEventId($group, $participantId, $eventId);
            }

            $this->repository->edit($participantId, $this->userId());

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
        foreach($data as $user) {
            if ($user['was_on_event']) {
                // registered before event
                if ($user['payment_number']) {
                   $this->repository->registerUser($user['user_id'], $eventId, $user['on_registration']);
                } else {
                    // register on event
                    $this->createParticipant([], $eventId, true);
                    $this->createPayment($event->need_pay + $this->REGISTRATION_FEE, $eventId, $user['on_registration']);
                }
            }
        }
    }

    private function createParticipant($data, $eventId, $wasOnEvent=false) {
        $this->repository->create([
            'note' => array_get($data, 'note'),
            'transport_in' => array_get($data, 'transportIn'),
            'transport_out' => array_get($data, 'transportOut'),
            'user_id' => $this->userId(),
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

    private function createPayment($needPay, $eventId, $onReg = 0) {
        $paymentNumber = $this->paymentRepository->generatePaymentNumber();
     
        $this->paymentRepository->create([
            'user_id' => $this->userId(),
            'payment_number' => $paymentNumber,
            'paid' => 0,
            'on_registration' => $onReg,
            'need_pay' => $needPay,
            'event_id' => $eventId,
        ]);

        return $paymentNumber;
    }

}
