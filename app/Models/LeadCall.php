<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LeadCall extends Model
{
    protected $fillable = [
        'lead_id',
        'telecaller_id',
        'call_date_time',
        'status',
        'customer_response',
        'next_action',
        'remarks',
        'duration',
    ];

    protected $casts = [
        'call_date_time' => 'datetime',
    ];

    public function lead()
    {
        return $this->belongsTo(Lead::class);
    }

    public function telecaller()
    {
        return $this->belongsTo(User::class, 'telecaller_id');
    }
}
