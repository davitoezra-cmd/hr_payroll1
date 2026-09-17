<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AttendanceQr extends Model
{
    protected $fillable = [
        'name',
        'token',
        'image',
        'is_active',
        'expired_at',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'expired_at' => 'datetime',
    ];
}