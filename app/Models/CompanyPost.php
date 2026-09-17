<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CompanyPost extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'type',
        'content',
        'image',
        'status',
        'created_by',
    ];

    /**
     * User yang membuat informasi.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Scope untuk mengambil artikel yang sudah dipublish.
     *
     * Artikel langsung dianggap tersedia ketika
     * status = published.
     */
    public function scopePublished($query)
    {
        return $query->where('status', 'published');
    }
}