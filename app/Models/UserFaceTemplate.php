<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserFaceTemplate extends Model
{
    protected $fillable = [
        'user_id', 'embedding', 'engine', 'engine_version', 'model_name',
        'embedding_dimension', 'is_active', 'enrolled_at', 'last_verified_at',
    ];

    protected $hidden = ['embedding'];

    protected function casts(): array
    {
        return [
            'embedding' => 'encrypted:array',
            'embedding_dimension' => 'integer',
            'is_active' => 'boolean',
            'enrolled_at' => 'datetime',
            'last_verified_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
