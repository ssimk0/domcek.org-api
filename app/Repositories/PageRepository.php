<?php


namespace App\Repositories;


use Illuminate\Support\Facades\DB;

class PageRepository extends Repository
{
    function findPagesWithoutParent()
    {
        return DB::table('pages')
            ->where('parent_id', null)
            ->where('active', true)
            ->get()
            ->all();
    }

    function findChildrenForPage($pageId)
    {
        return DB::table('pages')
            ->where('parent_id', $pageId)
            ->where('active', true)
            ->get()
            ->all();
    }

    function findAllActivePages()
    {
        return DB::table('pages')
            ->where('active', true)
            ->get()
            ->all();
    }
}
