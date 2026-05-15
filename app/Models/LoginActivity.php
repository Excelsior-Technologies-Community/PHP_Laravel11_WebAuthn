<?php
// app/Models/LoginActivity.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LoginActivity extends Model
{
    protected $fillable = [
        'user_id',
        'ip_address',
        'browser',
        'login_method',
        'country',
        'city',
        'device_type',
        'device_name',
        'is_trusted'
    ];

    protected $casts = [
        'is_trusted' => 'boolean'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}