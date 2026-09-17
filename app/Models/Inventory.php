<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Inventory extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'inventory_code',
        'name',
        'category',
        'description',
        'quantity',
        'condition',
        'location',
        'status',
        'image',
    ];

    protected $casts = [
        'quantity' => 'integer',
    ];
}