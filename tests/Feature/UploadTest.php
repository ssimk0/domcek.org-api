<?php


use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class UploadTest extends TestCase {

    public function test_upload()
    {
        Storage::fake('avatars');

        $file = UploadedFile::fake()->image('avatar.jpg');

        $response = $this->post('/api/media/upload', [
            'file' => $file
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure(["url", "url_small"]);

    }
}
