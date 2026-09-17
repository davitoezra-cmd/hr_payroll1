<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FaceIdentity extends Model
{
    protected $fillable = [
        'embedding',
        'engine',
        'engine_version',
        'model_name',
        'embedding_dimension',
        'is_active',
        'enrolled_at',
        'last_verified_at',
    ];

    protected function casts(): array
    {
        return [
            'embedding' => 'encrypted:array',
            'is_active' => 'boolean',
            'enrolled_at' => 'datetime',
            'last_verified_at' => 'datetime',
        ];
    }

    public function accounts(): HasMany
    {
        return $this->hasMany(FaceIdentityAccount::class);
    }
}
