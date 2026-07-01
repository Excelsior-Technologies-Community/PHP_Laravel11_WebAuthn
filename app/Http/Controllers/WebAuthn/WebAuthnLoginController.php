<?php

namespace App\Http\Controllers\WebAuthn;

use Illuminate\Contracts\Support\Responsable;
use Illuminate\Http\Response;
use Laragear\WebAuthn\Http\Requests\AssertedRequest;
use Laragear\WebAuthn\Http\Requests\AssertionRequest;
use App\Models\LoginActivity;
use App\Models\TrustedDevice;
use App\Notifications\NewDeviceLoginNotification;
use Jenssegers\Agent\Agent;

class WebAuthnLoginController
{
    public function options(AssertionRequest $request): Responsable
    {
        return $request->toVerify($request->validate(['email' => 'sometimes|email|string']));
    }

    public function login(AssertedRequest $request): Response
    {
        $success = $request->login();
        
        if ($success) {
            $user = $request->user();
            $agent = new Agent();
            $ip = $request->ip();
            
            $user->update(['last_passkey_login' => now()]);
            
            $activity = LoginActivity::create([
                'user_id' => $user->id,
                'ip_address' => $ip,
                'browser' => $agent->browser() . ' - ' . $agent->platform(),
                'login_method' => 'Passkey',
                'device_type' => $agent->device(),
                'device_name' => $agent->device() ?: 'Unknown'
            ]);
            
            $identifier = hash('sha256', $request->userAgent() . $ip);
            
            if (!TrustedDevice::where('user_id', $user->id)->where('device_identifier', $identifier)->exists()) {
                $user->notify(new NewDeviceLoginNotification($activity));
            }
            
            return response()->noContent(204);
        }
        
        return response()->noContent(422);
    }
}