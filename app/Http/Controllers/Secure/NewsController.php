<?php
namespace App\Http\Controllers\Secure;


use App\Constants\ErrorMessagesConstant;
use App\Http\Controllers\Controller;
use App\Services\NewsService;
use  Illuminate\Http\Request;

class NewsController extends Controller
{
    private $service;

    public function __construct(NewsService $service)
    {
        $this->service = $service;
    }

    function create(Request $request)
    {
        $data = $this->validate($request, [
            'title' => 'required|string',
            'body' => 'required|string',
            'short' => 'required|string|max:200',
            'image' => 'required|url',
            'status' => 'required|in:draft,archived,published'
        ]);

        $result = $this->service->create($data);
        if ($result) {
            return $this->jsonResponse([
                'success' => true,
                'news' => $result
            ], 201);
        }

        return ErrorMessagesConstant::badAttempt();
    }

    function edit($slug, Request $request)
    {
        $data = $this->validate($request, [
            'title' => 'required|string',
            'body' => 'required|string',
            'short' => 'required|string|max:200',
            'image' => 'image',
            'status' => 'required|in:draft,archived,published'
        ]);

        $this->service->edit($data, $slug);
    }
}
