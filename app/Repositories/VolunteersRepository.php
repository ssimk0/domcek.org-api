<?php


namespace App\Repositories;

use App\Constants\TableConstants;
use Illuminate\Support\Facades\DB;

class VolunteersRepository extends Repository
{
    public function eventVolunteerTypes($eventId)
    {
        return DB::table(TableConstants::EVENT_VOLUNTEERS_TYPES)
            ->join(TableConstants::VOLUNTEERS_TYPES, TableConstants::EVENT_VOLUNTEERS_TYPES . '.volunteer_type_id', TableConstants::VOLUNTEERS_TYPES . '.id')
            ->where(TableConstants::EVENT_VOLUNTEERS_TYPES . '.event_id', $eventId)
            ->get([TableConstants::VOLUNTEERS_TYPES . '.*', TableConstants::EVENT_VOLUNTEERS_TYPES . '.event_id']);
    }

    public function edit(array $data, $volunteerId)
    {
        DB::table(TableConstants::VOLUNTEERS)
            ->where('id', $volunteerId)
            ->update($data);
    }

    public function list($eventId)
    {
        return DB::table(TableConstants::VOLUNTEERS)
            ->join(TableConstants::USERS, TableConstants::USERS . ".id", TableConstants::VOLUNTEERS . ".user_id")
            ->join(TableConstants::PROFILES, TableConstants::USERS . ".id", TableConstants::PROFILES . ".user_id")
            ->where('event_id', $eventId)
            ->select([TableConstants::VOLUNTEERS . '.*', TableConstants::USERS . '.email', TableConstants::PROFILES . '.*'])
            ->paginate(10);
    }

    public function detail($volunteerId)
    {
        return DB::table(TableConstants::VOLUNTEERS)
            ->join(TableConstants::USERS, TableConstants::USERS . ".id", TableConstants::VOLUNTEERS . ".user_id")
            ->where(TableConstants::VOLUNTEERS . '.id', $volunteerId)
            ->first();
    }

    public function types()
    {
        return DB::table('volunteer_types')->get();
    }

    public function create(array $array, $eventId)
    {

    }
}
