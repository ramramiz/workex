@extends('layouts.app')

@section('title', 'Payments Report')
@section('page-title', 'Payments Report')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('reports.index') }}">Reports</a></li>
    <li class="breadcrumb-item active">Payments</li>
@endsection

@section('content')
<div class="row g-4 mb-4">
    <!-- Summary Stat Cards -->
    <div class="col-12 col-md-6 col-lg-4">
        <div class="card border border-light shadow-sm">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="bg-success-subtle text-success border border-success-subtle rounded-circle p-3 d-inline-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                    <i class="bi bi-wallet2 fs-4"></i>
                </div>
                <div>
                    <h6 class="text-muted fs-7 mb-1">Page Total Amount</h6>
                    <h3 class="fw-bold mb-0">₹{{ number_format($total, 2) }}</h3>
                </div>
            </div>
        </div>
    </div>
    <div class="col-12 col-md-6 col-lg-4">
        <div class="card border border-light shadow-sm">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="bg-info-subtle text-info border border-info-subtle rounded-circle p-3 d-inline-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                    <i class="bi bi-file-earmark-spreadsheet fs-4"></i>
                </div>
                <div>
                    <h6 class="text-muted fs-7 mb-1">Transactions This Page</h6>
                    <h3 class="fw-bold mb-0">{{ $payments->count() }}</h3>
                </div>
            </div>
        </div>
    </div>
    <div class="col-12 col-md-6 col-lg-4">
        <div class="card border border-light shadow-sm">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="bg-primary-subtle text-primary border border-primary-subtle rounded-circle p-3 d-inline-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                    <i class="bi bi-check-circle fs-4"></i>
                </div>
                <div>
                    <h6 class="text-muted fs-7 mb-1">Total System Payments</h6>
                    <h3 class="fw-bold mb-0">{{ $payments->total() }}</h3>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card border border-light shadow-sm">
    <div class="card-header d-flex align-items-center justify-content-between flex-wrap gap-3">
        <h5 class="mb-0">Payments Collected Registry</h5>
        <a href="{{ route('reports.index') }}" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left"></i> Back to Reports</a>
    </div>

    <!-- Table -->
    <div class="table-responsive">
        <table class="table align-middle mb-0">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Invoice No.</th>
                    <th>Client</th>
                    <th>Payment Mode</th>
                    <th>Ref / Trans ID</th>
                    <th>Notes</th>
                    <th class="text-end">Amount</th>
                </tr>
            </thead>
            <tbody>
                @forelse($payments as $payment)
                    <tr>
                        <td>
                            <div class="fw-semibold text-dark">{{ $payment->payment_date ? $payment->payment_date->format('d M Y') : '—' }}</div>
                        </td>
                        <td>
                            @if($payment->invoice)
                                <a href="{{ route('invoices.show', $payment->invoice_id) }}" class="fw-bold text-decoration-none text-primary">
                                    {{ $payment->invoice->invoice_number }}
                                </a>
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>
                        <td>
                            <div class="fw-semibold text-dark">{{ $payment->client?->company_name ?? '—' }}</div>
                            <div class="text-muted fs-8">{{ $payment->client?->contact_person ?? '' }}</div>
                        </td>
                        <td>
                            <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle text-capitalize">
                                {{ str_replace('_', ' ', $payment->payment_mode) }}
                            </span>
                        </td>
                        <td>
                            <div class="fs-7 text-dark">{{ $payment->payment_reference ?: '—' }}</div>
                            @if($payment->transaction_id)
                                <div class="text-muted fs-8">TXN: {{ $payment->transaction_id }}</div>
                            @endif
                        </td>
                        <td class="text-muted fs-7" title="{{ $payment->notes }}">
                            {{ Str::limit($payment->notes ?: 'No notes', 35) }}
                        </td>
                        <td class="text-end fw-bold text-success">
                            ₹{{ number_format($payment->amount, 2) }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center py-5 text-muted">
                            <i class="bi bi-credit-card-2-back" style="font-size: 32px;"></i>
                            <div class="mt-2">No payment transactions found.</div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    @if($payments->hasPages())
        <div class="card-footer bg-white border-top">
            {{ $payments->links() }}
        </div>
    @endif
</div>
@endsection
