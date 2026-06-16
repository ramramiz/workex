@extends('layouts.app')

@section('title', 'User Logins')
@section('page-title', 'User Logins')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('settings.index') }}">Settings</a></li>
    <li class="breadcrumb-item active">User Logins</li>
@endsection

@section('content')
<div class="row g-4">
    <!-- Left Navigation Sidebar for Settings -->
    <div class="col-12 col-md-3">
        <div class="card">
            @include('settings.sidebar')
        </div>
    </div>

    <!-- Users Content -->
    <div class="col-12 col-md-9">
        <div class="card">
            <div class="card-header d-flex align-items-center justify-content-between flex-wrap gap-2">
                <h5 class="mb-0">System Users</h5>
                <a href="{{ route('users.create') }}" class="btn btn-primary btn-sm">
                    <i class="bi bi-person-plus me-1"></i> Add User Login
                </a>
            </div>

            <!-- Search -->
            <div class="card-body bg-light border-bottom py-3">
                <form method="GET" action="{{ route('users.index') }}" class="row g-3">
                    <div class="col-12 col-md-9">
                        <div class="input-group input-group-sm">
                            <span class="input-group-text"><i class="bi bi-search"></i></span>
                            <input type="text" name="search" class="form-control" placeholder="Search by name or email..." value="{{ request('search') }}">
                        </div>
                    </div>
                    <div class="col-12 col-md-3 d-grid">
                        <button type="submit" class="btn btn-primary btn-sm">Search</button>
                    </div>
                </form>
            </div>
            
            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead>
                        <tr>
                            <th>User</th>
                            <th>Role</th>
                            <th>Status</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($users as $user)
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <img src="{{ $user->avatar_url }}" alt="" class="avatar-circle" style="width: 32px; height: 32px;">
                                        <div>
                                            <div class="fw-semibold">{{ $user->name }}</div>
                                            <div class="text-muted fs-7">{{ $user->email }}</div>
                                            @if($user->emails && $user->emails->count() > 0)
                                                <div class="mt-1 d-flex flex-wrap gap-1">
                                                    @foreach($user->emails as $secEmail)
                                                        <span class="badge bg-light text-secondary border font-monospace" style="font-size: 0.7rem;">
                                                            {{ $secEmail->email }}
                                                        </span>
                                                    @endforeach
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle">
                                        {{ $user->role->name ?? 'N/A' }}
                                    </span>
                                </td>
                                <td>
                                    @if(($user->status ?? 'active') === 'active')
                                        <span class="badge bg-success-subtle text-success border border-success-subtle">Active</span>
                                    @else
                                        <span class="badge bg-danger-subtle text-danger border border-danger-subtle">Inactive</span>
                                    @endif
                                </td>
                                <td class="text-end">
                                    <div class="d-inline-flex gap-2">
                                        <a href="{{ route('users.emails.index', $user) }}" class="btn btn-outline-info btn-sm" title="Manage Emails">
                                            <i class="bi bi-envelope"></i>
                                        </a>
                                        <a href="{{ route('users.edit', $user) }}" class="btn btn-outline-primary btn-sm">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        @if($user->id !== auth()->id())
                                            <form method="POST" action="{{ route('users.destroy', $user) }}" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this user?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-outline-danger btn-sm">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center py-5 text-muted">
                                    <i class="bi bi-people" style="font-size: 32px;"></i>
                                    <div class="mt-2">No users found.</div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($users->hasPages())
                <div class="card-footer bg-white border-top">
                    {{ $users->withQueryString()->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
