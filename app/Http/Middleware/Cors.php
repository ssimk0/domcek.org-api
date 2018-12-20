<?php

namespace App\Http\Middleware;

use App\Helpers;
use Closure;
use Illuminate\Http\Response;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

/**
 * Class Cors
 *
 * Responsible for setting correct header for cors origin and any other needed purposes
 *
 * @package App\Http\Middleware
 */
class Cors
{

    const ALLOW_METHODS = 'allowMethods';
    const ALLOW_CRED = 'allowCredentials';
    const MAX_AGE = 'maxAge';
    const EXPOSE_HEADERS = 'exposeHeaders';
    const ALLOW_HEADER = 'allowHeaders';

    protected $settings = [];

    public function __construct()
    {
        $this->settings = array(
            static::ALLOW_METHODS => 'GET,HEAD,PUT,POST,DELETE,PATCH,OPTIONS',
            static::ALLOW_CRED => True,
            static::MAX_AGE => 600,
            static::EXPOSE_HEADERS => null,
            static::ALLOW_HEADER => null,
        );
    }

    /**
     * Check if origin is allowed in env property when is allowed set correct header
     *
     * @param $req
     * @param $rsp
     */
    protected function setOrigin($rsp)
    {
        if (!($rsp instanceof BinaryFileResponse)) {
            $rsp->header('Access-Control-Allow-Origin', '*');
        }
    }

    protected function setExposeHeaders($rsp)
    {
        if (isset($this->settings[static::EXPOSE_HEADERS])) {
            $exposeHeaders = $this->settings[static::EXPOSE_HEADERS];

            $rsp->header('Access-Control-Expose-Headers', $exposeHeaders);
        }
    }

    protected function setMaxAge($rsp)
    {
        if (isset($this->settings[static::MAX_AGE])) {
            $rsp->header('Access-Control-Max-Age', $this->settings[static::MAX_AGE]);
        }
    }

    protected function setAllowCredentials($rsp)
    {
        if (isset($this->settings[static::ALLOW_CRED]) && $this->settings[static::ALLOW_CRED] === True && !($rsp instanceof BinaryFileResponse)) {
            $rsp->header('Access-Control-Allow-Credentials', 'true');
        }
    }

    protected function setAllowMethods($rsp)
    {
        if (isset($this->settings[static::ALLOW_METHODS])) {
            $allowMethods = $this->settings[static::ALLOW_METHODS];

            $rsp->header('Access-Control-Allow-Methods', $allowMethods);
        }
    }

    protected function setAllowHeaders($req, $rsp)
    {
        if (isset($this->settings[static::ALLOW_HEADER])) {
            $allowHeaders = $this->settings[static::ALLOW_HEADER];
        } else {  // Otherwise, use request headers
            $allowHeaders = $req->header("Access-Control-Request-Headers");
        }
        if (isset($allowHeaders)) {
            $rsp->header('Access-Control-Allow-Headers', $allowHeaders);
        }
    }

    protected function setCorsHeaders($req, $rsp)
    {
        // http://www.html5rocks.com/static/images/cors_server_flowchart.png
        // Pre-flight
        if ($req->isMethod('OPTIONS')) {
            $this->setOrigin($rsp);
            $this->setMaxAge($rsp);
            $this->setAllowCredentials($rsp);
            $this->setAllowMethods($rsp);
            $this->setAllowHeaders($req, $rsp);
        } else {
            $this->setOrigin($rsp);
            $this->setExposeHeaders($rsp);
            $this->setAllowCredentials($rsp);
        }
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if ($request->isMethod('OPTIONS')) {
            $response = new Response("", 200);
        } else {
            $response = $next($request);
        }
        $this->setCorsHeaders($request, $response);
        return $response;
    }
}
