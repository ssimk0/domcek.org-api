<?php


namespace App\Repositories;


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
        $query = DB::table('events')
            ->where('name', 'like', $this->prepareStringForLikeFilter($filter));

        return $this->addWhereForFilter($query, $filter, ['name', 'theme'])
            ->orderBy('start_date', 'desc')
            ->paginate($size);
    }

    public function edit(array $data, $eventId)
    {
        DB::table('events')
            ->where('id', $eventId)
            ->update($data);
    }

    public function detail($eventId)
    {
        return DB::table('events')
        ->find( $eventId);
    }

    public function delete($eventId)
    {
        return DB::table('events')
            ->delete($eventId);
    }

    public function instance($eventId)
    {
        return Event::find($eventId);
    }

    public function createEventTransportTime($eventId, $time, $type) {
        DB::table('event_transport_times')->insert([
            'event_id' => $eventId,
            'time' => $time,
            'type' => $type
        ]);
    }

    public function deleteAllTransportTimesForEvent($eventId) {
        DB::table('event_transport_times')->where('event_id', $eventId)->delete();
    }

    public function availableEvents()
    {
        $today = Carbon::now()->format('Y-m-d');

        return DB::table('events')
            ->whereDate('start_registration', '<=', $today)
            ->whereDate('end_registration', '>=', $today)
            ->orderBy('start_date', 'desc')
            ->get();
    }
}
