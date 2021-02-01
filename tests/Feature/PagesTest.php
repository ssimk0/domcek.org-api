<?php

namespace Tests\Feature;

use App\Models\Page;
use Tests\TestCase;

class PagesTest extends TestCase {


    public function testPages()
    {
        $pages = Page::factory(5)->create();
        Page::factory(2)->create(['parent_id' => $pages[0]->id]);

        $response = $this->getJson('/api/pages');
        $response->assertJsonCount(5);
    }

    public function testPageDetail()
    {
        $page = Page::factory()->createOne();
        Page::factory(2)->create(['parent_id' => $page->id]);

        $response = $this->getJson('/api/pages/' . $page->slug);
        $response
            ->assertJsonCount(2, 'children')
            ->assertJsonPath('title', $page->title);
    }


    public function testPageDetailNotFound()
    {

       $this->getJson('/api/pages/not-exists')->assertStatus(404);
    }

    public function testPageDetailByChildSlug()
    {
        $page = Page::factory()->createOne();
        $children = Page::factory(2)->create(['parent_id' => $page->id]);

        $response = $this->getJson('/api/pages/' . $children[0]->slug);
        $response
            ->assertJsonCount(2, 'children')
            ->assertJsonPath('title', $page->title);
    }

    public function testSecurePageDetail()
    {
        $token = $this->login(false, true);
        $page = Page::factory()->createOne();

        $response = $this->getJson('/api/secure/pages/' . $page->slug, [
            'Authorization' => 'Bearer ' . $token,
        ]);
        $response
            ->assertJsonPath('title', $page->title);
    }

    public function testEditPage()
    {
        $token = $this->login(false, true);

        $s = Page::factory(1)->createOne();
        $parent = Page::factory()->createOne();


        $response = $this->putJson('/api/secure/pages/' . $s->slug, [
            'body' => $s->body,
            'title' => 'Test',
            'parent_id' => $parent->id,
            'active' => true,
            'order' => 1
        ], [
            'Authorization' => 'Bearer ' . $token,
        ]);

        $response->assertStatus(200);
    }

    public function testStorePage()
    {
        $token = $this->login(false, true);

        $s = Page::factory(1)->makeOne();
        $parent = Page::factory()->createOne();


        $response = $this->postJson('/api/secure/pages', [
            'body' => $s->body,
            'title' => 'Test',
            'parent_slug' => $parent->slug,
            'active' => true,
            'order' => 1
        ], [
            'Authorization' => 'Bearer ' . $token,
        ]);

        $response->assertStatus(201);
    }
}
