<?php


namespace App\Repositories;

use App\Constants\TableConstants;
use App\Models\Volunteer;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class VolunteersRepository extends Repository
{
    public function eventVolunteerTypes($eventIds)
    {
        return DB::table(TableConstants::EVENT_VOLUNTEERS_TYPES)
            ->join(TableConstants::VOLUNTEERS_TYPES, TableConstants::EVENT_VOLUNTEERS_TYPES . '.volunteer_type_id', TableConstants::VOLUNTEERS_TYPES . '.id')
            ->whereIn(TableConstants::EVENT_VOLUNTEERS_TYPES . '.event_id', $eventIds)
            ->get([TableConstants::VOLUNTEERS_TYPES . '.*', TableConstants::EVENT_VOLUNTEERS_TYPES . '.event_id']);
    }

    public function edit(array $data, $id)
    {
        DB::table(TableConstants::VOLUNTEERS)
            ->where('id', $id)
            ->update($data);
    }

    public function editByUserAndEventId(array $data, $userId, $eventId)
    {
        $volunteer = DB::table(TableConstants::VOLUNTEERS)
            ->where('user_id', $userId)
            ->where('event_id', $eventId)
            ->first();
        if ($volunteer) {
            DB::table(TableConstants::VOLUNTEERS)
                ->where('user_id', $userId)
                ->where('event_id', $eventId)
                ->update($data);
        } else {
            $this->create(Arr::collapse([[
                'event_id' => $eventId,
                'user_id' => $userId
            ], $data]));
        }
    }

    public function list($eventId)
    {
        return DB::table(TableConstants::VOLUNTEERS)
            ->join(TableConstants::USERS, TableConstants::USERS . ".id", TableConstants::VOLUNTEERS . ".user_id")
            ->join(TableConstants::PROFILES, TableConstants::USERS . ".id", TableConstants::PROFILES . ".user_id")
            ->where(TableConstants::VOLUNTEERS . '.event_id', $eventId)
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
        return DB::table(TableConstants::VOLUNTEERS_TYPES)->where('active', true)->get();
    }

    public function typeByName($name)
    {
        return DB::table(TableConstants::VOLUNTEERS_TYPES)->where('name', $name)->first();
    }

    public function create(array $data)
    {
        $volunteer = new Volunteer($data);
        $volunteer->save();

        return $volunteer;
    }


    public function deleteIfExist($userId, $eventId) {
        $query =  DB::table(TableConstants::VOLUNTEERS)
        ->where('event_id', $eventId)
        ->where('user_id', $userId);
        if ($query->exists()) {
            $query->delete();
        }
    }

    public function getNameplateDetail($eventId)
    {
        return DB::table(TableConstants::PARTICIPANTS)
            ->join(TableConstants::PROFILES, TableConstants::PROFILES . '.user_id', TableConstants::PARTICIPANTS . '.user_id')
            ->join(TableConstants::USERS, TableConstants::USERS . '.id', TableConstants::PARTICIPANTS . '.user_id')
            ->leftJoin(TableConstants::VOLUNTEERS, function ($join) {
                $join->on(TableConstants::VOLUNTEERS . '.user_id', TableConstants::PARTICIPANTS . '.user_id');
                $join->on(TableConstants::VOLUNTEERS . '.event_id', TableConstants::PARTICIPANTS . '.event_id');
            })
            ->leftJoin(TableConstants::EVENT_GROUPS, TableConstants::EVENT_GROUPS . '.id', TableConstants::PARTICIPANTS . '.group_id')
            ->join(TableConstants::VOLUNTEERS_TYPES, TableConstants::VOLUNTEERS_TYPES.'.id', TableConstants::VOLUNTEERS . '.volunteer_type_id')
            ->where(TableConstants::PARTICIPANTS . '.event_id', $eventId)
            ->where(TableConstants::VOLUNTEERS . '.id', '!=', null)
            ->where(TableConstants::PARTICIPANTS . '.subscribed', true)
            ->orderBy(TableConstants::EVENT_GROUPS.'.group_name')
            ->orderBy(TableConstants::PROFILES.'.last_name')
            ->orderBy(TableConstants::PROFILES.'.first_name')
            ->select([TableConstants::VOLUNTEERS_TYPES . '.name', TableConstants::PROFILES . '.nick', TableConstants::PROFILES . '.first_name', TableConstants::EVENT_GROUPS . '.group_name'])
            ->get();
    }
}
