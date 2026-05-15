<?php
// app/Models/TrustedDevice.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TrustedDevice extends Model
{
    protected $fillable = [
        'user_id',
        'device_identifier',
        'device_name',
        'ip_address',
        'browser'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}