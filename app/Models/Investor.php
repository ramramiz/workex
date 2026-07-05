<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\BelongsToCompany;

class Investor extends Model
{
    use SoftDeletes, BelongsToCompany;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'description',
        'opening_balance',
        'status',
        'company_id',
    ];

    protected $casts = [
        'opening_balance' => 'decimal:2',
    ];

    public function transactions()
    {
        return $this->hasMany(InvestorTransaction::class);
    }
}
