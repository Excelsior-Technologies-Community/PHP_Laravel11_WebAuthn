<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    // Show registration form
    public function register() {
        return view('register');
    }

    // Handle registration
    public function store(Request $request) {
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password)
        ]);

        Auth::login($user);
        return redirect('/dashboard');
    }

    // Show login form
    public function login() {
        return view('login');
    }

    // Handle login
    public function authenticate(Request $request) {
        if (Auth::attempt($request->only('email','password'))) {
            return redirect('/dashboard');
        }
        return back()->with('error','Invalid login');
    }

    // Dashboard view
    public function dashboard() {
        return view('dashboard');
    }

    // Logout
    public function logout() {
        Auth::logout();
        return redirect('/login');
    }
}