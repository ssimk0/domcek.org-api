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
        $times = array_get($data, 'transportTimes', []);

        try {
            $this->event->edit(array_filter($editData), $eventId);
            $volunteerTypes = array_get($data, 'volunteerTypes', []);
            if (count($volunteerTypes) > 0) {
                $event = $this->event->instance($eventId);
                $event->volunteerTypes()->sync($volunteerTypes);
            }

            foreach ($times as $time) {
                $this->event->deleteAllTransportTimesForEvent($event->id);
                $this->event->createEventTransportTime($event->id, $time);
            }

            return true;
        } catch (\Exception $e) {
            $this->logError('Problem with creating event with error: ' . $e);
        }

        return false;
    }

    public function eventDetail($eventId)
    {
        return $this->event->detail($eventId);
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
