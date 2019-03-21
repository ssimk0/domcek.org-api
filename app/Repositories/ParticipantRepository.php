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

    public function userEvents($userId)
    {
        $today = Carbon::now()->format('Y-m-d');

        return DB::table(TableConstants::PARTICIPANTS)
            ->join(TableConstants::PROFILES, TableConstants::PROFILES . '.user_id', TableConstants::PARTICIPANTS . '.user_id')
            ->join(TableConstants::USERS, TableConstants::USERS . '.id', TableConstants::PARTICIPANTS . '.user_id')
            ->join(TableConstants::EVENTS, TableConstants::EVENTS . '.id', TableConstants::PARTICIPANTS . '.event_id')
            ->leftJoin(TableConstants::VOLUNTEERS, TableConstants::VOLUNTEERS . '.user_id', TableConstants::PARTICIPANTS . '.user_id')
            ->leftJoin(TableConstants::PAYMENTS, TableConstants::PAYMENTS . '.user_id', TableConstants::PARTICIPANTS . '.user_id')
            ->leftJoin(TableConstants::GROUPS, TableConstants::GROUPS . '.participant_id', TableConstants::PARTICIPANTS . '.id')
            ->where(TableConstants::PARTICIPANTS . '.user_id', $userId)
            ->whereDate(TableConstants::EVENTS . '.end_date', '>=', $today)
            ->select(
                TableConstants::EVENTS . '.name',
                TableConstants::EVENTS . '.theme',
                TableConstants::EVENTS . '.start_date',
                TableConstants::EVENTS . '.end_date',
                TableConstants::PARTICIPANTS . '.*',
                TableConstants::VOLUNTEERS . '.is_leader',
                TableConstants::VOLUNTEERS . '.volunteer_type_id',
                TableConstants::PAYMENTS . '.need_pay',
                TableConstants::PAYMENTS . '.paid',
                TableConstants::PAYMENTS . '.payment_number',
                TableConstants::PAYMENTS . '.on_registration',
                TableConstants::GROUPS . '.group_name'
            )->get();
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
                TableConstants::PAYMENTS . '.payment_number',
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
            ->leftJoin(TableConstants::VOLUNTEERS, function($join) use ($eventId) {
                $join->on(TableConstants::VOLUNTEERS . '.user_id', '=', TableConstants::PARTICIPANTS . '.user_id');
                $join->on(TableConstants::VOLUNTEERS . '.event_id', '=', $eventId);
            })
            ->leftJoin(TableConstants::PAYMENTS, function($join) use ($eventId) {
                $join->on(TableConstants::PAYMENTS . '.user_id', '=', TableConstants::PARTICIPANTS . '.user_id');
                $join->on(TableConstants::PAYMENTS . '.event_id', '=', $eventId);
            })
            ->leftJoin(TableConstants::GROUPS, function($join) use ($eventId) {
                $join->on(TableConstants::GROUPS . '.participant_id', '=', TableConstants::PARTICIPANTS . '.id');
                $join->on(TableConstants::GROUPS . '.event_id', '=', $eventId);
            })
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
                TableConstants::PAYMENTS . '.payment_number',
                TableConstants::PAYMENTS . '.paid',
                TableConstants::PAYMENTS . '.on_registration',
                TableConstants::GROUPS . '.group_name'
            )
            ->paginate(10);
    }

    public function registrationList($eventId)
    {
        return DB::table(TableConstants::PARTICIPANTS)
            ->join(TableConstants::PROFILES, TableConstants::PROFILES . '.user_id', TableConstants::PARTICIPANTS . '.user_id')
            ->join(TableConstants::USERS, TableConstants::USERS . '.id', TableConstants::PARTICIPANTS . '.user_id')
            ->leftJoin(TableConstants::VOLUNTEERS, TableConstants::VOLUNTEERS . '.user_id', TableConstants::PARTICIPANTS . '.user_id')
            ->leftJoin(TableConstants::PAYMENTS, TableConstants::PAYMENTS . '.user_id', TableConstants::PARTICIPANTS . '.user_id')
            ->leftJoin(TableConstants::GROUPS, TableConstants::GROUPS . '.participant_id', TableConstants::PARTICIPANTS . '.id')
            ->where(TableConstants::PARTICIPANTS . '.event_id', $eventId)
            ->where(TableConstants::PAYMENTS . '.event_id', $eventId)
            ->where(TableConstants::VOLUNTEERS . '.event_id', $eventId)
            ->select(
                TableConstants::PROFILES . '.first_name',
                TableConstants::PARTICIPANTS . '.*',
                TableConstants::VOLUNTEERS . '.is_leader',
                TableConstants::VOLUNTEERS . '.volunteer_type_id',
                TableConstants::PAYMENTS . '.payment_number',
                TableConstants::PAYMENTS . '.need_pay',
                TableConstants::PAYMENTS . '.paid',
                TableConstants::PAYMENTS . '.on_registration',
                TableConstants::GROUPS . '.group_name'
            )->get();
    }

    public function edit($participantId, $registrationUserId, $changedBy)
    {
        $data = [
            'changed_by_user_id' => $changedBy
        ];

        if (!empty($registrationUserId)) {
            $data['register_by_user_id'] = $registrationUserId;
        }

        DB::table(TableConstants::PARTICIPANTS)
            ->where('id', $participantId)
            ->update($data);
    }

    public function getCountForEvent($event_id)
    {
        return DB::table(TableConstants::PARTICIPANTS)
            ->where('event_id', $event_id)
            ->count();
    }
}
