<?php

namespace App\Repositories;

use App\Models\Page;
use Illuminate\Support\Facades\DB;

class PageRepository extends Repository
{
    public function findPagesWithoutParent()
    {
        return DB::table('pages')
            ->where('parent_id', null)
            ->where('active', true)
            ->orderBy('order', 'asc')
            ->get()
            ->all();
    }

    public function findChildrenForPage($pageId)
    {
        return DB::table('pages')
            ->where('parent_id', $pageId)
            ->where('active', true)
            ->orderBy('order', 'asc')
            ->get()
            ->all();
    }

    public function findAllActivePages()
    {
        return DB::table('pages')
            ->where('active', true)
            ->orderBy('order', 'asc')
            ->get()
            ->all();
    }

    public function findPageBySlug($slug)
    {
        return DB::table('pages')
            ->where('slug', $slug)
            ->first();
    }

    public function edit(array $data, $slug)
    {
        Page::where('slug', $slug)->update($data);

        return $this->findPageBySlug($slug);
    }

    public function create(array $data)
    {
        $page = new Page($data);
        $page->save();

        return $page;
    }
}
