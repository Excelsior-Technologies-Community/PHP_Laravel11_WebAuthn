<?php

namespace App\Http\Controllers\WebAuthn;

use Illuminate\Contracts\Support\Responsable;
use Illuminate\Http\Response;
use Laragear\WebAuthn\Http\Requests\AttestationRequest;
use Laragear\WebAuthn\Http\Requests\AttestedRequest;
use App\Models\LoginActivity;
use App\Models\TrustedDevice;
use App\Notifications\NewDeviceLoginNotification;
use Jenssegers\Agent\Agent;

class WebAuthnRegisterController
{
    public function options(AttestationRequest $request): Responsable
    {
        return $request
            ->fastRegistration()
            ->toCreate();
    }

    public function register(AttestedRequest $request): Response
    {
        $credential = $request->save();
        $user = $request->user();
        $agent = new Agent();
        $ip = $request->ip();

        $activity = LoginActivity::create([
            'user_id' => $user->id,
            'ip_address' => $ip,
            'browser' => $agent->browser() . ' - ' . $agent->platform(),
            'login_method' => 'Passkey Registered',
            'device_type' => $agent->device(),
            'device_name' => $credential->aaguid ?? 'Security Key'
        ]);

        $identifier = hash('sha256', $request->userAgent() . $ip);
        
        if (!TrustedDevice::where('user_id', $user->id)->where('device_identifier', $identifier)->exists()) {
            $user->notify(new NewDeviceLoginNotification($activity));
        }

        return response()->noContent();
    }
}