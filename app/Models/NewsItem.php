<?php

namespace App\Models;

use Cviebrock\EloquentSluggable\Sluggable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NewsItem extends Model
{
    use Sluggable;
    use HasFactory;
    protected $table = 'news_items';

    protected $fillable = [
        'title', 'slug', 'image', 'short', 'body', 'status', 'is_featured', 'viewed', 'category',
    ];

    protected $hidden = ['image_file_name', 'image_file_size', 'image_content_type', 'image_updated_at'];

    public function sluggable()
    {
        return [
            'slug' => [
                'source' => 'title',
                'onUpdate' => true,
                'separator' => '-',
            ],
        ];
    }
}
