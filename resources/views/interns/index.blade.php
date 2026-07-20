@extends('layouts.app')

@section('title', 'Interns Directory')
@section('page-title', 'Interns')

@section('breadcrumb')
    <li class="breadcrumb-item active">Interns</li>
@endsection

@section('content')
<div class="row g-4 mb-4 no-print">
    <div class="col-12">
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
            <div class="btn-group shadow-sm">
                <a href="{{ route('interns.index') }}" class="btn btn-primary btn-sm active">
                    <i class="bi bi-people-fill"></i> Active Interns Directory
                </a>
                <a href="{{ route('interns.onboardings.index') }}" class="btn btn-outline-primary btn-sm">
                    <i class="bi bi-link-45deg"></i> Onboarding Links
                </a>
            </div>
        </div>
    </div>
</div>

<div class="card shadow-sm border-0">
    <div class="card-header bg-white py-3 d-flex align-items-center justify-content-between flex-wrap gap-3">
        <h5 class="mb-0 fw-bold text-dark">Interns List</h5>
        @if(auth()->user()->isAdminOrAbove() || auth()->user()->isHR())
            <a href="{{ route('interns.create') }}" class="btn btn-primary btn-sm d-inline-flex align-items-center gap-2">
                <i class="bi bi-person-plus-fill"></i> Register Intern
            </a>
        @endif
    </div>
    
    <!-- Filters -->
    <div class="card-body border-bottom py-3" style="background: var(--body-bg);">
        <form method="GET" action="{{ route('interns.index') }}" class="row g-3">
            <div class="col-12 col-md-4">
                <div class="input-group input-group-sm">
                    <span class="input-group-text"><i class="bi bi-search"></i></span>
                    <input type="text" name="search" class="form-control" placeholder="Search by name or email..." value="{{ request('search') }}">
                </div>
            </div>
            <div class="col-12 col-md-4">
                <select name="department" class="form-select form-select-sm">
                    <option value="">All Departments</option>
                    @foreach($departments as $dept)
                        <option value="{{ $dept->id }}" {{ request('department') == $dept->id ? 'selected' : '' }}>{{ $dept->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-12 col-md-4 d-flex gap-2">
                <button type="submit" class="btn btn-primary btn-sm px-4 flex-grow-1">Filter</button>
                @if(request()->filled('search') || request()->filled('department'))
                    <a href="{{ route('interns.index') }}" class="btn btn-outline-secondary btn-sm px-3">Reset</a>
                @endif
            </div>
        </form>
    </div>

    <!-- Table -->
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead>
                <tr>
                    <th class="ps-4">Intern Name</th>
                    <th>Dept & Role</th>
                    <th>Duration</th>
                    <th>Certificate Code</th>
                    <th>Status</th>
                    <th class="pe-4 text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($interns as $intern)
                    <tr>
                        <td class="ps-4">
                            <div class="d-flex align-items-center gap-3">
                                <img src="https://ui-avatars.com/api/?name={{ urlencode($intern->name) }}&background=6366f1&color=fff" alt="{{ $intern->name }}" class="avatar-circle" style="width: 40px; height: 40px; border-radius: 50%;">
                                <div>
                                    <div class="fw-semibold text-dark">{{ $intern->name }}</div>
                                    <div class="text-secondary style-subtext" style="font-size: 12.5px;">{{ $intern->email }}</div>
                                </div>
                            </div>
                        </td>
                        <td>
                            <div class="fw-semibold text-dark">{{ $intern->department->name ?? 'N/A' }}</div>
                            <div class="text-secondary style-subtext" style="font-size: 12.5px;">{{ $intern->designation->name ?? 'Intern' }}</div>
                        </td>
                        <td>
                            <div class="fw-semibold text-dark">{{ $intern->joining_date ? $intern->joining_date->format('M d, Y') : 'N/A' }}</div>
                            <div class="text-secondary style-subtext" style="font-size: 12px;">to {{ $intern->end_date ? $intern->end_date->format('M d, Y') : 'N/A' }}</div>
                        </td>
                        <td>
                            <code class="text-primary fw-medium">{{ $intern->certificate_code ?? 'Pending' }}</code>
                        </td>
                        <td>
                            @if($intern->status === 'active')
                                <span class="badge bg-success-subtle text-success border border-success-subtle px-2.5 py-1 rounded-pill">Active</span>
                            @elseif($intern->status === 'completed')
                                <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2.5 py-1 rounded-pill">Completed</span>
                            @elseif($intern->status === 'pending_onboarding')
                                <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle px-2.5 py-1 rounded-pill">Pending Onboarding</span>
                            @else
                                <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2.5 py-1 rounded-pill">Cancelled</span>
                            @endif
                        </td>
                        <td class="pe-4 text-end">
                            <div class="d-inline-flex gap-2">
                                <a href="{{ route('interns.certificate', $intern->id) }}" target="_blank" class="btn btn-sm btn-outline-primary d-inline-flex align-items-center gap-1.5" title="View Internship Certificate in new tab">
                                    <i class="bi bi-file-earmark-pdf-fill"></i> Certificate
                                </a>
                                <a href="{{ route('interns.qr-code', $intern->id) }}" class="btn btn-sm btn-outline-success d-inline-flex align-items-center gap-1" title="Download QR Code only">
                                    <i class="bi bi-qr-code"></i> QR Code
                                </a>
                                @if(auth()->user()->isAdminOrAbove() || auth()->user()->isHR())
                                    <a href="{{ route('interns.edit', $intern->id) }}" class="btn btn-sm btn-outline-secondary d-inline-flex align-items-center gap-1" title="Edit details">
                                        <i class="bi bi-pencil-square"></i> Edit
                                    </a>
                                    <form action="{{ route('interns.destroy', $intern->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this intern?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger d-inline-flex align-items-center gap-1" title="Delete">
                                            <i class="bi bi-trash-fill"></i> Delete
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center py-5 text-secondary">
                            <i class="bi bi-person-x" style="font-size: 32px;"></i>
                            <div class="mt-2 fw-medium">No interns found.</div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    @if($interns->hasPages())
        <div class="card-footer bg-white border-top">
            {{ $interns->withQueryString()->links() }}
        </div>
    @endif
</div>
@endsection
