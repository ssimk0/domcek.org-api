<?php

namespace App\Services;

use App\Repositories\EventRepository;
use App\Repositories\GroupRepository;
use App\Repositories\ParticipantRepository;
use App\Repositories\VolunteersRepository;
use Carbon\Carbon;

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
            'theme' => array_get($data, 'theme', null),
            'type' => array_get($data, 'type', null),
            'start_date' => array_get($data, 'startDate', false),
            'end_date' => array_get($data, 'endDate', false),
            'start_registration' => array_get($data, 'startRegistration', false),
            'end_registration' => array_get($data, 'endRegistration', false),
            'end_volunteer_registration' => array_get($data, 'endVolunteerRegistration', false),
        ];

        $timesIn = array_get($data, 'transportTimesIn', []);
        $timesOut = array_get($data, 'transportTimesOut', []);
        $volunteerTypes = array_get($data, 'volunteerTypes', []);

        try {
            $event = $this->event->create(array_filter($createData));

            if (empty($event)) {
                return false;
            }

            $this->event->createPrices(array_get($data, 'prices', []), $event->id);

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
                array_get($data, 'endDate', false)
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
            'theme' => array_get($data, 'theme', false),
            'start_date' => array_get($data, 'startDate', false),
            'end_date' => array_get($data, 'endDate', false),
            'start_registration' => array_get($data, 'startRegistration', false),
            'end_registration' => array_get($data, 'endRegistration', false),
            'end_volunteer_registration' => array_get($data, 'endVolunteerRegistration', false),
        ];
        $timesIn = array_get($data, 'transportTimesIn', []);
        $timesOut = array_get($data, 'transportTimesOut', []);

        try {
            $this->event->edit(array_filter($editData), $eventId);
            $volunteerTypes = array_get($data, 'volunteerTypes', []);
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
        if (array_get($stats, 'ages.0', true)) {
            $stats['ages'] = $this->getStatDefault();
        }

        if (array_get($stats, 'cities.0', true)) {
            $stats['cities'] = $this->getStatDefault();
        }

        if (array_get($stats, 'names-female.0', true)) {
            $stats['names-female'] = $this->getStatDefault();
        }

        if (array_get($stats, 'names-male.0', true)) {
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
        array_get($stats, 'ages.0')->count, array_get($stats, 'ages.0')->year ? Carbon::now()->year - array_get($stats, 'ages.0')->year : '',
        array_get($stats, 'ages.1')->count, array_get($stats, 'ages.1')->year ? Carbon::now()->year - array_get($stats, 'ages.1')->year : '',
        array_get($stats, 'ages.2')->count, array_get($stats, 'ages.2')->year ? Carbon::now()->year - array_get($stats, 'ages.2')->year : '',
        array_get($stats, 'cities.0')->count,  array_get($stats, 'cities.0')->city,
        array_get($stats, 'cities.1')->count,  array_get($stats, 'cities.1')->city,
        array_get($stats, 'cities.2')->count,  array_get($stats, 'cities.2')->city,
        array_get($stats, 'names-female.0')->count,  array_get($stats, 'names-female.0')->first_name,
        array_get($stats, 'names-female.1')->count,  array_get($stats, 'names-female.1')->first_name,
        array_get($stats, 'names-female.2')->count,  array_get($stats, 'names-female.2')->first_name,
        array_get($stats, 'names-male.0')->count,  array_get($stats, 'names-male.0')->first_name,
        array_get($stats, 'names-male.1')->count,  array_get($stats, 'names-male.1')->first_name,
        array_get($stats, 'names-male.2')->count,  array_get($stats, 'names-male.2')->first_name
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
