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

    function menuPages()
    {
        $menuPages = $this->repository->findPagesWithoutParent();

        foreach ($menuPages as $page) {
            $page->children = $this->repository->findChildrenForPage($page->id);
        }

        return $menuPages;
    }

    function pageBySlug($slug)
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

    function create(array $data) {
        try {
            $parent = $this->repository->findPageBySlug($data['parent_slug']);
            $page = $this->repository->create([
                'parent_id' => $parent->id,
                'title' => $data['title'],
                'body' => $data['body'],
                'order' => $data['order'],
                'active' => $data['active']
            ]);
            return $page;
        } catch (\Exception $e) {
            // this error can be ignored
            $this->logWarning("Problem with creating news");
        }

        return false;
    }

    function edit(array $data, $slug) {
        try {
            return $this->repository->edit($data, $slug);
        } catch (\Exception $e) {
            // this error can be ignored
            $this->logWarning("Problem with creating news");
        }

        return false;
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

    function detail($slug)
    {
        try {
            return $this->repository->findPageBySlug($slug);
        } catch (\Exception $e) {
            // this error can be ignored
            $this->logWarning("Problem with creating news");
        }

        return false;
    }
}
