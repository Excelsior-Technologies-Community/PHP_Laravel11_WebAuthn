<?php
// app/Models/RecoveryCode.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RecoveryCode extends Model
{
    protected $fillable = [
        'user_id',
        'code',
        'used'
    ];

    protected $casts = [
        'used' => 'boolean'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}