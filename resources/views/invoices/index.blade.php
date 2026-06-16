@extends('layouts.app')

@section('title', 'Invoices')
@section('page-title', 'Invoices')

@section('breadcrumb')
    <li class="breadcrumb-item active">Invoices</li>
@endsection

@section('content')
<div class="card">
    <div class="card-header d-flex align-items-center justify-content-between flex-wrap gap-3">
        <h5 class="mb-0">Invoices Directory</h5>
        @if(auth()->user()->isAdminOrAbove() || auth()->user()->isAccounts())
            <a href="{{ route('invoices.create') }}" class="btn btn-primary btn-sm">
                <i class="bi bi-file-earmark-diff me-1"></i> Create Invoice
            </a>
        @endif
    </div>

    <!-- Filters -->
    <div class="card-body bg-light border-bottom py-3">
        <form method="GET" action="{{ route('invoices.index') }}" class="row g-3">
            <div class="col-12 col-md-4">
                <div class="input-group input-group-sm">
                    <span class="input-group-text"><i class="bi bi-search"></i></span>
                    <input type="text" name="search" class="form-control" placeholder="Search invoice number..." value="{{ request('search') }}">
                </div>
            </div>
            <div class="col-12 col-md-3">
                <select name="client" class="form-select form-select-sm">
                    <option value="">All Clients</option>
                    @foreach($clients as $c)
                        <option value="{{ $c->id }}" {{ request('client') == $c->id ? 'selected' : '' }}>{{ $c->company_name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-12 col-md-3">
                <select name="status" class="form-select form-select-sm">
                    <option value="">All Statuses</option>
                    <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="sent" {{ request('status') === 'sent' ? 'selected' : '' }}>Sent</option>
                    <option value="paid" {{ request('status') === 'paid' ? 'selected' : '' }}>Paid</option>
                </select>
            </div>
            <div class="col-12 col-md-2 d-grid">
                <button type="submit" class="btn btn-primary btn-sm">Filter</button>
            </div>
        </form>
    </div>

    <!-- Table -->
    <div class="table-responsive">
        <table class="table align-middle mb-0">
            <thead>
                <tr>
                    <th>Invoice #</th>
                    <th>Client / Project</th>
                    <th>Invoice Date</th>
                    <th>Due Date</th>
                    <th>Total</th>
                    <th>Balance</th>
                    <th>Status</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($invoices as $inv)
                    <tr>
                        <td>
                            <div class="fw-semibold">{{ $inv->invoice_number }}</div>
                        </td>
                        <td>
                            <div class="fw-semibold text-dark">{{ $inv->client->company_name ?? '—' }}</div>
                            <small class="text-muted d-block" style="font-size:11px;">Project: {{ $inv->project->name ?? '—' }}</small>
                        </td>
                        <td>{{ $inv->invoice_date ? $inv->invoice_date->format('d M Y') : '—' }}</td>
                        <td>
                            @if($inv->due_date)
                                <span class="{{ $inv->is_overdue ? 'text-danger fw-bold' : '' }}">
                                    {{ $inv->due_date->format('d M Y') }}
                                </span>
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>
                        <td class="fw-bold">₹{{ number_format($inv->total, 2) }}</td>
                        <td class="fw-medium text-danger">₹{{ number_format($inv->balance_amount, 2) }}</td>
                        <td>
                            @if($inv->status === 'paid')
                                <span class="badge bg-success-subtle text-success border border-success-subtle text-capitalize">{{ $inv->status }}</span>
                            @elseif($inv->status === 'sent')
                                <span class="badge bg-primary-subtle text-primary border border-primary-subtle text-capitalize">{{ $inv->status }}</span>
                            @else
                                <span class="badge bg-warning-subtle text-warning border border-warning-subtle text-capitalize">{{ $inv->status }}</span>
                            @endif
                        </td>
                        <td class="text-end">
                            <div class="d-inline-flex gap-2">
                                <a href="{{ route('invoices.show', $inv) }}" class="btn btn-outline-secondary btn-sm" title="View details">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <a href="{{ route('invoices.pdf', $inv) }}" class="btn btn-outline-info btn-sm text-info" title="Download PDF">
                                    <i class="bi bi-file-pdf"></i>
                                </a>
                                @if($inv->status !== 'paid')
                                    <a href="{{ route('invoices.edit', $inv) }}" class="btn btn-outline-primary btn-sm" title="Edit">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <form method="POST" action="{{ route('invoices.destroy', $inv) }}" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this invoice?');">
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
                        <td colspan="8" class="text-center py-5 text-muted">
                            <i class="bi bi-receipt" style="font-size: 32px;"></i>
                            <div class="mt-2">No invoices found.</div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    @if($invoices->hasPages())
        <div class="card-footer bg-white border-top">
            {{ $invoices->withQueryString()->links() }}
        </div>
    @endif
</div>
@endsection
