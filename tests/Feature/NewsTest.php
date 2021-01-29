<?php

namespace Tests\Feature;

use App\Models\NewsItem;
use Tests\TestCase;

class NewsTest extends TestCase
{

    /**
     * A basic test example.
     *
     * @return void
     */
    public function testNews()
    {
        NewsItem::factory(5)->create();

        $response = $this->get('/api/news');
        $response = json_decode($response->getContent());
        $this->assertEquals(3, count($response->data));
        $this->assertEquals(3, $response->per_page);
        $this->assertEquals(5, $response->total);
        $this->assertEquals(1, $response->current_page);
    }

    public function testNewsDetail()
    {
        $s = NewsItem::factory(1)->create()[0];

        $response = $this->get('/api/news/'.$s->slug)->assertStatus(200);
        $response = json_decode($response->getContent());

        $this->assertEquals($s->title, $response->title);
    }

    public function testUpdateNews()
    {
        $token = $this->login(false, true);

        $s = NewsItem::factory(1)->create()[0];

        $this->put('/api/secure/news/'.$s->slug, [
            'body' => $s->body,
            'title' => 'Test',
            'image' => $s->image,
            'short' => $s->short,
            'status' => $s->status,
            'is_featured' => $s->is_featured,
        ], [
            'Authorization' => 'Bearer '.$token,
        ])->assertStatus(200);
    }
}
