<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AppAlertUser extends Model
{
    protected $table = 'app_alert_users';

    protected $fillable = ['app_alert_id', 'user_id', 'confirmed_at'];

    protected $casts = [
        'confirmed_at' => 'datetime',
    ];

    public function alert()
    {
        return $this->belongsTo(AppAlert::class, 'app_alert_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
