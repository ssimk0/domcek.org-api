<?php

namespace App\Models;

use App\Models\NewsCategory as AppNewsCategory;
use Codesleeve\Stapler\ORM\EloquentTrait;
use Codesleeve\Stapler\ORM\StaplerableInterface;
use Cviebrock\EloquentSluggable\Sluggable;
use Illuminate\Database\Eloquent\Model;

class NewsItem extends Model implements StaplerableInterface
{
    use Sluggable;
    use EloquentTrait;

    static public $statuses = [
        'draft' => 'Draft',
        'published' => 'Published',
        'archived' => 'Archived',
    ];

    protected $table = 'news_items';

    protected $fillable = [
        'title', 'slug', 'image', 'short', 'body', 'status', 'is_featured', 'viewed'
    ];

    protected $appends = ['url', 'images'];

    protected $hidden = ['image_file_name', 'image_file_size', 'image_content_type', 'image_updated_at'];

    public function sluggable()
    {
        return [
            'slug' => [
                'source' => 'title',
                'onUpdate' => true,
            ]
        ];
    }

    public function __construct($attributes = [])
    {
        $this->hasAttachedFile('image', [
            'styles' => [
                'thumb' => '250x80',
                'medium' => '300x300',
                'large' => '600x600',
            ],
        ]);

        parent::__construct($attributes);
    }

    /**
     * @widget
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function categories()
    {
        return $this->belongsToMany(AppNewsCategory::class, 'news_category_items');
    }

    public function presentStatus()
    {
        $classes = [
            'draft' => 'bg-yellow',
            'published' => 'bg-green',
            'archived' => 'bg-gray',
        ];

        return \admin\output\label($s = $this->attributes['status'], $classes[$s]);
    }

    public function presentTitle()
    {
        return link_to_route(
            'news.show',
            $this->attributes['title'],
            ['slug' => $this->slug],
            ['target' => '_blank']
        );
    }

    public function presentExcerpt()
    {
        return '<div class="well text-muted">' . $this->attributes['short'] . '</div>';
    }

    public function presentBody()
    {
        return '<div class="well">' . $this->attributes['body'] . '</div>';
    }

    public function linkToRoute()
    {
        return link_to_route('news.show', $this->title, ['slug' => $this->slug]);
    }

    public function getImagesAttribute()
    {
        if (! $this->image->originalFilename()) {
            return [];
        }

        return array_build($this->image->getConfig()->styles, function ($index, $style) {
            if ((! $size = $style->dimensions)) {
                list($w, $h) = getimagesize($this->image->path());

                $size = "{$w}x{$h}";
            }

            return [
                $style->name,
                [
                    'url' => $this->image->url($style->name),
                    'name' => $this->attributes['image_file_name'],
                    'dimensions' => $size,
                    'type' => $this->attributes['image_content_type'],
                ],
            ];
        });
    }

    public function getUrlAttribute($value = null)
    {
        return $this->route();
    }

    public function url($value = null)
    {
        return $this->route();
    }

    public function route()
    {
        return route('news.show', ['slug' => $this->slug]);
    }
}
