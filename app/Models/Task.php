<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

use App\Traits\BelongsToCompany;

class Task extends Model
{
    use SoftDeletes, BelongsToCompany;

    protected $fillable = [
        'project_id', 'assigned_to', 'created_by', 'title', 'description',
        'priority', 'estimated_hours', 'actual_hours', 'start_date', 'deadline',
        'completed_date', 'status', 'progress_percentage', 'order', 'meeting_id',
        'completed_description', 'completed_link', 'company_id',
    ];

    protected $casts = [
        'start_date' => 'date',
        'deadline' => 'date',
        'completed_date' => 'date',
        'estimated_hours' => 'decimal:2',
        'actual_hours' => 'decimal:2',
    ];

    public function project() { return $this->belongsTo(Project::class); }
    public function meeting() { return $this->belongsTo(Meeting::class); }
    public function assignee() { return $this->belongsTo(User::class, 'assigned_to'); }
    public function creator() { return $this->belongsTo(User::class, 'created_by'); }
    public function comments() { return $this->hasMany(TaskComment::class)->whereNull('parent_id'); }
    public function allComments() { return $this->hasMany(TaskComment::class); }
    public function files() { return $this->hasMany(TaskFile::class); }
    public function timeLogs() { return $this->hasMany(TaskTimeLog::class); }

    public function getActiveTimeLogAttribute()
    {
        return $this->timeLogs()->where('status', 'running')->latest()->first();
    }

    public function getIsDelayedAttribute(): bool
    {
        return $this->deadline && $this->deadline->isPast() && !in_array($this->status, ['completed', 'cancelled']);
    }

    public function getPriorityBadgeAttribute(): string
    {
        return match($this->priority) {
            'low' => 'success',
            'medium' => 'warning',
            'high' => 'danger',
            'critical' => 'dark',
            default => 'secondary',
        };
    }
}
