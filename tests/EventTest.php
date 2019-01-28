<?php


use App\Models\Event;
use Illuminate\Support\Facades\DB;
use Laravel\Lumen\Testing\DatabaseMigrations;

class EventTest extends TestCase
{
    use DatabaseMigrations;

    function testAuthEvent()
    {
        $this->post('/api/secure/admin/events', [], [
            'Authorization' => 'Bearer ' . $this->login()
        ]);

        $this->assertResponseStatus(403);
    }

    function testCreateEvent()
    {
        $this->post('/api/secure/admin/events', [
            "name" => "81. Púť radosti",
            "needPay" => 10,
            "deposit" => 5,
            "startDate" => "2019-02-03",
            "endDate" => "2019-02-04",
            "startRegistration" => "2019-01-15",
            "endRegistration" => "2019-01-25",
            "endVolunteerRegistration" => "2019-01-20",
            "volunteerTypes" => [1],
            "transportTimes" => ['10:00', '11:00']
        ], [
            'Authorization' => 'Bearer ' . $this->login(true)
        ]);

        $event = Event::where('name', "81. Púť radosti")->first();
        $times = DB::table('event_transport_times')->where('event_id', $event->id)->get();

        $this->assertResponseStatus(201);
        $this->assertCount(2, $times);

        $this->assertEquals('10:00', $times[0]->time);
    }

    function testEventList()
    {
        $types = factory(App\Models\VolunteerType::class, 5)->create();
        $events = factory(App\Models\Event::class, 11)->create();

        foreach ($events as $event) {
            $event->volunteerTypes()->attach($types);
        }

        $this->get('/api/secure/admin/events', [
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

        $this->get('/api/secure/admin/events/' . $events[0]->id, [
            'Authorization' => 'Bearer ' . $this->login(true)
        ]);

        $this->assertResponseOk();
        $content = json_decode($this->response->getContent());

        $this->assertEquals($events[0]->id, $content->id);
    }

    function testEventDetailNotFound()
    {

        $this->get('/api/secure/admin/events/notFound', [
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

        $this->delete('/api/secure/admin/events/' . $events[0]->id, [
            'Authorization' => 'Bearer ' . $this->login(true)
        ]);

        $this->assertResponseOk();
    }

    function testEventDeleteNotFound()
    {
        $this->delete('/api/secure/admin/events/notFound', [
            'Authorization' => 'Bearer ' . $this->login(true)
        ]);

        $this->assertResponseStatus(404);
    }


    function testEditEvent()
    {
        $types = factory(App\Models\VolunteerType::class, 5)->create();
        $events = factory(App\Models\Event::class, 1)->create();

        foreach ($events as $event) {
            $event->volunteerTypes()->attach([$types[1]]);
        }

        $this->put('/api/secure/admin/events/' . $events[0]->id, [
            "name" => "81. Púť radosti",
            'theme' => 'test',
            "startDate" => "2019-02-03",
            "endDate" => "2019-02-04",
            "startRegistration" => "2019-01-15",
            "endRegistration" => "2019-01-25",
            "endVolunteerRegistration" => "2019-01-20",
            "volunteerTypes" => [$types[0]->id]
        ], [
            'Authorization' => 'Bearer ' . $this->login(true)
        ]);

        $this->assertResponseStatus(200);
        $event = Event::find($events[0]->id);
        $volunteerTypes = $event->volunteerTypes()->get();

        $this->assertEquals("81. Púť radosti", $event->name);
        $this->assertNotEquals($types[1]->id, $volunteerTypes[0]->id);
        $this->assertCount(1, $volunteerTypes);
    }
}
