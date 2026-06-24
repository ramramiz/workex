<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

use App\Traits\BelongsToCompany;

class Lead extends Model
{
    use SoftDeletes, BelongsToCompany;

    protected $fillable = [
        'client_id', 'lead_room_id', 'client_name', 'client_email', 'client_phone', 'location', 'business_type', 'source',
        'service_required', 'requirement', 'estimated_budget', 'assigned_to', 'follow_up_date', 'preferred_date', 'status', 'notes', 'company_details', 'created_by', 'company_id',
    ];

    protected $casts = [
        'follow_up_date' => 'date',
        'preferred_date' => 'date',
        'estimated_budget' => 'decimal:2',
    ];

    public function client() { return $this->belongsTo(Client::class); }
    public function room() { return $this->belongsTo(LeadRoom::class, 'lead_room_id'); }
    public function assignedTo() { return $this->belongsTo(User::class, 'assigned_to'); }
    public function createdBy() { return $this->belongsTo(User::class, 'created_by'); }
    public function followUps() { return $this->hasMany(LeadFollowUp::class); }
    public function quotations() { return $this->hasMany(Quotation::class); }
    public function calls() { return $this->hasMany(LeadCall::class); }
    public function latestCall() { return $this->hasOne(LeadCall::class)->latestOfMany(); }
    public function appointments() { return $this->hasMany(LeadAppointment::class); }

    public function scopeForUser($query, $user)
    {
        if ($user->isTelecaller()) {
            return $query->where(function($q) use ($user) {
                $q->whereIn('lead_room_id', function($sq) use ($user) {
                    $sq->select('lead_room_id')
                      ->from('lead_room_user')
                      ->where('user_id', $user->id);
                })->orWhere(function($sq) use ($user) {
                    $sq->whereNull('lead_room_id')->where('assigned_to', $user->id);
                });
            })->where(function($q) use ($user) {
                $q->where('assigned_to', $user->id)
                  ->orWhereNull('assigned_to');
            });
        }
        return $query;
    }
}
