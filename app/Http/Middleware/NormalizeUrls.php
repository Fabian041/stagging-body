<?php

namespace App\Http\Middleware;

use Closure;

class NormalizeUrls
{
    public function handle($request, Closure $next)
    {
        $url = $request->getRequestUri();

        // contoh: hilangkan trailing slash selain root
        if ($url !== '/' && substr($url, -1) === '/') {
            return redirect(rtrim($url, '/'), 301);
        }

        return $next($request);
    }
}
