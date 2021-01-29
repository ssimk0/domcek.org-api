<?php

namespace App\Repositories;

use App\Constants\TableConstants;
use Illuminate\Support\Arr;

abstract class Repository
{
    protected $globalFilters = [
        'volunteer' => TableConstants::VOLUNTEERS.'.volunteer_type_id',
        'group' => TableConstants::EVENT_GROUPS.'.group_name',
        'variant' => TableConstants::PAYMENTS.'.event_price_id',
    ];

    public function prepareStringForLikeFilter($string)
    {
        return '%'.$string.'%';
    }

    public function addWhereForFilter($query, $filterString, $columns)
    {
        $query->where(function ($q) use ($filterString, $columns) {
            $filters = explode(' ', $filterString);

            foreach ($columns as $column) {
                foreach ($filters as $filter) {
                    $q->orWhere($column, 'like', $this->prepareStringForLikeFilter($filter));
                }
            }
        });

        return $query;
    }

    public function filterQuery($query, $filters)
    {
        foreach ($this->globalFilters as $filterName => $filterField) {
            if (Arr::get($filters, $filterName) != null) {
                if (is_array($filters[$filterName])) {
                    $query->whereIn($filterField, $filters[$filterName]);
                } else {
                    $query->where($filterField, $filters[$filterName]);
                }
            }
        }

        return $query;
    }
}
