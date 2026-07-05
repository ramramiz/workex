@extends('layouts.app')

@section('title', 'Payments')
@section('page-title', 'Payments')

@section('breadcrumb')
    <li class="breadcrumb-item active">Payments</li>
@endsection

@section('content')
<div class="card">
    <div class="card-header d-flex align-items-center justify-content-between flex-wrap gap-3">
        <h5 class="mb-0">Payments Registry</h5>
        @if(auth()->user()->isAdminOrAbove() || auth()->user()->isAccounts())
            <a href="{{ route('payments.create') }}" class="btn btn-primary btn-sm">
                <i class="bi bi-cash-card me-1"></i> Record Payment
            </a>
        @endif
    </div>

    <!-- Filters -->
    <div class="card-body bg-light border-bottom py-3">
        <form method="GET" action="{{ route('payments.index') }}" class="row g-3">
            <div class="col-12 col-md-8">
                <select name="client" class="form-select form-select-sm">
                    <option value="">All Clients</option>
                    @foreach($clients as $c)
                        <option value="{{ $c->id }}" {{ request('client') == $c->id ? 'selected' : '' }}>{{ $c->company_name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-12 col-md-4 d-grid">
                <button type="submit" class="btn btn-primary btn-sm">Filter</button>
            </div>
        </form>
    </div>

    <!-- Table -->
    <div class="table-responsive">
        <table class="table align-middle mb-0">
            <thead>
                <tr>
                    <th>Ref Number / Date</th>
                    <th>Invoice Number</th>
                    <th>Client Name</th>
                    <th>Payment Mode</th>
                    <th>Transaction ID</th>
                    <th>Paid Amount</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($payments as $payment)
                    <tr>
                        <td>
                            <div class="fw-semibold text-primary font-monospace">{{ $payment->payment_reference }}</div>
                            <small class="text-muted d-block" style="font-size:11px;">Paid: {{ \Carbon\Carbon::parse($payment->payment_date)->format('d M Y') }}</small>
                        </td>
                        <td>
                            @if($payment->invoice)
                                <a href="{{ route('invoices.show', $payment->invoice) }}" class="fw-semibold text-decoration-none text-dark">{{ $payment->invoice->invoice_number }}</a>
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>
                        <td>
                            <span class="fw-semibold text-dark">{{ $payment->client->company_name ?? '—' }}</span>
                        </td>
                        <td>
                            @php
                                $matchedBank = $banks->firstWhere('name', $payment->payment_mode);
                                $payModeDisplay = $matchedBank 
                                    ? ($matchedBank->name . ' - ' . $matchedBank->branch . ' - ****' . substr($matchedBank->account_number, -4)) 
                                    : $payment->payment_mode;
                            @endphp
                            <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle text-capitalize fs-8">
                                {{ $payModeDisplay }}
                            </span>
                        </td>
                        <td>
                            <span class="font-monospace text-muted">{{ $payment->transaction_id ?? '—' }}</span>
                        </td>
                        <td>
                            <span class="fw-bold text-success">₹{{ number_format($payment->amount, 2) }}</span>
                        </td>
                        <td class="text-end">
                            <div class="d-inline-flex gap-2">
                                <a href="{{ route('payments.show', $payment) }}" class="btn btn-outline-secondary btn-sm" title="View details">
                                    <i class="bi bi-eye"></i>
                                </a>
                                @if(auth()->user()->isAdminOrAbove() || auth()->user()->isAccounts())
                                    <form method="POST" action="{{ route('payments.destroy', $payment) }}" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this payment log? This will NOT restore the invoice balance automatically.');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-outline-danger btn-sm" title="Delete">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center py-5 text-muted">
                            <i class="bi bi-credit-card" style="font-size: 32px;"></i>
                            <div class="mt-2">No payments recorded.</div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    @if($payments->hasPages())
        <div class="card-footer bg-white border-top">
            {{ $payments->withQueryString()->links() }}
        </div>
    @endif
</div>
@endsection
