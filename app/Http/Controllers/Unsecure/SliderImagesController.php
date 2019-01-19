<?php


namespace App\Http\Controllers\Unsecure;


use App\Http\Controllers\Controller;
use App\Services\SliderImageService;

class SliderImagesController extends Controller
{
    private $service;

    public function __construct(SliderImageService $service)
    {
        $this->service = $service;
    }

    function list() {
        return $this->jsonResponse(
            $this->service->activeSliderImages()
        );
    }
}
