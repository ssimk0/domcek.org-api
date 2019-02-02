<?php


namespace App\Services;

use App\Repositories\EventRepository;
use App\Repositories\ParticipantRepository;
use App\Repositories\PaymentRepository;
use App\Repositories\VolunteersRepository;

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

    public function detail($eventId)
    {
        return $this->adminDetail($eventId, $this->userId());
    }

    public function create(array $data, $eventId)
    {
        $volunteerTypeId = array_get($data, 'volunteerTypeId');

        try {
            $event = $this->eventRepository->detail($eventId);

            $this->repository->create([
                'note' => array_get($data, 'note'),
                'transport_in' => array_get($data, 'transportIn'),
                'transport_out' => array_get($data, 'transportOut'),
                'user_id' => $this->userId(),
                'event_id' => $eventId
            ]);

            if ($volunteerTypeId) {
                $this->volunteersRepository->create([
                    'volunteer_type_id' => $volunteerTypeId,
                    'event_id' => $eventId,
                    'user_id' => $this->userId()
                ]);
            }

            $this->paymentRepository->create([
                'user_id' => $this->userId(),
                'payment_number' => $this->paymentRepository->generatePaymentNumber(),
                'paid' => 0,
                'need_pay' => $event->need_pay,
                'event_id' => $eventId,
            ]);

            // TODO: check if we need send a email about payment

            return true;
        } catch (\Exception $e) {
            $this->logError("Problem with creating participant with error: " . $e);
        }

        return false;
    }

    public function adminDetail($eventId, $userId)
    {
        try {
            return $this->repository->detail($eventId, $userId);
        } catch (\Exception $e) {
            $this->logError("Problem with getting participant detail with error: " . $e);
        }

        return false;
    }

}
