<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\BelongsToCompany;

class SalaryDisbursal extends Model
{
    use BelongsToCompany;

    protected $fillable = [
        'company_id', 'employee_id', 'month', 'year', 'cycle', 'basic_salary',
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

    protected $appends = ['encrypted_id'];

    public static function encryptId($id)
    {
        $key = substr(md5(config('app.key')), 0, 16);
        $encrypted = openssl_encrypt((string) $id, 'AES-128-ECB', $key);
        return str_replace(['+', '/', '='], ['-', '_', ''], $encrypted);
    }

    public static function decryptId($token)
    {
        $key = substr(md5(config('app.key')), 0, 16);
        $data = str_replace(['-', '_'], ['+', '/'], $token);
        return openssl_decrypt($data, 'AES-128-ECB', $key);
    }

    public function getEncryptedIdAttribute()
    {
        return self::encryptId($this->id);
    }

    public function getRouteKey()
    {
        return self::encryptId($this->id);
    }

    public function resolveRouteBinding($value, $field = null)
    {
        $decryptedId = self::decryptId($value);
        if (!$decryptedId) {
            abort(404);
        }
        return $this->where($field ?? $this->getRouteKeyName(), $decryptedId)->firstOrFail();
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
}
