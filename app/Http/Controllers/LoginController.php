<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;

class LoginController extends Controller
{
    public function index()
    {
        return view('layouts.auth.login');
    }

    public function authenticate(Request $request)
    {

        $credentials = $request->validate([
            'npk' => 'required|min:6|max:6',
            'password' => 'required'
        ]);

        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();

            if(auth()->user()->role == 'prod'){
                // redirect to prod
                return redirect()->intended('/production');
            }else if(auth()->user()->role == 'ppic'){

                // Perform login and obtain the Bearer token (API Dea)
                $response = Http::post('http://api-dea-dev/api/v1/auth/login', [
                    'npk' => Auth::user()->npk,
                    'password' => '123456'
                ]);

                if($response->successful()){
                    $token = json_decode($response->body(), true)['data']['access_token'];
    
                    // store  token to session
                    session()->put('token', $token);

                }else{
                    return redirect()->intended('/pulling', [
                        'message' => 'failed to generate token'
                    ]);
                }
                // redirect to ppic
                return redirect()->intended('/pulling');
            }

        }

        return redirect()->back()->with('error', 'Email or password do not match our records!');
    }

    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login.index');
    }
}
