<?php


namespace App\Services;


use App\Repositories\VolunteersRepository;
use Illuminate\Support\Arr;

class VolunteerService extends Service
{
    private $repository;

    public function __construct(VolunteersRepository $repository)
    {
        $this->repository = $repository;
    }


    public function editVolunteer(array $data, $volunteerId)
    {
        $data = [
            'volunteer_type_id' => Arr::get($data, 'volunteerTypeId', false),
            'is_leader' => Arr::get($data, 'isLeader', false)
        ];

        try {
            $this->repository->edit(array_filter($data), $volunteerId);
            return true;
        } catch (\Exception $e) {
           $this->logError("Problem while editing volunteer ". $e->getMessage());
        }

        return false;
    }


    public function volunteerList($eventId)
    {
        return $this->repository->list($eventId);
    }

    public function volunteerDetail($volunteerId)
    {
        try {
            return $this->repository->detail($volunteerId);
        } catch (\Exception $e) {
            $this->logError("Problem while getting volunteer detail " . $e->getMessage());
        }

        return null;
    }

    public function volunteerTypes()
    {
        return $this->repository->types();
    }

}
