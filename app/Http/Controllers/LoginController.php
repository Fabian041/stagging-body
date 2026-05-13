<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Log;


class LoginController extends Controller
{
    public function index()
    {
        return view('layouts.auth.login');
    }

    public function authenticate(Request $request)
    {
        $this->ensureIsNotRateLimited($request);

        $credentials = $request->validate([
            'npk'      => 'required|digits:6',
            'password' => 'required'
        ]);

        if (!Auth::attempt($credentials)) {
            RateLimiter::hit($this->throttleKey($request), 60);

            Log::info('Failed login attempt', [
                'npk' => $request->input('npk'),
                'ip'  => $request->ip(),
            ]);

            return redirect()
                ->back()
                ->with('error', 'NPK or password do not match our records!');
        }

        RateLimiter::clear($this->throttleKey($request));
        $request->session()->regenerate();

        $user = Auth::user();

        $redirectRoutes = [
            'prod'      => 'production.index',
            'ppic'      => 'pulling.index',
            'mh'        => 'validation.index',
            'injection' => 'pc2b.index',
            'direct'    => 'production.direct.index',
        ];

        return redirect()->route($redirectRoutes[$user->role] ?? 'dashboard.index');
    }


    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->forget('token');

        $request->session()->invalidate();
        $request->session()->regenerateToken();



        return redirect()->route('login.index');
    }

    protected function throttleKey(Request $request)
    {
        return Str::lower($request->input('npk')) . '|' . $request->ip();
    }

    protected function ensureIsNotRateLimited(Request $request)
    {
        $maxAttempst = 5;

        if (! RateLimiter::tooManyAttempts($this->throttleKey($request), $maxAttempst)) {
            return;
        }

        event(new Lockout($request));

        $seconds = RateLimiter::availableIn($this->throttleKey($request));
        Log::warning('Too many login attempts', [
            'npk' => $request->input('npk'),
            'ip'  => $request->ip(),
            'retry_after_seconds' => $seconds,
        ]);

        // lempar error ke user
        throw ValidationException::withMessages([
            'npk' => [__('Terlalu banyak percobaan login. Silakan coba lagi dalam :seconds detik.', [
                'seconds' => $seconds,
            ])],
        ])->status(429);
    }
}
