<?php


namespace App\Repositories;


use App\Models\SliderImage;
use Illuminate\Support\Facades\DB;

class SliderImageRepository extends Repository
{
    function findAllSliderImages()
    {
        return DB::table("slider_images")
            ->orderBy('order')
            ->get()
            ->all();
    }

    function findAllActiveSliderImages()
    {
        return DB::table("slider_images")
            ->where('active', true)
            ->orderBy('order')
            ->get()
            ->all();
    }

    function findById($id)
    {
        return DB::table("slider_images")
            ->where('id', $id)
            ->first();
    }

    function create(array $data)
    {
        $image = new SliderImage($data);
        $image->save();
        return $image;
    }

    function edit(array $data, $id)
    {
        SliderImage::where('id', $id)->update($data);
        return $this->findById($id);
    }

    function delete($id)
    {
        return DB::table("slider_images")
            ->where('id', $id)
            ->update([
                'active' => false
            ]);
    }
}
