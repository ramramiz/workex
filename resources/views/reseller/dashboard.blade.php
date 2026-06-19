@extends('layouts.app')

@section('title', 'Reseller Dashboard')
@section('page-title', 'Companies Management')

@section('content')
<div class="row g-3 mb-4">
    <div class="col-md-6 col-lg-3">
        <div class="stat-card">
            <div class="d-flex align-items-center justify-content-between mb-3">
                <div style="width:46px;height:46px;border-radius:12px;background:#ede9fe;display:flex;align-items:center;justify-content:center;">
                    <i class="bi bi-building" style="font-size:22px;color:#6366f1;"></i>
                </div>
            </div>
            <div style="font-size:28px;font-weight:700;line-height:1;">{{ $companies->count() }}</div>
            <div style="font-size:13px;color:#64748b;margin-top:4px;">Total Companies</div>
        </div>
    </div>
    <div class="col-md-6 col-lg-3">
        <div class="stat-card">
            <div class="d-flex align-items-center justify-content-between mb-3">
                <div style="width:46px;height:46px;border-radius:12px;background:#dcfce7;display:flex;align-items:center;justify-content:center;">
                    <i class="bi bi-check-circle" style="font-size:22px;color:#16a34a;"></i>
                </div>
            </div>
            <div style="font-size:28px;font-weight:700;line-height:1;">{{ $companies->where('status', 'active')->count() }}</div>
            <div style="font-size:13px;color:#64748b;margin-top:4px;">Active Companies</div>
        </div>
    </div>
</div>

<div class="card shadow-sm border-0">
    <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
        <h5 class="card-title mb-0 fw-bold text-dark"><i class="bi bi-list-task me-2 text-primary"></i>Company Accounts</h5>
        <a href="{{ route('reseller.companies.create') }}" class="btn btn-primary btn-sm"><i class="bi bi-plus-lg me-1"></i>Create New Company</a>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead>
                    <tr>
                        <th>Company Name</th>
                        <th>Administrator</th>
                        <th>Admin Email</th>
                        <th>Active Staff</th>
                        <th>Status</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($companies as $company)
                        <tr>
                            <td class="fw-bold text-dark">
                                {{ $company->name }}
                                @if($company->id === 1)
                                    <span class="badge bg-secondary-subtle text-secondary ms-1">Default System</span>
                                @endif
                            </td>
                            <td>{{ $company->admin->name ?? 'N/A' }}</td>
                            <td>{{ $company->admin->email ?? 'N/A' }}</td>
                            <td>
                                <span class="badge bg-light text-dark border px-2.5 py-1 fw-semibold">
                                    {{ $company->users_count }} staff
                                </span>
                            </td>
                            <td>
                                @if($company->status === 'active')
                                    <span class="badge bg-success-subtle text-success border border-success-subtle">Active</span>
                                @else
                                    <span class="badge bg-danger-subtle text-danger border border-danger-subtle">Suspended</span>
                                @endif
                            </td>
                            <td class="text-end">
                                <a href="{{ route('reseller.companies.edit', $company) }}" class="btn btn-outline-primary btn-sm px-3 me-1">
                                    <i class="bi bi-pencil me-1"></i>Edit
                                </a>
                                @if($company->id !== 1)
                                    <form action="{{ route('reseller.companies.toggle-status', $company) }}" method="POST" class="d-inline">
                                        @csrf
                                        @if($company->status === 'active')
                                            <button type="submit" class="btn btn-outline-danger btn-sm px-3">
                                                <i class="bi bi-slash-circle me-1"></i>Suspend
                                            </button>
                                        @else
                                            <button type="submit" class="btn btn-outline-success btn-sm px-3">
                                                <i class="bi bi-check-circle me-1"></i>Activate
                                            </button>
                                        @endif
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-4 text-muted">No companies registered yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
