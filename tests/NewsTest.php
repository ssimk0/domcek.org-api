<?php

use Illuminate\Support\Facades\Log;
use Laravel\Lumen\Testing\DatabaseMigrations;

class NewsTest extends TestCase
{
    use DatabaseMigrations;

    /**
     * A basic test example.
     *
     * @return void
     */
    public function testNews()
    {
        factory(App\Models\NewsItem::class, 5)->create();

        $this->get('/api/news');
        $response = json_decode($this->response->getContent());
        $this->assertEquals(3, count($response->data));
        $this->assertEquals(3, $response->per_page);
        $this->assertEquals(5, $response->total);
        $this->assertEquals(1, $response->current_page);
    }

    public function testNewsDetail()
    {
        $s = factory(App\Models\NewsItem::class, 1)->create()[0];

        $this->get('/api/news/'.$s->slug);
        $response = json_decode($this->response->getContent());

        $this->assertResponseOk();
        $this->assertEquals($s->title, $response->title);
    }

    public function testUpdateNews()
    {
        $token = $this->login(false, true);

        $s = factory(App\Models\NewsItem::class, 1)->create()[0];

        $this->put('/api/secure/news/'.$s->slug, [
            'body' => $s->body,
            'title' => 'Test',
            'image' => $s->image,
            'short' => $s->short,
            'status' => $s->status,
            'is_featured' => $s->is_featured,
        ], [
            'Authorization' => 'Bearer '.$token,
        ]);

        $this->assertResponseOk();
    }
}
