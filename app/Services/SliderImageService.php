<?php


namespace App\Services;


use App\Repositories\SliderImageRepository;

class SliderImageService extends Service
{
    private $repository;

    public function __construct(SliderImageRepository $repository)
    {
        $this->repository = $repository;
    }

    function getSliderImages() {
        return $this->repository->findAllSliderImages();
    }
}
