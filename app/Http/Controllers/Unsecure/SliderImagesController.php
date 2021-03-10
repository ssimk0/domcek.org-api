<?php

namespace App\Http\Controllers\Unsecure;

use App\Http\Controllers\Controller;
use App\Models\SliderImage;
use App\Services\SliderImageService;

class SliderImagesController extends Controller
{
    public function list()
    {
        return $this->jsonResponse(
            SliderImage::query()
                ->where("active", true)
                ->orderBy("order")
                ->get()
        );
    }
}
