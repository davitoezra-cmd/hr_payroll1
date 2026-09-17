<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BPJSPaymentProof extends Model
{
     protected $table = 'bpjs_payment_proofs';
     protected $appends = ['file_url'];
    protected $fillable = [

        'finance_id',

        'employee_id',

        'bpjs_type',

        'period',

        'document_name',

        'file_path',

        'notes',

    ];

    public function finance()
    {
        return $this->belongsTo(Finance::class);
    }
    public function employee()
{
    return $this->belongsTo(Employee::class);
}

    public function getFileUrlAttribute()
    {
        return asset('storage/' . $this->file_path);
    }
}