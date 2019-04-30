<?php


namespace App\Http\Controllers\Secure;


use App\Constants\ErrorMessagesConstant;
use App\Http\Controllers\Controller;
use App\Services\PageService;
use Illuminate\Http\Request;

class PageController extends Controller
{
    private $service;

    public function __construct(PageService $service)
    {
        $this->service = $service;
    }

    function create(Request $request)
    {
        $data = $this->validate($request, [
            'title' => 'required|string',
            'body' => 'required|string',
            'order' => 'required|integer',
            'parent_slug' => 'required|string',
            'active' => 'required|boolean'
        ]);

        $result = $this->service->create($data);
        if ($result) {
            return $this->jsonResponse([
                'success' => true,
                'page' => $result
            ], 201);
        }

        return ErrorMessagesConstant::badAttempt();
    }

    function edit($slug, Request $request)
    {
        $data = $this->validate($request, [
            'title' => 'required|string',
            'body' => 'required|string',
            'order' => 'required|integer',
            'parent_id' => 'required|integer',
            'active' => 'required|boolean'
        ]);

        $result = $this->service->edit($data, $slug);

        if ($result) {
            return $this->jsonResponse([
                'success' => true,
                'page' => $result
            ], 200);
        }

        return ErrorMessagesConstant::badAttempt();
    }

    function detail($slug)
    {
       $result =  $this->service->detail($slug);

        if ($result) {
            return $this->jsonResponse($result, 200);
        }

        return ErrorMessagesConstant::notFound();
    }
}
