<?php


use Illuminate\Support\Facades\DB;
use Laravel\Lumen\Testing\DatabaseMigrations;

class EventTest extends TestCase
{
    use DatabaseMigrations;

    function testAuthEvent()
    {
        $this->post('/api/secure/admin/event', [], [
            'Authorization' => 'Bearer ' . $this->login()
        ]);

        $this->assertResponseStatus(403);
    }

    function testCreateEvent()
    {
        $this->post('/api/secure/admin/event', [
            "name" => "81. Púť radosti",
            "startDate" => "2019-02-03",
            "endDate" => "2019-02-04",
            "startRegistration" => "2019-01-15",
            "endRegistration" => "2019-01-25",
            "endVolunteerRegistration" => "2019-01-20",
            "volunteerTypes" => [1]
        ], [
            'Authorization' => 'Bearer ' . $this->login(true)
        ]);

        $this->assertResponseStatus(201);
    }

    function testEventList()
    {
        $types = factory(App\Models\VolunteerType::class, 5)->create();
        $events = factory(App\Models\Event::class, 11)->create();

        foreach ($events as $event) {
            $event->volunteerTypes()->attach($types);
        }

        $this->get('/api/secure/admin/event', [
            'Authorization' => 'Bearer ' . $this->login(true)
        ]);

        $this->assertResponseOk();
        $content = json_decode($this->response->getContent());

        $this->assertCount(10, $content->data);
        $this->assertCount(5, $content->data[0]->volunteerTypes);
    }


    function testEventDetail()
    {
        $types = factory(App\Models\VolunteerType::class, 5)->create();
        $events = factory(App\Models\Event::class, 11)->create();

        foreach ($events as $event) {
            $event->volunteerTypes()->attach($types);
        }

        $this->get('/api/secure/admin/event/' . $events[0]->id, [
            'Authorization' => 'Bearer ' . $this->login(true)
        ]);

        $this->assertResponseOk();
        $content = json_decode($this->response->getContent());

        $this->assertEquals($events[0]->id, $content->id);
    }

    function testEventDetailNotFound()
    {

        $this->get('/api/secure/admin/event/notFound', [
            'Authorization' => 'Bearer ' . $this->login(true)
        ]);

        $this->assertResponseStatus(404);
    }

    function testEventDelete()
    {
        $types = factory(App\Models\VolunteerType::class, 5)->create();
        $events = factory(App\Models\Event::class, 1)->create();

        foreach ($events as $event) {
            $event->volunteerTypes()->attach($types);
        }

        $this->delete('/api/secure/admin/event/' . $events[0]->id, [
            'Authorization' => 'Bearer ' . $this->login(true)
        ]);

        $this->assertResponseOk();
    }

    function testEventDeleteNotFound()
    {
        $this->delete('/api/secure/admin/event/notFound', [
            'Authorization' => 'Bearer ' . $this->login(true)
        ]);

        $this->assertResponseStatus(404);
    }


    function testEditEvent()
    {
        $types = factory(App\Models\VolunteerType::class, 5)->create();
        $events = factory(App\Models\Event::class, 1)->create();

        foreach ($events as $event) {
            $event->volunteerTypes()->attach($types);
        }

        $this->put('/api/secure/admin/event/' . $events[0]->id, [
            "name" => "81. Púť radosti",
            'theme' => 'test',
            "startDate" => "2019-02-03",
            "endDate" => "2019-02-04",
            "startRegistration" => "2019-01-15",
            "endRegistration" => "2019-01-25",
            "endVolunteerRegistration" => "2019-01-20"
        ], [
            'Authorization' => 'Bearer ' . $this->login(true)
        ]);

        $this->assertResponseStatus(200);
        $event = DB::table('events')->where('id', $events[0]->id)->first();

        $this->assertEquals("81. Púť radosti", $event->name);
    }
}
