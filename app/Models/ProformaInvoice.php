<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

use App\Traits\BelongsToCompany;

class ProformaInvoice extends Model
{
    use SoftDeletes, BelongsToCompany;

    protected $fillable = [
        'project_id', 'client_id', 'proforma_number', 'proforma_date', 'due_date',
        'items', 'subtotal', 'tax_percentage', 'tax_amount', 'discount', 'total',
        'status', 'notes', 'pdf_path', 'created_by', 'company_id', 'converted_invoice_id',
    ];

    protected $casts = [
        'items' => 'array',
        'proforma_date' => 'date',
        'due_date' => 'date',
        'subtotal' => 'decimal:2',
        'tax_percentage' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'discount' => 'decimal:2',
        'total' => 'decimal:2',
    ];

    public function project() { return $this->belongsTo(Project::class); }
    public function client() { return $this->belongsTo(Client::class); }
    public function createdBy() { return $this->belongsTo(User::class, 'created_by'); }
    public function convertedInvoice() { return $this->belongsTo(Invoice::class, 'converted_invoice_id'); }

    public function getIsOverdueAttribute(): bool
    {
        return $this->due_date && $this->due_date->isPast() && $this->status === 'sent';
    }
}
