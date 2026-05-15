<?php
// app/Models/User.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laragear\WebAuthn\Contracts\WebAuthnAuthenticatable;
use Laragear\WebAuthn\WebAuthnAuthentication;

class User extends Authenticatable implements WebAuthnAuthenticatable
{
    use HasFactory, Notifiable, WebAuthnAuthentication;

    protected $fillable = [
        'name',
        'email',
        'password',
        'webauthn_required',
        'email_verified',
        'last_passkey_login'
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'webauthn_required' => 'boolean',
            'email_verified' => 'boolean',
            'last_passkey_login' => 'datetime',
        ];
    }

    public function loginActivities()
    {
        return $this->hasMany(LoginActivity::class);
    }

    public function recoveryCodes()
    {
        return $this->hasMany(RecoveryCode::class);
    }

    public function trustedDevices()
    {
        return $this->hasMany(TrustedDevice::class);
    }

    public function getSecurityScore()
    {
        $score = 0;
        
        if ($this->webauthnCredentials()->count() > 0) $score += 40;
        if (strlen($this->password) > 10) $score += 20;
        if ($this->email_verified) $score += 20;
        if ($this->webauthn_required) $score += 20;
        
        return min($score, 100);
    }
}