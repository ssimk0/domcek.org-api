<?php

namespace App\Http\Middleware;

use App\Services\UserService;
use Closure;

class Permission
{
    private $service;

    public function __construct(UserService $service)
    {
        $this->service = $service;
    }

    public function handle($request, Closure $next, $perm)
    {
        if ($request->user()) {
            if ($this->service->checkPermission($perm, $request->user())) {
                return $next($request);
            } else {
                return response('', 403);
            }
        }

        return response('', 401);
    }
}
