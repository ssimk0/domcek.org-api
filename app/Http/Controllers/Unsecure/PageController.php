<?php

namespace App\Http\Controllers\Unsecure;

use App\Http\Controllers\Controller;
use App\Models\Page;

class PageController extends Controller {

    public function menuPages()
    {
        $pages = Page::query()
            ->with(['children' => function ($query) {
                $query->where('active', true);
            }])
            ->where('active', true)
            ->where('parent_id', null)
            ->orderBy('created_at', 'desc')
            ->orderBy('order')
            ->get();

        return $pages;
    }

    public function page(Page $page)
    {
        return $this->extractTopParent($page);
    }

    protected function extractTopParent($page)
    {
        if ($page->parent)
        {
            return $this->extractTopParent($page->parent);
        }

        return $page;
    }
}
