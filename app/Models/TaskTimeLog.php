<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TaskTimeLog extends Model
{
    protected $fillable = [
        'task_id', 'user_id', 'work_session_id', 'started_at', 'paused_at',
        'resumed_at', 'ended_at', 'total_minutes', 'break_minutes', 'note', 'status',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'paused_at' => 'datetime',
        'resumed_at' => 'datetime',
        'ended_at' => 'datetime',
    ];

    public function task() { return $this->belongsTo(Task::class); }
    public function user() { return $this->belongsTo(User::class); }
    public function workSession() { return $this->belongsTo(WorkSession::class); }
}
