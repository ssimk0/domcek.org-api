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
}