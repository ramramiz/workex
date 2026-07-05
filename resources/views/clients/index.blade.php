@extends('layouts.app')

@section('title', 'Clients')
@section('page-title', 'Clients')

@section('breadcrumb')
    <li class="breadcrumb-item active">Clients</li>
@endsection

@section('content')
<div class="card">
    <div class="card-header d-flex align-items-center justify-content-between flex-wrap gap-3">
        <h5 class="mb-0">Client Directory</h5>
        <div class="d-flex gap-2">
            @if(auth()->user()->isSuperAdmin())
                <a href="{{ route('clients.import.template') }}" class="btn btn-outline-secondary btn-sm">
                    <i class="bi bi-download me-1"></i> Download Template
                </a>
                <button type="button" class="btn btn-outline-primary btn-sm" data-bs-toggle="modal" data-bs-target="#importClientsModal">
                    <i class="bi bi-file-earmark-arrow-up me-1"></i> Import Excel
                </button>
            @endif
            <a href="{{ route('clients.create') }}" class="btn btn-primary btn-sm">
                <i class="bi bi-building-add me-1"></i> Add Client
            </a>
        </div>
    </div>

    <!-- Filters -->
    <div class="card-body bg-light border-bottom py-3">
        <form method="GET" action="{{ route('clients.index') }}" class="row g-3">
            <div class="col-12 col-md-6">
                <div class="input-group input-group-sm">
                    <span class="input-group-text"><i class="bi bi-search"></i></span>
                    <input type="text" name="search" class="form-control" placeholder="Search by company, person or email..." value="{{ request('search') }}">
                </div>
            </div>
            <div class="col-12 col-md-3">
                <select name="status" class="form-select form-select-sm">
                    <option value="">All Statuses</option>
                    <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                    <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                </select>
            </div>
            <div class="col-12 col-md-3 d-grid">
                <button type="submit" class="btn btn-primary btn-sm">Filter</button>
            </div>
        </form>
    </div>

    <!-- Table -->
    <div class="table-responsive">
        <table class="table align-middle mb-0">
            <thead>
                <tr>
                    <th>Company Name</th>
                    <th>Contact Person</th>
                    <th>Email & Phone</th>
                    <th>Website</th>
                    <th>Status</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($clients as $client)
                    <tr>
                        <td>
                            <div class="fw-semibold text-primary">{{ $client->company_name }}</div>
                            @if($client->gst_number)
                                <small class="text-muted" style="font-size: 11px;">GST: {{ $client->gst_number }}</small>
                            @endif
                        </td>
                        <td>
                            <div class="fw-medium">{{ $client->contact_person ?? '—' }}</div>
                        </td>
                        <td>
                            <div>{{ $client->email }}</div>
                            <small class="text-muted">{{ $client->mobile ?? $client->phone ?? '—' }}</small>
                        </td>
                        <td>
                            @if($client->website)
                                <a href="{{ Str::startsWith($client->website, 'http') ? $client->website : 'https://' . $client->website }}" target="_blank" class="text-decoration-none text-truncate d-inline-block" style="max-width: 150px;">
                                    <i class="bi bi-globe me-1"></i> {{ $client->website }}
                                </a>
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>
                        <td>
                            @if(($client->status ?? 'active') === 'active')
                                <span class="badge bg-success-subtle text-success border border-success-subtle">Active</span>
                            @else
                                <span class="badge bg-danger-subtle text-danger border border-danger-subtle">Inactive</span>
                            @endif
                        </td>
                        <td class="text-end">
                            <div class="d-inline-flex gap-2">
                                <a href="{{ route('clients.show', $client) }}" class="btn btn-outline-secondary btn-sm" title="View details">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <a href="{{ route('clients.edit', $client) }}" class="btn btn-outline-primary btn-sm" title="Edit">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <form method="POST" action="{{ route('clients.destroy', $client) }}" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this client?');">
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
                        <td colspan="6" class="text-center py-5 text-muted">
                            <i class="bi bi-building" style="font-size: 32px;"></i>
                            <div class="mt-2">No clients found.</div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    @if($clients->hasPages())
        <div class="card-footer bg-white border-top">
            {{ $clients->withQueryString()->links() }}
        </div>
    @endif
</div>

@if(auth()->user()->isSuperAdmin())
<div class="modal fade" id="importClientsModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow" style="border-radius: 12px;">
            <div class="modal-header bg-dark text-white border-0 py-3" style="border-radius: 12px 12px 0 0;">
                <h6 class="modal-title fw-bold"><i class="bi bi-file-earmark-arrow-up me-2"></i>Import Clients</h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ route('clients.import.preview') }}" enctype="multipart/form-data">
                @csrf
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-medium text-secondary">Upload Excel File (.xlsx, .xls, .csv)</label>
                        <input type="file" name="file" class="form-control" accept=".xlsx,.xls,.csv" required style="border-radius: 8px;">
                        <small class="text-muted d-block mt-2">
                            Download the template first. The fields <strong>Company Name</strong>, <strong>Contact Person</strong>, and <strong>Email</strong> (which must be unique) are required.
                        </small>
                    </div>
                </div>
                <div class="modal-footer border-top-0 p-3 bg-light" style="border-radius: 0 0 12px 12px;">
                    <button type="button" class="btn btn-secondary btn-sm px-3" data-bs-dismiss="modal" style="border-radius:6px;">Cancel</button>
                    <button type="submit" class="btn btn-primary btn-sm px-4" style="border-radius:6px;">Upload & Import</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif
@endsection
