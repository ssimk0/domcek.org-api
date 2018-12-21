<?php


namespace App\Http\Controllers\Unsecure;


use App\Constants\ErrorMessagesConstant;
use App\Http\Controllers\Controller;
use App\Services\PageService;
use Laravel\Lumen\Http\Request;

class PageController extends Controller
{
    private $service;

    public function __construct(PageService $service)
    {
        $this->service = $service;
    }

    function menuPages() {
        return $this->service->getMenuPages();
    }

    function page($slug) {
        $page = $this->service->getPageBySlug($slug);
        if ($page) {
            return $this->jsonResponse($page);
        } else {
            return ErrorMessagesConstant::notFound();
        }
    }
}
