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
        if ($offset == 0 ) {
            return DB::table('news_items')
                ->where('status', NewsConstant::PUBLISHED)
                ->where('is_featured', true)
                ->orderBy('created_at', 'desc')
                ->offset(2)
                ->paginate($size);
        } else {
             $skiped = DB::table('news_items')
                ->where('status', NewsConstant::PUBLISHED)
                ->where('is_featured', true)
                ->orderBy('created_at', 'desc')
                ->limit($offset)
                ->get(['id']);
            $ids = [];
            foreach ($skiped as $item) {
                $ids []= $item->id;
            }

            return DB::table('news_items')
                ->where('status', NewsConstant::PUBLISHED)
                ->where('is_featured', true)
                ->whereNotIn('id', $ids)
                ->orderBy('created_at', 'desc')
                ->paginate($size);
        }
    }

    function findNewsDetail($slug)
    {
        return DB::table('news_items')
            ->whereIn('status', [NewsConstant::PUBLISHED, NewsConstant::DRAFT])
            ->where('slug', $slug)
            ->first();
    }

    function findNewsBySlug($slug)
    {
        return DB::table('news_items')
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
        NewsItem::where('slug', $slug)->update($data);
        return $this->findNewsBySlug($slug);
    }

    function unpublished($size)
    {
        return DB::table('news_items')
            ->where('status', "!=", NewsConstant::PUBLISHED)
            ->where('is_featured', true)
            ->orderBy('created_at', 'desc')
            ->paginate($size);
    }
}
