<?php


namespace App\Http\Controllers\Secure;


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

    function create(Request $request) {
        $data = $this->validate($request, [
            'image' => 'required|url',
            'title' => 'required|string',
            'body' => 'required|string',
            'order' => 'required|string'
        ]);

        $result = $this->service->create($data);
    }

    function edit($id, Request $request) {
        $data = $this->validate($request, [
            'image' => 'required|url',
            'title' => 'required|string',
            'body' => 'required|string',
            'order' => 'required|string'
        ]);

        $result = $this->service->edit($id, $data);
    }

    function delete($id) {
        $result = $this->service->delete($id);
    }
}
