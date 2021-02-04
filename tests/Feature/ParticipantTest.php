<?php

namespace Tests\Feature;
use App\Models\Event;
use App\Models\EventPrice;
use App\Models\Participant;
use App\Models\Payment;
use App\Models\Profile;
use App\Models\User;
use App\Models\Volunteer;
use App\Models\VolunteerType;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

class ParticipantTest extends TestCase
{

    public function testAuthEvent()
    {
        $this->get('/api/secure/admin/events/1/participants', [
            'Authorization' => 'Bearer '.$this->login(),
        ])->assertStatus(403);
    }

    public function testListParticipant()
    {
        $event = Event::factory()->createOne();
        Participant::factory( 15)->create(["event_id" => $event->id]);

        $response = $this->get("/api/secure/admin/events/$event->id/participants", [
            'Authorization' => 'Bearer '.$this->login(true),
        ])->assertStatus(200);

        $content = json_decode($response->getContent());

        $this->assertEquals(15, $content->total);
    }

    public function testAdminDetailParticipant()
    {
        $participant = Participant::factory()->createOne();
        $response = $this->get("/api/secure/admin/events/$participant->event_id/participants/".$participant->id, [
            'Authorization' => 'Bearer '.$this->login(true),
        ])->assertStatus(200);

        $content = json_decode($response->getContent());

        $this->assertEquals($participant->id, $content->id);
        $this->assertEquals($participant->note, $content->note);
    }

    public function testRegisterParticipant()
    {
        $event = Event::factory()->create();
        $price = new EventPrice([
            'event_id' => $event->id,
            'need_pay' => $this->faker->randomDigit,
            'deposit' => $this->faker->randomDigit,
        ]);

        $price->save();
        $token = $this->login();

        $this->post('/api/secure/user/events/1', [
            'note' => 'test',
            'transportIn' => 'test',
            'transportOut' => 'test',
            'priceId' => $price->id,
            'GDPRRegistration' => true,
            'audioVisualKnowledgeAgreement' => true,
        ], [
            'Authorization' => 'Bearer '.$token,
        ])->assertStatus(201);

        $payment = Payment::where('user_id', Auth::user()->id)
            ->where('event_id', $event->id)
            ->first();

        $this->assertEquals($price->need_pay, $payment->need_pay);
        $this->assertEquals($price->id, $payment->event_price_id);
        $this->assertEquals('0', $payment->paid);
        $this->assertEquals('1', $payment->event_id);
    }

    public function testLateRegisterParticipant()
    {
        $event = new Event([
            'name' => 'test',
            'start_date' => Carbon::now()->addDays(1)->format('Y-m-d'),
            'end_date' => Carbon::now()->addDays(4)->format('Y-m-d'),
            'end_registration' => Carbon::now()->subDays(3)->format('Y-m-d'),
            'end_volunteer_registration' => Carbon::now()->subDays(14)->format('Y-m-d'),
            'start_registration' => Carbon::now()->subDays(30)->format('Y-m-d'),
        ]);
        $event->save();

        $price = new EventPrice([
            'event_id' => $event->id,
            'need_pay' => 5,
            'deposit' => 0,
        ]);
        $price->save();
        $token = $this->login();

        $this->post('/api/secure/user/events/'.$event->id, [
            'note' => 'test',
            'transportIn' => 'test',
            'transportOut' => 'test',
            'priceId' => $price->id,
            'GDPRRegistration' => 'true',
            'audioVisualKnowledgeAgreement' => 'true',
        ], [
            'Authorization' => 'Bearer '.$token,
        ])->assertStatus(201);

        $payment = Payment::where('user_id', Auth::user()->id)
            ->where('event_id', $event->id)
            ->first();
        // test fee
        $this->assertEquals($price->need_pay + 5, $payment->need_pay);
        $this->assertEquals('0', $payment->paid);
        $this->assertEquals('1', $payment->event_id);
    }

    public function testInStartDateRegisterParticipant()
    {
        $event = new Event([
            'name' => 'test',
            'start_date' => Carbon::now()->format('Y-m-d'),
            'end_date' => Carbon::now()->addDays(4)->format('Y-m-d'),
            'end_registration' => Carbon::now()->subDays(3)->format('Y-m-d'),
            'end_volunteer_registration' => Carbon::now()->subDays(14)->format('Y-m-d'),
            'start_registration' => Carbon::now()->subDays(30)->format('Y-m-d'),
        ]);
        $event->save();

        $price = new EventPrice([
            'event_id' => $event->id,
            'need_pay' => 5,
            'deposit' => 0,
        ]);
        $price->save();
        $token = $this->login();

        $this->post('/api/secure/user/events/'.$event->id, [
            'note' => 'test',
            'transportIn' => 'test',
            'transportOut' => 'test',
            'priceId' => $price->id,
            'GDPRRegistration' => true,
            'audioVisualKnowledgeAgreement' => true,
        ], [
            'Authorization' => 'Bearer '.$token,
        ])->assertStatus(400);
    }

    public function testUserEditParticipant()
    {
        $participant = Participant::factory()->create();
        $user = User::find($participant->user_id);
        $token = Auth::login($user);

        $this->put('/api/secure/user/events/'.$participant->event_id, [
            'note' => 'test',
            'transportIn' => 'CAR',
            'transportOut' => 'CAR',
        ], [
            'Authorization' => 'Bearer '.$token,
        ])->assertStatus(200);

        $participant = \App\Models\Participant::where('user_id', $participant->user_id)->first();

        $this->assertEquals('CAR', $participant->transport_in);
        $this->assertEquals('CAR', $participant->transport_out);
        $this->assertEquals('test', $participant->note);
    }

    public function testUserUnsubscribe()
    {
        $participant = Participant::factory()->create();
        $user = User::find($participant->user_id);
        $token = Auth::login($user);

        $this->put('/api/secure/user/events/'.$participant->event_id.'/unsubscribe', [
        ], [
            'Authorization' => 'Bearer '.$token,
        ])->assertStatus(200);

        $participant = Participant::where('user_id', $participant->user_id)->first();

        $this->assertEquals('0', $participant->subscribed);
    }

    public function testUserSubscribe()
    {
        $participant = Participant::factory()->createOne(['subscribed' => false]);
        $user = User::find($participant->user_id);
        $token = Auth::login($user);

        $this->assertEquals(false, $participant->subscribed);

        $this->put('/api/secure/user/events/'.$participant->event_id.'/subscribe', [
        ], [
            'Authorization' => 'Bearer '.$token,
        ])->assertStatus(200);

        $participant = Participant::where('user_id', $participant->user_id)->first();

        $this->assertEquals(true, $participant->subscribed);
    }

    public function testEditParticipant()
    {
        $event = Event::factory()->create();
        $token = $this->login(true);
        $participant = Participant::factory()->create([
            'user_id' => 1,
            'event_id' => $event->id,
        ]);
        $volunteerType = VolunteerType::factory()->create();
        $regUser = User::factory()->create();

        $this->put('/api/secure/admin/events/'.$event->id.'/participants/'.$participant->id, [
            'registrationUserId' => $regUser->id,
            'userId' => 1,
            'volunteerTypeId' => $volunteerType->id,
            'isLeader' => true,
        ], [
            'Authorization' => 'Bearer '.$token,
        ])->assertStatus(200);

        $volunteer = Volunteer::where('user_id', 1)->first();

        $this->assertEquals(true, $volunteer->is_leader);
    }
}
