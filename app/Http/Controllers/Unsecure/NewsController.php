<?php

namespace App\Http\Controllers\Unsecure;

use App\Constants\ErrorMessagesConstant;
use App\Http\Controllers\Controller;
use App\Services\NewsService;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;

class NewsController extends Controller
{
    private $service;

    public function __construct(NewsService $service)
    {
        $this->service = $service;
    }

    public function list(Request $request)
    {
        $data = $request->validate([
            'order' => 'in:best,featured',
            'size' => 'nullable|integer',
            'offset' => 'nullable|integer',
            'category' => 'nullable|string',
        ]);

        $news = $this->service->newsList(
            Arr::get($data, 'order'),
            Arr::get($data, 'size', 3),
            Arr::get($data, 'offset', 0),
            Arr::get($data, 'category', 'news')
        );

        return $this->jsonResponse($news);
    }

    public function news($slug)
    {
        $newsDetail = $this->service->newsBySlug($slug);

        if ($newsDetail) {
            return $this->jsonResponse($newsDetail);
        } else {
            return ErrorMessagesConstant::notFound();
        }
    }
}
