<?php


namespace App\Http\Middleware;


use App\Services\TokenService;
use Closure;

class TokenAuth
{
    private $service;

    public function __construct(TokenService $service)
    {
        $this->service = $service;
    }

    public function handle($request, Closure $next, $type)
    {
        $token = $request->token;
        if ($token) {
            if ($this->service->checkToken($type, $token)) {
                return $next($request);
            } else {
                return response('', 403);
            }
        }

        return response('', 401);
    }
}