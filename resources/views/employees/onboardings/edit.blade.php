@extends('layouts.app')

@section('title', 'Edit Employee Onboarding')
@section('page-title', 'Edit Onboarding Details')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('employees.index') }}">Employees</a></li>
    <li class="breadcrumb-item"><a href="{{ route('employees.onboardings.index') }}">Onboarding Links</a></li>
    <li class="breadcrumb-item active">Edit</li>
@endsection

@section('content')
<div class="card shadow-sm border-0">
    <div class="card-header bg-white py-3">
        <h5 class="mb-0 fw-bold text-dark">Edit Onboarding Details: {{ $onboarding->name }}</h5>
    </div>
    
    <div class="card-body p-4">
        <form method="POST" action="{{ route('employees.onboardings.update', $onboarding->id) }}" id="editOnboardingForm">
            @csrf
            @method('PUT')

            <h6 class="text-primary fw-bold border-bottom pb-2 mb-3"><i class="bi bi-gear-fill me-1"></i> Core Administration Parameters</h6>
            <div class="row g-3 mb-4">
                <div class="col-md-6">
                    <label class="form-label">Full Name (As per Aadhaar) <span class="text-danger">*</span></label>
                    <input type="text" name="name" class="form-control" value="{{ old('name', $onboarding->name) }}" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Email Address (Invited Link) <span class="text-danger">*</span></label>
                    <input type="email" name="email" class="form-control" value="{{ old('email', $onboarding->email) }}" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Department <span class="text-danger">*</span></label>
                    <select name="department_id" id="edit_department_id" class="form-select" onchange="filterDesignations()" required>
                        <option value="">Select Department</option>
                        @foreach($departments as $dept)
                            <option value="{{ $dept->id }}" {{ old('department_id', $onboarding->department_id) == $dept->id ? 'selected' : '' }}>{{ $dept->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Designation</label>
                    <select name="designation_id" id="edit_designation_id" class="form-select">
                        <option value="">Select Designation</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Sector <span class="text-danger">*</span></label>
                    <select name="sector" class="form-select" required>
                        <option value="">Select Sector</option>
                        <option value="Techsoul Technologies" {{ old('sector', $onboarding->sector) === 'Techsoul Technologies' ? 'selected' : '' }}>Techsoul Technologies</option>
                        <option value="Techsoul IT Solutions" {{ old('sector', $onboarding->sector) === 'Techsoul IT Solutions' ? 'selected' : '' }}>Techsoul IT Solutions</option>
                        <option value="Techsoul Solar" {{ old('sector', $onboarding->sector) === 'Techsoul Solar' ? 'selected' : '' }}>Techsoul Solar</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Reporting Manager</label>
                    <select name="team_leader_id" class="form-select">
                        <option value="">Select Reporting Manager</option>
                        @foreach($teamLeaders as $leader)
                            <option value="{{ $leader->id }}" {{ old('team_leader_id', $onboarding->team_leader_id) == $leader->id ? 'selected' : '' }}>{{ $leader->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">System Role <span class="text-danger">*</span></label>
                    <select name="role_id" class="form-select" required>
                        <option value="">Select Role</option>
                        @foreach($roles as $role)
                            <option value="{{ $role->id }}" {{ old('role_id', $onboarding->role_id) == $role->id ? 'selected' : '' }}>{{ $role->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Salary / Stipend <span class="text-danger">*</span></label>
                    <input type="number" name="salary" class="form-control" value="{{ old('salary', $onboarding->salary) }}" min="0" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Joining Date <span class="text-danger">*</span></label>
                    <input type="date" name="joining_date" class="form-control" value="{{ old('joining_date', $onboarding->joining_date ? $onboarding->joining_date->format('Y-m-d') : '') }}" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Onboarding Link Token</label>
                    <input type="text" class="form-control bg-light" value="{{ $onboarding->token }}" readonly>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Current Status</label>
                    <input type="text" class="form-control bg-light" value="{{ strtoupper($onboarding->status) }}" readonly>
                </div>
            </div>

            <h6 class="text-primary fw-bold border-bottom pb-2 mb-3"><i class="bi bi-person-fill me-1"></i> Candidate Profile Details (Section 1 & 2)</h6>
            <div class="row g-3 mb-4">
                <div class="col-md-6">
                    <label class="form-label">Gender</label>
                    <select name="gender" class="form-select">
                        <option value="">Select Gender</option>
                        <option value="Male" {{ old('gender', $onboarding->gender) === 'Male' ? 'selected' : '' }}>Male</option>
                        <option value="Female" {{ old('gender', $onboarding->gender) === 'Female' ? 'selected' : '' }}>Female</option>
                        <option value="Other" {{ old('gender', $onboarding->gender) === 'Other' ? 'selected' : '' }}>Other</option>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Date of Birth</label>
                    <input type="date" name="dob" class="form-control" value="{{ old('dob', $onboarding->dob ? $onboarding->dob->format('Y-m-d') : '') }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Blood Group</label>
                    <input type="text" name="blood_group" class="form-control" value="{{ old('blood_group', $onboarding->blood_group) }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Marital Status</label>
                    <select name="marital_status" class="form-select">
                        <option value="">Select Status</option>
                        <option value="Single" {{ old('marital_status', $onboarding->marital_status) === 'Single' ? 'selected' : '' }}>Single</option>
                        <option value="Married" {{ old('marital_status', $onboarding->marital_status) === 'Married' ? 'selected' : '' }}>Married</option>
                        <option value="Divorced" {{ old('marital_status', $onboarding->marital_status) === 'Divorced' ? 'selected' : '' }}>Divorced</option>
                        <option value="Widowed" {{ old('marital_status', $onboarding->marital_status) === 'Widowed' ? 'selected' : '' }}>Widowed</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Nationality</label>
                    <input type="text" name="nationality" class="form-control" value="{{ old('nationality', $onboarding->nationality) }}">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Mobile Number</label>
                    <input type="text" name="phone" class="form-control" value="{{ old('phone', $onboarding->phone) }}">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Personal Email</label>
                    <input type="email" name="personal_email" class="form-control" value="{{ old('personal_email', $onboarding->personal_email) }}">
                </div>
                <div class="col-md-8">
                    <label class="form-label">Current Address</label>
                    <textarea name="current_address" class="form-control" rows="2">{{ old('current_address', $onboarding->current_address) }}</textarea>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Current PIN Code</label>
                    <input type="text" name="current_pin_code" class="form-control" value="{{ old('current_pin_code', $onboarding->current_pin_code) }}">
                </div>
            </div>

            <div class="d-flex align-items-center justify-content-between border-top pt-4">
                <a href="{{ route('employees.onboardings.index') }}" class="btn btn-outline-secondary py-2 px-4"><i class="bi bi-x-circle me-1.5"></i>Cancel</a>
                <button type="submit" class="btn btn-primary py-2 px-5"><i class="bi bi-save me-1.5"></i>Update Details</button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
    const departments = @json($departments);
    const selectedDesignationId = "{{ $onboarding->designation_id }}";

    function filterDesignations() {
        const deptId = document.getElementById('edit_department_id').value;
        const desigSelect = document.getElementById('edit_designation_id');
        
        // Reset designations
        desigSelect.innerHTML = '<option value="">Select Designation</option>';
        
        if (!deptId) return;
        
        const department = departments.find(d => d.id == deptId);
        if (department && department.designations) {
            department.designations.forEach(desig => {
                const opt = document.createElement('option');
                opt.value = desig.id;
                opt.textContent = desig.name;
                if (desig.id == selectedDesignationId) {
                    opt.selected = true;
                }
                desigSelect.appendChild(opt);
            });
        }
    }

    // Run filter on page load to pre-select designation
    document.addEventListener("DOMContentLoaded", function() {
        filterDesignations();
    });
</script>
@endpush
