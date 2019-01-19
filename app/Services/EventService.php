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
        $createData = [
            'name' => $data['name'],
            'theme' => array_get($data, 'theme', null),
            'start_date' => array_get($data, 'startDate', false),
            'end_date' => array_get($data, 'endDate', false),
            'start_registration' => array_get($data, 'startRegistration', false),
            'end_registration' => array_get($data, 'endRegistration', false),
            'end_volunteer_registration' => array_get($data, 'endVolunteerRegistration', false),
        ];

        try {
            $event = $this->event->create(array_filter($createData));

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

        try {
            $this->event->edit(array_filter($editData), $eventId);

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
