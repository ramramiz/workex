<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WorkBreak extends Model
{
    protected $fillable = ['work_session_id', 'user_id', 'started_at', 'ended_at', 'duration_minutes', 'reason'];
    protected $casts = ['started_at' => 'datetime', 'ended_at' => 'datetime'];

    public function workSession() { return $this->belongsTo(WorkSession::class); }
    public function user() { return $this->belongsTo(User::class); }
}
