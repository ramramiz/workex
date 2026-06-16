@extends('layouts.app')

@section('title', 'Quotations')
@section('page-title', 'Quotations')

@section('breadcrumb')
    <li class="breadcrumb-item active">Quotations</li>
@endsection

@section('content')
<div class="card">
    <div class="card-header d-flex align-items-center justify-content-between flex-wrap gap-3">
        <h5 class="mb-0">Quotations Registry</h5>
        <a href="{{ route('quotations.create') }}" class="btn btn-primary btn-sm">
            <i class="bi bi-file-earmark-plus me-1"></i> Add Quotation
        </a>
    </div>

    <!-- Filters -->
    <div class="card-body bg-light border-bottom py-3">
        <form method="GET" action="{{ route('quotations.index') }}" class="row g-3">
            <div class="col-12 col-md-8">
                <div class="input-group input-group-sm">
                    <span class="input-group-text"><i class="bi bi-search"></i></span>
                    <input type="text" name="search" class="form-control" placeholder="Search by quotation number or client..." value="{{ request('search') }}">
                </div>
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
                    <th>Quotation Info</th>
                    <th>Client Name</th>
                    <th>Subtotal</th>
                    <th>Grand Total</th>
                    <th>Valid Until</th>
                    <th>Status</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($quotations as $quo)
                    <tr>
                        <td>
                            <div class="fw-semibold">{{ $quo->quotation_number }}</div>
                            <small class="text-muted" style="font-size: 11px;">{{ $quo->title }}</small>
                        </td>
                        <td>
                            @if($quo->client)
                                <a href="{{ route('clients.show', $quo->client) }}" class="fw-semibold text-decoration-none">{{ $quo->client->company_name }}</a>
                            @elseif($quo->lead)
                                <span class="fw-medium text-dark">{{ $quo->lead->client_name }}</span>
                                <small class="text-muted d-block" style="font-size:11px;">(Lead)</small>
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>
                        <td>₹{{ number_format($quo->subtotal ?? 0, 2) }}</td>
                        <td class="fw-bold text-success">₹{{ number_format($quo->total ?? 0, 2) }}</td>
                        <td>
                            @if($quo->valid_until)
                                <span class="{{ \Carbon\Carbon::parse($quo->valid_until)->isPast() && $quo->status === 'draft' ? 'text-danger' : '' }}">
                                    {{ \Carbon\Carbon::parse($quo->valid_until)->format('d M Y') }}
                                </span>
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>
                        <td>
                            @if($quo->status === 'accepted')
                                <span class="badge bg-success-subtle text-success border border-success-subtle text-capitalize">{{ $quo->status }}</span>
                            @elseif($quo->status === 'sent')
                                <span class="badge bg-primary-subtle text-primary border border-primary-subtle text-capitalize">{{ $quo->status }}</span>
                            @elseif($quo->status === 'declined')
                                <span class="badge bg-danger-subtle text-danger border border-danger-subtle text-capitalize">{{ $quo->status }}</span>
                            @else
                                <span class="badge bg-warning-subtle text-warning border border-warning-subtle text-capitalize">{{ $quo->status }}</span>
                            @endif
                        </td>
                        <td class="text-end">
                            <div class="d-inline-flex gap-2">
                                <a href="{{ route('quotations.show', $quo) }}" class="btn btn-outline-secondary btn-sm" title="View details">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <a href="{{ route('quotations.pdf', $quo) }}" class="btn btn-outline-info btn-sm" title="Download PDF">
                                    <i class="bi bi-file-pdf"></i>
                                </a>
                                <a href="{{ route('quotations.edit', $quo) }}" class="btn btn-outline-primary btn-sm" title="Edit">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <form method="POST" action="{{ route('quotations.destroy', $quo) }}" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this quotation?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-outline-danger btn-sm" title="Delete">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center py-5 text-muted">
                            <i class="bi bi-file-earmark-text" style="font-size: 32px;"></i>
                            <div class="mt-2">No quotations generated yet.</div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    @if($quotations->hasPages())
        <div class="card-footer bg-white border-top">
            {{ $quotations->withQueryString()->links() }}
        </div>
    @endif
</div>
@endsection
