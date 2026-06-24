<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\BelongsToCompany;

class DirectMessage extends Model
{
    use BelongsToCompany;

    protected $fillable = [
        'sender_id', 'receiver_id', 'message', 'image_path', 'read_at', 'company_id', 'parent_id', 'is_edited', 'is_pinned', 'is_important'
    ];

    protected $casts = [
        'read_at' => 'datetime',
        'is_edited' => 'boolean',
        'is_pinned' => 'boolean',
        'is_important' => 'boolean',
    ];

    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    public function receiver()
    {
        return $this->belongsTo(User::class, 'receiver_id');
    }

    public function parent()
    {
        return $this->belongsTo(DirectMessage::class, 'parent_id');
    }

    public function replies()
    {
        return $this->hasMany(DirectMessage::class, 'parent_id');
    }
}
