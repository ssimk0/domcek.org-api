<?php


namespace App\Services;


use App\Repositories\VolunteersRepository;
use Illuminate\Support\Facades\Log;

class VolunteerService extends Service
{
    private $repository;

    public function __construct(VolunteersRepository $repository)
    {
        $this->repository = $repository;
    }


    public function editVolunteer(array $data, $volunteerId)
    {
        try {
            $this->repository->edit($data, $volunteerId);
            return true;
        } catch (\Exception $e) {
            Log::debug("Problem while editing volunteer ${$e}");
        }

        return false;
    }


    public function volunteerList($eventId)
    {
        return $this->repository->list($eventId);
    }

    public function volunteerDetail($volunteerId) {
        try {
            return $this->repository->detail($volunteerId);
        } catch (\Exception $e) {
            Log::debug("Problem while getting volunteer detail ${$e}");
        }

        return null;
    }
}
