@extends('layouts.app')

@section('title', 'Edit Quotation')
@section('page-title', 'Edit Quotation')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('quotations.index') }}">Quotations</a></li>
    <li class="breadcrumb-item"><a href="{{ route('quotations.show', $quotation) }}">{{ $quotation->quotation_number }}</a></li>
    <li class="breadcrumb-item active">Edit</li>
@endsection

@section('content')
<div class="row">
    <div class="col-12 col-lg-10 mx-auto">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Edit Proposal / Quotation</h5>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('quotations.update', $quotation) }}" id="quotationForm">
                    @csrf
                    @method('PUT')
                    
                    <h6 class="text-uppercase text-primary fs-7 mb-3 border-bottom pb-2">General Info</h6>
                    <div class="row g-3 mb-4">
                        <div class="col-12 col-md-4">
                            <label class="form-label">Quotation Number <span class="text-danger">*</span></label>
                            <input type="text" name="quotation_number" class="form-control fw-semibold" value="{{ old('quotation_number', $quotation->quotation_number) }}" required readonly>
                        </div>
                        <div class="col-12 col-md-8">
                            <label class="form-label">Quotation Title <span class="text-danger">*</span></label>
                            <input type="text" name="title" class="form-control @error('title') is-invalid @enderror" value="{{ old('title', $quotation->title) }}" required>
                            @error('title')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-12 col-md-4">
                            <label class="form-label">Select Client <span class="text-danger">*</span></label>
                            <select name="client_id" class="form-select @error('client_id') is-invalid @enderror" required>
                                @foreach($clients as $c)
                                    <option value="{{ $c->id }}" {{ old('client_id', $quotation->client_id) == $c->id ? 'selected' : '' }}>{{ $c->company_name }}</option>
                                @endforeach
                            </select>
                            @error('client_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-12 col-md-4">
                            <label class="form-label">Quotation Status <span class="text-danger">*</span></label>
                            <select name="status" class="form-select @error('status') is-invalid @enderror" required>
                                <option value="draft" {{ old('status', $quotation->status) === 'draft' ? 'selected' : '' }}>Draft</option>
                                <option value="sent" {{ old('status', $quotation->status) === 'sent' ? 'selected' : '' }}>Sent</option>
                                <option value="accepted" {{ old('status', $quotation->status) === 'accepted' ? 'selected' : '' }}>Accepted</option>
                                <option value="declined" {{ old('status', $quotation->status) === 'declined' ? 'selected' : '' }}>Declined</option>
                            </select>
                            @error('status')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-12 col-md-4">
                            <label class="form-label">Valid Until</label>
                            <input type="date" name="valid_until" class="form-control" value="{{ old('valid_until', $quotation->valid_until ? $quotation->valid_until->format('Y-m-d') : '') }}">
                        </div>
                    </div>

                    <h6 class="text-uppercase text-primary fs-7 mb-3 border-bottom pb-2">Scope of Work</h6>
                    <div class="mb-4">
                        <textarea name="scope" class="form-control" rows="3">{{ old('scope', $quotation->scope) }}</textarea>
                    </div>

                    <h6 class="text-uppercase text-primary fs-7 mb-3 border-bottom pb-2">Breakdown of Modules / Line Items</h6>
                    <div class="table-responsive mb-3">
                        <table class="table table-bordered align-middle" id="modulesTable">
                            <thead class="bg-light">
                                <tr>
                                    <th>Module / Feature Title</th>
                                    <th style="width: 200px;">Price (₹)</th>
                                    <th style="width: 80px;" class="text-center">Action</th>
                                </tr>
                            </thead>
                            <tbody id="modulesContainer">
                                @php $index = 0; @endphp
                                @forelse($quotation->modules ?? [] as $mod)
                                    <tr>
                                        <td>
                                            <input type="text" name="modules[{{ $index }}][name]" class="form-control form-control-sm" required value="{{ $mod['name'] ?? '' }}">
                                        </td>
                                        <td>
                                            <input type="number" step="0.01" name="modules[{{ $index }}][price]" class="form-control form-control-sm module-price" required value="{{ $mod['price'] ?? '0.00' }}" oninput="calculateTotals()">
                                        </td>
                                        <td class="text-center">
                                            <button type="button" class="btn btn-outline-danger btn-sm" onclick="removeRow(this)"><i class="bi bi-trash"></i></button>
                                        </td>
                                    </tr>
                                    @php $index++; @endphp
                                @empty
                                    <tr>
                                        <td>
                                            <input type="text" name="modules[0][name]" class="form-control form-control-sm" required placeholder="e.g. Frontend Development">
                                        </td>
                                        <td>
                                            <input type="number" step="0.01" name="modules[0][price]" class="form-control form-control-sm module-price" required value="0.00" oninput="calculateTotals()">
                                        </td>
                                        <td class="text-center">
                                            <button type="button" class="btn btn-outline-danger btn-sm" onclick="removeRow(this)"><i class="bi bi-trash"></i></button>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                        <button type="button" class="btn btn-outline-primary btn-sm" onclick="addRow()"><i class="bi bi-plus-lg me-1"></i> Add Module Row</button>
                    </div>

                    <!-- Financial Summary Calculations -->
                    <h6 class="text-uppercase text-primary fs-7 mb-3 border-bottom pb-2">Summary Calculations</h6>
                    <div class="row g-3 justify-content-end mb-4">
                        <div class="col-12 col-md-4">
                            <div class="card bg-light p-3">
                                <div class="mb-2 row align-items-center">
                                    <label class="col-sm-5 col-form-label col-form-label-sm fw-medium">Subtotal (₹)</label>
                                    <div class="col-sm-7">
                                        <input type="number" step="0.01" name="subtotal" id="subtotal" class="form-control form-control-sm fw-semibold" value="{{ $quotation->subtotal }}" readonly>
                                    </div>
                                </div>
                                <div class="mb-2 row align-items-center">
                                    <label class="col-sm-5 col-form-label col-form-label-sm fw-medium">Discount (₹)</label>
                                    <div class="col-sm-7">
                                        <input type="number" step="0.01" name="discount" id="discount" class="form-control form-control-sm" value="{{ $quotation->discount }}" oninput="calculateTotals()">
                                    </div>
                                </div>
                                @php
                                    $gstPercent = 18;
                                    if ($quotation->subtotal > 0 && $quotation->tax > 0) {
                                        $taxable = max(0, $quotation->subtotal - $quotation->discount);
                                        if ($taxable > 0) {
                                            $gstPercent = round(($quotation->tax / $taxable) * 100, 1);
                                        }
                                    }
                                @endphp
                                <div class="mb-2 row align-items-center">
                                    <label class="col-sm-5 col-form-label col-form-label-sm fw-medium">GST (%)</label>
                                    <div class="col-sm-7">
                                        <input type="number" step="0.1" name="tax_percent" id="tax_percent" class="form-control form-control-sm" value="{{ $gstPercent }}" oninput="calculateTotals()">
                                    </div>
                                </div>
                                <div class="mb-2 row align-items-center">
                                    <label class="col-sm-5 col-form-label col-form-label-sm fw-medium">GST Amount (₹)</label>
                                    <div class="col-sm-7">
                                        <input type="number" step="0.01" name="tax" id="tax" class="form-control form-control-sm" value="{{ $quotation->tax }}" readonly>
                                    </div>
                                </div>
                                <hr class="my-2">
                                <div class="row align-items-center">
                                    <label class="col-sm-5 col-form-label col-form-label-sm fw-bold">Grand Total (₹)</label>
                                    <div class="col-sm-7">
                                        <input type="number" step="0.01" name="total" id="total" class="form-control form-control-sm fw-bold text-success fs-6" value="{{ $quotation->total }}" readonly required>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <h6 class="text-uppercase text-primary fs-7 mb-3 border-bottom pb-2">Terms & Conditions</h6>
                    <div class="mb-4">
                        <textarea name="terms" class="form-control" rows="3">{{ old('terms', $quotation->terms) }}</textarea>
                    </div>

                    <div class="d-flex align-items-center justify-content-end gap-2 border-top pt-3">
                        <a href="{{ route('quotations.show', $quotation) }}" class="btn btn-outline-secondary">Cancel</a>
                        <button type="submit" class="btn btn-primary">Update Proposal</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    let rowIndex = {{ max(1, count($quotation->modules ?? [])) }};

    function addRow() {
        const container = document.getElementById('modulesContainer');
        const row = document.createElement('tr');
        row.innerHTML = `
            <td>
                <input type="text" name="modules[${rowIndex}][name]" class="form-control form-control-sm" required placeholder="Module / Feature Title">
            </td>
            <td>
                <input type="number" step="0.01" name="modules[${rowIndex}][price]" class="form-control form-control-sm module-price" required value="0.00" oninput="calculateTotals()">
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
        const container = document.getElementById('modulesContainer');
        if (container.children.length > 1) {
            row.remove();
            calculateTotals();
        } else {
            alert('At least one item is required.');
        }
    }

    function calculateTotals() {
        const priceInputs = document.querySelectorAll('.module-price');
        let subtotal = 0;
        
        priceInputs.forEach(input => {
            const val = parseFloat(input.value) || 0;
            subtotal += val;
        });

        const discount = parseFloat(document.getElementById('discount').value) || 0;
        const taxPercent = parseFloat(document.getElementById('tax_percent').value) || 0;

        const taxableAmount = Math.max(0, subtotal - discount);
        const taxAmount = (taxableAmount * taxPercent) / 100;
        const total = taxableAmount + taxAmount;

        document.getElementById('subtotal').value = subtotal.toFixed(2);
        document.getElementById('tax').value = taxAmount.toFixed(2);
        document.getElementById('total').value = total.toFixed(2);
    }

    document.addEventListener('DOMContentLoaded', () => {
        calculateTotals();
    });
</script>
@endpush
