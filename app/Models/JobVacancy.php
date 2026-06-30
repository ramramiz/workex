<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\BelongsToCompany;
use Illuminate\Support\Str;

class JobVacancy extends Model
{
    use SoftDeletes, BelongsToCompany;

    protected $fillable = [
        'company_id',
        'department_id',
        'title',
        'description',
        'requirements',
        'location',
        'job_type',
        'status',
        'token',
    ];

    protected static function booted()
    {
        static::creating(function ($vacancy) {
            if (empty($vacancy->token)) {
                do {
                    $token = Str::random(10);
                } while (static::where('token', $token)->exists());
                $vacancy->token = $token;
            }
        });
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function applications()
    {
        return $this->hasMany(JobApplication::class);
    }
}
