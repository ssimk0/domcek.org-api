<?php


namespace App\Services;


use App\Logging\Logger;
use Illuminate\Support\Facades\Auth;

abstract class Service
{
    use Logger;

    function userId()
    {
        return Auth::user()->id;
    }

    protected function parseExistingData($data, $mapping) {
        $result = [];
        foreach ($mapping as $mapKey => $dataKey) {
            $item = array_get($data, $dataKey, false);
            if ($item) {
                $result[$mapKey] = $item;
            }
        }

        return $result;
    }
}
