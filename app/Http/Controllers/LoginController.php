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
        // 1. Cek apakah sudah melebihi batas
        $this->ensureIsNotRateLimited($request);

        // 2. Validasi input seperti biasa
        $credentials = $request->validate([
            'npk'      => 'required|min:6|max:6',
            'password' => 'required'
        ]);

        // 3. Coba login
        if (Auth::attempt($credentials)) {
            // kalau berhasil, reset counter rate limiter
            RateLimiter::clear($this->throttleKey($request));

            $request->session()->regenerate();

            // ===== redirect sesuai role (kode kamu sebelumnya) =====
            if (auth()->user()->role == 'prod') {
                return redirect()->route('production.index');
            } else if (auth()->user()->role == 'ppic') {

                $response = Http::withoutVerifying()->post('https://dea-dev.aiia.co.id/api/v1/auth/login', [
                    'npk'      => Auth::user()->npk,
                    'password' => '123456'
                ]);

                if ($response->successful()) {
                    $token = json_decode($response->body(), true)['data']['access_token'];
                    session()->put('token', $token);
                } else {
                    return redirect()->back()->with('error', 'Failed to generate token');
                }

                return redirect()->route('pulling.index');
            } else if (auth()->user()->role == 'mh') {

                return redirect()->route('validation.index');
            } else if (auth()->user()->role == 'direct') {

                $response = Http::withoutVerifying()->post('https://dea-dev.aiia.co.id/api/v1/auth/login', [
                    'npk'      => Auth::user()->npk,
                    'password' => '123456'
                ]);

                if ($response->successful()) {
                    $token = json_decode($response->body(), true)['data']['access_token'];
                    session()->put('token', $token);
                } else {
                    return redirect()->back()->with('error', 'Failed to generate token');
                }

                return redirect()->route('production.direct.index');
            }

            return redirect()->route('dashboard.index');
        }

        RateLimiter::hit($this->throttleKey($request), 60);

        Log::info('Failed login attempt', [
            'npk' => $request->input('npk'),
            'ip'  => $request->ip(),
        ]);

        return redirect()->back()->with('error', 'Email or password do not match our records!');
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
