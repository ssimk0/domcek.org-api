<?php


namespace App\Repositories;


use App\Constants\TableConstants;
use App\Models\Event;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class EventRepository extends Repository
{
    public function create(array $data)
    {
        $event = new Event($data);

        $success = $event->save();
        if ($success) {
            return $event;
        } else {
            return null;
        }
    }

    public function list($size, $filter)
    {
        $query = DB::table(TableConstants::EVENTS);

        return $this->addWhereForFilter($query, $filter, ['name', 'theme'])
            ->orderBy('start_date', 'desc')
            ->paginate($size);
    }

    public function edit(array $data, $eventId)
    {
        DB::table(TableConstants::EVENTS)
            ->where('id', $eventId)
            ->update($data);
    }

    public function detail($eventId)
    {
        return DB::table('events')
            ->find($eventId);
    }

    public function delete($eventId)
    {
        return DB::table(TableConstants::EVENTS)
            ->delete($eventId);
    }

    public function instance($eventId)
    {
        return Event::find($eventId);
    }

    public function createEventTransportTime($eventId, $time, $type)
    {
        DB::table(TableConstants::EVENT_TRANSPORT_TIMES)->insert([
            'event_id' => $eventId,
            'time' => $time,
            'type' => $type
        ]);
    }

    public function getEventTransportTimes($eventId, $type)
    {
        return DB::table(TableConstants::EVENT_TRANSPORT_TIMES)
            ->where('event_id', $eventId)
            ->where('type', $type)
            ->get();
    }

    public function deleteAllTransportTimesForEvent($eventId)
    {
        $query = DB::table('event_transport_times')->where('event_id', $eventId);

        if ($query->exists()) {
            $query->delete();
        }
    }

    public function availableEvents()
    {
        $today = Carbon::now()->format('Y-m-d');

        return DB::table(TableConstants::EVENTS)
            ->whereDate('start_registration', '<=', $today)
            ->whereDate('start_date', '>', $today)
            ->orderBy('start_date', 'desc')
            ->get();
    }

    public function stats($eventId)
    {
        $countParticipants = $this->getCountParticipant('participant', $eventId);
        $countVolunteer = $this->getCountParticipant('volunteer', $eventId);
        $countInBusPassengers = $this->getCountBusPassengers($eventId, 'transport_in');
        $countOutBusPassengers = DB::table(TableConstants::PARTICIPANTS)
            ->where('event_id', $eventId)
            ->where('transport_out', 'like', '%:%')
            ->count();

        return [
            'bus-in' => $countInBusPassengers,
            'bus-out' => $countOutBusPassengers,
            'volunteers' => $countVolunteer,
            'participants' => $countParticipants,
            'count-all' => $countVolunteer + $countParticipants
        ];
    }

    public function detailedStats($eventId)
    {
        $countParticipantsFemale = $this->getCountParticipant('participant', $eventId, 'f');
        $countParticipantsMale = $this->getCountParticipant('participant', $eventId, 'm');
        $countVolunteer = $this->getCountParticipant('volunteer', $eventId);

        $topNamesF = $this->getTop($eventId, TableConstants::PROFILES.'.first_name', 5, null, 'f');
        $topNamesM = $this->getTop($eventId, TableConstants::PROFILES.'.first_name', 5, null, 'm');
        $topCities = $this->getTop($eventId, TableConstants::PROFILES.'.city');
        $topAges = $this->getTop($eventId, DB::raw('YEAR(profiles.birth_date) as year'), 5, DB::raw('YEAR(profiles.birth_date)'));
        return [
            'ages' => $topAges->toArray(),
            'cities' => $topCities->toArray(),
            'names-male' => $topNamesM->toArray(),
            'names-female' => $topNamesF->toArray(),
            'volunteers' => $countVolunteer,
            'participants-female' => $countParticipantsFemale,
            'participants-male' => $countParticipantsMale,
            'count-all' => $countVolunteer + $countParticipantsFemale + $countParticipantsMale
        ];
    }


    private function getCountParticipant($type, $eventId, $sex=null) {
        $query = DB::table(TableConstants::PARTICIPANTS)
            ->join(TableConstants::PROFILES, TableConstants::PROFILES.'.user_id', TableConstants::PARTICIPANTS . '.user_id')
            ->leftJoin(TableConstants::VOLUNTEERS, function ($join) {
                $join->on(TableConstants::VOLUNTEERS . '.user_id', TableConstants::PARTICIPANTS . '.user_id');
                $join->on(TableConstants::VOLUNTEERS . '.event_id', TableConstants::PARTICIPANTS . '.event_id');
            })
            ->where(TableConstants::PARTICIPANTS.'.event_id', $eventId)
            ->where(TableConstants::PARTICIPANTS.'.was_on_event', true);

        if ($type == 'volunteer') {
            $query =  $query
                ->where(TableConstants::VOLUNTEERS . '.id', '!=', null);
        } else if ($type == 'participant') {
            $query = $query
                ->where(TableConstants::VOLUNTEERS . '.id', '=', null);
        }

        if ($sex != null) {
            $query = $query->
                where(TableConstants::PROFILES.'.sex', $sex);
        }

        return $query->count();
    }

    private function getTop($eventId, $column, $limit = 5, $groupBy = null, $sex = null) {
        $groupBy = $groupBy ? $groupBy : $column;
        $query = DB::table(TableConstants::PROFILES)
            ->leftJoin(TableConstants::PARTICIPANTS, function ($join) {
                $join->on(TableConstants::PARTICIPANTS . '.user_id', TableConstants::PROFILES . '.user_id');
            })
            ->where(TableConstants::PARTICIPANTS.'.event_id', $eventId)
            ->where(TableConstants::PARTICIPANTS.'.was_on_event', true);

        if (!empty($sex)) {
            $query->where(TableConstants::PROFILES.'.sex', $sex);
        }

        return $query->groupBy($groupBy)
            ->orderByRaw('COUNT(*) desc')
            ->select(
                DB::raw('COUNT(*) as count'),
                $column
            )
            ->limit($limit)
            ->get();
    }

    private function getCountBusPassengers($eventId, $type)
    {
        return DB::table(TableConstants::PARTICIPANTS)
            ->where('event_id', $eventId)
            ->where($type, 'like', '%:%')
            ->groupBy($type)
            ->select(
                DB::raw('COUNT(*) as count'),
                $type
            )->get();
    }

    public function createPrices($prices, $eventId)
    {
        foreach ($prices as $price) {
            $data = [
                'event_id' => $eventId,
                'need_pay' => array_get($price, 'need_pay', false),
                'deposit' => array_get($price, 'deposit', false),
                'description' => array_get($price, 'description', false),
            ];

            DB::table(TableConstants::EVENT_PRICES)->insert($data);
        }
    }

    public function eventPrices($ids)
    {
        return DB::table(TableConstants::EVENT_PRICES)->whereIn('event_id', $ids)->get();
    }

    public function eventPriceById($id, $eventId)
    {
        return DB::table(TableConstants::EVENT_PRICES)->where('id', $id)->where('event_id', $eventId)->first();
    }

    public function generateRegistrationToken($eventId, $endDate)
    {
        try {
            DB::table(TableConstants::AUTH_TOKEN)
                ->insert([
                    'event_id' => $eventId,
                    'valid_until' => $endDate,
                    'type' => 'registration',
                    'token' => Str::random(10)
                ]);
        } catch (\Exception $e) {
            Log::warning("Error while generating token: " . $e);
        }
    }

    public function registrationToken($eventId)
    {
        return DB::table(TableConstants::AUTH_TOKEN)->where('event_id', $eventId)->first();
    }
}
