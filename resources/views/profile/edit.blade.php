@extends('layouts.app')

@section('title', 'My Profile')
@section('page-title', 'My Profile')

@section('breadcrumb')
    <li class="breadcrumb-item active">My Profile</li>
@endsection

@section('content')
<div class="row g-4">
    <!-- Left Column: Forms -->
    <div class="col-12 col-lg-8">
        <!-- Profile Information -->
        <div class="card mb-4 shadow-sm border-0">
            <div class="card-header bg-white border-bottom py-3">
                <h5 class="card-title mb-0 fw-bold"><i class="bi bi-person-gear me-2 text-primary"></i>Profile Information</h5>
            </div>
            <div class="card-body p-4">
                <form method="post" action="{{ route('profile.update') }}">
                    @csrf
                    @method('patch')

                    <div class="mb-3">
                        <label for="name" class="form-label fw-semibold">Full Name <span class="text-danger">*</span></label>
                        <input type="text" id="name" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $user->name) }}" required autofocus autocomplete="name">
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-4">
                        <label for="email" class="form-label fw-semibold">Email Address <span class="text-danger">*</span></label>
                        <input type="email" id="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email', $user->email) }}" required autocomplete="username">
                        @error('email')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror

                        @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                            <div class="mt-3 p-3 bg-warning-subtle text-warning-emphasis rounded border border-warning-subtle">
                                <p class="mb-0 small">
                                    Your email address is unverified.
                                    <button form="send-verification" class="btn btn-link btn-sm p-0 align-baseline text-decoration-underline text-warning-emphasis fw-bold">
                                        Click here to re-send the verification email.
                                    </button>
                                </p>

                                @if (session('status') === 'verification-link-sent')
                                    <p class="mt-2 mb-0 small text-success fw-bold">
                                        A new verification link has been sent to your email address.
                                    </p>
                                @endif
                            </div>
                        @endif
                    </div>

                    <div class="d-flex align-items-center gap-3">
                        <button type="submit" class="btn btn-primary px-4">Save Profile</button>
                        @if (session('status') === 'profile-updated')
                            <span class="text-success small fw-semibold"><i class="bi bi-check-circle-fill me-1"></i>Saved successfully!</span>
                        @endif
                    </div>
                </form>

                @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                    <form id="send-verification" method="post" action="{{ route('verification.send') }}">
                        @csrf
                    </form>
                @endif
            </div>
        </div>

        <!-- Update Password -->
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white border-bottom py-3">
                <h5 class="card-title mb-0 fw-bold"><i class="bi bi-shield-lock me-2 text-primary"></i>Update Password</h5>
            </div>
            <div class="card-body p-4">
                <form method="post" action="{{ route('password.update') }}">
                    @csrf
                    @method('put')

                    <div class="mb-3">
                        <label for="update_password_current_password" class="form-label fw-semibold">Current Password</label>
                        <input type="password" id="update_password_current_password" name="current_password" class="form-control @error('current_password', 'updatePassword') is-invalid @enderror" autocomplete="current-password" required>
                        @error('current_password', 'updatePassword')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="update_password_password" class="form-label fw-semibold">New Password</label>
                        <input type="password" id="update_password_password" name="password" class="form-control @error('password', 'updatePassword') is-invalid @enderror" autocomplete="new-password" required>
                        @error('password', 'updatePassword')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-4">
                        <label for="update_password_password_confirmation" class="form-label fw-semibold">Confirm Password</label>
                        <input type="password" id="update_password_password_confirmation" name="password_confirmation" class="form-control @error('password_confirmation', 'updatePassword') is-invalid @enderror" autocomplete="new-password" required>
                        @error('password_confirmation', 'updatePassword')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="d-flex align-items-center gap-3">
                        <button type="submit" class="btn btn-primary px-4">Update Password</button>
                        @if (session('status') === 'password-updated')
                            <span class="text-success small fw-semibold"><i class="bi bi-check-circle-fill me-1"></i>Password updated successfully!</span>
                        @endif
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Right Column: Info & Danger Zone -->
    <div class="col-12 col-lg-4">
        <!-- Profile Details Card -->
        <div class="card mb-4 shadow-sm border-0 text-center overflow-hidden">
            <div style="height: 100px; background: linear-gradient(135deg, var(--primary), #818cf8);"></div>
            <div class="card-body p-4 position-relative" style="margin-top: -50px;">
                <img src="{{ $user->avatar_url }}" class="rounded-circle border border-4 border-white mb-3 shadow-sm" style="width: 100px; height: 100px; object-fit: cover;">
                <h4 class="fw-bold text-dark mb-1">{{ $user->name }}</h4>
                <p class="text-muted small mb-2">{{ $user->email }}</p>
                
                <span class="badge bg-primary-subtle text-primary rounded-pill px-3 py-1.5 fw-semibold mb-4">
                    {{ $user->role?->name ?? 'User' }}
                </span>

                @if($user->employee)
                    <div class="border-top pt-3 text-start">
                        <div class="row g-2 mb-2">
                            <div class="col-5 text-muted small fw-medium">Employee Code:</div>
                            <div class="col-7 text-dark small fw-bold">{{ $user->employee->employee_code }}</div>
                        </div>
                        @if($user->employee->department)
                            <div class="row g-2 mb-2">
                                <div class="col-5 text-muted small fw-medium">Department:</div>
                                <div class="col-7 text-dark small fw-bold">{{ $user->employee->department->name }}</div>
                            </div>
                        @endif
                        @if($user->employee->designation)
                            <div class="row g-2 mb-2">
                                <div class="col-5 text-muted small fw-medium">Designation:</div>
                                <div class="col-7 text-dark small fw-bold">{{ $user->employee->designation->name }}</div>
                            </div>
                        @endif
                        @if($user->employee->phone)
                            <div class="row g-2 mb-2">
                                <div class="col-5 text-muted small fw-medium">Phone:</div>
                                <div class="col-7 text-dark small fw-bold">{{ $user->employee->phone }}</div>
                            </div>
                        @endif
                        @if($user->employee->joining_date)
                            <div class="row g-2 mb-2">
                                <div class="col-5 text-muted small fw-medium">Joining Date:</div>
                                <div class="col-7 text-dark small fw-bold">{{ $user->employee->joining_date->format('d M Y') }}</div>
                            </div>
                        @endif
                        @if($user->employee->blood_group)
                            <div class="row g-2">
                                <div class="col-5 text-muted small fw-medium">Blood Group:</div>
                                <div class="col-7 text-dark small fw-bold">{{ $user->employee->blood_group }}</div>
                            </div>
                        @endif
                    </div>
                @endif
            </div>
        </div>

        <!-- Danger Zone -->
        <div class="card shadow-sm border-0 border-top border-danger border-3">
            <div class="card-header bg-white border-bottom py-3">
                <h5 class="card-title mb-0 fw-bold text-danger"><i class="bi bi-exclamation-triangle-fill me-2"></i>Danger Zone</h5>
            </div>
            <div class="card-body p-4">
                <p class="text-muted small">Once your account is deleted, all of its resources and data will be permanently deleted.</p>
                <button type="button" class="btn btn-outline-danger w-100" data-bs-toggle="modal" data-bs-target="#confirmUserDeletionModal">
                    Delete Account
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Delete Account Modal -->
<div class="modal fade" id="confirmUserDeletionModal" tabindex="-1" aria-labelledby="confirmUserDeletionModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <form method="post" action="{{ route('profile.destroy') }}" class="modal-content border-0 shadow-lg" style="border-radius: 12px;">
            @csrf
            @method('delete')
            <div class="modal-header border-bottom-0">
                <h5 class="modal-title fw-bold text-danger" id="confirmUserDeletionModalLabel">Delete Account</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body pb-4">
                <p class="fw-bold">Are you sure you want to delete your account?</p>
                <p class="text-secondary small mb-3">Once your account is deleted, all of its resources and data will be permanently deleted. Please enter your password to confirm you would like to permanently delete your account.</p>
                
                <div class="mb-0">
                    <label for="password" class="form-label fw-semibold">Password</label>
                    <input type="password" name="password" id="password" class="form-control @error('password', 'userDeletion') is-invalid @enderror" placeholder="Enter your password to confirm" required>
                    @error('password', 'userDeletion')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
            <div class="modal-footer bg-light border-top-0" style="border-bottom-left-radius: 12px; border-bottom-right-radius: 12px;">
                <button type="button" class="btn btn-secondary px-3" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-danger px-3">Delete Account</button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        @if ($errors->userDeletion->isNotEmpty())
            var deleteModal = new bootstrap.Modal(document.getElementById('confirmUserDeletionModal'));
            deleteModal.show();
        @endif
    });
</script>
@endpush
