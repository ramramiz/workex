<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WorkSession extends Model
{
    protected $fillable = [
        'user_id', 'date', 'started_at', 'ended_at', 'total_minutes',
        'productive_minutes', 'break_minutes', 'ip_address', 'device_type',
        'browser', 'device_info', 'status', 'work_done',
    ];

    protected static function booted()
    {
        static::addGlobalScope('company', function ($builder) {
            $builder->whereHas('user');
        });
    }

    protected $casts = [
        'date' => 'date',
        'started_at' => 'datetime',
        'ended_at' => 'datetime',
    ];

    public function user() { return $this->belongsTo(User::class); }
    public function timeLogs() { return $this->hasMany(TaskTimeLog::class); }
    public function breaks() { return $this->hasMany(WorkBreak::class); }

    public function getActiveTaskLogAttribute()
    {
        return $this->timeLogs()->where('status', 'running')->with('task')->latest()->first();
    }

    public function getTotalMinutesAttribute($value)
    {
        if ($this->status === 'active') {
            $firstLog = $this->timeLogs()->orderBy('started_at', 'asc')->first();
            if ($firstLog && $firstLog->started_at) {
                return $firstLog->started_at->diffInMinutes(now());
            }
            return $this->started_at->diffInMinutes(now());
        }
        return $value ?? 0;
    }

    public function getTotalHoursAttribute(): string
    {
        $hours = intdiv($this->total_minutes, 60);
        $mins = $this->total_minutes % 60;
        return sprintf('%02d:%02d', $hours, $mins);
    }

    public function getProductiveHoursAttribute(): string
    {
        $hours = intdiv($this->productive_minutes, 60);
        $mins = $this->productive_minutes % 60;
        return sprintf('%02d:%02d', $hours, $mins);
    }
}
