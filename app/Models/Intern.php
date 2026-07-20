<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\BelongsToCompany;

class Intern extends Model
{
    use SoftDeletes, BelongsToCompany;

    protected $fillable = [
        'company_id',
        'name',
        'email',
        'phone',
        'department_id',
        'designation_id',
        'sector',
        'joining_date',
        'end_date',
        'certificate_code',
        'photo',
        'status',
    ];

    protected $casts = [
        'joining_date' => 'date',
        'end_date' => 'date',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function designation()
    {
        return $this->belongsTo(Designation::class);
    }

    public static function encryptCode($code)
    {
        $key = substr(md5(config('app.key')), 0, 16);
        $encrypted = openssl_encrypt($code, 'AES-128-ECB', $key);
        return str_replace(['+', '/', '='], ['-', '_', ''], $encrypted);
    }

    public static function decryptCode($token)
    {
        $key = substr(md5(config('app.key')), 0, 16);
        $data = str_replace(['-', '_'], ['+', '/'], $token);
        return openssl_decrypt($data, 'AES-128-ECB', $key);
    }

    public function uploadedDocuments()
    {
        return $this->morphMany(Document::class, 'documentable');
    }

    public function onboarding()
    {
        return $this->hasOne(InternOnboarding::class);
    }
}
