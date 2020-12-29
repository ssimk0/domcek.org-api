<?php

namespace App\Services;

use App\Repositories\EventRepository;
use App\Repositories\ParticipantRepository;
use App\Repositories\VolunteersRepository;
use Carbon\Carbon;
use Illuminate\Support\Arr;

class EventService extends Service
{
    private $participant;
    private $event;
    private $eventVolunteer;

    public function __construct(EventRepository $event, ParticipantRepository $participant, VolunteersRepository $eventVolunteer)
    {
        $this->event = $event;
        $this->participant = $participant;
        $this->eventVolunteer = $eventVolunteer;
    }

    public function createEvent(array $data)
    {
        $createData = [
            'name' => $data['name'],
            'theme' => Arr::get($data, 'theme', null),
            'type' => Arr::get($data, 'type', null),
            'start_date' => Arr::get($data, 'startDate', false),
            'end_date' => Arr::get($data, 'endDate', false),
            'start_registration' => Arr::get($data, 'startRegistration', false),
            'end_registration' => Arr::get($data, 'endRegistration', false),
            'end_volunteer_registration' => Arr::get($data, 'endVolunteerRegistration', false),
        ];

        $timesIn = Arr::get($data, 'transportTimesIn', []);
        $timesOut = Arr::get($data, 'transportTimesOut', []);
        $volunteerTypes = Arr::get($data, 'volunteerTypes', []);

        try {
            $event = $this->event->create(array_filter($createData));

            if (empty($event)) {
                return false;
            }

            $this->event->createPrices(Arr::get($data, 'prices', []), $event->id);

            if (!empty($volunteerTypes)) {
                $event->volunteerTypes()->attach($volunteerTypes);
            }

            foreach ($timesIn as $time) {
                $this->event->createEventTransportTime($event->id, $time, 'in');
            }

            foreach ($timesOut as $time) {
                $this->event->createEventTransportTime($event->id, $time, 'out');
            }

            $this->event->generateRegistrationToken(
                $event->id,
                Arr::get($data, 'endDate', false)
            );

            return true;
        } catch (\Exception $e) {
            $this->logError('Problem with creating event with error: ' . $e);
        }

        return false;
    }

    public function eventList($size, $filter)
    {
        $events = $this->event->list($size, $filter);

        foreach ($events as $event) {
            $event->volunteerTypes = $this->eventVolunteer->eventVolunteerTypes([$event->id]);
            $event->prices = $this->event->eventPrices([$event->id]);
        }



        return $events;
    }

    public function availableEvents()
    {
        $events = $this->event->availableEvents();

        foreach ($events as $event) {
           $event->prices = $this->event->eventPrices([$event->id]);
           $event->volunteerTypes = $this->eventVolunteer->eventVolunteerTypes([$event->id]);
           $event->participantCount = $this->participant->getCountForEvent([$event->id]);
           $event->busInTimes = $this->event->getEventTransportTimes($event->id, 'in');
           $event->busOutTimes = $this->event->getEventTransportTimes($event->id, 'out');
        }

        return $events;
    }

    public function editEvent(array $data, $eventId)
    {
        $editData = [
            'name' => $data['name'],
            'theme' => Arr::get($data, 'theme', false),
            'start_date' => Arr::get($data, 'startDate', false),
            'end_date' => Arr::get($data, 'endDate', false),
            'start_registration' => Arr::get($data, 'startRegistration', false),
            'end_registration' => Arr::get($data, 'endRegistration', false),
            'end_volunteer_registration' => Arr::get($data, 'endVolunteerRegistration', false),
        ];
        $timesIn = Arr::get($data, 'transportTimesIn', []);
        $timesOut = Arr::get($data, 'transportTimesOut', []);

        try {
            $this->event->edit(array_filter($editData), $eventId);
            $volunteerTypes = Arr::get($data, 'volunteerTypes', []);
            if (count($volunteerTypes) > 0) {
                $event = $this->event->instance($eventId);
                $event->volunteerTypes()->sync($volunteerTypes);
            }

            $this->event->deleteAllTransportTimesForEvent($event->id);

            foreach ($timesIn as $time) {
                $this->event->createEventTransportTime($event->id, $time, 'in');
            }

            foreach ($timesOut as $time) {
                $this->event->createEventTransportTime($event->id, $time, 'out');
            }

            return true;
        } catch (\Exception $e) {
            $this->logError('Problem with creating event with error: ' . $e);
        }

        return false;
    }

