<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class Cors
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $paths = explode('/', \Request::getPathInfo());
        if ($paths[1] === 'api') {
            return $next($request)
                ->header('Access-Control-Allow-Origin', '*')
                ->header('Access-Control-Allow-Methods', 'GET, POST')
                ->header('Access-Control-Allow-Headers', 'Accept, X-Requested-With, Origin, Content-Type');
        }
        return $next($request)
    }
}
