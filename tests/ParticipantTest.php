<?php

use App\Models\Event;
use App\Models\Participant;
use App\Models\Payment;
use App\Models\Profile;
use App\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
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


    function testRegisterParticipant()
    {

        $event = factory(App\Models\Event::class, 1)->create()[0];
        $profile = factory(App\Models\Profile::class)->create();
        $token = $this->login();

        $this->post('/api/secure/user/events/1', [
            'note' => 'test',
            'transportIn' => 'test',
            'transportOut' => 'test',
        ], [
            'Authorization' => 'Bearer ' . $token
        ]);

        $this->assertResponseStatus(201);

        $payment = \App\Models\Payment::where('user_id', $profile->user_id)
            ->where('event_id', $event->id)
            ->first();

        $this->assertEquals($event->need_pay, $payment->need_pay);
        $this->assertEquals('0', $payment->paid);
        $this->assertEquals('1', $payment->event_id);
    }

    function testLateRegisterParticipant()
    {

        $event = new Event([
            'name' => 'test',
            'start_date' => Carbon::now()->format('Y-m-d'),
            'end_date' => Carbon::now()->addDays(3)->format('Y-m-d'),
            'end_registration' => Carbon::now()->subDays(3)->format('Y-m-d'),
            'end_volunteer_registration' => Carbon::now()->subDays(14)->format('Y-m-d'),
            'start_registration' => Carbon::now()->subDays(30)->format('Y-m-d'),
            'need_pay' => 5,
            'deposit' => 0
        ]);
        $event->save();
        $profile = factory(App\Models\Profile::class)->create();
        $token = $this->login();

        $this->post('/api/secure/user/events/' . $event->id, [
            'note' => 'test',
            'transportIn' => 'test',
            'transportOut' => 'test',
        ], [
            'Authorization' => 'Bearer ' . $token
        ]);

        $this->assertResponseStatus(201);

        $payment = Payment::where('user_id', $profile->user_id)
            ->where('event_id', $event->id)
            ->first();
        // test fee
        $this->assertEquals($event->need_pay + 5, $payment->need_pay);
        $this->assertEquals('0', $payment->paid);
        $this->assertEquals('1', $payment->event_id);
    }

    function testUserEditParticipant()
    {
        $participant = factory(App\Models\Participant::class)->create();
        $user = User::find($participant->user_id);
        $token = Auth::login($user);

        $this->put('/api/secure/user/events/' . $participant->event_id, [
            'note' => 'test',
            'transportIn' => 'CAR',
            'transportOut' => 'CAR',
        ], [
            'Authorization' => 'Bearer ' . $token
        ]);

        $this->assertResponseOk();

        $participant = \App\Models\Participant::where('user_id', $participant->user_id)->first();

        $this->assertEquals('CAR', $participant->transport_in);
        $this->assertEquals('CAR', $participant->transport_out);
        $this->assertEquals('test', $participant->note);
    }

    function testUserUnsubscribe()
    {
        $participant = factory(App\Models\Participant::class)->create();
        $user = User::find($participant->user_id);
        $token = Auth::login($user);

        $this->put('/api/secure/user/events/' . $participant->event_id . '/unsubscribe', [
        ], [
            'Authorization' => 'Bearer ' . $token
        ]);

        $this->assertResponseOk();

        $participant = Participant::where('user_id', $participant->user_id)->first();

        $this->assertEquals('0', $participant->subscribed);
    }

    function testUserSubscribe()
    {
        $participant = factory(Participant::class)->create();
        $participant->update(['subscribed' => false]);
        $user = User::find($participant->user_id);
        $token = Auth::login($user);

        $this->assertEquals(false, $participant->subscribed);

        $this->put('/api/secure/user/events/' . $participant->event_id . '/subscribe', [
        ], [
            'Authorization' => 'Bearer ' . $token
        ]);

        $this->assertResponseOk();

        $participant = \App\Models\Participant::where('user_id', $participant->user_id)->first();

        $this->assertEquals(true, $participant->subscribed);
    }

    function testEditParticipant()
    {

        $event = factory(App\Models\Event::class)->create();
        $token = $this->login(true);
        $participant = factory(App\Models\Participant::class)->create([
            'user_id' => 1,
            'event_id' => $event->id,
        ]);
        $volunteerType = factory(App\Models\VolunteerType::class)->create();
        $regUser = factory(User::class)->create();


        $this->put('/api/secure/admin/events/' . $event->id . '/participants/' . $participant->id, [
            'registrationUserId' => $regUser->id,
            'userId' => 1,
            'volunteerTypeId' => $volunteerType->id,
            'isLeader' => true
        ], [
            'Authorization' => 'Bearer ' . $token
        ]);

        $this->assertResponseOk();

        $volunteer = \App\Models\Volunteer::where('user_id', 1)->first();

        $this->assertEquals(true, $volunteer->is_leader);
    }

}
