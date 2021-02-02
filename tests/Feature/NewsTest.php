<?php

namespace Tests\Feature;

use App\Models\NewsItem;
use Tests\TestCase;

class NewsTest extends TestCase {

    public function testNews()
    {
        NewsItem::factory(5)->create();

        $response = $this->getJson('/api/news');
        $response->assertJsonCount(3, 'data')
            ->assertJsonPath('per_page', 3)
            ->assertJsonPath('total', 5)
            ->assertJsonPath('current_page', 1);
    }

    public function testNewsDetail()
    {
        $s = NewsItem::factory(1)->createOne();

        $response = $this->get('/api/news/' . $s->slug)->assertStatus(200);
        $response->assertJsonPath('title', $s->title);
    }

    public function testNewsDetailNotFound()
    {

        $this->get('/api/news/not-found')->assertStatus(404);
    }

    public function testNewsUnpublished()
    {
        $token = $this->login(false, true);
        NewsItem::factory(3)->create(["status" => 'draft']);
        NewsItem::factory(5)->create();

        $response = $this->get('/api/secure/news', [
            'Authorization' => 'Bearer ' . $token,
        ]);
        $response->assertJsonCount(3, 'data')
            ->assertJsonPath('per_page', 5)
            ->assertJsonPath('total', 3)
            ->assertJsonPath('current_page', 1);
    }

    public function testNewsUnpublishedDetail()
    {
        $token = $this->login(false, true);
        $s = NewsItem::factory(1)->createOne(["status" => 'draft']);

        $response = $this->get('/api/news/' . $s->slug, [
            'Authorization' => 'Bearer ' . $token,
        ])->assertStatus(200);

        $response->assertJsonPath('title', $s->title);
    }


    public function testUpdateNews()
    {
        $token = $this->login(false, true);

        $s = NewsItem::factory(1)->create()[0];

        $this->put('/api/secure/news/' . $s->slug, [
            'body' => $s->body,
            'title' => 'Test',
            'image' => $s->image,
            'short' => $s->short,
            'status' => $s->status,
            'is_featured' => $s->is_featured,
        ], [
            'Authorization' => 'Bearer ' . $token,
        ])->assertStatus(200);
    }

    public function testStoreNews()
    {
        $token = $this->login(false, true);

        $s = NewsItem::factory(1)->makeOne();


        $this->postJson('/api/secure/news', [
            'body' => $s->body,
            'title' => 'Test',
            'image' => $s->image,
            'short' => $s->short,
            'status' => $s->status,
            'is_featured' => $s->is_featured,
            'category' => 'news'
        ], [
            'Authorization' => 'Bearer ' . $token,
        ])->assertStatus(201);
    }
}
