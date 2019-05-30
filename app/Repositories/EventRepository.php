<?php


namespace App\Repositories;


use App\Constants\TableConstants;
use App\Models\Event;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

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
        $topNames = $this->getTop($eventId, TableConstants::PROFILES.'.first_name', 15);
        $topCities = $this->getTop($eventId, TableConstants::PROFILES.'.city');
        $topAges = $this->getTop($eventId, DB::raw('YEAR(profiles.birth_date) as year'), 5, DB::raw('YEAR(profiles.birth_date)'));

        return [
            'ages' => $topAges,
            'cities' => $topCities,
            'names' => $topNames,
            'bus-in' => $countInBusPassengers,
            'bus-out' => $countOutBusPassengers,
            'volunteers' => $countVolunteer,
            'participants' => $countParticipants,
            'count-all' => $countVolunteer + $countParticipants
        ];
    }


    private function getCountParticipant($type, $eventId) {
        $query = DB::table(TableConstants::PARTICIPANTS)
            ->leftJoin(TableConstants::VOLUNTEERS, function ($join) {
                $join->on(TableConstants::VOLUNTEERS . '.user_id', TableConstants::PARTICIPANTS . '.user_id');
                $join->on(TableConstants::VOLUNTEERS . '.event_id', TableConstants::PARTICIPANTS . '.event_id');
            })
            ->where(TableConstants::PARTICIPANTS.'.event_id', $eventId)
            ->where(TableConstants::PARTICIPANTS.'.was_on_event', true);

        if ($type == 'volunteer') {
            return $query
                ->where(TableConstants::VOLUNTEERS . '.id', '!=', null)
                ->count();
        } else if ($type == 'participant') {
            return $query
                ->where(TableConstants::VOLUNTEERS . '.id', '=', null)
                ->count();
        } else {
            return $query->count();
        }
    }

    private function getTop($eventId, $column, $limit = 5, $groupBy = null) {
        $groupBy = $groupBy ? $groupBy : $column;
        return DB::table(TableConstants::PROFILES)
            ->leftJoin(TableConstants::PARTICIPANTS, function ($join) {
                $join->on(TableConstants::PARTICIPANTS . '.user_id', TableConstants::PROFILES . '.user_id');
            })
            ->where(TableConstants::PARTICIPANTS.'.event_id', $eventId)
            ->where(TableConstants::PARTICIPANTS.'.was_on_event', true)
            ->groupBy($groupBy)
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
}
