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

    function getNewsList($order, $size, $offset)
    {
        if ($order === 'best') {
            return $this->repository->findAllMostViewedPublishedNews($size, $offset);
        } else if ($order === 'featured') {
            return $this->repository->findAllLatestFeaturedPublishedNews($size, $offset);
        }

        return $this->repository->findAllLatestPublishedNews( $size, $offset);
    }

    function getNewsBySlug($slug)
    {
        $news = $this->repository->findNewsDetail($slug);
        try {
            $this->repository->updateViewed($slug, $news->viewed + 1);
        }catch (\Exception $e) {
            // this error can be ignored
            $this->logWarning("Problem with updating news with slug: $slug");
        }

        return $news;
    }
}