    public function eventDetail($eventId)
    {
        $event = $this->event->detail($eventId);
        $price = $this->event->eventPrices([$eventId]);
        $registrationToken = $this->event->registrationToken($eventId);
        $stats = $this->event->stats($eventId);
        if (empty($event)) return $event;
        $event->prices = $price;
        $event->busInTimes = $this->event->getEventTransportTimes([$event->id], 'in');
        $event->busOutTimes = $this->event->getEventTransportTimes([$event->id], 'out');
        $event->volunteerTypes = $this->eventVolunteer->eventVolunteerTypes([$event->id]);
        $event->participantCount = $this->participant->getCountForEvent([$event->id]);
        $event->stats = $stats;
        $event->registrationToken = empty($registrationToken) ? null : $registrationToken->token;
        return $event;
    }

    public function detailedStatsFile($eventId)
    {
        $stats = $this->event->detailedStats($eventId);
        // SETUP DEFAULTS
        if (!Arr::get($stats, 'ages.0', false)) {
            $stats['ages'] = $this->getStatDefault();
        }

        if (!Arr::get($stats, 'cities.0', false)) {
            $stats['cities'] = $this->getStatDefault();
        }

        if (!Arr::get($stats, 'names-female.0', false)) {
            $stats['names-female'] = $this->getStatDefault();
        }

        if (!Arr::get($stats, 'names-male.0', false)) {
            $stats['names-male'] = $this->getStatDefault();
        }


        return sprintf("
        Počet Dobrovolníkov: %d\n
        Počet Učasničok: %d\n
        Počet Učasnikov: %d\n
        Počet Všetkych Dokopy: %d\n
        Top Vek: \n 
          1. Počet: %d Vek: %d
          2. Počet: %d Vek: %d
          3. Počet: %d Vek: %d
          
        Top Mestá:\n
          1. Počet: %d Mesto: %s
          2. Počet: %d Mesto: %s
          3. Počet: %d Mesto: %s
        
        Top Ženské mená:\n
          1. Počet: %d Meno: %s
          2. Počet: %d Meno: %s
          3. Počet: %d Meno: %s
          
        Top Mužské mená:\n
          1. Počet: %d Meno: %s
          2. Počet: %d Meno: %s
          3. Počet: %d Meno: %s
        ",
        $stats['volunteers'],
        $stats['participants-female'],
        $stats['participants-male'],
        $stats['count-all'],
        Arr::get($stats, 'ages.0')->count, Arr::get($stats, 'ages.0')->year ? Carbon::now()->year - Arr::get($stats, 'ages.0')->year : '',
        Arr::get($stats, 'ages.1')->count, Arr::get($stats, 'ages.1')->year ? Carbon::now()->year - Arr::get($stats, 'ages.1')->year : '',
        Arr::get($stats, 'ages.2')->count, Arr::get($stats, 'ages.2')->year ? Carbon::now()->year - Arr::get($stats, 'ages.2')->year : '',
        Arr::get($stats, 'cities.0')->count,  Arr::get($stats, 'cities.0')->city,
        Arr::get($stats, 'cities.1')->count,  Arr::get($stats, 'cities.1')->city,
        Arr::get($stats, 'cities.2')->count,  Arr::get($stats, 'cities.2')->city,
        Arr::get($stats, 'names-female.0')->count,  Arr::get($stats, 'names-female.0')->first_name,
        Arr::get($stats, 'names-female.1')->count,  Arr::get($stats, 'names-female.1')->first_name,
        Arr::get($stats, 'names-female.2')->count,  Arr::get($stats, 'names-female.2')->first_name,
        Arr::get($stats, 'names-male.0')->count,  Arr::get($stats, 'names-male.0')->first_name,
        Arr::get($stats, 'names-male.1')->count,  Arr::get($stats, 'names-male.1')->first_name,
        Arr::get($stats, 'names-male.2')->count,  Arr::get($stats, 'names-male.2')->first_name
        );
    }

    public function delete($eventId)
    {
        try {
            return $this->event->delete($eventId);
        } catch (\Exception $e) {
            $this->logError('Problem with deleting event with error: ' . $e);
        }

        return false;
    }

    private function getStatDefault() {
        return [
            new class {
                public $count = 0;
                public $first_name = '';
                public $city = '';
                public $year = '';
            },
            new class {
                public $count = 0;
                public $first_name = '';
                public $city = '';
                public $year = '';
            },
            new class {
                public $count = 0;
                public $first_name = '';
                public $city = '';
                public $year = '';
            }
        ];
    }

}
