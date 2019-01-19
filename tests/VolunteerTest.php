<?php


use Illuminate\Support\Facades\DB;
use Laravel\Lumen\Testing\DatabaseMigrations;

class VolunteerTest extends TestCase
{
    use DatabaseMigrations;

    function testTypes()
    {
        factory(App\Models\VolunteerType::class, 11)->create();

        $this->get('/api/secure/admin/volunteer/types', [
            'Authorization' => 'Bearer ' . $this->login(true)
        ]);

        $this->assertResponseOk();
        $content = json_decode($this->response->getContent());

        $this->assertCount(11, $content);
    }

    function testEventVolunteerList()
    {
        factory(App\Models\Event::class, 2)->create();
        $volunteers = factory(App\Models\Volunteer::class, 11)->create();

        $this->get('/api/secure/admin/event/' . $volunteers[0]->event_id . '/volunteer', [
            'Authorization' => 'Bearer ' . $this->login(true)
        ]);

        $this->assertResponseOk();
        $content = json_decode($this->response->getContent());

        $this->assertCount(10, $content->data);
        $this->assertEquals(11, $content->total);
    }

    function testVolunteerDetailList()
    {
        factory(App\Models\Event::class, 2)->create();
        $volunteers = factory(App\Models\Volunteer::class, 1)->create();

        $this->get('/api/secure/admin/volunteer/' . $volunteers[0]->id, [
            'Authorization' => 'Bearer ' . $this->login(true)
        ]);

        $this->assertResponseOk();
    }

    function testNotFoundVolunteerDetailList()
    {
        $this->get('/api/secure/admin/volunteer/1', [
            'Authorization' => 'Bearer ' . $this->login(true)
        ]);

        $this->assertResponseStatus(404);
    }

    function testUpdateVolunteerDetail()
    {
        factory(App\Models\Event::class, 2)->create();
        $volunteers = factory(App\Models\Volunteer::class, 1)->create();

        $this->put('/api/secure/admin/volunteer/' . $volunteers[0]->id, [
            'isLeader' => true
        ], [
            'Authorization' => 'Bearer ' . $this->login(true)
        ]);

        $this->assertResponseOk();

        $volunteer = DB::table(\App\Constants\TableConstants::VOLUNTEERS)->where('id', $volunteers[0]->id)->first();

        $this->assertEquals(true, $volunteer->is_leader);
    }

}
