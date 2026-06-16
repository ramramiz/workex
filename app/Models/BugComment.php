<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BugComment extends Model
{
    protected $fillable = ['bug_id', 'user_id', 'comment'];
    public function bug() { return $this->belongsTo(Bug::class); }
    public function user() { return $this->belongsTo(User::class); }
}
