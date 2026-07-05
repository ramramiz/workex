<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\BelongsToCompany;

class Bank extends Model
{
    use SoftDeletes, BelongsToCompany;

    protected $fillable = [
        'name',
        'account_name',
        'account_number',
        'ifsc_code',
        'branch',
        'opening_balance',
        'status',
        'company_id',
    ];
}
