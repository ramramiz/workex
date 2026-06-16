<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    protected $fillable = [
        'invoice_id', 'project_id', 'client_id', 'payment_reference', 'amount',
        'payment_date', 'payment_mode', 'transaction_id', 'notes', 'attachment', 'recorded_by',
    ];

    protected $casts = [
        'payment_date' => 'date',
        'amount' => 'decimal:2',
    ];

    public function invoice() { return $this->belongsTo(Invoice::class); }
    public function project() { return $this->belongsTo(Project::class); }
    public function client() { return $this->belongsTo(Client::class); }
    public function recordedBy() { return $this->belongsTo(User::class, 'recorded_by'); }
}
