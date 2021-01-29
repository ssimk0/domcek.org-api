<?php

use App\Models\Event;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Laravel\Lumen\Testing\DatabaseMigrations;

class EventTest extends TestCase
{
    use DatabaseMigrations;

    public function testAuthEvent()
    {
        $this->post('/api/secure/admin/events', [], [
            'Authorization' => 'Bearer '.$this->login(),
        ]);

        $this->assertResponseStatus(403);
    }

    public function testCreateEvent()
    {
        $types = factory(App\Models\VolunteerType::class, 5)->create();

        $this->post('/api/secure/admin/events', [
            'name' => '81. Púť radosti',
            'prices' => [[
            'needPay' => 10,
            'deposit' => 5,
            ]],
            'type' => 'pz',
            'startDate' => '2019-02-03',
            'endDate' => '2019-02-04',
            'startRegistration' => '2019-01-15',
            'endRegistration' => '2019-01-25',
            'endVolunteerRegistration' => '2019-01-20',
            'volunteerTypes' => [$types[0]->id],
            'transportTimesIn' => ['10:00', '11:00'],
            'transportTimesOut' => ['10:01', '11:01'],
        ], [
            'Authorization' => 'Bearer '.$this->login(true),
        ]);

        $event = Event::where('name', '81. Púť radosti')->first();
        $times = DB::table('event_transport_times')
            ->where('event_id', $event->id)
            ->where('type', 'in')
            ->get();

        $this->assertResponseStatus(201);
        $this->assertCount(2, $times);

        $this->assertEquals('10:00', $times[0]->time);
        $this->assertEquals('pz', $event->type);
    }

    public function testEventList()
    {
        $types = factory(App\Models\VolunteerType::class, 5)->create();
        $events = factory(App\Models\Event::class, 11)->create();

        foreach ($events as $event) {
            $event->volunteerTypes()->attach($types);
        }

        $this->get('/api/secure/admin/events', [
            'Authorization' => 'Bearer '.$this->login(true),
        ]);

        $this->assertResponseOk();
        $content = json_decode($this->response->getContent());

        $this->assertCount(10, $content->data);
        $this->assertCount(5, $content->data[0]->volunteerTypes);
    }

    public function testEventDetail()
    {
//        $this->markTestSkipped('must be fixed problem with YEAR in sqlite.');

        $types = factory(App\Models\VolunteerType::class, 5)->create();
        $events = factory(App\Models\Event::class, 11)->create();

        foreach ($events as $event) {
            $event->volunteerTypes()->attach($types);
        }

        $this->get('/api/secure/admin/events/'.$events[0]->id, [
            'Authorization' => 'Bearer '.$this->login(true),
        ]);

        $this->assertResponseOk();
        $content = json_decode($this->response->getContent());

        $this->assertEquals($events[0]->id, $content->id);
    }

    public function testEventDetailNotFound()
    {
//        $this->markTestSkipped('must be fixed problem with YEAR in sqlite.');

        $this->get('/api/secure/admin/events/notFound', [
            'Authorization' => 'Bearer '.$this->login(true),
        ]);

        $this->assertResponseStatus(404);
    }

    public function testEventDelete()
    {
        $types = factory(App\Models\VolunteerType::class, 5)->create();
        $events = factory(App\Models\Event::class, 1)->create();

        foreach ($events as $event) {
            $event->volunteerTypes()->attach($types);
        }

        $this->delete('/api/secure/admin/events/'.$events[0]->id, [
            'Authorization' => 'Bearer '.$this->login(true),
        ]);

        $this->assertResponseOk();
    }

    public function testEventDeleteNotFound()
    {
        $this->delete('/api/secure/admin/events/notFound', [
            'Authorization' => 'Bearer '.$this->login(true),
        ]);

        $this->assertResponseStatus(404);
    }

    public function testEditEvent()
    {
        $types = factory(App\Models\VolunteerType::class, 5)->create();
        $events = factory(App\Models\Event::class, 1)->create();

        foreach ($events as $event) {
            $event->volunteerTypes()->attach($types);
        }

        $this->put('/api/secure/admin/events/'.$events[0]->id, [
            'name' => '81. Púť radosti',
            'theme' => 'test',
            'startDate' => '2019-02-03',
            'endDate' => '2019-02-04',
            'startRegistration' => '2019-01-15',
            'endRegistration' => '2019-01-25',
            'endVolunteerRegistration' => '2019-01-20',
            'volunteerTypes' => [$types[0]->id],
        ], [
            'Authorization' => 'Bearer '.$this->login(true),
        ]);

        $this->assertResponseStatus(200);
        $event = Event::find($events[0]->id);
        $volunteerTypes = $event->volunteerTypes()->get();

        $this->assertEquals('81. Púť radosti', $event->name);
        $this->assertNotEquals($types[1]->id, $volunteerTypes[0]->id);
        $this->assertCount(1, $volunteerTypes);
    }

    public function testAvailableEvents()
    {
        $faker = \Faker\Factory::create();

        $types = factory(App\Models\VolunteerType::class, 5)->create();

        $eventAvailable = new App\Models\Event([
            'name' => $faker->sentence(),
            'theme' => $faker->sentence(),
            'start_date' => Carbon::now()->addYear()->format('Y-m-d'),
            'end_date' => $faker->date(),
            'start_registration' => Carbon::now()->format('Y-m-d'),
            'end_registration' => Carbon::now()->addDays(5)->format('Y-m-d'),
            'end_volunteer_registration' => $faker->date(),
        ]);

        $eventNotAvailable = new App\Models\Event([
            'name' => $faker->sentence,
            'theme' => $faker->sentence,
            'start_date' => \Carbon\Carbon::now()->addYear()->format('Y-m-d'),
            'end_date' => $faker->date(),
            'start_registration' => \Carbon\Carbon::now()->addDay()->format('Y-m-d'),
            'end_registration' => \Carbon\Carbon::now()->addDays(5)->format('Y-m-d'),
            'end_volunteer_registration' => $faker->date(),
        ]);

        $eventNotAvailable->save();
        $eventAvailable->save();

        $participant = new App\Models\Participant([
            'note' => $faker->sentence,
            'event_id' => $eventAvailable->id,
            'user_id' => 1,
        ]);

        $participant->save();

        foreach ([$eventAvailable, $eventNotAvailable] as $event) {
            $event->volunteerTypes()->attach($types);
        }

        $this->get('/api/events', [
            'Authorization' => 'Bearer '.$this->login(true),
        ]);

        $this->assertResponseStatus(200);

        $content = json_decode($this->response->getContent());

        $this->assertCount(1, $content);
        $this->assertEquals(1, $content[0]->participantCount);
    }
}
