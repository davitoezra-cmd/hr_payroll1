<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FaceIdentityAccount extends Model
{
    protected $fillable = [
        'face_identity_id',
        'account_type',
        'account_id',
    ];

    public function identity(): BelongsTo
    {
        return $this->belongsTo(FaceIdentity::class, 'face_identity_id');
    }
}
