<?php
// app/Http/Controllers/WebAuthn/WebAuthnLoginController.php

namespace App\Http\Controllers\WebAuthn;

use Illuminate\Contracts\Support\Responsable;
use Illuminate\Http\Response;
use Laragear\WebAuthn\Http\Requests\AssertedRequest;
use Laragear\WebAuthn\Http\Requests\AssertionRequest;
use App\Models\LoginActivity;
use App\Models\User;
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
            
            // Update last passkey login
            $user->last_passkey_login = now();
            $user->save();
            
            // Log the activity
            $agent = new Agent();
            LoginActivity::create([
                'user_id' => $user->id,
                'ip_address' => $request->ip(),
                'browser' => $agent->browser() . ' - ' . $agent->platform(),
                'login_method' => 'Passkey',
                'device_type' => $agent->device(),
                'device_name' => $agent->device() ?: 'Unknown'
            ]);
            
            return response()->noContent(204);
        }
        
        return response()->noContent(422);
    }
}