<?php

namespace App\Repositories;

use App\Constants\NewsConstant;
use App\Models\NewsItem;
use Illuminate\Support\Facades\DB;

class NewsRepository extends Repository
{
    public function findAllLatestPublishedNews($size, $offset, $category)
    {
        return DB::table('news_items')
            ->where('status', NewsConstant::PUBLISHED)
            ->where('category', $category)
            ->orderBy('created_at', 'desc')
            ->skip($offset)
            ->paginate($size);
    }

    public function findAllMostViewedPublishedNews($size, $offset, $category)
    {
        return DB::table('news_items')
            ->where('status', NewsConstant::PUBLISHED)
            ->where('category', $category)
            ->orderBy('viewed', 'desc')
            ->skip($offset)
            ->paginate($size);
    }

    public function findAllLatestFeaturedPublishedNews($size, $offset, $category)
    {
        if ($offset == 0) {
            return DB::table('news_items')
                ->where('status', NewsConstant::PUBLISHED)
                ->where('category', $category)
                ->where('is_featured', true)
                ->orderBy('created_at', 'desc')
                ->offset(2)
                ->paginate($size);
        } else {
            $skiped = DB::table('news_items')
                ->where('status', NewsConstant::PUBLISHED)
                ->where('category', $category)
                ->where('is_featured', true)
                ->orderBy('created_at', 'desc')
                ->limit($offset)
                ->get(['id']);
            $ids = [];
            foreach ($skiped as $item) {
                $ids[] = $item->id;
            }

            return DB::table('news_items')
                ->where('status', NewsConstant::PUBLISHED)
                ->where('category', $category)
                ->where('is_featured', true)
                ->whereNotIn('id', $ids)
                ->orderBy('created_at', 'desc')
                ->paginate($size);
        }
    }

    public function findNewsDetail($slug)
    {
        return DB::table('news_items')
            ->whereIn('status', [NewsConstant::PUBLISHED, NewsConstant::DRAFT])
            ->where('slug', $slug)
            ->first();
    }

    public function findNewsBySlug($slug)
    {
        return DB::table('news_items')
            ->where('slug', $slug)
            ->first();
    }

    public function updateViewed($slug, $viewed)
    {
        DB::table('news_items')
            ->where('status', NewsConstant::PUBLISHED)
            ->where('slug', $slug)
            ->update([
                'viewed' => $viewed,
            ]);
    }

    public function create(array $data)
    {
        $newsItem = new NewsItem($data);
        $newsItem->save();

        return $newsItem;
    }

    public function edit(array $data, $slug)
    {
        NewsItem::where('slug', $slug)->update($data);

        return $this->findNewsBySlug($slug);
    }

    public function unpublished($size)
    {
        return DB::table('news_items')
            ->where('status', '!=', NewsConstant::PUBLISHED)
            ->orderBy('created_at', 'desc')
            ->paginate($size);
    }
}
