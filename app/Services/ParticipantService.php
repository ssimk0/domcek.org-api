<?php


namespace App\Services;

use App\Mails\RegistrationMail;
use App\Repositories\EventRepository;
use App\Repositories\ParticipantRepository;
use App\Repositories\PaymentRepository;
use App\Repositories\VolunteersRepository;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;

class ParticipantService extends Service
{
    private $repository;
    private $volunteersRepository;
    private $paymentRepository;
    private $eventRepository;

    public function __construct(ParticipantRepository $repository, VolunteersRepository $volunteersRepository, PaymentRepository $paymentRepository, EventRepository $eventRepository)
    {
        $this->repository = $repository;
        $this->eventRepository = $eventRepository;
        $this->paymentRepository = $paymentRepository;
        $this->volunteersRepository = $volunteersRepository;
    }

    public function list($eventId)
    {
        return $this->repository->list($eventId);
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
        $registrationUserId = array_get($data, 'registrationUserId');

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

            $this->repository->edit($participantId, $registrationUserId, $this->userId());

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

            $this->repository->create([
                'note' => array_get($data, 'note'),
                'transport_in' => array_get($data, 'transportIn'),
                'transport_out' => array_get($data, 'transportOut'),
                'user_id' => $this->userId(),
                'event_id' => $eventId
            ]);

            if ($volunteerTypeId && $now <= Carbon::parse($event->end_volunteer_registration)) {
                $this->volunteersRepository->create([
                    'volunteer_type_id' => $volunteerTypeId,
                    'event_id' => $eventId,
                    'user_id' => $this->userId()
                ]);
            }
            $paymentNumber = $this->paymentRepository->generatePaymentNumber();
            $needPay = $event->need_pay;

            if ($now > Carbon::parse($event->end_registration)) {
                // if you register after end of registration you need pay 5 euro fee
                $needPay += 5;
            }
            $this->paymentRepository->create([
                'user_id' => $this->userId(),
                'payment_number' => $paymentNumber,
                'paid' => 0,
                'need_pay' => $needPay,
                'event_id' => $eventId,
            ]);
            $user = Auth::user();
            $profile = $user->profile()->first();

            Mail::to($user->email)->send(new RegistrationMail(
                $event->deposit,
                $profile->first_name,
                $profile->birth_date,
                $paymentNumber,
                $event->name,
                "https://domcek.org/login?next=/user/registrations"
                ));

            return true;
        } catch (\Exception $e) {
            $this->logError("Problem with creating participant with error: " . $e);
        }

        return false;
    }

    public function unsubscribe($eventId) {
        try {
            $this->repository->unsubscribeToEvent($eventId, $this->userId());
        } catch (\Exception $e) {
            $this->logError("Problem with unsubscribe to event with error: " . $e);
            return false;
        }

        return true;
    }

    public function subscribe($eventId) {
        try {
            $this->repository->resubscribeToEvent($eventId, $this->userId());
        } catch (Exception $e) {
            $this->logError("Problem with subscribe to event with error: " . $e);
            return false;
        }

        return true;
    }

    public function userEdit($data, $eventId) {
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

}
