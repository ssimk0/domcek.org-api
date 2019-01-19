<?php


namespace App\Services;

use App\Repositories\ParticipantRepository;
use App\Repositories\VolunteersRepository;

class ParticipantService extends Service
{
    private $repository;
    private $volunteersRepository;

    public function __construct(ParticipantRepository $repository, VolunteersRepository $volunteersRepository)
    {
        $this->repository = $repository;
        $this->volunteersRepository = $volunteersRepository;
    }

    public function list($eventId)
    {
        return $this->repository->list($eventId);
    }

    public function edit(array $data, $participantId)
    {

    }

    public function detail($eventId)
    {

        try {
            $participant = $this->repository->detail($eventId, $this->userId());

            return true;
        } catch (\Exception $e) {
            $this->logError("Problem with getting participant detail with error: " . $e);
        }

        return false;
    }

    public function create(array $data, $eventId)
    {
        $volunteerTypeId = array_get($data, 'volunteerTypeId');

        try {
            $this->repository->create([
                'note' => array_get($data, 'note'),
                'user_id' => $this->userId(),
                'event_id' => $eventId
            ], $eventId);

            if ($volunteerTypeId) {
                $this->volunteersRepository->create([
                    'volunteer_type_id' => $volunteerTypeId,
                    'event_id' => $eventId,
                    'user_id' => $this->userId()
                ], $eventId);
            }

            return true;
        } catch (\Exception $e) {
            $this->logError("Problem with creating participant with error: " . $e);
        }

        return false;
    }
}
