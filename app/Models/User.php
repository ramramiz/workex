<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;

use App\Traits\BelongsToCompany;

class User extends Authenticatable
{
    use HasFactory, Notifiable, BelongsToCompany;

    protected $fillable = [
        'name', 'email', 'password', 'role_id', 'status', 'avatar',
        'last_login_at', 'last_login_ip', 'email_verified_at',
        'mailbox_imap_enabled', 'mailbox_imap_host', 'mailbox_imap_port',
        'mailbox_imap_encryption', 'mailbox_imap_username', 'mailbox_imap_password',
        'mailbox_smtp_host', 'mailbox_smtp_port', 'mailbox_smtp_encryption',
        'mailbox_smtp_username', 'mailbox_smtp_password',
        'active_room_work_session_id', 'company_id',
    ];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'last_login_at' => 'datetime',
            'password' => 'hashed',
            'mailbox_imap_password' => 'encrypted',
            'mailbox_imap_enabled' => 'boolean',
            'mailbox_smtp_password' => 'encrypted',
        ];
    }

    protected static function booted()
    {
        static::created(function ($user) {
            $user->loadMissing('role');
            if ($user->role && $user->role->slug !== 'client') {
                Employee::firstOrCreate(
                    ['user_id' => $user->id],
                    [
                        'employee_code' => 'EMP' . str_pad($user->id, 4, '0', STR_PAD_LEFT),
                        'status' => 'active',
                        'joining_date' => now()->toDateString(),
                    ]
                );
            }
        });
    }

    // Relationships
    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    public function emails()
    {
        return $this->hasMany(UserEmail::class);
    }

    public function employee()
    {
        return $this->hasOne(Employee::class);
    }

    public function managedProjects()
    {
        return $this->hasMany(Project::class, 'manager_id');
    }

    public function ledProjects()
    {
        return $this->hasMany(Project::class, 'team_leader_id');
    }

    public function assignedTasks()
    {
        return $this->hasMany(Task::class, 'assigned_to');
    }
    public function assignedLeads()
    {
        return $this->hasMany(Lead::class, 'assigned_to');
    }

    public function rooms()
    {
        return $this->belongsToMany(LeadRoom::class, 'lead_room_user', 'user_id', 'lead_room_id');
    }

    public function activeRoomWorkSession()
    {
        return $this->belongsTo(LeadRoomWorkSession::class, 'active_room_work_session_id');
    }

    public function workSessions()
    {
        return $this->hasMany(WorkSession::class);
    }

    public function todayWorkSession()
    {
        return $this->hasOne(WorkSession::class)->whereDate('date', today());
    }

    public function timeLogs()
    {
        return $this->hasMany(TaskTimeLog::class);
    }

    public function dailyReports()
    {
        return $this->hasMany(DailyReport::class);
    }

    public function attendance()
    {
        return $this->hasMany(Attendance::class);
    }

    public function leaves()
    {
        return $this->hasMany(Leave::class);
    }

    public function notifications()
    {
        return $this->hasMany(AppNotification::class);
    }

    public function unreadNotifications()
    {
        return $this->hasMany(AppNotification::class)->whereNull('read_at');
    }

    // Role helpers
    public function hasRole(string $slug): bool
    {
        return $this->role?->slug === $slug;
    }

    public function isSuperAdmin(): bool { return $this->hasRole('super-admin'); }
    public function isAdmin(): bool { return $this->hasRole('admin'); }
    public function isTeamLeader(): bool { return $this->hasRole('team-leader'); }
    public function isEmployee(): bool { return $this->hasRole('employee'); }
    public function isHR(): bool { return $this->hasRole('hr'); }
    public function isAccounts(): bool { return $this->hasRole('accounts'); }
    public function isClient(): bool { return $this->hasRole('client'); }
    public function isTelecaller(): bool { return $this->hasRole('telecaller'); }
    public function isReseller(): bool { return $this->hasRole('reseller'); }
    public function isAdminOrAbove(): bool { return in_array($this->role?->slug, ['super-admin', 'admin']); }
    public function isLeaderOrAbove(): bool { return in_array($this->role?->slug, ['super-admin', 'admin', 'team-leader']); }

    public function getAvatarUrlAttribute(): string
    {
        if ($this->avatar) return asset('storage/' . $this->avatar);
        return 'https://ui-avatars.com/api/?name=' . urlencode($this->name) . '&background=6366f1&color=fff';
    }

    public function getIsWorkingTodayAttribute(): bool
    {
        return $this->todayWorkSession?->status === 'active';
    }
}
