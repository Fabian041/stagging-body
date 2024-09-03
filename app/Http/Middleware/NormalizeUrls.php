<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class NormalizeUrls
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle($request, Closure $next)
    {
        // Remove any extra slashes from the URL
        $normalizedUri = preg_replace('/\/+/', '/', $request->path());
        if ($request->path() !== $normalizedUri) {
            return redirect($normalizedUri, 301);
        }

        return $next($request);
    }
}
