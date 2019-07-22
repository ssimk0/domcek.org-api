<?php


namespace App\Http\Middleware;


use App\Services\TokenService;
use Carbon\Carbon;
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
            $tokenData = $this->service->checkToken($type, $token);
            if ($tokenData && Carbon::now()->lessThanOrEqualTo($tokenData->valid_until)) {
                $request->event_id = $tokenData->event_id;
                return $next($request);
            } else {
                return response('', 403);
            }
        }

        return response('', 401);
    }
}