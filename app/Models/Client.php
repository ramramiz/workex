<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\BelongsToCompany;

class Client extends Model
{
    use SoftDeletes, BelongsToCompany;

    protected $fillable = [
        'company_name', 'contact_person', 'phone', 'email', 'address',
        'city', 'state', 'country', 'gst_number', 'website', 'notes', 'status', 'created_by', 'company_id',
    ];

    public function projects()
    {
        return $this->hasMany(Project::class);
    }

    public function rooms()
    {
        return $this->hasMany(LeadRoom::class, 'client_id');
    }

    public function leads()
    {
        return $this->hasMany(Lead::class);
    }

    public function invoices()
    {
        return $this->hasMany(Invoice::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function supportTickets()
    {
        return $this->hasMany(SupportTicket::class);
    }

    public function getTotalPaidAttribute(): float
    {
        return $this->payments()->sum('amount');
    }
}
