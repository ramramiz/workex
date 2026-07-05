<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\BelongsToCompany;

class DomainRegistration extends Model
{
    use BelongsToCompany;

    protected $fillable = [
        'name',
        'url',
        'username',
        'password',
        'renewal_date',
        'notes',
        'company_id',
    ];

    protected $casts = [
        'renewal_date' => 'date',
    ];

    public function projects()
    {
        return $this->hasMany(Project::class);
    }
}
