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
}
