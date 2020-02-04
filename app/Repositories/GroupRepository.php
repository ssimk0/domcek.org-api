<?php
namespace App\Repositories;

use Illuminate\Support\Facades\DB;
use App\Constants\TableConstants;
use App\Models\EventsGroup;
use App\Models\Group;

class GroupRepository extends Repository
{
    public function editGroupByParticipantAndEventId($group, $participantId, $eventId)
    {
        
        $eventGroup = DB::table(TableConstants::EVENT_GROUPS)
        ->where('group_name', $group)
        ->where('event_id', $eventId);

        if ($eventGroup->exists()) {
            $eventGroup = $eventGroup->get(["id"])->first();
        } else {
            $eventGroup = new EventsGroup([
                'group_name' => $group,
                'animator' => null,
                'event_id' => $eventId
            ]);

            $eventGroup->save();

            $eventGroup = DB::table(TableConstants::EVENT_GROUPS)
            ->where('group_name', $group)
            ->where('event_id', $eventId)
            ->get(["id"])
            ->first();
        }

       return DB::table(TableConstants::PARTICIPANTS)
            ->where('id', $participantId)
            ->update([
                'group_id' => empty($eventGroup) ? null : $eventGroup->id,
            ]);
    }

    public function getGroupsForEvent($eventId)
    {
        return DB::table(TableConstants::EVENT_GROUPS)
            ->where('events_group.event_id', $eventId)
            ->leftJoin(TableConstants::PARTICIPANTS, TableConstants::PARTICIPANTS.'.group_id', TableConstants::EVENT_GROUPS.'.id')
            ->groupBy(TableConstants::EVENT_GROUPS.'.group_name')
            ->orderByRaw('cast(events_group.group_name as unsigned)')
            ->get([
                'group_name',
                DB::raw('(select CONCAT(first_name, " ", last_name) from profiles where profiles.user_id = animator ) as group_animator'),
                'animator',
                'events_group.event_id',
                'events_group.id'
            ]);
    }

    public function getGroupInfo($eventId, $groupName)
    {
        return DB::table(TableConstants::PARTICIPANTS)
            ->join(TableConstants::PROFILES, TableConstants::PROFILES.'.user_id', TableConstants::PARTICIPANTS.'.user_id')
            ->where(TableConstants::PARTICIPANTS.'.event_id', $eventId)
            ->where(TableConstants::PARTICIPANTS.'.group_id', $groupName)
            ->selectRaw('MIN(YEAR(profiles.birth_date)) as min, MAX(YEAR(profiles.birth_date)) as max, COUNT(*) as count')
            ->first();
    }

    public function deleteGroupByParticipantAndEventId($participantId, $eventId)
    {
        $query = DB::table(TableConstants::PARTICIPANTS)
            ->where('id', $participantId)
            ->where('event_id', $eventId);
        $query->update([
            'group_id' => null
        ]);
    }

    public function addAnimatorToGroup($eventId, $groupName, $userId)
    {
        return DB::table(TableConstants::EVENT_GROUPS)
        ->where('group_name', $groupName)
        ->where('event_id', $eventId)
        ->update([
            "animator" => $userId
        ]);
    }
}
