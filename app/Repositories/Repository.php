<?php
namespace App\Repositories;

abstract class Repository {

    public function prepareStringForLikeFilter($string) {
        return '%'.$string.'%';
    }

    public function addWhereForFilter($query, $filterString, $columns) {
        $query->where(function($q) use ($filterString, $columns) {
            $filters = explode(' ', $filterString);

            foreach ($columns as $column) {
                foreach ($filters as $filter) {
                    $q->orWhere($column, 'like', $this->prepareStringForLikeFilter($filter));
                }
            }
        });

        return $query;
    }

}
