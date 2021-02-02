<?php

namespace App\Http\Controllers\Secure;

use App\Http\Controllers\Controller;
use App\Models\SliderImage;
use Illuminate\Http\Request;

class SliderImagesController extends Controller {

    public function list()
    {
        return $this->jsonResponse(
            SliderImage::query()
                ->orderBy("order")
                ->get()
        );
    }

    public function create(Request $request)
    {
        $data = $request->validate([
            'image' => ['required', 'regex:(https?:\/\/(?:www\.|(?!www))[a-zA-Z0-9][a-zA-Z0-9-]+[a-zA-Z0-9]\.[^\s]{2,}|www\.[a-zA-Z0-9][a-zA-Z0-9-]+[a-zA-Z0-9]\.[^\s]{2,}|https?:\/\/(?:www\.|(?!www))[a-zA-Z0-9]+\.[^\s]{2,}|www\.[a-zA-Z0-9]+\.[^\s]{2,})'],
            'title' => 'required|string',
            'text' => 'required|string',
            'order' => 'required|integer',
            'active' => 'required|boolean'
        ]);

        $result = SliderImage::query()->create($data);

        return $this->jsonResponse($result, 201);
    }

    public function edit(SliderImage $image, Request $request)
    {
        $data = $request->validate([
            'image' => ['required', 'regex:(https?:\/\/(?:www\.|(?!www))[a-zA-Z0-9][a-zA-Z0-9-]+[a-zA-Z0-9]\.[^\s]{2,}|www\.[a-zA-Z0-9][a-zA-Z0-9-]+[a-zA-Z0-9]\.[^\s]{2,}|https?:\/\/(?:www\.|(?!www))[a-zA-Z0-9]+\.[^\s]{2,}|www\.[a-zA-Z0-9]+\.[^\s]{2,})'],
            'title' => 'required|string',
            'text' => 'required|string',
            'order' => 'required|integer',
            'active' => 'required|boolean',
        ]);

        $image->update($data);

        return $this->jsonResponse($image);
    }

    public function delete(SliderImage $image)
    {
        $image->delete();

        return $this->successResponse();
    }
}
