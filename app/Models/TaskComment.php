<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TaskComment extends Model
{
    use \Illuminate\Database\Eloquent\SoftDeletes;
    protected $fillable = ['task_id', 'user_id', 'comment', 'parent_id', 'image_path'];

    public function task() { return $this->belongsTo(Task::class); }
    public function user() { return $this->belongsTo(User::class); }
    public function replies() { return $this->hasMany(TaskComment::class, 'parent_id'); }
    public function parent() { return $this->belongsTo(TaskComment::class, 'parent_id'); }
    public function views() { return $this->hasMany(TaskCommentView::class, 'task_comment_id'); }
}
