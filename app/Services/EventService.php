<?php

namespace App\Services;

use App\Repositories\EventRepository;
use App\Repositories\GroupRepository;
use App\Repositories\ParticipantRepository;
use App\Repositories\VolunteersRepository;

class EventService extends Service
{
    private $participant;
    private $event;
    private $eventVolunteer;
    private $group;

    public function __construct(EventRepository $event, ParticipantRepository $participant, VolunteersRepository $eventVolunteer, GroupRepository $group)
    {
        $this->event = $event;
        $this->participant = $participant;
        $this->eventVolunteer = $eventVolunteer;
        $this->group = $group;
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
        $stats = $this->event->stats($eventId);
        if (empty($event)) return $event;
        $event->prices = $price;
        $event->busInTimes = $this->event->getEventTransportTimes([$event->id], 'in');
        $event->busOutTimes = $this->event->getEventTransportTimes([$event->id], 'out');
        $event->volunteerTypes = $this->eventVolunteer->eventVolunteerTypes([$event->id]);
        $event->participantCount = $this->participant->getCountForEvent([$event->id]);
        $event->stats = $stats;
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

    public function eventGroups($eventId)
    {
        $groups = $this->group->getGroupsForEvent($eventId);

        foreach ($groups as $group) {
            $group->info = $this->group->getGroupInfo($eventId, $group->group_name);
        }

        return $groups;
    }

    public function generateGroups($eventId, $data)
    {

        $participants =  $this->participant->getParticipantsForMakeGroup($eventId)->toArray();
        $countGroupMembers = floor(count($participants) / $data['groupsCount']);
        $countBiggerGroups = count($participants) % $data['groupsCount'];
        $startOffset = 0;

        try {
            foreach (range(0, $data['groupsCount'] - 1) as $groupNumber) {
                $memberCount = $countGroupMembers;
                $start = $countGroupMembers * $groupNumber;
                if (($data['groupsCount'] - $groupNumber) <= $countBiggerGroups) {
                    $memberCount++;
                    $start += $startOffset;
                    $startOffset++;
                }
                $group = array_slice($participants, $start, $memberCount);
                foreach ($group as $member) {
                    $this->group->editGroupByParticipantAndEventId($groupNumber + 1, $member->id, $eventId);
                };
            }

            return true;
        } catch (\Exception $e) {
            $this->logError('Problem with assign event group to participants with error: ' . $e);
        }

        return false;
    }

}
