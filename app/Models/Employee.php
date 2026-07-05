<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Employee extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id', 'employee_code', 'department_id', 'designation_id', 'team_leader_id',
        'phone', 'personal_email', 'joining_date', 'salary', 'is_applicable_for_salary', 'salary_type', 'hourly_rate', 'work_type',
        'address', 'emergency_contact', 'blood_group', 'documents', 'google_drive_link', 'status',
    ];

    protected $casts = [
        'joining_date' => 'date',
        'salary' => 'decimal:2',
        'is_applicable_for_salary' => 'boolean',
        'hourly_rate' => 'decimal:2',
        'documents' => 'array',
    ];

    protected static function booted()
    {
        static::addGlobalScope('company', function ($builder) {
            $builder->whereHas('user', function ($q) {
                $q->whereHas('role', fn($r) => $r->where('slug', '!=', 'super-admin'));
            });
        });
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function designation()
    {
        return $this->belongsTo(Designation::class);
    }

    public function teamLeader()
    {
        return $this->belongsTo(User::class, 'team_leader_id');
    }

    public function getNameAttribute(): string
    {
        return $this->user?->name ?? '';
    }

    public function getEmailAttribute(): string
    {
        return $this->user?->email ?? '';
    }

    public function uploadedDocuments()
    {
        return $this->morphMany(Document::class, 'documentable');
    }
}
