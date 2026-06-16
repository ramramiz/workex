<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\BelongsToCompany;

class Meeting extends Model
{
    use HasFactory, BelongsToCompany;

    protected $fillable = [
        'title',
        'description',
        'meeting_date',
        'location',
        'created_by',
        'company_id',
    ];

    protected $casts = [
        'meeting_date' => 'date',
    ];

    /**
     * Get the tasks linked to this meeting.
     */
    public function tasks()
    {
        return $this->hasMany(Task::class);
    }

    /**
     * Get the user who created this meeting.
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
