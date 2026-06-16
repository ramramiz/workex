@extends('layouts.app')

@section('title', 'Record Payment')
@section('page-title', 'Record Payment')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('payments.index') }}">Payments</a></li>
    <li class="breadcrumb-item active">Record</li>
@endsection

@section('content')
<div class="row">
    <div class="col-12 col-md-6 mx-auto">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Record Invoice Payment</h5>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('payments.store') }}">
                    @csrf
                    
                    <div class="mb-3">
                        <label class="form-label">Select Invoice <span class="text-danger">*</span></label>
                        <select name="invoice_id" id="invoice_id" class="form-select @error('invoice_id') is-invalid @enderror" required onchange="showInvoiceBalance()">
                            <option value="">-- Choose Invoice --</option>
                            @foreach($invoices as $inv)
                                <option value="{{ $inv->id }}" data-balance="{{ $inv->balance_amount }}" data-total="{{ $inv->total }}" data-client="{{ $inv->client->company_name ?? '—' }}" {{ (old('invoice_id') == $inv->id || ($selectedInvoice && $selectedInvoice->id == $inv->id)) ? 'selected' : '' }}>
                                    {{ $inv->invoice_number }} ({{ $inv->client->company_name ?? '—' }})
                                </option>
                            @endforeach
                        </select>
                        @error('invoice_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <!-- Selected Invoice Stats -->
                    <div id="invoiceStatsCard" class="card bg-light border p-3 mb-3 d-none">
                        <div class="fs-7 text-muted">Client Account: <strong id="statsClient" class="text-dark"></strong></div>
                        <div class="d-flex justify-content-between fs-7 mt-2">
                            <span>Invoice Amount:</span>
                            <span class="fw-semibold" id="statsTotal"></span>
                        </div>
                        <div class="d-flex justify-content-between fs-7 text-danger mt-1">
                            <span>Outstanding Balance:</span>
                            <span class="fw-semibold" id="statsBalance"></span>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Payment Date <span class="text-danger">*</span></label>
                        <input type="date" name="payment_date" class="form-control @error('payment_date') is-invalid @enderror" value="{{ old('payment_date', date('Y-m-d')) }}" required>
                        @error('payment_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Payment Mode <span class="text-danger">*</span></label>
                        <select name="payment_mode" class="form-select @error('payment_mode') is-invalid @enderror" required>
                            <option value="">-- Select Mode --</option>
                            <option value="bank_transfer" {{ old('payment_mode') === 'bank_transfer' ? 'selected' : '' }}>Bank Wire Transfer</option>
                            <option value="online" {{ old('payment_mode') === 'online' ? 'selected' : '' }}>Online Card/UPI</option>
                            <option value="cheque" {{ old('payment_mode') === 'cheque' ? 'selected' : '' }}>Cheque</option>
                            <option value="cash" {{ old('payment_mode') === 'cash' ? 'selected' : '' }}>Cash</option>
                        </select>
                        @error('payment_mode')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Amount Received (₹) <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text">₹</span>
                            <input type="number" step="0.01" name="amount" id="amount" class="form-control @error('amount') is-invalid @enderror" value="{{ old('amount') }}" required placeholder="0.00">
                        </div>
                        @error('amount')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Transaction ID / Reference Number</label>
                        <input type="text" name="transaction_id" class="form-control @error('transaction_id') is-invalid @enderror" value="{{ old('transaction_id') }}" placeholder="e.g. TXN98765432">
                        @error('transaction_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="mb-4">
                        <label class="form-label">Payment Remarks</label>
                        <textarea name="notes" class="form-control @error('notes') is-invalid @enderror" rows="3" placeholder="Any additional notes...">{{ old('notes') }}</textarea>
                        @error('notes')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="d-flex align-items-center justify-content-end gap-2 border-top pt-3">
                        <a href="{{ route('payments.index') }}" class="btn btn-outline-secondary">Cancel</a>
                        <button type="submit" class="btn btn-primary">Record Payment</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function showInvoiceBalance() {
        const select = document.getElementById('invoice_id');
        const selected = select.options[select.selectedIndex];
        const statsCard = document.getElementById('invoiceStatsCard');

        if (selected && selected.value) {
            const balance = parseFloat(selected.getAttribute('data-balance')) || 0;
            const total = parseFloat(selected.getAttribute('data-total')) || 0;
            const client = selected.getAttribute('data-client') || '';

            document.getElementById('statsClient').textContent = client;
            document.getElementById('statsTotal').textContent = "₹" + total.toLocaleString('en-IN', { minimumFractionDigits: 2 });
            document.getElementById('statsBalance').textContent = "₹" + balance.toLocaleString('en-IN', { minimumFractionDigits: 2 });
            document.getElementById('amount').value = balance.toFixed(2);
            statsCard.classList.remove('d-none');
        } else {
            statsCard.classList.add('d-none');
        }
    }

    document.addEventListener('DOMContentLoaded', () => {
        showInvoiceBalance();
    });
</script>
@endpush
