<?php

namespace App\Models;

use App\Models\NewsItem as AppNewsItem;
use Cviebrock\EloquentSluggable\Sluggable;
use Illuminate\Database\Eloquent\Model;

class NewsCategory extends Model
{
    use Sluggable;

    public $timestamps = false;

    protected $table = 'news_categories';

    protected $fillable = ['title', 'slug'];

    protected $appends = ['url'];

    public function news()
    {
        return $this->belongsToMany(
            AppNewsItem::class,
            'news_category_items'
        );
    }

    public function sluggable()
    {
        return [
            'slug' => [
                'source' => 'title',
                'onUpdate' => true,
            ]
        ];
    }

    /**
     * @return mixed
     */
    public function getUrlAttribute()
    {
        return $this->route();
    }

    public function route()
    {
        return route('news.category', ['slug' => $this->slug]);
    }
}
