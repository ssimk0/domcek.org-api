<?php


namespace App\Repositories;

use Illuminate\Support\Facades\DB;

class VolunteersRepository extends Repository
{
    public function eventTypes($eventId)
    {
        return DB::table('event_volunteer_types')
            ->join('volunteer_types', 'event_volunteer_types.volunteer_type_id', 'volunteer_types.id')
            ->where('event_volunteer_types.event_id', $eventId)
            ->get(['event_volunteer_types.*']);
    }

    public function edit(array $data, $volunteerId)
    {

    }

    public function list($eventId)
    {

    }

    public function detail($volunteerId)
    {

    }
}
