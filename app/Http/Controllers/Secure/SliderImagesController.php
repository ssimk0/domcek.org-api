<?php


namespace App\Http\Controllers\Secure;


use App\Constants\ErrorMessagesConstant;
use App\Http\Controllers\Controller;
use App\Services\SliderImageService;
use Illuminate\Http\Request;

class SliderImagesController extends Controller
{
    private $service;

    public function __construct(SliderImageService $service)
    {
        $this->service = $service;
    }

    function list() {
        return $this->jsonResponse(
            $this->service->getSliderImages()
        );
    }

    function create(Request $request) {
        $data = $this->validate($request, [
            'image' => 'required|url',
            'title' => 'required|string',
            'text' => 'required|string',
            'order' => 'required|integer'
        ]);

        $result = $this->service->create($data);
        if ($result) {
            return $this->jsonResponse($result, 201);
        }

        return ErrorMessagesConstant::badAttempt();
    }

    function edit($id, Request $request) {
        $data = $this->validate($request, [
            'image' => 'required|url',
            'title' => 'required|string',
            'text' => 'required|string',
            'order' => 'required|integer'
        ]);

        $result = $this->service->edit($id, $data);

        if ($result) {
            return $this->jsonResponse($result);
        }

        return ErrorMessagesConstant::badAttempt();
    }

    function delete($id) {
        $result = $this->service->delete($id);

        if ($result) {
            return $this->successResponse();
        }

        return ErrorMessagesConstant::badAttempt();
    }
}
