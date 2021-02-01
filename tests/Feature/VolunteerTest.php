<?php

namespace Tests\Feature;
use App\Models\Event;
use App\Models\Volunteer;
use App\Models\VolunteerType;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class VolunteerTest extends TestCase
{

    public function testTypes()
    {
        VolunteerType::factory(11)->create();

        $response = $this->get('/api/secure/volunteers-types', [
            'Authorization' => 'Bearer '.$this->login(true),
        ])->assertStatus(200);
        $content = json_decode($response->getContent());

        $this->assertCount(11, $content);
    }

    public function testEventVolunteerList()
    {
        $events = Event::factory(2)->create();
        $volunteers = Volunteer::factory(11)->create(["event_id" => $events[0]->id]);

        $response = $this->get('/api/secure/admin/events/'.$volunteers[0]->event_id.'/volunteers', [
            'Authorization' => 'Bearer '.$this->login(true),
        ])->assertStatus(200);

        $content = json_decode($response->getContent());

        $this->assertCount(10, $content->data);
        $this->assertEquals(11, $content->total);
    }

    public function testVolunteerDetailList()
    {
        Event::factory(2)->create();
        $volunteers = Volunteer::factory(1)->create();

        $this->get('/api/secure/admin/volunteers/'.$volunteers[0]->id, [
            'Authorization' => 'Bearer '.$this->login(true),
        ])->assertStatus(200);
    }

    public function testNotFoundVolunteerDetailList()
    {
        $this->get('/api/secure/admin/volunteers/1', [
            'Authorization' => 'Bearer '.$this->login(true),
        ])->assertStatus(404);
    }

    public function testUpdateVolunteerDetail()
    {
        Event::factory(2)->create();
        $volunteers = Volunteer::factory(1)->create();

        $this->put('/api/secure/admin/volunteers/'.$volunteers[0]->id, [
            'isLeader' => true,
        ], [
            'Authorization' => 'Bearer '.$this->login(true),
        ])->assertStatus(200);

        $volunteer = DB::table(\App\Constants\TableConstants::VOLUNTEERS)->where('id', $volunteers[0]->id)->first();

        $this->assertEquals(true, $volunteer->is_leader);
    }
}
