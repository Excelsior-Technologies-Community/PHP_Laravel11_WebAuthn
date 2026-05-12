<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\LoginActivity;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Jenssegers\Agent\Agent;

class AuthController extends Controller
{
    // ================= REGISTER PAGE =================
    public function register()
    {
        return view('register');
    }

    // ================= REGISTER USER + ACTIVITY =================
    public function store(Request $request)
    {
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password)
        ]);

        Auth::login($user);

        $agent = new Agent();

        // SAVE REGISTER ACTIVITY
        LoginActivity::create([
            'user_id' => $user->id,
            'ip_address' => $request->ip() . ' (' . request()->getHost() . ')',
            'browser' => $agent->browser() . ' - ' . $agent->platform(),
            'login_method' => 'Register'
        ]);

        return redirect('/dashboard');
    }

    // ================= LOGIN PAGE =================
    public function login()
    {
        return view('login');
    }

    // ================= LOGIN USER + ACTIVITY =================
    public function authenticate(Request $request)
    {
        if (Auth::attempt($request->only('email', 'password'))) {

            $user = Auth::user();

            $agent = new Agent();

            // SAVE LOGIN ACTIVITY
            LoginActivity::create([
                'user_id' => $user->id,
                'ip_address' => $request->ip() . ' (' . request()->getHost() . ')',
                'browser' => $agent->browser() . ' - ' . $agent->platform(),
                'login_method' => 'Password'
            ]);

            return redirect('/dashboard');
        }

        return back()->with('error', 'Invalid login');
    }

    // ================= DASHBOARD =================
    public function dashboard()
    {
        $user = auth()->user();

        if (!$user) {
            return redirect('/login');
        }

        return view('dashboard', [
            'credentials' => $user->webauthnCredentials ?? collect(),

            'activities' => LoginActivity::where('user_id', $user->id)
                ->latest()
                ->take(10)
                ->get()
        ]);
    }

    // ================= LOGOUT =================
    public function logout()
    {
        Auth::logout();

        return redirect('/login');
    }

    // ================= DELETE PASSKEY =================
    public function deletePasskey($id)
    {
        $user = Auth::user();

        if (!$user) {
            return redirect('/login');
        }

        $credential = $user->webauthnCredentials()
            ->findOrFail($id);

        $credential->delete();

        return back()->with('success', 'Passkey deleted successfully');
    }
}