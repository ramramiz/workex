<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\BelongsToCompany;

class HiringMailLog extends Model
{
    use BelongsToCompany;

    protected $fillable = [
        'company_id',
        'job_application_id',
        'candidate_name',
        'candidate_email',
        'vacancy_title',
        'subject',
        'interview_date',
        'interview_time',
        'interview_venue',
        'sent_by',
    ];

    public function application()
    {
        return $this->belongsTo(JobApplication::class, 'job_application_id')->withTrashed();
    }

    public function sender()
    {
        return $this->belongsTo(User::class, 'sent_by');
    }
}
