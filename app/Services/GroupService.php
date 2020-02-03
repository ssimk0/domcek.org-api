<?php


namespace App\Services;

use App\Repositories\GroupRepository;
use App\Repositories\ParticipantRepository;

class GroupService extends Service
{
    private $repository;
    private $participant;

    public function __construct(GroupRepository $group, ParticipantRepository $participant)
    {
        $this->repository = $group;
        $this->participant = $participant;
    }

    public function eventGroups($eventId)
    {
        $groups = $this->repository->getGroupsForEvent($eventId);

        foreach ($groups as $group) {
            $group->info = $this->repository->getGroupInfo($eventId, $group->id);
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
                    $this->repository->editGroupByParticipantAndEventId($groupNumber + 1, $member->id, $eventId);
                };
            }

            return true;
        } catch (\Exception $e) {
            $this->logError('Problem with assign event group to participants with error: ' . $e);
        }

        return false;
    }

    public function assignAnimator($eventId, $groupName, $userId)
    {
        return $this->repository->addAnimatorToGroup($eventId, $groupName, $userId);
    }
}
