<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TaskCommentView extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'task_comment_id', 'user_id', 'viewed_at',
    ];

    protected $casts = [
        'viewed_at' => 'datetime',
    ];

    public function comment()
    {
        return $this->belongsTo(TaskComment::class, 'task_comment_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
