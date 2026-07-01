<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\LoginActivity;
use App\Models\RecoveryCode;
use App\Models\TrustedDevice;
use App\Notifications\NewDeviceLoginNotification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Jenssegers\Agent\Agent;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    public function register()
    {
        return view('register');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:8'
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password)
        ]);

        Auth::login($user);
        $this->logActivity($user, 'Register', $request);
        $this->generateRecoveryCodes($user);

        return redirect('/dashboard');
    }

    public function login()
    {
        return view('login');
    }

    public function passkeyLogin()
    {
        return view('passkey-login');
    }

    public function authenticate(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required'
        ]);

        if (Auth::attempt($request->only('email', 'password'))) {
            $user = Auth::user();

            if ($user->webauthn_required && $user->webauthnCredentials()->count() > 0) {
                Auth::logout();
                return redirect('/passkey-login')->with('email', $request->email)
                    ->with('error', 'This account requires passkey authentication');
            }

            $this->logActivity($user, 'Password', $request);
            
            return redirect('/dashboard');
        }

        return back()->with('error', 'Invalid login credentials');
    }

    public function dashboard()
    {
        $user = auth()->user();
        if (!$user) return redirect('/login');

        $credentials = $user->webauthnCredentials()->get();
        $activities = LoginActivity::where('user_id', $user->id)->latest()->take(20)->get();
        $trustedDevices = TrustedDevice::where('user_id', $user->id)->get();
        $securityScore = $user->getSecurityScore();
        $recoveryCodes = RecoveryCode::where('user_id', $user->id)->where('used', false)->get();

        return view('dashboard', compact('credentials', 'activities', 'trustedDevices', 'securityScore', 'recoveryCodes'));
    }

    public function logout()
    {
        Auth::logout();
        return redirect('/login');
    }

    public function deletePasskey($id)
    {
        $user = Auth::user();
        $user->webauthnCredentials()->findOrFail($id)->delete();
        return back()->with('success', 'Passkey deleted successfully');
    }

    public function updateWebauthnRequired(Request $request)
    {
        $user = Auth::user();
        $user->webauthn_required = $request->enabled;
        $user->save();
        return response()->json(['success' => true]);
    }

    public function trustDevice(Request $request)
    {
        $user = Auth::user();
        $agent = new Agent();

        TrustedDevice::updateOrCreate(
            [
                'user_id' => $user->id,
                'device_identifier' => hash('sha256', $request->userAgent() . $request->ip())
            ],
            [
                'device_name' => $agent->device() ?: 'Unknown Device',
                'ip_address' => $request->ip(),
                'browser' => $agent->browser() . ' on ' . $agent->platform()
            ]
        );

        return response()->json(['success' => true]);
    }

    public function generateNewRecoveryCodes()
    {
        $user = Auth::user();
        RecoveryCode::where('user_id', $user->id)->delete();
        $this->generateRecoveryCodes($user);
        
        $codes = RecoveryCode::where('user_id', $user->id)->where('used', false)->pluck('code')->toArray();
        return response()->json(['codes' => $codes]);
    }

  public function verifyRecoveryCode(Request $request)
{
    $request->validate([
        'code' => 'required|string',
        'email' => 'required|email'
    ]);

    $email = strtolower(trim($request->email));
    $code = strtoupper(trim($request->code));

    $user = User::where('email', $email)->first();

    if (!$user) {
        return response()->json(['success' => false, 'message' => 'User not found'], 404);
    }

    $recoveryCode = RecoveryCode::where('user_id', $user->id)
        ->where('code', $code)
        ->where('used', false)
        ->first();

    if (!$recoveryCode) {
        return response()->json(['success' => false, 'message' => 'Invalid code'], 422);
    }

    $recoveryCode->update(['used' => true, 'used_at' => now()]);
    $user->update(['webauthn_required' => false]);

    Auth::login($user, true);
    $request->session()->regenerate();

    return response()->json(['success' => true]);
}
public function updateProfile(Request $request)
{
    $user = Auth::user();

    $request->validate([
        'name' => 'required|string|max:255',
        'email' => 'required|email|unique:users,email,' . $user->id,
        'password' => 'nullable|min:8'
    ]);

    $user->name = $request->name;
    $user->email = $request->email;

    if ($request->filled('password')) {
        $user->password = Hash::make($request->password);
    }

    $user->save();

    return response()->json(['success' => true]);
}
    private function logActivity($user, $method, $request)
    {
        $agent = new Agent();
        $location = $this->getLocationFromIP($request->ip());
        $isTrusted = $this->isTrustedDevice($user, $request);
        
        $activity = LoginActivity::create([
            'user_id' => $user->id,
            'ip_address' => $request->ip(),
            'browser' => $agent->browser() . ' - ' . $agent->platform(),
            'login_method' => $method,
            'device_type' => $agent->device(),
            'device_name' => $agent->device() ?: 'Unknown',
            'country' => $location['country'] ?? null,
            'city' => $location['city'] ?? null,
            'is_trusted' => $isTrusted
        ]);

        if (!$isTrusted) {
            $user->notify(new NewDeviceLoginNotification($activity));
        }
    }

    private function getLocationFromIP($ip)
    {
        try {
            $response = @file_get_contents("http://ip-api.com/json/{$ip}");
            if ($response) {
                $data = json_decode($response, true);
                if ($data && $data['status'] === 'success') {
                    return ['country' => $data['country'], 'city' => $data['city']];
                }
            }
        } catch (\Exception $e) {}
        return ['country' => null, 'city' => null];
    }

    private function isTrustedDevice($user, $request)
    {
        $identifier = hash('sha256', $request->userAgent() . $request->ip());
        return TrustedDevice::where('user_id', $user->id)->where('device_identifier', $identifier)->exists();
    }

    private function generateRecoveryCodes($user)
    {
        $codes = [];
        for ($i = 0; $i < 8; $i++) {
            $codes[] = [
                'user_id' => $user->id,
                'code' => Str::upper(Str::random(4) . '-' . Str::random(4)),
                'created_at' => now(),
                'updated_at' => now()
            ];
        }
        RecoveryCode::insert($codes);
    }
}