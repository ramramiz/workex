<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\BelongsToCompany;

class Expense extends Model
{
    use SoftDeletes, BelongsToCompany;

    protected $fillable = [
        'project_id', 'category', 'title', 'description', 'amount', 'payment_mode', 'date', 'attachment', 'added_by', 'status', 'company_id',
    ];

    protected $casts = [
        'date' => 'date',
        'amount' => 'decimal:2',
    ];

    public function project() { return $this->belongsTo(Project::class); }
    public function addedBy() { return $this->belongsTo(User::class, 'added_by'); }
}
