<?php

namespace App\Http\Controllers\Secure;

use App\Http\Controllers\Controller;
use App\Models\Page;
use Illuminate\Http\Request;

class PageController extends Controller {

    public function create(Request $request)
    {
        $data = $request->validate([
            'title' => 'required|string',
            'body' => 'required|string',
            'order' => 'required|integer',
            'parent_slug' => 'required|string',
            'active' => 'required|boolean',
        ]);
        $parent = Page::query()->firstWhere('slug', $data['parent_slug']);
        Page::query()->create([
            'parent_id' => $parent->id,
            'title' => $data['title'],
            'body' => $data['body'],
            'order' => $data['order'],
            'active' => $data['active'],
        ]);

        return $this->successResponse(201);
    }

    public function edit(Page $page, Request $request)
    {
        $data = $request->validate([
            'title' => 'required|string',
            'body' => 'required|string',
            'order' => 'required|integer',
            'parent_id' => 'required|integer',
            'active' => 'required|boolean',
        ]);

        $page->update($data);

        return $this->successResponse();
    }

    public function detail(Page $page)
    {

        return $this->jsonResponse($page);
    }
}
