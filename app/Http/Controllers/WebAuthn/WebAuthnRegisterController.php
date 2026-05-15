<?php
// app/Http/Controllers/WebAuthn/WebAuthnRegisterController.php

namespace App\Http\Controllers\WebAuthn;

use Illuminate\Contracts\Support\Responsable;
use Illuminate\Http\Response;
use Laragear\WebAuthn\Http\Requests\AttestationRequest;
use Laragear\WebAuthn\Http\Requests\AttestedRequest;
use App\Models\LoginActivity;
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
        
        // Log the registration
        $agent = new Agent();
        LoginActivity::create([
            'user_id' => $request->user()->id,
            'ip_address' => $request->ip(),
            'browser' => $agent->browser() . ' - ' . $agent->platform(),
            'login_method' => 'Passkey Registered',
            'device_type' => $agent->device(),
            'device_name' => $credential->aaguid ?? 'Security Key'
        ]);
        
        return response()->noContent();
    }
}