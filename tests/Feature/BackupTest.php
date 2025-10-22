<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class BackupTest extends TestCase
{
    public function testBackupUploadWithParticipantsData()
    {
        Storage::fake('local');

        $participants = [
            ['id' => 1, 'name' => 'John Doe', 'email' => 'john@example.com'],
            ['id' => 2, 'name' => 'Jane Smith', 'email' => 'jane@example.com'],
        ];

        $response = $this->post('/api/registration/backup', [
            'participants' => $participants,
        ], [
            'X-API-TOKEN' => env('REGISTRATION_TOKEN', 'test-token'),
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

        $wrongPayments = [
            ['payment_number' => '12345', 'amount' => 50, 'reason' => 'Invalid number'],
            ['payment_number' => '67890', 'amount' => 100, 'reason' => 'Expired'],
        ];

        $response = $this->post('/api/registration/backup', [
            'wrong-payments' => $wrongPayments,
        ], [
            'X-API-TOKEN' => env('REGISTRATION_TOKEN', 'test-token'),
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
            'X-API-TOKEN' => env('REGISTRATION_TOKEN', 'test-token'),
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

        $response = $this->post('/api/registration/backup', [], [
            'X-API-TOKEN' => env('REGISTRATION_TOKEN', 'test-token'),
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

        $response = $this->post('/api/registration/backup', [
            'participants' => 'not an array',
        ], [
            'X-API-TOKEN' => env('REGISTRATION_TOKEN', 'test-token'),
        ]);

        $response->assertStatus(422);
    }

    public function testBackupUploadValidatesWrongPaymentsAsArray()
    {
        Storage::fake('local');

        $response = $this->post('/api/registration/backup', [
            'wrong-payments' => 'not an array',
        ], [
            'X-API-TOKEN' => env('REGISTRATION_TOKEN', 'test-token'),
        ]);

        $response->assertStatus(422);
    }
}
