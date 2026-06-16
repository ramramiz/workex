<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

use App\Traits\BelongsToCompany;

class Quotation extends Model
{
    use SoftDeletes, BelongsToCompany;

    protected $fillable = [
        'lead_id', 'client_id', 'quotation_number', 'title', 'scope', 'modules',
        'subtotal', 'discount', 'tax', 'total', 'terms', 'valid_until', 'status', 'pdf_path', 'created_by', 'company_id',
    ];

    protected $casts = [
        'modules' => 'array',
        'valid_until' => 'date',
        'subtotal' => 'decimal:2',
        'discount' => 'decimal:2',
        'tax' => 'decimal:2',
        'total' => 'decimal:2',
    ];

    public function lead() { return $this->belongsTo(Lead::class); }
    public function client() { return $this->belongsTo(Client::class); }
    public function project() { return $this->hasOne(Project::class); }
    public function createdBy() { return $this->belongsTo(User::class, 'created_by'); }
}
