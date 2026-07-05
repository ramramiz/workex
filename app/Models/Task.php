<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

use App\Traits\BelongsToCompany;

class Task extends Model
{
    use SoftDeletes, BelongsToCompany;

    protected static function boot()
    {
        parent::boot();

        static::saving(function ($task) {
            if ($task->project_id) {
                $project = Project::find($task->project_id);
                if ($project && in_array($project->status, ['completed', 'delivered', 'cancelled', 'completed_started_amc'])) {
                    $task->priority = 'special';
                }
            }
        });
    }

    protected $fillable = [
        'project_id', 'assigned_to', 'created_by', 'title', 'description',
        'priority', 'estimated_hours', 'actual_hours', 'start_date', 'deadline',
        'completed_date', 'status', 'progress_percentage', 'order', 'meeting_id',
        'completed_description', 'completed_link', 'company_id',
        'team_leader_approved', 'team_leader_approved_by', 'team_leader_approved_at',
    ];

    protected $casts = [
        'start_date' => 'date',
        'deadline' => 'date',
        'completed_date' => 'date',
        'estimated_hours' => 'decimal:2',
        'actual_hours' => 'decimal:2',
        'team_leader_approved' => 'boolean',
        'team_leader_approved_at' => 'datetime',
    ];

    public function project() { return $this->belongsTo(Project::class); }
    public function meeting() { return $this->belongsTo(Meeting::class); }
    public function teamLeaderApprovedBy() { return $this->belongsTo(User::class, 'team_leader_approved_by'); }
    public function assignee() { return $this->belongsTo(User::class, 'assigned_to'); }
    public function creator() { return $this->belongsTo(User::class, 'created_by'); }
    public function comments() { return $this->hasMany(TaskComment::class); }
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
            'special' => 'info',
            default => 'secondary',
        };
    }

    public function getAvatarUrlAttribute(): string
    {
        if ($this->project && $this->project->logo_path) {
            return asset('storage/' . $this->project->logo_path);
        }

        if (str_starts_with($this->title, 'Room Calling: ')) {
            $roomName = substr($this->title, strlen('Room Calling: '));
            $colors = ['f43f5e', 'ec4899', 'd946ef', 'a855f7', '8b5cf6', '6366f1', '3b82f6', '0ea5e9', '06b6d4', '14b8a6', '10b981', '22c55e', '84cc16', 'eab308', 'f97316'];
            $hash = crc32($roomName);
            $color = $colors[abs($hash) % count($colors)];
            return 'https://ui-avatars.com/api/?name=' . urlencode($roomName) . '&background=' . $color . '&color=fff';
        }

        if ($this->assignee) {
            return $this->assignee->avatar_url;
        }

        return 'https://ui-avatars.com/api/?name=Unassigned&background=cbd5e1&color=64748b';
    }
}
