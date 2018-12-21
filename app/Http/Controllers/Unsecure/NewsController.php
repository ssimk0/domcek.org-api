<?php
namespace App\Http\Controllers\Unsecure;



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

    function list(Request $request) {
        $data = $this->validate($request, [
            'order' => 'in:best,featured',
            'size' => 'integer',
            'offset' => 'integer'
        ]);

        $news = $this->service->getNewsList(
            array_get($data, 'order'),
            array_get($data, 'size', 3),
            array_get($data, 'offset', 0)
        );

        return $this->jsonResponse($news);
    }

    function news($slug) {
        $newsDetail = $this->service->getNewsBySlug($slug);
        if ($newsDetail) {
            return $this->jsonResponse($newsDetail);
        } else {
            return ErrorMessagesConstant::notFound();
        }
    }
}
