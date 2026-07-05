@extends('layouts.app')

@section('title', 'Proforma Invoices')
@section('page-title', 'Proforma Invoices')

@section('breadcrumb')
    <li class="breadcrumb-item active">Proforma Invoices</li>
@endsection

@section('content')
<div class="card">
    <div class="card-header d-flex align-items-center justify-content-between flex-wrap gap-3">
        <h5 class="mb-0">Proforma Invoices Directory</h5>
        @if(auth()->user()->isAdminOrAbove() || auth()->user()->isAccounts())
            <a href="{{ route('proforma-invoices.create') }}" class="btn btn-primary btn-sm">
                <i class="bi bi-file-earmark-plus me-1"></i> Create Proforma Invoice
            </a>
        @endif
    </div>

    <!-- Filters -->
    <div class="card-body bg-light border-bottom py-3">
        <form method="GET" action="{{ route('proforma-invoices.index') }}" class="row g-3">
            <div class="col-12 col-md-4">
                <div class="input-group input-group-sm">
                    <span class="input-group-text"><i class="bi bi-search"></i></span>
                    <input type="text" name="search" class="form-control" placeholder="Search proforma number..." value="{{ request('search') }}">
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
                    <option value="draft" {{ request('status') === 'draft' ? 'selected' : '' }}>Draft</option>
                    <option value="sent" {{ request('status') === 'sent' ? 'selected' : '' }}>Sent</option>
                    <option value="converted" {{ request('status') === 'converted' ? 'selected' : '' }}>Converted</option>
                    <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
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
                    <th>Proforma #</th>
                    <th>Client / Project</th>
                    <th>Date</th>
                    <th>Due Date</th>
                    <th>Total</th>
                    <th>Status</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($proformas as $pf)
                    <tr>
                        <td>
                            <div class="fw-semibold">{{ $pf->proforma_number }}</div>
                        </td>
                        <td>
                            <div class="fw-semibold text-dark">{{ $pf->client->company_name ?? '—' }}</div>
                            <small class="text-muted d-block" style="font-size:11px;">Project: {{ $pf->project->name ?? '—' }}</small>
                        </td>
                        <td>{{ $pf->proforma_date ? $pf->proforma_date->format('d M Y') : '—' }}</td>
                        <td>
                            @if($pf->due_date)
                                <span class="{{ $pf->is_overdue ? 'text-danger fw-bold' : '' }}">
                                    {{ $pf->due_date->format('d M Y') }}
                                </span>
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>
                        <td class="fw-bold">₹{{ number_format($pf->total, 2) }}</td>
                        <td>
                            @if($pf->status === 'converted')
                                <span class="badge bg-success-subtle text-success border border-success-subtle text-capitalize">{{ $pf->status }}</span>
                            @elseif($pf->status === 'sent')
                                <span class="badge bg-primary-subtle text-primary border border-primary-subtle text-capitalize">{{ $pf->status }}</span>
                            @elseif($pf->status === 'cancelled')
                                <span class="badge bg-danger-subtle text-danger border border-danger-subtle text-capitalize">{{ $pf->status }}</span>
                            @else
                                <span class="badge bg-warning-subtle text-warning border border-warning-subtle text-capitalize">{{ $pf->status }}</span>
                            @endif
                        </td>
                        <td class="text-end">
                            <div class="d-inline-flex gap-2">
                                <a href="{{ route('proforma-invoices.show', $pf) }}" class="btn btn-outline-secondary btn-sm" title="View details">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <a href="{{ route('proforma-invoices.pdf', $pf) }}" class="btn btn-outline-info btn-sm text-info" title="Download PDF">
                                    <i class="bi bi-file-pdf"></i>
                                </a>
                                @if($pf->status !== 'converted' && $pf->status !== 'cancelled')
                                    <a href="{{ route('proforma-invoices.edit', $pf) }}" class="btn btn-outline-primary btn-sm" title="Edit">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <form method="POST" action="{{ route('proforma-invoices.destroy', $pf) }}" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this proforma invoice?');">
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
                            <i class="bi bi-file-earmark-ruled" style="font-size: 32px;"></i>
                            <div class="mt-2">No proforma invoices found.</div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    @if($proformas->hasPages())
        <div class="card-footer bg-white border-top">
            {{ $proformas->withQueryString()->links() }}
        </div>
    @endif
</div>
@endsection
