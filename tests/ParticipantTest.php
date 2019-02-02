<?php


use App\Models\Event;
use Laravel\Lumen\Testing\DatabaseMigrations;

class ParticipantTest extends TestCase
{
    use DatabaseMigrations;

    function testAuthEvent()
    {
        $this->get('/api/secure/admin/events/1/participants', [], [
            'Authorization' => 'Bearer ' . $this->login()
        ]);

        $this->assertResponseStatus(403);
    }

    function testListParticipant()
    {

        factory(App\Models\Participant::class, 15)->create();
        $this->get('/api/secure/admin/events/1/participants', [
            'Authorization' => 'Bearer ' . $this->login(true)
        ]);

        $this->assertResponseOk();

        $content = json_decode($this->response->getContent());

        $this->assertEquals(15, $content->total);
    }

    function testAdminDetailParticipant()
    {

        $participant = factory(App\Models\Participant::class, 1)->create()[0];
        $this->get('/api/secure/admin/events/1/participants/' . $participant->id, [
            'Authorization' => 'Bearer ' . $this->login(true)
        ]);

        $this->assertResponseOk();

        $content = json_decode($this->response->getContent());

        $this->assertEquals($participant->id, $content->id);
        $this->assertEquals($participant->note, $content->note);
    }

    function testDetailParticipant()
    {

        $participant = factory(App\Models\Participant::class, 1)->create()[0];
        $this->get('/api/secure/events/1/status', [
            'Authorization' => 'Bearer ' . $this->login()
        ]);

        $this->assertResponseOk();

        $content = json_decode($this->response->getContent());

        $this->assertEquals($participant->id, $content->id);
        $this->assertEquals($participant->note, $content->note);
    }

    function testRegisterParticipant()
    {

        $event = factory(App\Models\Event::class, 1)->create()[0];
        $this->post('/api/secure/events/1/register', [
            'note' => 'test',
            'transportIn' => 'test',
            'transportOut' => 'test',
        ], [
            'Authorization' => 'Bearer ' . $this->login()
        ]);

        $this->assertResponseStatus(201);

        $payment = \App\Models\Payment::where('user_id', 1)->first();

        $this->assertEquals($event->need_pay, $payment->need_pay);
        $this->assertEquals('0', $payment->paid);
        $this->assertEquals('1', $payment->event_id);
    }

    function testEditParticipant()
    {

        factory(App\Models\Event::class, 1)->create();
        factory(App\Models\Participant::class, 1)->create();
        factory(App\Models\VolunteerType::class, 1)->create();

        $this->put('/api/secure/admin/events/1/participants/1', [
            'registrationUserId' => 2,
            'userId' => 1,
            'volunteerTypeId' => 1,
            'isLeader' => true
        ], [
            'Authorization' => 'Bearer ' . $this->login(true)
        ]);

        $this->assertResponseOk();

        $volunteer = \App\Models\Volunteer::where('user_id', 1)->first();

        $this->assertEquals(true, $volunteer->is_leader);
    }

}
