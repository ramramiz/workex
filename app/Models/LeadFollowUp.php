<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LeadFollowUp extends Model
{
    protected $fillable = ['lead_id', 'user_id', 'note', 'next_follow_up', 'follow_up_time', 'status', 'reminder_sent'];
    protected $casts = [
        'next_follow_up' => 'date',
        'reminder_sent' => 'boolean',
    ];

    public function lead() { return $this->belongsTo(Lead::class); }
    public function user() { return $this->belongsTo(User::class); }
}
