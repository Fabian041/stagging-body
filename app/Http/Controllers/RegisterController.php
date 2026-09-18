<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class RegisterController extends Controller
{
    public function index()
    {
        return view('layouts.auth.register', [
            'departments' => $this->departmentOptions(),
        ]);
    }

    public function store(Request $request)
    {
        $departments = $this->departmentOptions();

        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'npk' => 'required|min:6|max:6|unique:users',
            'email' => 'required|email:dns|unique:users',
            'department' => ['required', 'string', Rule::in($departments)],
            'password' => 'required|min:6|string',
        ]);

        // hash password
        $validatedData['password'] = Hash::make($validatedData['password']);

        User::create($validatedData);

        return redirect('/login')->with('success', 'Your account has been created');
    }

    private function departmentOptions(): array
    {
        return [
            'production',
            'injection',
            'material handling',
            'ppic',
            'QC',
        ];
    }
}
