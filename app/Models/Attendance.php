<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    protected $table = 'attendance';

    protected $fillable = [
        'user_id', 'date', 'login_time', 'logout_time', 'total_minutes',
        'type', 'status', 'late_minutes', 'early_logout', 'notes',
    ];

    protected static function booted()
    {
        static::addGlobalScope('company', function ($builder) {
            $builder->whereHas('user');
        });
    }

    protected $casts = [
        'date' => 'date',
        'login_time' => 'datetime',
        'logout_time' => 'datetime',
        'early_logout' => 'boolean',
    ];

    public function user() { return $this->belongsTo(User::class); }

    public function getTotalHoursAttribute(): string
    {
        $h = intdiv($this->total_minutes, 60);
        $m = $this->total_minutes % 60;
        return sprintf('%02d:%02d', $h, $m);
    }
}
