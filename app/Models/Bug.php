<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\BelongsToCompany;

class Bug extends Model
{
    use SoftDeletes, BelongsToCompany;

    protected $fillable = [
        'project_id', 'task_id', 'title', 'description', 'reported_by', 'assigned_to',
        'priority', 'screenshots', 'browser_info', 'os_info', 'steps_to_reproduce', 'status', 'company_id',
    ];

    protected $casts = ['screenshots' => 'array'];

    public function project() { return $this->belongsTo(Project::class); }
    public function task() { return $this->belongsTo(Task::class); }
    public function reportedBy() { return $this->belongsTo(User::class, 'reported_by'); }
    public function assignedTo() { return $this->belongsTo(User::class, 'assigned_to'); }
    public function comments() { return $this->hasMany(BugComment::class); }
}
