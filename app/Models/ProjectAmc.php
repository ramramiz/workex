<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\BelongsToCompany;

class ProjectAmc extends Model
{
    use SoftDeletes, BelongsToCompany;

    protected $fillable = [
        'project_id',
        'amount',
        'start_date',
        'end_date',
        'frequency',
        'status', // active, expired, pending_renewal
        'remarks',
        'company_id',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date'   => 'date',
        'amount'     => 'decimal:2',
    ];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function logs()
    {
        return $this->hasMany(ProjectAmcLog::class);
    }

    public function getStatusBadgeAttribute()
    {
        return match($this->status) {
            'active'          => 'success',
            'expired'         => 'danger',
            'pending_renewal' => 'warning',
            default           => 'secondary',
        };
    }
}
