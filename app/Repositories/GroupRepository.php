<?php
namespace App\Repositories;

use Illuminate\Support\Facades\DB;
use App\Constants\TableConstants;
use App\Models\Group;

class GroupRepository extends Repository {

    function editGroupByParticipantAndEventId($group, $participantId, $eventId) {
        $query = DB::table(TableConstants::GROUPS)
        ->where('participant_id', $participantId)
        ->where('event_id', $eventId);

        if ($query->exists()) {
            $query->update([
                'group_name' => $group
            ]);
        } else {
            $group = new Group([
                'group_name' => $group,
                'group_animator' => null,
                'event_id' => $eventId,
                'participant_id' => $participantId
            ]);
            $group->save();
        }
    }

    public function getGroupsForEvent($eventId)
    {
        return DB::table(TableConstants::GROUPS)
            ->where('event_id', $eventId)
            ->groupBy('group_name')
            ->orderByRaw('cast(group_name as unsigned)')
            ->get(['group_name', 'group_animator', 'event_id']);
    }

    public function getGroupInfo($eventId, $groupName)
    {
        return DB::table(TableConstants::GROUPS)
            ->join(TableConstants::PARTICIPANTS, TableConstants::PARTICIPANTS.'.id', TableConstants::GROUPS.'.participant_id')
            ->join(TableConstants::PROFILES, TableConstants::PROFILES.'.user_id', TableConstants::PARTICIPANTS.'.user_id')
            ->where(TableConstants::PARTICIPANTS.'.event_id', $eventId)
            ->where(TableConstants::GROUPS.'.group_name', $groupName)
            ->selectRaw('MIN(YEAR(profiles.birth_date)) as min, MAX(YEAR(profiles.birth_date)) as max, COUNT(*) as count')
            ->first();
    }
}