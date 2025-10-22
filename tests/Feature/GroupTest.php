<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\Group;
use App\Models\Participant;
use App\Models\User;
use Tests\TestCase;

class GroupTest extends TestCase
{
    public function testEventGroupsRequiresAuth()
    {
        $event = Event::factory()->createOne();

        $this->get("/api/secure/admin/events/{$event->id}/groups")
            ->assertStatus(401);
    }

    public function testEventGroupsRequiresAdmin()
    {
        $event = Event::factory()->createOne();
        $token = $this->login(false); // non-admin user

        $this->get("/api/secure/admin/events/{$event->id}/groups", [
            'Authorization' => 'Bearer ' . $token,
        ])->assertStatus(403);
    }

    public function testEventGroupsList()
    {
        $event = Event::factory()->createOne();
        Group::factory(5)->create(['event_id' => $event->id]);

        $response = $this->get("/api/secure/admin/events/{$event->id}/groups", $this->getAuthHeader());

        $response->assertStatus(200)
            ->assertJsonCount(5);
    }

    public function testEventGroupsListEmpty()
    {
        $event = Event::factory()->createOne();

        $response = $this->get("/api/secure/admin/events/{$event->id}/groups", $this->getAuthHeader());

        $response->assertStatus(200)
            ->assertJsonCount(0);
    }

    public function testGenerateGroups()
    {
        $event = Event::factory()->createOne(['max_group_size' => 10]);
        Participant::factory(25)->create(['event_id' => $event->id]);

        $response = $this->put(
            "/api/secure/admin/events/{$event->id}/groups",
            [],
            $this->getAuthHeader()
        );

        $response->assertStatus(200);

        // Verify groups were created
        $this->assertDatabaseCount('events_group', 3); // 25 participants, max 10 per group = 3 groups
    }

    public function testGenerateGroupsRequiresAdmin()
    {
        $event = Event::factory()->createOne();
        $token = $this->login(false); // non-admin user

        $this->put("/api/secure/admin/events/{$event->id}/groups", [], [
            'Authorization' => 'Bearer ' . $token,
        ])->assertStatus(403);
    }

    public function testAssignAnimator()
    {
        $event = Event::factory()->createOne();
        $group = Group::factory()->createOne(['event_id' => $event->id]);
        $participant = Participant::factory()->createOne(['event_id' => $event->id]);

        $response = $this->put(
            "/api/secure/admin/events/{$event->id}/groups/animator",
            [
                'group_id' => $group->id,
                'participant_id' => $participant->id,
            ],
            $this->getAuthHeader()
        );

        $response->assertStatus(200);
    }

    public function testAssignAnimatorRequiresAdmin()
    {
        $event = Event::factory()->createOne();
        $group = Group::factory()->createOne(['event_id' => $event->id]);
        $participant = Participant::factory()->createOne(['event_id' => $event->id]);
        $token = $this->login(false); // non-admin user

        $this->put("/api/secure/admin/events/{$event->id}/groups/animator", [
            'group_id' => $group->id,
            'participant_id' => $participant->id,
        ], [
            'Authorization' => 'Bearer ' . $token,
        ])->assertStatus(403);
    }
}
