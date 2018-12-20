<?php


namespace App\Repositories;


use Illuminate\Support\Facades\DB;

class SliderImageRepository extends Repository
{
    function findAllSliderImages() {
        return DB::table("slider_images")->orderBy('order')->get()->all();
    }
}
