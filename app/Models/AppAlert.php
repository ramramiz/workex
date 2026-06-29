<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AppAlert extends Model
{
    protected $fillable = ['heading', 'title', 'created_by'];

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function users()
    {
        return $this->hasMany(AppAlertUser::class);
    }
}
