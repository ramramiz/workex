<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LeadAppointment extends Model
{
    protected $fillable = [
        'lead_id',
        'sales_executive_id',
        'meeting_date_time',
        'type',
        'status',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'meeting_date_time' => 'datetime',
    ];

    public function lead()
    {
        return $this->belongsTo(Lead::class);
    }

    public function salesExecutive()
    {
        return $this->belongsTo(User::class, 'sales_executive_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
