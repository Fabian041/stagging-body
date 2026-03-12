<?php

namespace App\Http\Middleware;

use Closure;

class PreventBackHistory
{
    public function handle($request, Closure $next)
    {
        $response = $next($request);

        $headers = [
            'Cache-Control' => 'no-cache, no-store, max-age=0, must-revalidate',
            'Pragma' => 'no-cache',
            'Expires' => 'Sat, 01 Jan 1990 00:00:00 GMT',
        ];

        // Some response types (e.g. BinaryFileResponse for downloads) don't have header().
        if (method_exists($response, 'header')) {
            return $response
                ->header('Cache-Control', $headers['Cache-Control'])
                ->header('Pragma', $headers['Pragma'])
                ->header('Expires', $headers['Expires']);
        }

        if (property_exists($response, 'headers') && $response->headers) {
            foreach ($headers as $key => $value) {
                $response->headers->set($key, $value);
            }
        }

        return $response;
    }
}
