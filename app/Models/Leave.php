<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Leave extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id', 'leave_type', 'from_date', 'to_date', 'total_days', 'reason',
        'status', 'team_leader_id', 'team_leader_status', 'team_leader_comment', 'team_leader_at',
        'hr_id', 'hr_status', 'hr_comment', 'hr_at', 'attachments',
    ];

    protected static function booted()
    {
        static::addGlobalScope('company', function ($builder) {
            $builder->whereHas('user');
        });
    }

    protected $casts = [
        'from_date' => 'date',
        'to_date' => 'date',
        'team_leader_at' => 'datetime',
        'hr_at' => 'datetime',
        'attachments' => 'array',
        'total_days' => 'decimal:1',
    ];

    public function user() { return $this->belongsTo(User::class); }
    public function teamLeader() { return $this->belongsTo(User::class, 'team_leader_id'); }
    public function hr() { return $this->belongsTo(User::class, 'hr_id'); }
}
