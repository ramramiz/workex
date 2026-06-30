<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\BelongsToCompany;

class JobApplication extends Model
{
    use SoftDeletes, BelongsToCompany;

    protected $fillable = [
        'company_id',
        'job_vacancy_id',
        'name',
        'gender',
        'dob',
        'qualification',
        'email',
        'state',
        'district',
        'home_town',
        'experience_years',
        'salary_expectation',
        'ready_to_relocate',
        'linkedin_id',
        'phone',
        'resume_path',
        'cover_letter',
        'status',
        'interview_date',
        'interview_time',
        'interview_venue',
    ];

    public function vacancy()
    {
        return $this->belongsTo(JobVacancy::class, 'job_vacancy_id');
    }
}
