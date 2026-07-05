@extends('layouts.app')

@section('title', 'Payment Receipt')
@section('page-title', 'Payment Receipt')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('payments.index') }}">Payments</a></li>
    <li class="breadcrumb-item active">Receipt</li>
@endsection

@section('content')
<div class="row">
    <div class="col-12 col-md-6 mx-auto">
        <div class="card shadow-sm border border-light">
            <div class="card-header bg-white d-flex align-items-center justify-content-between">
                <h5 class="mb-0">Payment Voucher</h5>
                @if(auth()->user()->isAdminOrAbove() || auth()->user()->isAccounts())
                    <form method="POST" action="{{ route('payments.destroy', $payment) }}" class="d-inline" onsubmit="return confirm('Delete this payment log?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-outline-danger btn-sm"><i class="bi bi-trash"></i> Delete Receipt</button>
                    </form>
                @endif
            </div>
            <div class="card-body">
                <div class="text-center mb-4">
                    <div class="bg-success-subtle text-success border border-success-subtle rounded-circle p-2 d-inline-flex align-items-center justify-content-center mb-2" style="width:60px; height:60px;">
                        <i class="bi bi-check-lg fs-2"></i>
                    </div>
                    <h3 class="fw-bold text-success">₹{{ number_format($payment->amount, 2) }}</h3>
                    <span class="fs-8 text-muted text-uppercase font-monospace">Transaction Reference: {{ $payment->payment_reference }}</span>
                </div>

                <hr>

                <div class="mb-3">
                    <small class="text-muted d-block fs-8">Billed Client</small>
                    <span class="fw-bold text-dark fs-6">{{ $payment->client->company_name ?? '—' }}</span>
                </div>

                <div class="row g-2 mb-3">
                    <div class="col-6">
                        <small class="text-muted d-block">Payment Date</small>
                        <span class="fw-medium text-dark">{{ \Carbon\Carbon::parse($payment->payment_date)->format('d M Y') }}</span>
                    </div>
                    <div class="col-6">
                        <small class="text-muted d-block">Invoice Link</small>
                        @if($payment->invoice)
                            <a href="{{ route('invoices.show', $payment->invoice) }}" class="fw-medium text-decoration-none">{{ $payment->invoice->invoice_number }}</a>
                        @else
                            <span class="text-muted">—</span>
                        @endif
                    </div>
                </div>

                <div class="row g-2 mb-3">
                    <div class="col-6">
                        @php
                            $matchedBank = \App\Models\Bank::where('name', $payment->payment_mode)->first();
                            $payModeDisplay = $matchedBank 
                                ? ($matchedBank->name . ' - ' . $matchedBank->branch . ' - ****' . substr($matchedBank->account_number, -4)) 
                                : $payment->payment_mode;
                        @endphp
                        <span class="text-capitalize fw-semibold text-dark">{{ $payModeDisplay }}</span>
                    </div>
                    <div class="col-6">
                        <small class="text-muted d-block">Transaction ID</small>
                        <span class="font-monospace text-dark">{{ $payment->transaction_id ?? '—' }}</span>
                    </div>
                </div>

                <div class="mb-3">
                    <small class="text-muted d-block">Recorded By</small>
                    <span class="fw-medium text-dark">{{ $payment->recordedBy->name ?? 'System' }}</span>
                </div>

                @if($payment->notes)
                    <div class="mb-3">
                        <small class="text-muted d-block">Remarks</small>
                        <p class="text-muted fs-7 mb-0 mt-1" style="white-space: pre-wrap;">{{ $payment->notes }}</p>
                    </div>
                @endif

                <div class="d-flex align-items-center justify-content-end gap-2 border-top pt-3 mt-4">
                    <a href="{{ route('payments.index') }}" class="btn btn-outline-secondary">Back to List</a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
