<?php

namespace App\Services;

use App\Models\TransportType;
use App\Repositories\EventRepository;
use App\Repositories\ParticipantRepository;
use App\Repositories\VolunteersRepository;

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
            'need_pay' => array_get($data, 'needPay', false),
            'deposit' => array_get($data, 'deposit', false),
        ];

        $timesIn = array_get($data, 'transportTimesIn', []);
        $timesOut = array_get($data, 'transportTimesOut', []);
        $volunteerTypes = array_get($data, 'volunteerTypes', []);

        try {
            $event = $this->event->create(array_filter($createData));

            if (empty($event)) {
                return false;
            }

            if (!empty($volunteerTypes)) {
                $event->volunteerTypes()->attach($volunteerTypes);
            }

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

    public function eventList($size, $filter)
    {
        $events = $this->event->list($size, $filter);

        foreach ($events as $event) {
            $event->volunteerTypes = $this->eventVolunteer->eventVolunteerTypes($event->id);
        }

        return $events;
    }

    public function availableEvents()
    {
        $events = $this->event->availableEvents();

        foreach ($events as $event) {
            $event->volunteerTypes = $this->eventVolunteer->eventVolunteerTypes($event->id);
        }

        foreach ($events as $event) {
            $event->participantCount = $this->participant->getCountForEvent($event->id);
        }

        foreach ($events as $event) {
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
            if (!empty($timesIn) || !empty($timesOut)) {
                $this->event->deleteAllTransportTimesForEvent($event->id);
            }

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
        if (empty($event)) return $event;

        $event->busInTimes = $this->event->getEventTransportTimes($event->id, 'in');
        $event->busOutTimes = $this->event->getEventTransportTimes($event->id, 'out');
        $event->volunteerTypes = $this->eventVolunteer->eventVolunteerTypes($event->id);
        $event->participantCount = $this->participant->getCountForEvent($event->id);

        return $event;
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

}
