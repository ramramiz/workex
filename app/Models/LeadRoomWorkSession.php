<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LeadRoomWorkSession extends Model
{
    protected $fillable = [
        'user_id',
        'lead_room_id',
        'started_at',
        'ended_at',
        'total_seconds',
        'calls_count',
        'converted_count',
        'status',
        'approved_by',
        'approved_at',
    ];

    protected static function booted()
    {
        static::addGlobalScope('company', function ($builder) {
            $builder->whereHas('user');
        });
    }

    protected $casts = [
        'started_at' => 'datetime',
        'ended_at' => 'datetime',
        'approved_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function room()
    {
        return $this->belongsTo(LeadRoom::class, 'lead_room_id');
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
