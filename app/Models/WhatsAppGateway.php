<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WhatsAppGateway extends Model
{
    use HasFactory;

    protected $table = 'whatsapp_gateways';

    protected $fillable = [
        'name',
        'provider',
        'url',
        'api_id',
        'api_key',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];
}

