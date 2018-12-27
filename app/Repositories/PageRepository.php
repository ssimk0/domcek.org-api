<?php


namespace App\Repositories;


use App\Models\Page;
use Illuminate\Support\Facades\DB;

class PageRepository extends Repository
{
    function findPagesWithoutParent()
    {
        return DB::table('pages')
            ->where('parent_id', null)
            ->where('active', true)
            ->orderBy('order', 'asc')
            ->get()
            ->all();
    }

    function findChildrenForPage($pageId)
    {
        return DB::table('pages')
            ->where('parent_id', $pageId)
            ->where('active', true)
            ->orderBy('order', 'asc')
            ->get()
            ->all();
    }

    function findAllActivePages()
    {
        return DB::table('pages')
            ->where('active', true)
            ->orderBy('order', 'asc')
            ->get()
            ->all();
    }

    function findPageBySlug($slug)
    {
        return DB::table('pages')
            ->where('slug', $slug)
            ->first();
    }

    function edit(array $data, $slug)
    {
        Page::where('slug', $slug)->update($data);
        return $this->findPageBySlug($slug);
    }

    function create(array $data)
    {
        $page = new Page($data);
        $page->save();
        return $page;
    }
}
