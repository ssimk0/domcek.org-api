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

    public function userEdit($data, $userId, $eventId)
    {
        Participant::where('user_id', $userId)
            ->where('event_id', $eventId)
            ->update($data);
    }

    public function userEvents($userId)
    {
        $today = Carbon::now()->format('Y-m-d');

        return DB::table(TableConstants::PARTICIPANTS)
            ->join(TableConstants::PROFILES, TableConstants::PROFILES . '.user_id', TableConstants::PARTICIPANTS . '.user_id')
            ->join(TableConstants::USERS, TableConstants::USERS . '.id', TableConstants::PARTICIPANTS . '.user_id')
            ->join(TableConstants::EVENTS, TableConstants::EVENTS . '.id', TableConstants::PARTICIPANTS . '.event_id')
            ->leftJoin(TableConstants::VOLUNTEERS, function ($join) {
                $join->on(TableConstants::VOLUNTEERS . '.user_id', TableConstants::PARTICIPANTS . '.user_id');
                $join->on(TableConstants::VOLUNTEERS . '.event_id', TableConstants::PARTICIPANTS . '.event_id');
            })
            ->leftJoin(TableConstants::PAYMENTS, function ($join) {
                $join->on(TableConstants::PAYMENTS . '.user_id', TableConstants::PARTICIPANTS . '.user_id');
                $join->on(TableConstants::PAYMENTS . '.event_id', TableConstants::PARTICIPANTS . '.event_id');
            })
            ->leftJoin(TableConstants::GROUPS, function ($join) {
                $join->on(TableConstants::GROUPS . '.participant_id', TableConstants::PARTICIPANTS . '.id');
                $join->on(TableConstants::GROUPS . '.event_id', TableConstants::PARTICIPANTS . '.event_id');
            })
            ->where(TableConstants::PARTICIPANTS . '.user_id', $userId)
            ->whereDate(TableConstants::EVENTS . '.end_date', '>=', $today)
            ->select(
                TableConstants::EVENTS . '.name',
                TableConstants::EVENTS . '.theme',
                TableConstants::EVENTS . '.start_date',
                TableConstants::EVENTS . '.end_date',
                TableConstants::EVENTS . '.id',
                TableConstants::PARTICIPANTS . '.note',
                TableConstants::PARTICIPANTS . '.event_id',
                TableConstants::PARTICIPANTS . '.user_id',
                TableConstants::PARTICIPANTS . '.subscribed',
                TableConstants::PARTICIPANTS . '.transport_in',
                TableConstants::PARTICIPANTS . '.transport_out',
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
            ->leftJoin(TableConstants::VOLUNTEERS, function ($join) {
                $join->on(TableConstants::VOLUNTEERS . '.user_id', TableConstants::PARTICIPANTS . '.user_id');
                $join->on(TableConstants::VOLUNTEERS . '.event_id', TableConstants::PARTICIPANTS . '.event_id');
            })
            ->leftJoin(TableConstants::PAYMENTS, function ($join) {
                $join->on(TableConstants::PAYMENTS . '.user_id', TableConstants::PARTICIPANTS . '.user_id');
                $join->on(TableConstants::PAYMENTS . '.event_id', TableConstants::PARTICIPANTS . '.event_id');
            })
            ->leftJoin(TableConstants::GROUPS, function ($join) {
                $join->on(TableConstants::GROUPS . '.participant_id', TableConstants::PARTICIPANTS . '.id');
                $join->on(TableConstants::GROUPS . '.event_id', TableConstants::PARTICIPANTS . '.event_id');
            })
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

    public function list($eventId, $filters)
    {
        $query = DB::table(TableConstants::PARTICIPANTS)
            ->join(TableConstants::PROFILES, TableConstants::PROFILES . '.user_id', TableConstants::PARTICIPANTS . '.user_id')
            ->join(TableConstants::USERS, TableConstants::USERS . '.id', TableConstants::PARTICIPANTS . '.user_id')
            ->leftJoin(TableConstants::VOLUNTEERS, function ($join) {
                $join->on(TableConstants::VOLUNTEERS . '.user_id', TableConstants::PARTICIPANTS . '.user_id');
                $join->on(TableConstants::VOLUNTEERS . '.event_id', TableConstants::PARTICIPANTS . '.event_id');
            })
            ->leftJoin(TableConstants::VOLUNTEERS_TYPES, TableConstants::VOLUNTEERS_TYPES.'.id', TableConstants::VOLUNTEERS.'.volunteer_type_id')
            ->leftJoin(TableConstants::PAYMENTS, function ($join) {
                $join->on(TableConstants::PAYMENTS . '.user_id', TableConstants::PARTICIPANTS . '.user_id');
                $join->on(TableConstants::PAYMENTS . '.event_id', TableConstants::PARTICIPANTS . '.event_id');
            })
            ->leftJoin(TableConstants::GROUPS, function ($join) {
                $join->on(TableConstants::GROUPS . '.participant_id', TableConstants::PARTICIPANTS . '.id');
                $join->on(TableConstants::GROUPS . '.event_id', TableConstants::PARTICIPANTS . '.event_id');
            })
            ->where(TableConstants::PARTICIPANTS . '.event_id', $eventId)
            ->where(TableConstants::PARTICIPANTS . '.subscribed', true);

            $query = $this->filterQuery($query, $filters);

            if (array_get($filters, 'sortBy') != null) {
                $sortBy = $filters['sortBy'];
                $sort = array_get($filters, 'sortDesc', 'false') == 'false' ? 'asc' : 'desc';
                $profileFields = ['birth_date'];
                $groupFields = ['group_name'];

                if ( in_array($sortBy, $profileFields) ) { 
                    $query->orderBy(TableConstants::PROFILES.'.'.$sortBy, $sort);
                } else if (in_array($sortBy, $groupFields)) {
                    $query->orderBy(TableConstants::GROUPS.'.'.$sortBy, $sort);
                }
            }

            if (array_get($filters, 'type') == 'volunteer') {
                $query->where(TableConstants::VOLUNTEERS.'.volunteer_type_id', '!=', null);
            } else if (array_get($filters, 'type') == 'participant') {
                $query->where(TableConstants::VOLUNTEERS.'.volunteer_type_id', '=', null);
            }

            return $this->addWhereForFilter($query, array_get($filters, 'filter', ''), [
                'profiles.last_name',
                'profiles.first_name',
                'profiles.birth_date',
                'profiles.phone',
                'profiles.city',
                'users.email',
                'volunteer_types.name',
                'payments.payment_number',
                'groups.group_name'
            ])
            ->select(
                TableConstants::PROFILES . '.first_name',
                TableConstants::PROFILES . '.last_name',
                TableConstants::PROFILES . '.city',
                TableConstants::PROFILES . '.birth_date',
                TableConstants::PROFILES . '.phone',
                DB::raw('profiles.admin_note as anote'),
                TableConstants::PARTICIPANTS . '.*',
                TableConstants::USERS . '.email',
                TableConstants::VOLUNTEERS . '.is_leader',
                TableConstants::VOLUNTEERS . '.volunteer_type_id',
                TableConstants::VOLUNTEERS_TYPES . '.name',
                TableConstants::PAYMENTS . '.need_pay',
                TableConstants::PAYMENTS . '.payment_number',
                TableConstants::PAYMENTS . '.paid',
                TableConstants::PAYMENTS . '.on_registration',
                TableConstants::GROUPS . '.group_name',
                DB::raw('(select count(*) from volunteers where users.id = volunteers.user_id and volunteers.was_on_event = 1 ) as volunteer_count'),
                DB::raw('(select count(*) from participants where users.id = participants.user_id and participants.was_on_event = 1 ) as participant_count')
            )
            ->groupBy('users.id')
            ->paginate(10);
    }

    public function registrationList($eventId)
    {
        return DB::table(TableConstants::PARTICIPANTS)
            ->join(TableConstants::PROFILES, TableConstants::PROFILES . '.user_id', TableConstants::PARTICIPANTS . '.user_id')
            ->join(TableConstants::USERS, TableConstants::USERS . '.id', TableConstants::PARTICIPANTS . '.user_id')
            ->leftJoin(TableConstants::VOLUNTEERS, function ($join) {
                $join->on(TableConstants::VOLUNTEERS . '.user_id', TableConstants::PARTICIPANTS . '.user_id');
                $join->on(TableConstants::VOLUNTEERS . '.event_id', TableConstants::PARTICIPANTS . '.event_id');
            })
            ->leftJoin(TableConstants::VOLUNTEERS_TYPES, TableConstants::VOLUNTEERS_TYPES.'.id', TableConstants::VOLUNTEERS.'.volunteer_type_id')
            ->leftJoin(TableConstants::PAYMENTS, function ($join) {
                $join->on(TableConstants::PAYMENTS . '.user_id', TableConstants::PARTICIPANTS . '.user_id');
                $join->on(TableConstants::PAYMENTS . '.event_id', TableConstants::PARTICIPANTS . '.event_id');
            })
            ->leftJoin(TableConstants::GROUPS, function ($join) {
                $join->on(TableConstants::GROUPS . '.participant_id', TableConstants::PARTICIPANTS . '.id');
                $join->on(TableConstants::GROUPS . '.event_id', TableConstants::PARTICIPANTS . '.event_id');
            })
            ->where(TableConstants::PARTICIPANTS . '.event_id', $eventId)
            ->select(
                TableConstants::PROFILES . '.first_name',
                TableConstants::PROFILES . '.birth_date',
                TableConstants::PARTICIPANTS . '.*',
                TableConstants::VOLUNTEERS . '.is_leader',
                TableConstants::VOLUNTEERS_TYPES . '.name',
                TableConstants::PAYMENTS . '.payment_number',
                TableConstants::PAYMENTS . '.need_pay',
                TableConstants::PAYMENTS . '.paid',
                TableConstants::PAYMENTS . '.on_registration',
                TableConstants::GROUPS . '.group_name'
            )->get();
    }

    public function edit($participantId, $changedBy, $data = [])
    {
        $data = array_merge($data, [
            'changed_by_user_id' => $changedBy
        ]);

        DB::table(TableConstants::PARTICIPANTS)
            ->where('id', $participantId)
            ->update($data);
    }

    public function getCountForEvent($event_ids)
    {
        return DB::table(TableConstants::PARTICIPANTS)
            ->whereIn('event_id', $event_ids)
            ->where('subscribed', true)
            ->count();
    }

    public function unsubscribeToEvent($eventId, $userId)
    {
        DB::table(TableConstants::PARTICIPANTS)
            ->where('event_id', $eventId)
            ->where('user_id', $userId)
            ->update([
                'subscribed' => false
            ]);
    }

    public function resubscribeToEvent($eventId, $userId)
    {
        DB::table(TableConstants::PARTICIPANTS)
            ->where('event_id', $eventId)
            ->where('user_id', $userId)
            ->update([
                'subscribed' => true
            ]);
    }

    public function registerUser($userId, $eventId, $transportOut, $payedOnRegistration) {
        DB::table(TableConstants::PARTICIPANTS)
        ->where('event_id', $eventId)
        ->where('user_id', $userId)
        ->update([
            'was_on_event' => true,
            'transport_out' => $transportOut
        ]);

        DB::table(TableConstants::PAYMENTS)
        ->where('event_id', $eventId)
        ->where('user_id', $userId)
        ->update([
            'on_registration' => $payedOnRegistration
        ]);

        $vol = DB::table(TableConstants::VOLUNTEERS)
        ->where('event_id', $eventId)
        ->where('user_id', $userId);

        if ($vol->exists()) {
            $vol->update([
                'was_on_event' => 1
            ]);
        }
    }

    public function participantExist($eventId, $userId) {
        return DB::table(TableConstants::PARTICIPANTS)
        ->where('event_id', $eventId)
        ->where('user_id', $userId)
        ->exists();
    }

    public function detailedRegistrationList($eventId)
    {
        $registered = DB::table(TableConstants::PARTICIPANTS)
            ->join(TableConstants::PROFILES, TableConstants::PROFILES . '.user_id', TableConstants::PARTICIPANTS . '.user_id')
            ->join(TableConstants::USERS, TableConstants::USERS . '.id', TableConstants::PARTICIPANTS . '.user_id')
            ->leftJoin(TableConstants::VOLUNTEERS, function ($join) {
                $join->on(TableConstants::VOLUNTEERS . '.user_id', TableConstants::PARTICIPANTS . '.user_id');
                $join->on(TableConstants::VOLUNTEERS . '.event_id', TableConstants::PARTICIPANTS . '.event_id');
            })
            ->leftJoin(TableConstants::VOLUNTEERS_TYPES, TableConstants::VOLUNTEERS_TYPES.'.id', TableConstants::VOLUNTEERS.'.volunteer_type_id')
            ->leftJoin(TableConstants::PAYMENTS, function ($join) {
                $join->on(TableConstants::PAYMENTS . '.user_id', TableConstants::PARTICIPANTS . '.user_id');
                $join->on(TableConstants::PAYMENTS . '.event_id', TableConstants::PARTICIPANTS . '.event_id');
            })
            ->leftJoin(TableConstants::GROUPS, function ($join) {
                $join->on(TableConstants::GROUPS . '.participant_id', TableConstants::PARTICIPANTS . '.id');
                $join->on(TableConstants::GROUPS . '.event_id', TableConstants::PARTICIPANTS . '.event_id');
            })
            ->where(TableConstants::PARTICIPANTS . '.event_id', $eventId)
            ->select(
                TableConstants::PROFILES . '.*',
                TableConstants::USERS.'.email',
                TableConstants::PARTICIPANTS . '.*',
                TableConstants::VOLUNTEERS . '.is_leader',
                TableConstants::PAYMENTS . '.payment_number',
                TableConstants::VOLUNTEERS_TYPES . '.name',
                TableConstants::PAYMENTS . '.need_pay',
                TableConstants::PAYMENTS . '.paid',
                TableConstants::PAYMENTS . '.on_registration',
                TableConstants::GROUPS . '.group_name'
            )->get()
            ->all();

        $profiles = DB::table(TableConstants::PROFILES)
            ->join(TableConstants::USERS, TableConstants::USERS.'.id', TableConstants::PROFILES.'.user_id')
            ->select(
                TableConstants::PROFILES . '.*',
                TableConstants::USERS.'.email'
            )
            ->get()
            ->all();
        $notRegistered = array_filter($profiles, function ($profile) use ($registered) {
            $notMatched = true;
            foreach ($registered as $user) {
                if ($user->user_id == $profile->user_id) {
                    $notMatched = false;
                }
            }

            return $notMatched;
        });

        return array_merge($registered, $notRegistered);
    }


    public function getParticipantsForMakeGroup($eventId) {

        return DB::table(TableConstants::PARTICIPANTS)
            ->join(TableConstants::PROFILES, TableConstants::PROFILES . '.user_id', TableConstants::PARTICIPANTS . '.user_id')
            ->join(TableConstants::USERS, TableConstants::USERS . '.id', TableConstants::PARTICIPANTS . '.user_id')
            ->leftJoin(TableConstants::VOLUNTEERS, function ($join) {
                $join->on(TableConstants::VOLUNTEERS . '.user_id', TableConstants::PARTICIPANTS . '.user_id');
                $join->on(TableConstants::VOLUNTEERS . '.event_id', TableConstants::PARTICIPANTS . '.event_id');
            })
            ->where(TableConstants::PARTICIPANTS.'.event_id', $eventId)
            ->where(TableConstants::VOLUNTEERS.'.id', null)
            ->orderByRaw('YEAR(profiles.birth_date), participants.created_at')
            ->groupBy(TableConstants::PROFILES.'.user_id')
            ->get([TableConstants::PROFILES.'.birth_date', TableConstants::PROFILES.'.user_id', TableConstants::PARTICIPANTS.'.id']);
    }

    public function getNameplateDetail($eventId)
    {
        return DB::table(TableConstants::PARTICIPANTS)
            ->join(TableConstants::PROFILES, TableConstants::PROFILES . '.user_id', TableConstants::PARTICIPANTS . '.user_id')
            ->join(TableConstants::USERS, TableConstants::USERS . '.id', TableConstants::PARTICIPANTS . '.user_id')
            ->leftJoin(TableConstants::GROUPS, function ($join) {
                $join->on(TableConstants::GROUPS . '.participant_id', TableConstants::PARTICIPANTS . '.id');
                $join->on(TableConstants::GROUPS . '.event_id', TableConstants::PARTICIPANTS . '.event_id');
            })
            ->leftJoin(TableConstants::VOLUNTEERS, function ($join) {
                $join->on(TableConstants::VOLUNTEERS . '.user_id', TableConstants::PARTICIPANTS . '.user_id');
                $join->on(TableConstants::VOLUNTEERS . '.event_id', TableConstants::PARTICIPANTS . '.event_id');
            })
            ->where(TableConstants::PARTICIPANTS . '.event_id', $eventId)
            ->where(TableConstants::VOLUNTEERS . '.id', '=', null)
            ->orderBy(TableConstants::GROUPS.'.group_name')
            ->orderBy(TableConstants::PROFILES.'.last_name')
            ->orderBy(TableConstants::PROFILES.'.first_name')
            ->select([TableConstants::PROFILES . '.nick', TableConstants::PROFILES . '.first_name', TableConstants::GROUPS . '.group_name'])
            ->get();
    }

    public function getCountOfParticipationOnEvents($userId)
    {
        DB::table(TableConstants::PARTICIPANTS)
            ->leftJoin(TableConstants::VOLUNTEERS, function ($join) {
                $join->on(TableConstants::VOLUNTEERS . '.user_id', TableConstants::PARTICIPANTS . '.user_id');
                $join->on(TableConstants::VOLUNTEERS . '.event_id', TableConstants::PARTICIPANTS . '.event_id');
            })
            ->where(TableConstants::VOLUNTEERS . '.id', '=', null)
            ->where(TableConstants::PARTICIPANTS.'user_id', $userId)
            ->count();
    }
}
