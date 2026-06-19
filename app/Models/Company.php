<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Company extends Model
{
    protected $fillable = [
        'name',
        'email',
        'status',
        'auth_person_name',
        'auth_person_email',
        'phone',
        'address',
        'gst'
    ];

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function departments()
    {
        return $this->hasMany(Department::class);
    }

    public function clients()
    {
        return $this->hasMany(Client::class);
    }

    public function leadRooms()
    {
        return $this->hasMany(LeadRoom::class);
    }

    public function projects()
    {
        return $this->hasMany(Project::class);
    }

    public function expenses()
    {
        return $this->hasMany(Expense::class);
    }

    public function bugs()
    {
        return $this->hasMany(Bug::class);
    }

    public function meetings()
    {
        return $this->hasMany(Meeting::class);
    }
}
