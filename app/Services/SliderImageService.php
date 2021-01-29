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

    public function sliderImages()
    {
        return $this->repository->findAllSliderImages();
    }

    public function activeSliderImages()
    {
        return $this->repository->findAllActiveSliderImages();
    }

    public function create(array $data)
    {
        try {
            return $this->repository->create($data);
        } catch (\Exception $e) {
            // this error can be ignored
            $this->logError('Problem with creating slider image with error: '.$e);
        }

        return false;
    }

    public function edit($id, array $data)
    {
        try {
            return $this->repository->edit($data, $id);
        } catch (\Exception $e) {
            // this error can be ignored
            $this->logError('Problem with editing slider image with error: '.$e);
        }

        return false;
    }

    public function delete($id)
    {
        try {
            return $this->repository->delete($id);
        } catch (\Exception $e) {
            // this error can be ignored
            $this->logError('Problem with deleting slider image with error: '.$e);
        }

        return false;
    }
}
