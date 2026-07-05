<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InvestorTransaction extends Model
{
    protected $fillable = [
        'investor_id',
        'type', // Credit, Debit
        'amount',
        'date',
        'reference',
        'description',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'date' => 'date',
    ];

    public function investor()
    {
        return $this->belongsTo(Investor::class);
    }
}
