<?php


namespace App\Repositories;


use App\Constants\TableConstants;
use Illuminate\Support\Facades\DB;

class ParticipantRepository extends Repository
{
    public function create($data, $eventId)
    {

    }

    public function detail($eventId, $userId)
    {
        return DB::table(TableConstants::PARTICIPANTS)
            ->join(TableConstants::PROFILES, TableConstants::PROFILES . '.user_id', TableConstants::PARTICIPANTS . '.user_id')
            ->join(TableConstants::USERS, TableConstants::USERS . '.user_id', TableConstants::PARTICIPANTS . '.user_id')
            ->join(TableConstants::VOLUNTEERS, TableConstants::VOLUNTEERS . '.user_id', TableConstants::PARTICIPANTS . '.user_id')
            ->join(TableConstants::PAYMENTS, TableConstants::PAYMENTS . '.user_id', TableConstants::PARTICIPANTS . '.user_id')
            ->join(TableConstants::GROUPS, TableConstants::GROUPS . '.participant_id', TableConstants::PARTICIPANTS . '.id')
            ->where(TableConstants::PARTICIPANTS . '.event_id', $eventId)
            ->where(TableConstants::PARTICIPANTS . '.user_id', $userId)
            ->get();
    }

    public function list($eventId)
    {
        return DB::table(TableConstants::PARTICIPANTS)
            ->join(TableConstants::PROFILES, TableConstants::PROFILES . '.user_id', TableConstants::PARTICIPANTS . '.user_id')
            ->join(TableConstants::USERS, TableConstants::USERS . '.user_id', TableConstants::PARTICIPANTS . '.user_id')
            ->join(TableConstants::VOLUNTEERS, TableConstants::VOLUNTEERS . '.user_id', TableConstants::PARTICIPANTS . '.user_id')
            ->join(TableConstants::PAYMENTS, TableConstants::PAYMENTS . '.user_id', TableConstants::PARTICIPANTS . '.user_id')
            ->join(TableConstants::GROUPS, TableConstants::GROUPS . '.participant_id', TableConstants::PARTICIPANTS . '.id')
            ->where(TableConstants::PARTICIPANTS . '.event_id', $eventId)
            ->get();
    }
}
