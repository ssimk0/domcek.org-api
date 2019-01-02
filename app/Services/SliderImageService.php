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

    function getSliderImages()
    {
        return $this->repository->findAllSliderImages();
    }

    function create(array $data)
    {
        try {
            return $this->repository->create($data);
        } catch (\Exception $e) {
            // this error can be ignored
            $this->logError("Problem with creating slider image with error: " . $e);
        }

        return false;
    }

    function edit($id, array $data)
    {
        try {
            return $this->repository->edit($data, $id);
        } catch (\Exception $e) {
            // this error can be ignored
            $this->logError("Problem with editing slider image with error: " . $e);
        }

        return false;
    }

    function delete($id)
    {
        try {
            return $this->repository->delete($id);
        } catch (\Exception $e) {
            // this error can be ignored
            $this->logError("Problem with deleting slider image with error: " . $e);
        }

        return false;
    }
}
