<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\BelongsToCompany;

class Project extends Model
{
    use SoftDeletes, BelongsToCompany;

    protected $fillable = [
        'quotation_id', 'client_id', 'project_code', 'name', 'description',
        'project_type', 'technologies', 'start_date', 'deadline', 'completed_date',
        'budget', 'project_value', 'advance_amount', 'balance_amount',
        'manager_id', 'team_leader_id', 'priority', 'status',
        'progress_percentage', 'notes', 'created_by', 'company_id',
    ];

    protected $casts = [
        'start_date' => 'date',
        'deadline' => 'date',
        'completed_date' => 'date',
        'project_value' => 'decimal:2',
        'advance_amount' => 'decimal:2',
        'balance_amount' => 'decimal:2',
        'budget' => 'decimal:2',
        'technology' => 'array',
    ];

    public function getTechnologiesAttribute()
    {
        return $this->technology ?? [];
    }

    public function setTechnologiesAttribute($value): void
    {
        $this->technology = $value;
    }

    public function getProjectTypeAttribute()
    {
        return $this->type;
    }

    public function setProjectTypeAttribute($value): void
    {
        $this->type = $value;
    }

    public function client() { return $this->belongsTo(Client::class); }
    public function manager() { return $this->belongsTo(User::class, 'manager_id'); }
    public function teamLeader() { return $this->belongsTo(User::class, 'team_leader_id'); }
    public function quotation() { return $this->belongsTo(Quotation::class); }
    public function tasks() { return $this->hasMany(Task::class); }
    public function members() { return $this->belongsToMany(User::class, 'project_members')->withPivot('role')->withTimestamps(); }
    public function invoices() { return $this->hasMany(Invoice::class); }
    public function payments() { return $this->hasMany(Payment::class); }
    public function expenses() { return $this->hasMany(Expense::class); }
    public function bugs() { return $this->hasMany(Bug::class); }

    public function getIsDelayedAttribute(): bool
    {
        return $this->deadline && $this->deadline->isPast() && !in_array($this->status, ['completed', 'delivered', 'cancelled']);
    }

    public function getTotalExpenseAttribute(): float
    {
        return $this->expenses()->sum('amount');
    }

    public function getTotalPaidAttribute(): float
    {
        return $this->payments()->sum('amount');
    }

    public function getProfitLossAttribute(): float
    {
        return $this->project_value - $this->total_expense;
    }

    public function getStatusBadgeAttribute(): string
    {
        return match($this->status) {
            'not_started' => 'secondary',
            'planning' => 'info',
            'design' => 'primary',
            'development' => 'warning',
            'testing' => 'warning',
            'client_review' => 'info',
            'rework' => 'danger',
            'completed' => 'success',
            'delivered' => 'success',
            'on_hold' => 'warning',
            'cancelled' => 'danger',
            default => 'secondary',
        };
    }

    public function getProgressPercentageAttribute($value): int
    {
        if ($value) return (int) $value;
        $total = $this->tasks()->count();
        if (!$total) return 0;
        $completed = $this->tasks()->where('status', 'completed')->count();
        return (int) round(($completed / $total) * 100);
    }

    public function getBudgetAttribute(): float
    {
        return (float) ($this->attributes['project_value'] ?? 0);
    }

    public function setBudgetAttribute($value): void
    {
        $this->attributes['project_value'] = $value;
    }
}
