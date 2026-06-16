<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MailboxMessage extends Model
{
    use HasFactory;

    protected $fillable = [
        'sender_id',
        'receiver_id',
        'subject',
        'body',
        'attachment_path',
        'attachment_name',
        'is_read',
        'sender_deleted_at',
        'receiver_deleted_at',
    ];

    protected $casts = [
        'is_read' => 'boolean',
        'sender_deleted_at' => 'datetime',
        'receiver_deleted_at' => 'datetime',
    ];

    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    public function receiver()
    {
        return $this->belongsTo(User::class, 'receiver_id');
    }

    public function scopeInbox($query, $userId)
    {
        return $query->where('receiver_id', $userId)
            ->whereNull('receiver_deleted_at');
    }

    public function scopeSent($query, $userId)
    {
        return $query->where('sender_id', $userId)
            ->whereNull('sender_deleted_at');
    }

    public function scopeTrash($query, $userId)
    {
        return $query->where(function($q) use ($userId) {
            $q->where('receiver_id', $userId)
                ->whereNotNull('receiver_deleted_at')
                ->where('receiver_deleted_at', '!=', '1970-01-01 00:00:00');
        })->orWhere(function($q) use ($userId) {
            $q->where('sender_id', $userId)
                ->whereNotNull('sender_deleted_at')
                ->where('sender_deleted_at', '!=', '1970-01-01 00:00:00');
        });
    }
}
