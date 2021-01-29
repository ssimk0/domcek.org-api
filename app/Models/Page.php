<?php

namespace App\Models;

use Cviebrock\EloquentSluggable\Sluggable;
use Illuminate\Database\Eloquent\Model;

class Page extends Model
{
    use Sluggable;

    protected $fillable = ['title', 'slug', 'body', 'active', 'parent_id', 'order'];

    /**
     * The page url.
     *
     * @return mixed null|string
     */
    public function url()
    {
        return route('pages.show', ['slug' => $this->getAttribute('slug')]);
    }

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
