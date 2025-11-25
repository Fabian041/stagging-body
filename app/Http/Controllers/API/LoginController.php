<?php

namespace App\Http\Controllers\API;

use Hash;
use Carbon\Carbon;
use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Log;

class LoginController extends Controller
{
    public function authenticate(Request $request)
    {
        $user = User::where('npk', $request->npk)->first();

        if (!$user || !\Illuminate\Support\Facades\Hash::check($request->password, $user->password)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid credentials'
            ], 401);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Login success',
            'data' => [
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'npk' => $user->npk,
                ],
                'created_at' => Carbon::now()->toDateTimeString(),
                'updated_at' => Carbon::now()->toDateTimeString(),
            ],
        ], 200);
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
