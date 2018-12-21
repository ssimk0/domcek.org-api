<?php


namespace App\Http\Controllers\Secure;


use App\Http\Controllers\Controller;
use App\Services\NewsService;
use Laravel\Lumen\Http\Request;

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
            'image' => 'required|image',
            'status' => 'required|in:draft,archived,published'
        ]);

        $image = request()->file( 'image' );
        $path = $image->store( 'news', 's3' );
        $data['image'] = $path;

        $this->service->create($data);
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

        if (array_get($data, 'image', false)) {
            $image = request()->file('image');
            $path = $image->store('news', 's3');
            $data['image'] = $path;
        }

        $this->service->edit($data, $slug);
    }
}
