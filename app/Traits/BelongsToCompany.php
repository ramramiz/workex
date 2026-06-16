<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;
use App\Models\Company;

trait BelongsToCompany
{
    protected static function bootBelongsToCompany()
    {
        static::creating(function ($model) {
            if (auth()->check() && auth()->user()->company_id) {
                $model->company_id = auth()->user()->company_id;
            }
        });

        static::addGlobalScope('company', function (Builder $builder) {
            if (auth()->hasUser() && auth()->user()->company_id) {
                $builder->where($builder->getQuery()->from . '.company_id', auth()->user()->company_id);
            }
        });
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }
}
