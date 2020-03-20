<?php

namespace App\Http\Controllers\Secure;

use App\Constants\ErrorMessagesConstant;
use App\Http\Controllers\Controller;
use App\Services\NewsService;
use Illuminate\Http\Request;

class NewsController extends Controller
{
    private $service;

    public function __construct(NewsService $service)
    {
        $this->service = $service;
    }

    public function create(Request $request)
    {
        $data = $this->validate($request, [
            'title' => 'required|string',
            'body' => 'required|string',
            'short' => 'required|string|max:300',
            'image' => ['required', 'regex:(https?:\/\/(?:www\.|(?!www))[a-zA-Z0-9][a-zA-Z0-9-]+[a-zA-Z0-9]\.[^\s]{2,}|www\.[a-zA-Z0-9][a-zA-Z0-9-]+[a-zA-Z0-9]\.[^\s]{2,}|https?:\/\/(?:www\.|(?!www))[a-zA-Z0-9]+\.[^\s]{2,}|www\.[a-zA-Z0-9]+\.[^\s]{2,})'],
            'status' => 'required|in:draft,archived,published',
            'category' => 'required|in:news,message',
            'is_featured' => 'required|boolean'
        ]);

        $result = $this->service->create($data);
        if ($result) {
            return $this->successResponse(201);
        }

        return ErrorMessagesConstant::badAttempt();
    }

    public function edit($slug, Request $request)
    {
        $data = $this->validate($request, [
            'title' => 'required|string',
            'body' => 'required|string',
            'short' => 'required|string|max:300',
            'image' => ['required', 'regex:(https?:\/\/(?:www\.|(?!www))[a-zA-Z0-9][a-zA-Z0-9-]+[a-zA-Z0-9]\.[^\s]{2,}|www\.[a-zA-Z0-9][a-zA-Z0-9-]+[a-zA-Z0-9]\.[^\s]{2,}|https?:\/\/(?:www\.|(?!www))[a-zA-Z0-9]+\.[^\s]{2,}|www\.[a-zA-Z0-9]+\.[^\s]{2,})'],
            'status' => 'required|in:draft,archived,published',
            'is_featured' => 'required|boolean'
        ]);

        $result = $this->service->edit($data, $slug);

        if ($result) {
            return $this-> successResponse();
        }

        return ErrorMessagesConstant::badAttempt();
    }

    public function listUnpublished(Request $request)
    {
        $data = $this->validate($request, [
            'order' => 'in:best,featured',
            'size' => 'integer'
        ]);

        return $this->service->unpublished(
            array_get($data, 'size', 5)
        );
    }

    public function unpublished($slug)
    {
        $news = $this->service->unpublishedDetail($slug);
        return $this->jsonResponse($news);
    }
}
