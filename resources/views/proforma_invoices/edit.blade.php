@extends('layouts.app')

@section('title', 'Edit Proforma Invoice')
@section('page-title', 'Edit Proforma Invoice')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('proforma-invoices.index') }}">Proforma Invoices</a></li>
    <li class="breadcrumb-item"><a href="{{ route('proforma-invoices.show', $proformaInvoice) }}">{{ $proformaInvoice->proforma_number }}</a></li>
    <li class="breadcrumb-item active">Edit</li>
@endsection

@section('content')
<div class="row">
    <div class="col-12 col-lg-10 mx-auto">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Edit Proforma Invoice: {{ $proformaInvoice->proforma_number }}</h5>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('proforma-invoices.update', $proformaInvoice) }}" id="proformaForm">
                    @csrf
                    @method('PUT')
                    
                    <h6 class="text-uppercase text-primary fs-7 mb-3 border-bottom pb-2">Billing Linkage & Schedule</h6>
                    <div class="row g-3 mb-4">
                        <div class="col-12 col-md-4">
                            <label class="form-label">Proforma Number <span class="text-danger">*</span></label>
                            <input type="text" name="proforma_number" class="form-control fw-semibold" value="{{ old('proforma_number', $proformaInvoice->proforma_number) }}" required readonly>
                        </div>
                        <div class="col-12 col-md-4">
                            <label class="form-label">Proforma Date <span class="text-danger">*</span></label>
                            <input type="date" name="proforma_date" class="form-control @error('proforma_date') is-invalid @enderror" value="{{ old('proforma_date', $proformaInvoice->proforma_date ? $proformaInvoice->proforma_date->format('Y-m-d') : '') }}" required>
                            @error('proforma_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-12 col-md-4">
                            <label class="form-label">Due Date <span class="text-danger">*</span></label>
                            <input type="date" name="due_date" class="form-control @error('due_date') is-invalid @enderror" value="{{ old('due_date', $proformaInvoice->due_date ? $proformaInvoice->due_date->format('Y-m-d') : '') }}" required>
                            @error('due_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label">Project board <span class="text-muted">(Optional)</span></label>
                            <select name="project_id" id="project_id" class="form-select select-search @error('project_id') is-invalid @enderror" onchange="autoselectClient()">
                                <option value="">-- Internal / No project board --</option>
                                @foreach($projects as $p)
                                    <option value="{{ $p->id }}" data-client-id="{{ $p->client_id }}" {{ old('project_id', $proformaInvoice->project_id) == $p->id ? 'selected' : '' }}>{{ $p->name }} ({{ $p->client?->company_name ?? 'Internal Project' }})</option>
                                @endforeach
                            </select>
                            @error('project_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-12 col-md-4">
                            <label class="form-label">Bill To Client <span class="text-danger">*</span></label>
                            <select name="client_id" id="client_id" class="form-select @error('client_id') is-invalid @enderror" required>
                                <option value="">-- Choose Client --</option>
                                @foreach($clients as $c)
                                    <option value="{{ $c->id }}" {{ old('client_id', $proformaInvoice->client_id) == $c->id ? 'selected' : '' }}>{{ $c->company_name }}</option>
                                @endforeach
                            </select>
                            @error('client_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-12 col-md-2">
                            <label class="form-label">Status <span class="text-danger">*</span></label>
                            <select name="status" class="form-select @error('status') is-invalid @enderror" required>
                                <option value="draft" {{ old('status', $proformaInvoice->status) === 'draft' ? 'selected' : '' }}>Draft</option>
                                <option value="sent" {{ old('status', $proformaInvoice->status) === 'sent' ? 'selected' : '' }}>Sent</option>
                                <option value="converted" {{ old('status', $proformaInvoice->status) === 'converted' ? 'selected' : '' }} disabled>Converted</option>
                                <option value="cancelled" {{ old('status', $proformaInvoice->status) === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                            </select>
                            @error('status')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>

                    <h6 class="text-uppercase text-primary fs-7 mb-3 border-bottom pb-2">Line Items Table</h6>
                    <div class="table-responsive mb-3">
                        <table class="table table-bordered align-middle" id="itemsTable">
                            <thead class="bg-light">
                                <tr>
                                    <th>Item Description</th>
                                    <th style="width: 100px;">Qty</th>
                                    <th style="width: 160px;">Rate (₹)</th>
                                    <th style="width: 180px;" class="text-end">Amount (₹)</th>
                                    <th style="width: 60px;" class="text-center">Action</th>
                                </tr>
                            </thead>
                            <tbody id="itemsContainer">
                                @php $index = 0; @endphp
                                @forelse($proformaInvoice->items ?? [] as $item)
                                    @php
                                        $qty = floatval($item['qty'] ?? 1);
                                        $price = floatval($item['price'] ?? 0);
                                        $rowTotal = $qty * $price;
                                    @endphp
                                    <tr>
                                        <td>
                                            <input type="text" name="items[{{ $index }}][name]" class="form-control form-control-sm" required value="{{ $item['name'] ?? '' }}">
                                        </td>
                                        <td>
                                            <input type="number" name="items[{{ $index }}][qty]" class="form-control form-control-sm item-qty" required value="{{ $qty }}" min="1" oninput="calculateRowTotal(this)">
                                        </td>
                                        <td>
                                            <input type="number" step="0.01" name="items[{{ $index }}][price]" class="form-control form-control-sm item-price" required value="{{ $price }}" oninput="calculateRowTotal(this)">
                                        </td>
                                        <td class="text-end fw-semibold">
                                            ₹<span class="row-amount-text">{{ number_format($rowTotal, 2) }}</span>
                                            <input type="hidden" name="items[{{ $index }}][total]" class="row-amount-val" value="{{ $rowTotal }}">
                                        </td>
                                        <td class="text-center">
                                            <button type="button" class="btn btn-outline-danger btn-sm" onclick="removeRow(this)"><i class="bi bi-trash"></i></button>
                                        </td>
                                    </tr>
                                    @php $index++; @endphp
                                @empty
                                    <tr>
                                        <td>
                                            <input type="text" name="items[0][name]" class="form-control form-control-sm" required placeholder="Item Description">
                                        </td>
                                        <td>
                                            <input type="number" name="items[0][qty]" class="form-control form-control-sm item-qty" required value="1" min="1" oninput="calculateRowTotal(this)">
                                        </td>
                                        <td>
                                            <input type="number" step="0.01" name="items[0][price]" class="form-control form-control-sm item-price" required value="0.00" oninput="calculateRowTotal(this)">
                                        </td>
                                        <td class="text-end fw-semibold">
                                            ₹<span class="row-amount-text">0.00</span>
                                            <input type="hidden" name="items[0][total]" class="row-amount-val" value="0">
                                        </td>
                                        <td class="text-center">
                                            <button type="button" class="btn btn-outline-danger btn-sm" onclick="removeRow(this)"><i class="bi bi-trash"></i></button>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                        <button type="button" class="btn btn-outline-primary btn-sm" onclick="addRow()"><i class="bi bi-plus-lg me-1"></i> Add Line Row</button>
                    </div>

                    <!-- Financial Summary Calculations -->
                    <h6 class="text-uppercase text-primary fs-7 mb-3 border-bottom pb-2">Proforma Calculations</h6>
                    <div class="row g-3 justify-content-end mb-4">
                        <div class="col-12 col-md-5">
                            <div class="card bg-light p-3">
                                <div class="mb-2 row align-items-center">
                                    <label class="col-sm-5 col-form-label col-form-label-sm fw-medium">Subtotal (₹)</label>
                                    <div class="col-sm-7">
                                        <input type="number" step="0.01" name="subtotal" id="subtotal" class="form-control form-control-sm fw-semibold" value="{{ $proformaInvoice->subtotal }}" readonly>
                                    </div>
                                </div>
                                <div class="mb-2 row align-items-center">
                                    <label class="col-sm-5 col-form-label col-form-label-sm fw-medium">Discount (₹)</label>
                                    <div class="col-sm-7">
                                        <input type="number" step="0.01" name="discount" id="discount" class="form-control form-control-sm" value="{{ $proformaInvoice->discount }}" oninput="calculateTotals()">
                                    </div>
                                </div>
                                <div class="mb-2 row align-items-center">
                                    <label class="col-sm-5 col-form-label col-form-label-sm fw-medium">GST (%)</label>
                                    <div class="col-sm-7">
                                        <input type="number" step="0.1" name="tax_percentage" id="tax_percentage" class="form-control form-control-sm" value="{{ $proformaInvoice->tax_percentage }}" oninput="calculateTotals()">
                                    </div>
                                </div>
                                <div class="mb-2 row align-items-center">
                                    <label class="col-sm-5 col-form-label col-form-label-sm fw-medium">GST Amount (₹)</label>
                                    <div class="col-sm-7">
                                        <input type="number" step="0.01" name="tax_amount" id="tax_amount" class="form-control form-control-sm" value="{{ $proformaInvoice->tax_amount }}" readonly>
                                    </div>
                                </div>
                                <hr class="my-2">
                                <div class="row align-items-center">
                                    <label class="col-sm-5 col-form-label col-form-label-sm fw-bold">Grand Total (₹)</label>
                                    <div class="col-sm-7">
                                        <input type="number" step="0.01" name="total" id="total" class="form-control form-control-sm fw-bold text-success fs-6" value="{{ $proformaInvoice->total }}" readonly required>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <h6 class="text-uppercase text-primary fs-7 mb-3 border-bottom pb-2">Notes & Instructions</h6>
                    <div class="mb-4">
                        <textarea name="notes" class="form-control" rows="3">{{ old('notes', $proformaInvoice->notes) }}</textarea>
                    </div>

                    <div class="d-flex align-items-center justify-content-end gap-2 border-top pt-3">
                        <a href="{{ route('proforma-invoices.show', $proformaInvoice) }}" class="btn btn-outline-secondary">Cancel</a>
                        <button type="submit" class="btn btn-primary">Update Proforma Invoice</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    let rowIndex = {{ max(1, count($proformaInvoice->items ?? [])) }};

    function autoselectClient() {
        const select = document.getElementById('project_id');
        const selectedOption = select.options[select.selectedIndex];
        
        if (selectedOption && selectedOption.value) {
            const clientId = selectedOption.getAttribute('data-client-id');
            if (clientId) {
                document.getElementById('client_id').value = clientId;
            }
        }
    }

    function addRow() {
        const container = document.getElementById('itemsContainer');
        const row = document.createElement('tr');
        row.innerHTML = `
            <td>
                <input type="text" name="items[${rowIndex}][name]" class="form-control form-control-sm" required placeholder="Item Description">
            </td>
            <td>
                <input type="number" name="items[${rowIndex}][qty]" class="form-control form-control-sm item-qty" required value="1" min="1" oninput="calculateRowTotal(this)">
            </td>
            <td>
                <input type="number" step="0.01" name="items[${rowIndex}][price]" class="form-control form-control-sm item-price" required value="0.00" oninput="calculateRowTotal(this)">
            </td>
            <td class="text-end fw-semibold">
                ₹<span class="row-amount-text">0.00</span>
                <input type="hidden" name="items[${rowIndex}][total]" class="row-amount-val" value="0">
            </td>
            <td class="text-center">
                <button type="button" class="btn btn-outline-danger btn-sm" onclick="removeRow(this)"><i class="bi bi-trash"></i></button>
            </td>
        `;
        container.appendChild(row);
        rowIndex++;
        calculateTotals();
    }

    function removeRow(btn) {
        const row = btn.closest('tr');
        const container = document.getElementById('itemsContainer');
        if (container.children.length > 1) {
            row.remove();
            calculateTotals();
        } else {
            alert('At least one item is required.');
        }
    }

    function calculateRowTotal(input) {
        const row = input.closest('tr');
        const qty = parseFloat(row.querySelector('.item-qty').value) || 0;
        const price = parseFloat(row.querySelector('.item-price').value) || 0;
        const total = qty * price;
        
        row.querySelector('.row-amount-text').textContent = total.toFixed(2);
        row.querySelector('.row-amount-val').value = total.toFixed(2);
        calculateTotals();
    }

    function calculateTotals() {
        const amountInputs = document.querySelectorAll('.row-amount-val');
        let subtotal = 0;
        
        amountInputs.forEach(input => {
            subtotal += parseFloat(input.value) || 0;
        });

        const discount = parseFloat(document.getElementById('discount').value) || 0;
        const taxPercent = parseFloat(document.getElementById('tax_percentage').value) || 0;

        const taxableAmount = Math.max(0, subtotal - discount);
        const taxAmount = (taxableAmount * taxPercent) / 100;
        const total = taxableAmount + taxAmount;

        document.getElementById('subtotal').value = subtotal.toFixed(2);
        document.getElementById('tax_amount').value = taxAmount.toFixed(2);
        document.getElementById('total').value = total.toFixed(2);
    }

    document.addEventListener('DOMContentLoaded', () => {
        if (document.getElementById('project_id').value) {
            autoselectClient();
        }
        calculateTotals();
    });
</script>
@endpush
