@extends('layouts.app')

@section('title', 'Edit Company')
@section('page-title', 'Edit Company')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('reseller.dashboard') }}">Companies</a></li>
    <li class="breadcrumb-item active">Edit</li>
@endsection

@section('content')
<div class="row justify-content-center">
    <div class="col-12 col-lg-8">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white py-3 border-bottom">
                <h5 class="card-title mb-0 fw-bold text-dark"><i class="bi bi-building me-2 text-primary"></i>Company Account Details</h5>
            </div>
            <div class="card-body p-4">
                <form method="POST" action="{{ route('reseller.companies.update', $company) }}">
                    @csrf
                    @method('PUT')

                    <h6 class="text-primary fw-bold mb-3 border-bottom pb-2">Organization Information</h6>
                    <div class="mb-4">
                        <label for="company_name" class="form-label fw-semibold">Company Name <span class="text-danger">*</span></label>
                        <input type="text" name="company_name" id="company_name" class="form-control @error('company_name') is-invalid @enderror" value="{{ old('company_name', $company->name) }}" placeholder="e.g. Acme Corporation" required autofocus>
                        @error('company_name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <h6 class="text-primary fw-bold mb-3 border-bottom pb-2">Super Administrator credentials</h6>
                    <div class="row g-3 mb-3">
                        <div class="col-12 col-md-6">
                            <label for="admin_name" class="form-label fw-semibold">Admin Full Name <span class="text-danger">*</span></label>
                            <input type="text" name="admin_name" id="admin_name" class="form-control @error('admin_name') is-invalid @enderror" value="{{ old('admin_name', $admin->name ?? '') }}" placeholder="e.g. John Doe" required>
                            @error('admin_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-12 col-md-6">
                            <label for="admin_email" class="form-label fw-semibold">Admin Email Address <span class="text-danger">*</span></label>
                            <input type="email" name="admin_email" id="admin_email" class="form-control @error('admin_email') is-invalid @enderror" value="{{ old('admin_email', $admin->email ?? '') }}" placeholder="admin@company.com" required>
                            @error('admin_email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="mb-4">
                        <label for="admin_password" class="form-label fw-semibold">Admin Password <span class="text-muted">(Leave empty to keep current password)</span></label>
                        <input type="password" name="admin_password" id="admin_password" class="form-control @error('admin_password') is-invalid @enderror" placeholder="Minimum 8 characters">
                        @error('admin_password')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="d-flex align-items-center justify-content-end gap-2 border-top pt-3 mt-4">
                        <a href="{{ route('reseller.dashboard') }}" class="btn btn-outline-secondary">Cancel</a>
                        <button type="submit" class="btn btn-primary px-4">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
