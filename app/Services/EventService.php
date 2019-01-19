<?php


namespace App\Services;


use App\Repositories\EventRepository;
use App\Repositories\VolunteersRepository;
use App\Repositories\ParticipantRepository;

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
        try {
            $event = $this->event->create([
                'name' => $data['name'],
                'theme' => array_get($data, 'theme'),
                'start_date' => array_get($data, 'startDate'),
                'end_date' => array_get($data, 'endDate'),
                'start_registration' => array_get($data, 'startRegistration'),
                'end_registration' => array_get($data, 'endRegistration'),
                'end_volunteer_registration' => array_get($data, 'endVolunteerRegistration'),
            ]);

            if (empty($event)) {
                return false;
            }

            $event->volunteerTypes()->attach($data['volunteerTypes']);

            return true;
        } catch (\Exception $e) {
            $this->logError("Problem with createing event with error: " . $e);
        }

        return false;
    }


    public function eventList($size)
    {
        $events = $this->event->list($size);

        foreach ($events as $event) {
            $event->eventTypes = $this->eventVolunteer->eventTypes($event->id);
        }

        return $events;
    }


    public function editEvent(array $data, $eventId)
    {
        try {
            $this->event->edit([
                'name' => $data['name'],
                'theme' => array_get($data, 'theme'),
                'start_date' => array_get($data, 'startDate'),
                'end_date' => array_get($data, 'endDate'),
                'start_registration' => array_get($data, 'startRegistration'),
                'end_registration' => array_get($data, 'endRegistration'),
                'end_volunteer_registration' => array_get($data, 'endVolunteerRegistration'),
            ], $eventId);

            return true;
        } catch (\Exception $e) {
            $this->logError("Problem with creating event with error: " . $e);
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
            $this->logError("Problem with deleting event with error: " . $e);
        }

        return false;
    }
}
