<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

/**
 * If the incoming request is an OPTIONS request
 * we will register a handler for the requested route.
 */
class CatchAllOptionsRequestsProvider extends ServiceProvider
{
    public function boot()
    {
        $request = $this->app['request'];
        if ($request->isMethod('OPTIONS')) {
            app()->router->options($request->path(), ['middleware' => 'cors'], function () {
                return response('', 200);
            });
        }
    }
}
