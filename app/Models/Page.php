<?php

namespace App;

use Terranet\Pages\Models\Page as GenericPage;

class Page extends GenericPage
{

    protected $fillable = ['title', 'slug', 'body', 'active', 'parent_id', 'order'];

    /**
     * The page url
     *
     * @return mixed null|string
     */
    public function url()
    {
        return route('pages.show', ['slug' => $this->getAttribute('slug')]);
    }

    public function getRouteKeyName()
    {
        return 'slug';
    }
}
