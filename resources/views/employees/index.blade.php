@extends('layouts.app')

@section('title', 'Employees')
@section('page-title', 'Employees')

@section('breadcrumb')
    <li class="breadcrumb-item active">Employees</li>
@endsection

@section('content')
<div class="card">
    <div class="card-header d-flex align-items-center justify-content-between flex-wrap gap-3">
        <h5 class="mb-0">Employee List</h5>
        @if(auth()->user()->isAdminOrAbove() || auth()->user()->isHR())
            <a href="{{ route('employees.create') }}" class="btn btn-primary btn-sm">
                <i class="bi bi-person-plus me-1"></i> Add Employee
            </a>
        @endif
    </div>
    
    <!-- Filters -->
    <div class="card-body border-bottom py-3" style="background: var(--body-bg);">
        <form method="GET" action="{{ route('employees.index') }}" class="row g-3">
            <div class="col-12 col-md-4">
                <div class="input-group input-group-sm">
                    <span class="input-group-text"><i class="bi bi-search"></i></span>
                    <input type="text" name="search" class="form-control" placeholder="Search name, email, code..." value="{{ request('search') }}">
                </div>
            </div>
            <div class="col-12 col-md-3">
                <select name="department" class="form-select form-select-sm">
                    <option value="">All Departments</option>
                    @foreach($departments as $dept)
                        <option value="{{ $dept->id }}" {{ request('department') == $dept->id ? 'selected' : '' }}>{{ $dept->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-12 col-md-3">
                <select name="status" class="form-select form-select-sm">
                    <option value="">All Statuses</option>
                    <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                    <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
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
                    <th>Employee</th>
                    <th>Dept & Designation</th>
                    <th>Team Leader</th>
                    <th>Joining Date</th>
                    <th>Salary</th>
                    <th>Status</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($employees as $emp)
                    <tr>
                        <td>
                            <div class="d-flex align-items-center gap-3">
                                <img src="{{ $emp->user?->avatar_url ?? 'https://ui-avatars.com/api/?name=' . urlencode($emp->name) }}" alt="{{ $emp->name }}" class="avatar-circle" style="width: 40px; height: 40px;">
                                <div>
                                    <div class="fw-semibold">{{ $emp->name }}</div>
                                    <div class="text-muted" style="font-size: 12px;">{{ $emp->employee_code }} • {{ $emp->user?->email ?? 'N/A' }}</div>
                                </div>
                            </div>
                        </td>
                        <td>
                            <div class="fw-medium">{{ $emp->department->name ?? 'N/A' }}</div>
                            <div class="text-muted" style="font-size: 12px;">{{ $emp->designation->name ?? 'N/A' }}</div>
                        </td>
                        <td>
                            @if($emp->teamLeader)
                                <div class="d-flex align-items-center gap-2">
                                    <img src="{{ $emp->teamLeader->avatar_url }}" alt="" class="avatar-circle" style="width: 24px; height: 24px;">
                                    <span class="fs-7">{{ $emp->teamLeader->name }}</span>
                                </div>
                            @else
                                <span class="text-muted fs-7">—</span>
                            @endif
                        </td>
                        <td>{{ $emp->joining_date ? $emp->joining_date->format('d M Y') : 'N/A' }}</td>
                        <td>
                            @if($emp->salary)
                                <span class="fw-medium">₹{{ number_format($emp->salary, 2) }}</span>
                                <span class="text-muted" style="font-size: 11px;">/ {{ $emp->salary_type }}</span>
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>
                        <td>
                            @if($emp->status === 'active')
                                <span class="badge bg-success-subtle text-success border border-success-subtle">Active</span>
                            @else
                                <span class="badge bg-danger-subtle text-danger border border-danger-subtle">Inactive</span>
                            @endif
                        </td>
                        <td class="text-end">
                            <div class="d-inline-flex gap-2">
                                <a href="{{ route('employees.show', $emp) }}" class="btn btn-outline-secondary btn-sm" title="View details">
                                    <i class="bi bi-eye"></i>
                                </a>
                                @if(auth()->user()->isAdminOrAbove() || auth()->user()->isHR())
                                    @if($emp->user)
                                        <a href="{{ route('mailbox.index', ['user_id' => $emp->user->id]) }}" target="_blank" class="btn btn-outline-info btn-sm" title="Open Mailbox">
                                            <i class="bi bi-envelope"></i>
                                        </a>
                                    @endif
                                    <a href="{{ route('employees.edit', $emp) }}" class="btn btn-outline-primary btn-sm" title="Edit">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <form method="POST" action="{{ route('employees.toggle-status', $emp) }}" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-outline-warning btn-sm" title="Toggle Status">
                                            <i class="bi bi-power"></i>
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center py-5 text-muted">
                            <i class="bi bi-people" style="font-size: 32px;"></i>
                            <div class="mt-2">No employees found.</div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    @if($employees->hasPages())
        <div class="card-footer border-top" style="background: var(--card-bg);">
            {{ $employees->withQueryString()->links() }}
        </div>
    @endif
</div>
@endsection
