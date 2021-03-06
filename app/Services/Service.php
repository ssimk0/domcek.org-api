<?php

namespace App\Services;

use App\Logging\Logger;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;

abstract class Service
{
    use Logger;

    public function userId()
    {
        return Auth::user()->id;
    }

    protected function parseExistingData($data, $mapping)
    {
        $result = [];
        foreach ($mapping as $mapKey => $dataKey) {
            $item = Arr::get($data, $dataKey, false);
            if ($item) {
                $result[$mapKey] = $item;
            }
        }

        return $result;
    }
}
