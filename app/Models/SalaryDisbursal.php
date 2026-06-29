<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\BelongsToCompany;

class SalaryDisbursal extends Model
{
    use BelongsToCompany;

    protected $fillable = [
        'company_id', 'employee_id', 'month', 'year', 'basic_salary',
        'allowances', 'deductions', 'net_salary', 'payment_method',
        'payment_date', 'status', 'remarks',
    ];

    protected $casts = [
        'payment_date' => 'date',
        'basic_salary' => 'decimal:2',
        'allowances' => 'decimal:2',
        'deductions' => 'decimal:2',
        'net_salary' => 'decimal:2',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
}
