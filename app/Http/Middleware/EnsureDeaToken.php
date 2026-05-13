<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class EnsureDeaToken
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $token = session('token');
        $expiredAt = session('token_expired_at');

        // Kalau token masih ada dan belum expired, lanjut
        if ($token && $expiredAt && now()->lt($expiredAt)) {
            return $next($request);
        }

        // Kalau token tidak ada / expired, generate ulang
        $user = Auth::user();

        try {
            $response = Http::withoutVerifying()
                ->timeout(10)
                ->post(env('DEA_API_LOGIN_URL'), [
                    'npk'      => $user->npk,
                    'password' => env('DEA_API_PASSWORD'),
                ]);

            if (!$response->successful()) {
                return redirect()
                    ->route('dashboard.index')
                    ->with('error', 'Failed to generate DEA token');
            }

            $token = $response->json('data.access_token');

            if (!$token) {
                return redirect()
                    ->route('dashboard.index')
                    ->with('error', 'DEA token not found');
            }

            session()->put('token', $token);

            // Sesuaikan dengan masa berlaku token DEA
            session()->put('token_expired_at', now()->addMinutes(55));

        } catch (\Throwable $e) {
            Log::error('DEA token request error', [
                'npk'   => $user->npk,
                'error' => $e->getMessage(),
            ]);

            return redirect()
                ->route('dashboard.index')
                ->with('error', 'DEA API connection failed');
        }

        return $next($request);
    }
}