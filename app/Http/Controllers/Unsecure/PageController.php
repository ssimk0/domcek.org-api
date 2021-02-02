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
            ->orderBy('order')
            ->get();

        return $this->jsonResponse($pages);
    }

    public function page(Page $page)
    {
        $result = $this->extractTopParent($page->with(['parent'])->first());
        return $this->jsonResponse($result);
    }

    protected function extractTopParent($page)
    {
        if ($page->parent)
        {
            return $this->extractTopParent($page->parent->with(['parent'])->first());
        }

        return $page;
    }
}
