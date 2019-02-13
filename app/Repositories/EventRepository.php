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
        $query = DB::table(TableConstants::EVENTS)
            ->where('name', 'like', $this->prepareStringForLikeFilter($filter));

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
        DB::table('event_transport_times')->where('event_id', $eventId)->delete();
    }

    public function availableEvents()
    {
        $today = Carbon::now()->format('Y-m-d');

        return DB::table(TableConstants::EVENTS)
            ->whereDate('start_registration', '<=', $today)
            ->whereDate('end_registration', '>=', $today)
            ->orderBy('start_date', 'desc')
            ->get();
    }
}
