<?php

namespace Tests\Feature;

use App\Models\Event;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Tests\TestCase;

class BackupTest extends TestCase
{
    protected function createRegistrationToken(): string
    {
        $event = Event::factory()->createOne();
        $token = Str::random(32);
        DB::table('auth_tokens')->insert([
            'token' => $token,
            'type' => 'registration',
            'valid_until' => Carbon::now()->addDays(1),
            'event_id' => $event->id,
        ]);
        return $token;
    }

    public function testBackupUploadWithParticipantsData()
    {
        Storage::fake('local');
        $token = $this->createRegistrationToken();

        $participants = [
            ['id' => 1, 'name' => 'John Doe', 'email' => 'john@example.com'],
            ['id' => 2, 'name' => 'Jane Smith', 'email' => 'jane@example.com'],
        ];

        $response = $this->post('/api/registration/backup', [
            'participants' => $participants,
        ], [
            'X-API-TOKEN' => $token,
        ]);

        $response->assertStatus(201)
            ->assertJson(['success' => true]);

        // Verify file was created
        $files = Storage::disk('local')->files('backup');
        $this->assertCount(1, $files);

        // Verify file contents
        $content = Storage::disk('local')->get($files[0]);
        $data = json_decode($content, true);

        $this->assertEquals($participants, $data['participants']);
    }

    public function testBackupUploadWithWrongPayments()
    {
        Storage::fake('local');
        $token = $this->createRegistrationToken();

        $wrongPayments = [
            ['payment_number' => '12345', 'amount' => 50, 'reason' => 'Invalid number'],
            ['payment_number' => '67890', 'amount' => 100, 'reason' => 'Expired'],
        ];

        $response = $this->post('/api/registration/backup', [
            'wrong-payments' => $wrongPayments,
        ], [
            'X-API-TOKEN' => $token,
        ]);

        $response->assertStatus(201)
            ->assertJson(['success' => true]);

        // Verify file was created
        $files = Storage::disk('local')->files('backup');
        $this->assertCount(1, $files);

        // Verify file contents
        $content = Storage::disk('local')->get($files[0]);
        $data = json_decode($content, true);

        $this->assertEquals($wrongPayments, $data['wrong-payments']);
    }

    public function testBackupUploadWithBothData()
    {
        Storage::fake('local');
        $token = $this->createRegistrationToken();

        $participants = [
            ['id' => 1, 'name' => 'John Doe'],
        ];

        $wrongPayments = [
            ['payment_number' => '12345', 'amount' => 50],
        ];

        $response = $this->post('/api/registration/backup', [
            'participants' => $participants,
            'wrong-payments' => $wrongPayments,
        ], [
            'X-API-TOKEN' => $token,
        ]);

        $response->assertStatus(201)
            ->assertJson(['success' => true]);

        // Verify file was created
        $files = Storage::disk('local')->files('backup');
        $this->assertCount(1, $files);

        // Verify file contents
        $content = Storage::disk('local')->get($files[0]);
        $data = json_decode($content, true);

        $this->assertArrayHasKey('participants', $data);
        $this->assertArrayHasKey('wrong-payments', $data);
    }

    public function testBackupUploadWithEmptyData()
    {
        Storage::fake('local');
        $token = $this->createRegistrationToken();

        $response = $this->post('/api/registration/backup', [], [
            'X-API-TOKEN' => $token,
        ]);

        $response->assertStatus(201)
            ->assertJson(['success' => true]);

        // Verify file was created even with empty data
        $files = Storage::disk('local')->files('backup');
        $this->assertCount(1, $files);
    }

    public function testBackupUploadValidatesParticipantsAsArray()
    {
        Storage::fake('local');
        $token = $this->createRegistrationToken();

        $response = $this->post('/api/registration/backup', [
            'participants' => 'not an array',
        ], [
            'X-API-TOKEN' => $token,
        ]);

        $response->assertStatus(422);
    }

    public function testBackupUploadValidatesWrongPaymentsAsArray()
    {
        Storage::fake('local');
        $token = $this->createRegistrationToken();

        $response = $this->post('/api/registration/backup', [
            'wrong-payments' => 'not an array',
        ], [
            'X-API-TOKEN' => $token,
        ]);

        $response->assertStatus(422);
    }
}
