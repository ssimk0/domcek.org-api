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

    public function list($size)
    {
        return DB::table('events')
            ->where('start_date', '>', Carbon::now()->format('Y-m-d'))
            ->orderBy('start_date')
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
}