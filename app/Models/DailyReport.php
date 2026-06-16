<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DailyReport extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id', 'date', 'completed_work', 'pending_work', 'issues_faced',
        'tomorrow_plan', 'git_commit_link', 'attachments', 'status',
        'reviewer_id', 'reviewer_comment', 'reviewed_at',
    ];

    protected $casts = [
        'date' => 'date',
        'attachments' => 'array',
        'reviewed_at' => 'datetime',
    ];

    public function user() { return $this->belongsTo(User::class); }
    public function reviewer() { return $this->belongsTo(User::class, 'reviewer_id'); }
}
