<?php

namespace Tests\Feature;

use App\Models\NewsItem;
use App\Models\SliderImage;
use Tests\TestCase;

class SliderImagesTest extends TestCase
{


    public function testSliderImages()
    {
        SliderImage::factory(5)->create();
        SliderImage::factory(5)->create(['active' => 0]);

        $response = $this->getJson('/api/slider-images');
        $response->assertJsonCount(5);
    }

    public function testAllSliderImages()
    {
        $token = $this->login(false, true);
        SliderImage::factory(5)->create();
        SliderImage::factory(5)->create(['active' => 0]);

        $response = $this->getJson('/api/secure/slider-images',  [
            'Authorization' => 'Bearer '.$token,
        ]);
        $response->assertJsonCount(10);
    }


    public function testEditNews()
    {
        $token = $this->login(false, true);

        $s = SliderImage::factory(1)->createOne();


        $response = $this->putJson('/api/secure/slider-images/'.$s->id, [
            'text' => 'new text',
            'title' => 'Test',
            'image' => $s->image,
            'order' => $s->order,
            'active' => true
        ], [
            'Authorization' => 'Bearer '.$token,
        ]);

        $response->assertStatus(200);
        $a = SliderImage::find($s->id);

        $this->assertEquals($a->text, 'new text');
        $this->assertEquals($a->title, 'Test');
    }

    public function testDeleteNews()
    {
        $token = $this->login(false, true);

        $s = SliderImage::factory(1)->createOne();


        $response = $this->deleteJson('/api/secure/slider-images/'.$s->id, [], [
            'Authorization' => 'Bearer '.$token,
        ]);

        $response->assertStatus(200);
        $a = SliderImage::find($s->id);

        $this->assertEquals($a, null);
    }

    public function testStoreNews()
    {
        $token = $this->login(false, true);

        $s = SliderImage::factory(1)->makeOne();


        $response = $this->postJson('/api/secure/slider-images', [
            'text' => $s->text,
            'title' => 'Test',
            'image' => $s->image,
            'order' => $s->order,
            'active' => true
        ], [
            'Authorization' => 'Bearer '.$token,
        ]);

        $response->assertStatus(201);
        $a = SliderImage::first();

        $this->assertEquals($a->text, $s->text);
    }
}
