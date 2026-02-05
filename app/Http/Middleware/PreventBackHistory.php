<?php

namespace App\Http\Middleware;

use Closure;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class PreventBackHistory
{
    public function handle($request, Closure $next)
    {
        $response = $next($request);

        $cacheHeaders = [
            'Cache-Control' => 'no-cache, no-store, max-age=0, must-revalidate',
            'Pragma'        => 'no-cache',
            'Expires'       => 'Sat, 01 Jan 1990 00:00:00 GMT',
        ];

        // Jika response file download (Excel, PDF, dll)
        if ($response instanceof BinaryFileResponse) {
            foreach ($cacheHeaders as $key => $val) {
                $response->headers->set($key, $val);
            }
            return $response;
        }

        // Response normal Laravel
        return $response
            ->header('Cache-Control', $cacheHeaders['Cache-Control'])
            ->header('Pragma', $cacheHeaders['Pragma'])
            ->header('Expires', $cacheHeaders['Expires']);
    }
}
