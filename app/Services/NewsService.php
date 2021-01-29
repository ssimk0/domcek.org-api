<?php

namespace App\Services;

use App\Repositories\NewsRepository;

class NewsService extends Service
{
    private $repository;

    public function __construct(NewsRepository $repository)
    {
        $this->repository = $repository;
    }

    public function newsList($order, $size, $offset, $category)
    {
        if ($order === 'best') {
            return $this->repository->findAllMostViewedPublishedNews($size, $offset, $category);
        } elseif ($order === 'featured') {
            return $this->repository->findAllLatestFeaturedPublishedNews($size, $offset, $category);
        }

        return $this->repository->findAllLatestPublishedNews($size, $offset, $category);
    }

    public function newsBySlug($slug)
    {
        $news = $this->repository->findNewsDetail($slug);
        if ($news) {
            try {
                $this->repository->updateViewed($slug, $news->viewed + 1);
            } catch (\Exception $e) {
                // this error can be ignored
                $this->logWarning("Problem with updating news with slug: $slug");
            }
        }

        return $news;
    }

    public function create(array $data)
    {
        try {
            $news = $this->repository->create($data);

            return $news;
        } catch (\Exception $e) {
            // this error can be ignored
            $this->logWarning('Problem with creating news');
        }

        return false;
    }

    public function edit(array $data, $slug)
    {
        try {
            return $this->repository->edit($data, $slug);
        } catch (\Exception $e) {
            // this error can be ignored
            $this->logError('Problem with creating news with error: '.$e);
        }

        return false;
    }

    public function unpublished($size)
    {
        return $this->repository->unpublished($size);
    }

    public function unpublishedDetail($slug)
    {
        return $this->repository->findNewsBySlug($slug);
    }
}
