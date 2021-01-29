<?php

namespace App\Repositories;

use App\Models\SliderImage;
use Illuminate\Support\Facades\DB;

class SliderImageRepository extends Repository
{
    public function findAllSliderImages()
    {
        return DB::table('slider_images')
            ->orderBy('order')
            ->get()
            ->all();
    }

    public function findAllActiveSliderImages()
    {
        return DB::table('slider_images')
            ->where('active', true)
            ->orderBy('order')
            ->get()
            ->all();
    }

    public function findById($id)
    {
        return DB::table('slider_images')
            ->find($id);
    }

    public function create(array $data)
    {
        $image = new SliderImage($data);
        $image->save();

        return $image;
    }

    public function edit(array $data, $id)
    {
        SliderImage::where('id', $id)->update($data);

        return $this->findById($id);
    }

    public function delete($id)
    {
        return DB::table('slider_images')
            ->where('id', $id)
            ->delete();
    }
}
