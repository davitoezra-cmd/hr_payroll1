<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmployeeFaceTemplate extends Model
{
    protected $fillable = [
        'employee_id',
        'embedding',
        'engine',
        'engine_version',
        'model_name',
        'embedding_dimension',
        'is_active',
        'enrolled_at',
        'last_verified_at',
    ];

    protected $hidden = [
        'embedding',
    ];

    protected function casts(): array
    {
        return [
            // Laravel encrypts/decrypts the vector using APP_KEY.
            'embedding' => 'encrypted:array',
            'embedding_dimension' => 'integer',
            'is_active' => 'boolean',
            'enrolled_at' => 'datetime',
            'last_verified_at' => 'datetime',
        ];
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }
}
