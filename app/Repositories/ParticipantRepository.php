<?php


namespace App\Repositories;


use App\Constants\TableConstants;
use App\Models\Participant;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ParticipantRepository extends Repository
{
    public function create($data)
    {
        $participant = new Participant($data);

        $participant->save();

        return $participant;
    }

    public function detail($eventId, $userId)
    {
        return DB::table(TableConstants::PARTICIPANTS)
            ->join(TableConstants::PROFILES, TableConstants::PROFILES . '.user_id', TableConstants::PARTICIPANTS . '.user_id')
            ->join(TableConstants::USERS, TableConstants::USERS . '.id', TableConstants::PARTICIPANTS . '.user_id')
            ->leftJoin(TableConstants::VOLUNTEERS, TableConstants::VOLUNTEERS . '.user_id', TableConstants::PARTICIPANTS . '.user_id')
            ->leftJoin(TableConstants::PAYMENTS, TableConstants::PAYMENTS . '.user_id', TableConstants::PARTICIPANTS . '.user_id')
            ->leftJoin(TableConstants::GROUPS, TableConstants::GROUPS . '.participant_id', TableConstants::PARTICIPANTS . '.id')
            ->where(TableConstants::PARTICIPANTS . '.event_id', $eventId)
            ->where(TableConstants::PARTICIPANTS . '.user_id', $userId)
            ->select(
                TableConstants::PROFILES . '.first_name',
                TableConstants::PROFILES . '.last_name',
                TableConstants::PROFILES . '.city',
                TableConstants::PROFILES . '.birth_date',
                TableConstants::PROFILES . '.phone',
                TableConstants::PARTICIPANTS . '.*',
                TableConstants::USERS . '.email',
                TableConstants::VOLUNTEERS . '.is_leader',
                TableConstants::VOLUNTEERS . '.volunteer_type_id',
                TableConstants::PAYMENTS . '.need_pay',
                TableConstants::PAYMENTS . '.paid',
                TableConstants::PAYMENTS . '.on_registration',
                TableConstants::GROUPS . '.group_name'
            )
            ->first();
    }

    public function list($eventId)
    {
        return DB::table(TableConstants::PARTICIPANTS)
            ->join(TableConstants::PROFILES, TableConstants::PROFILES . '.user_id', TableConstants::PARTICIPANTS . '.user_id')
            ->join(TableConstants::USERS, TableConstants::USERS . '.id', TableConstants::PARTICIPANTS . '.user_id')
            ->leftJoin(TableConstants::VOLUNTEERS, TableConstants::VOLUNTEERS . '.user_id', TableConstants::PARTICIPANTS . '.user_id')
            ->leftJoin(TableConstants::PAYMENTS, TableConstants::PAYMENTS . '.user_id', TableConstants::PARTICIPANTS . '.user_id')
            ->leftJoin(TableConstants::GROUPS, TableConstants::GROUPS . '.participant_id', TableConstants::PARTICIPANTS . '.id')
            ->where(TableConstants::PARTICIPANTS . '.event_id', $eventId)
            ->select(
                TableConstants::PROFILES . '.first_name',
                TableConstants::PROFILES . '.last_name',
                TableConstants::PROFILES . '.city',
                TableConstants::PROFILES . '.birth_date',
                TableConstants::PROFILES . '.phone',
                TableConstants::PARTICIPANTS . '.*',
                TableConstants::USERS . '.email',
                TableConstants::VOLUNTEERS . '.is_leader',
                TableConstants::VOLUNTEERS . '.volunteer_type_id',
                TableConstants::PAYMENTS . '.need_pay',
                TableConstants::PAYMENTS . '.paid',
                TableConstants::PAYMENTS . '.on_registration',
                TableConstants::GROUPS . '.group_name'
            )
            ->paginate(10);
    }

    public function edit($participantId, $registrationUserId, $changedBy)
    {
        $data = [
            'changed_by_user_id' => $changedBy
        ];

        if (!empty($registrationUserId)) {
            $data['register_by_user_id'] = $registrationUserId;
            $data['registration_date'] = Carbon::now()->format('Y-m-d');
        }

        DB::table(TableConstants::PARTICIPANTS)
            ->where('id', $participantId)
            ->update($data);
    }
}
