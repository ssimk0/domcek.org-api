<?php


namespace App\Services;


use App\Repositories\PageRepository;

class PageService extends Service
{
    private $repository;

    public function __construct(PageRepository $pageRepository)
    {
        $this->repository = $pageRepository;
    }

    function getMenuPages()
    {
        $menuPages = $this->repository->findPagesWithoutParent();

        foreach ($menuPages as $page) {
            $page->children = $this->repository->findChildrenForPage($page->id);
        }

        return $menuPages;
    }

    function getPageBySlug($slug)
    {
        $pages = $this->repository->findAllActivePages();
        $page = array_filter($pages, function ($page) use ($slug) {
            return $page->slug === $slug;
        });

        if (!empty($page)) {
            $page = array_values($page)[0];

            if ($page->parent_id != null) {
                $parentPage = array_filter($pages, function ($p) use ($page) {
                    return $page->parent_id === $p->id;
                });
                $parentPage = array_values($parentPage)[0];

                return $this->findPageChildren($parentPage, $pages);
            }

            return $this->findPageChildren($page, $pages);
        }

        return null;
    }

    private function findPageChildren($page, $pages)
    {
        $children = [];

        foreach ($pages as $p) {
            if ($page->id === $p->parent_id) {
                $children []= $this->findPageChildren($p, $pages);
            }
        }

        $page->children = $children;

        return $page;
    }
}
