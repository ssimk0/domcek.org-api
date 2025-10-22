<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\Payment;
use App\Models\Participant;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PaymentTest extends TestCase
{
    public function testUploadTransferLogRequiresAuth()
    {
        $event = Event::factory()->createOne();

        $this->post("/api/secure/admin/events/{$event->id}/payments")
            ->assertStatus(401);
    }

    public function testUploadTransferLogRequiresAdmin()
    {
        $event = Event::factory()->createOne();
        $token = $this->login(false); // non-admin user

        $this->post("/api/secure/admin/events/{$event->id}/payments", [], [
            'Authorization' => 'Bearer ' . $token,
        ])->assertStatus(403);
    }

    public function testUploadTransferLogWithValidFile()
    {
        Storage::fake('local');

        $event = Event::factory()->createOne();

        // Create a participant with payment
        $participant = Participant::factory()->createOne(['event_id' => $event->id]);
        $payment = Payment::factory()->createOne([
            'event_id' => $event->id,
            'user_id' => $participant->user_id,
            'payment_number' => '12345',
            'paid' => 0,
        ]);

        // Create a mock transfer log file with proper format
        $fileContent = "header1\nheader2\nheader3\nheader4\n";
        $fileContent .= "2024-01-01|100|50|test|Kredit|test|test|test|SK123456|12345|test|test|test|Payment note|\n";

        $file = UploadedFile::fake()->createWithContent('transfer.log', $fileContent);

        $response = $this->post(
            "/api/secure/admin/events/{$event->id}/payments",
            ['file' => $file],
            $this->getAuthHeader()
        );

        $response->assertStatus(200);
    }

    public function testUploadTransferLogRequiresFile()
    {
        $event = Event::factory()->createOne();

        $response = $this->post(
            "/api/secure/admin/events/{$event->id}/payments",
            [],
            $this->getAuthHeader()
        );

        $response->assertStatus(422);
    }

    public function testPaymentProcessingMatchesCorrectParticipant()
    {
        Storage::fake('local');

        $event = Event::factory()->createOne();

        // Create multiple participants with different payment numbers
        $participant1 = Participant::factory()->createOne(['event_id' => $event->id]);
        $payment1 = Payment::factory()->createOne([
            'event_id' => $event->id,
            'user_id' => $participant1->user_id,
            'payment_number' => '12345',
            'paid' => 0,
        ]);

        $participant2 = Participant::factory()->createOne(['event_id' => $event->id]);
        $payment2 = Payment::factory()->createOne([
            'event_id' => $event->id,
            'user_id' => $participant2->user_id,
            'payment_number' => '67890',
            'paid' => 0,
        ]);

        // File contains payment for participant1 only
        $fileContent = "header1\nheader2\nheader3\nheader4\n";
        $fileContent .= "2024-01-01|100|50|test|Kredit|test|test|test|SK123456|12345|test|test|test|Payment for participant1|\n";

        $file = UploadedFile::fake()->createWithContent('transfer.log', $fileContent);

        $this->post(
            "/api/secure/admin/events/{$event->id}/payments",
            ['file' => $file],
            $this->getAuthHeader()
        );

        // Verify only payment1 was updated
        $payment1->refresh();
        $payment2->refresh();

        $this->assertEquals(50, $payment1->paid);
        $this->assertEquals(0, $payment2->paid);
    }
}
