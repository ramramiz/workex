<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProjectAmcLog extends Model
{
    protected $fillable = [
        'project_amc_id',
        'payment_date',
        'amount_paid',
        'payment_mode',
        'reference_no',
        'remarks',
    ];

    protected $casts = [
        'payment_date' => 'date',
        'amount_paid'  => 'decimal:2',
    ];

    public function projectAmc()
    {
        return $this->belongsTo(ProjectAmc::class);
    }
}
