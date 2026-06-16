<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

use App\Traits\BelongsToCompany;

class SupportTicket extends Model
{
    use SoftDeletes, BelongsToCompany;

    protected $fillable = [
        'ticket_number', 'client_id', 'project_id', 'title', 'description',
        'priority', 'assigned_to', 'amc_start_date', 'amc_end_date',
        'amc_renewal_notified', 'status', 'attachments', 'created_by', 'company_id',
    ];

    protected $casts = [
        'amc_start_date' => 'date',
        'amc_end_date' => 'date',
        'attachments' => 'array',
        'amc_renewal_notified' => 'boolean',
    ];

    public function client() { return $this->belongsTo(Client::class); }
    public function project() { return $this->belongsTo(Project::class); }
    public function assignedTo() { return $this->belongsTo(User::class, 'assigned_to'); }
    public function createdBy() { return $this->belongsTo(User::class, 'created_by'); }
    public function replies() { return $this->hasMany(TicketReply::class); }

    public function getAmcExpiringAttribute(): bool
    {
        return $this->amc_end_date && $this->amc_end_date->diffInDays(now()) <= 30 && $this->amc_end_date->isFuture();
    }
}
