<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

use App\Traits\BelongsToCompany;

class Invoice extends Model
{
    use SoftDeletes, BelongsToCompany;

    protected $fillable = [
        'project_id', 'client_id', 'invoice_number', 'invoice_date', 'due_date',
        'items', 'subtotal', 'tax_percentage', 'tax_amount', 'discount', 'total',
        'paid_amount', 'balance_amount', 'status', 'notes', 'pdf_path', 'created_by', 'company_id',
        'sales_person', 'payment_method', 'bank_details',
    ];

    protected $casts = [
        'items' => 'array',
        'invoice_date' => 'date',
        'due_date' => 'date',
        'subtotal' => 'decimal:2',
        'tax_percentage' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'discount' => 'decimal:2',
        'total' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'balance_amount' => 'decimal:2',
    ];

    public function project() { return $this->belongsTo(Project::class); }
    public function client() { return $this->belongsTo(Client::class); }
    public function payments() { return $this->hasMany(Payment::class); }
    public function createdBy() { return $this->belongsTo(User::class, 'created_by'); }

    public function getIsOverdueAttribute(): bool
    {
        return $this->due_date && $this->due_date->isPast() && $this->status !== 'paid';
    }
}
