@extends('layouts.app')

@section('title', 'Manage Emails - ' . $user->name)
@section('page-title', 'Manage Email Accounts')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('settings.index') }}">Settings</a></li>
    <li class="breadcrumb-item"><a href="{{ route('users.index') }}">User Logins</a></li>
    <li class="breadcrumb-item active">{{ $user->name }}'s Emails</li>
@endsection

@section('content')
<div class="row g-4">
    <!-- Left Navigation Sidebar for Settings -->
    <div class="col-12 col-md-3">
        <div class="card shadow-sm border-0">
            @include('settings.sidebar')
        </div>
    </div>

    <!-- Emails Content -->
    <div class="col-12 col-md-9">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white border-bottom py-3 d-flex align-items-center justify-content-between">
                <div>
                    <h5 class="mb-0 text-dark fw-bold">Email Accounts for {{ $user->name }}</h5>
                    <p class="text-muted mb-0 small">Add and manage secondary email addresses associated with this user account.</p>
                </div>
                <a href="{{ route('users.index') }}" class="btn btn-outline-secondary btn-sm d-flex align-items-center gap-1">
                    <i class="bi bi-arrow-left"></i> Back to Users
                </a>
            </div>
            
            <div class="card-body py-4">
                <!-- Info Alert: Primary Email -->
                <div class="alert alert-light border d-flex align-items-center justify-content-between mb-4 p-3 rounded">
                    <div class="d-flex align-items-center gap-3">
                        <div class="bg-primary-subtle text-primary p-2.5 rounded-circle d-flex align-items-center justify-content-center" style="width: 45px; height: 45px;">
                            <i class="bi bi-shield-lock-fill fs-5"></i>
                        </div>
                        <div>
                            <h6 class="mb-0 fw-semibold text-dark">Primary Login Account</h6>
                            <p class="text-muted mb-0 small">This email is used for credentials and notifications.</p>
                        </div>
                    </div>
                    <span class="badge bg-primary px-3 py-2 fs-7">{{ $user->email }}</span>
                </div>

                <div class="row g-4 mt-2">
                    <!-- Add Email Form (Left Column) -->
                    <div class="col-12 col-lg-5">
                        <div class="card border bg-light shadow-none">
                            <div class="card-body">
                                <h6 class="fw-bold mb-3 text-dark">Add Email Address</h6>
                                <form method="POST" action="{{ route('users.emails.store', $user) }}">
                                    @csrf
                                    
                                    <div class="mb-3">
                                        <label for="emailInput" class="form-label small text-uppercase fw-semibold text-muted">Email Address</label>
                                        <input type="email" name="email" id="emailInput" class="form-control @error('email') is-invalid @enderror" value="{{ old('email') }}" required placeholder="e.g. user@example.com">
                                        @error('email')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <button type="submit" class="btn btn-primary w-100 d-flex align-items-center justify-content-center gap-2">
                                        <i class="bi bi-plus-circle"></i> Add Email Address
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- Associated Emails List (Right Column) -->
                    <div class="col-12 col-lg-7">
                        <h6 class="fw-bold mb-3 text-dark">Associated Emails ({{ $user->emails->count() }})</h6>
                        
                        <div class="border rounded bg-white">
                            @if($user->emails->count() > 0)
                                <div class="table-responsive">
                                    <table class="table align-middle mb-0">
                                        <thead class="table-light">
                                            <tr>
                                                <th class="py-2.5 fs-7 text-uppercase text-muted">Email</th>
                                                <th class="py-2.5 fs-7 text-uppercase text-muted">Added On</th>
                                                <th class="py-2.5 fs-7 text-uppercase text-muted text-end">Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($user->emails as $email)
                                                <tr>
                                                    <td class="fw-semibold text-dark">{{ $email->email }}</td>
                                                    <td class="text-muted small">{{ $email->created_at->format('M d, Y H:i') }}</td>
                                                    <td class="text-end">
                                                        <form method="POST" action="{{ route('users.emails.destroy', [$user, $email]) }}" class="d-inline" onsubmit="return confirm('Are you sure you want to remove this email address?');">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="btn btn-link text-danger p-0" title="Delete Email">
                                                                <i class="bi bi-trash fs-5"></i>
                                                            </button>
                                                        </form>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <div class="text-center py-5 text-muted">
                                    <i class="bi bi-envelope-x fs-1 opacity-50"></i>
                                    <div class="mt-2 fw-medium">No secondary emails associated.</div>
                                    <div class="small text-muted">Add emails using the form on the left.</div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>
@endsection
