<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\BelongsToCompany;

class LeadRoom extends Model
{
    use BelongsToCompany;
    protected $fillable = ['client_id', 'name', 'description', 'created_by', 'company_id'];

    public function client()
    {
        return $this->belongsTo(Client::class, 'client_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function users()
    {
        return $this->belongsToMany(User::class, 'lead_room_user', 'lead_room_id', 'user_id');
    }

    public function leads()
    {
        return $this->hasMany(Lead::class, 'lead_room_id');
    }
}
