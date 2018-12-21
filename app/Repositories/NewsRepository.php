<?php


namespace App\Repositories;


use App\Constants\NewsConstant;
use App\Models\NewsItem;
use Illuminate\Support\Facades\DB;

class NewsRepository extends Repository
{
    function findAllLatestPublishedNews($size, $offset)
    {
        return DB::table('news_items')
            ->where('status', NewsConstant::PUBLISHED)
            ->orderBy('created_at', 'desc')
            ->skip($offset)
            ->paginate($size);
    }

    function findAllMostViewedPublishedNews($size, $offset)
    {
        return DB::table('news_items')
            ->where('status', NewsConstant::PUBLISHED)
            ->orderBy('viewed', 'desc')
            ->skip($offset)
            ->paginate($size);
    }

    function findAllLatestFeaturedPublishedNews($size, $offset)
    {
        return DB::table('news_items')
            ->where('status', NewsConstant::PUBLISHED)
            ->where('is_featured', true)
            ->orderBy('created_at', 'desc')
            ->skip($offset)
            ->paginate($size);
    }

    function findNewsDetail($slug)
    {
        return DB::table('news_items')
            ->where('status', NewsConstant::PUBLISHED)
            ->where('slug', $slug)
            ->first();
    }

    function updateViewed($slug, $viewed)
    {
        DB::table('news_items')
            ->where('status', NewsConstant::PUBLISHED)
            ->where('slug', $slug)
            ->update([
                'viewed' => $viewed
            ]);
    }

    public function create(array $data)
    {
        $newsItem = new NewsItem($data);
        $newsItem->save();
        return $newsItem;
    }

    function edit(array $data, $slug)
    {
        NewsItem::where('slug', $slug)
            ->update($data);
    }
}
