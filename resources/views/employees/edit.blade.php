@extends('layouts.app')

@section('title', 'Edit Employee')
@section('page-title', 'Edit Employee')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('employees.index') }}">Employees</a></li>
    <li class="breadcrumb-item"><a href="{{ route('employees.show', $employee) }}">{{ $employee->name }}</a></li>
    <li class="breadcrumb-item active">Edit</li>
@endsection

@section('content')
<div class="row">
    <div class="col-12 col-lg-10 mx-auto">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Edit Employee: {{ $employee->name }}</h5>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('employees.update', $employee) }}" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')
                    
                    <h6 class="text-uppercase text-primary fs-7 mb-3 border-bottom pb-2">Account Details</h6>
                    <div class="row g-3 mb-4">
                        <div class="col-12 col-md-6">
                            <label class="form-label">Full Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $employee->user->name ?? '') }}" required>
                            @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label">Work Email <span class="text-danger">*</span></label>
                            <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email', $employee->user->email ?? '') }}" required>
                            @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label">Personal Email <span class="text-muted">(Optional)</span></label>
                            <input type="email" name="personal_email" class="form-control @error('personal_email') is-invalid @enderror" value="{{ old('personal_email', $employee->personal_email ?? '') }}">
                            @error('personal_email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label">Phone Number</label>
                            <input type="text" name="phone" class="form-control @error('phone') is-invalid @enderror" value="{{ old('phone', $employee->phone ?? $employee->user->phone ?? '') }}">
                            @error('phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label">System Role <span class="text-danger">*</span></label>
                            <select name="role_id" class="form-select @error('role_id') is-invalid @enderror" required>
                                @foreach($roles as $role)
                                    <option value="{{ $role->id }}" {{ old('role_id', $employee->user->role_id ?? '') == $role->id ? 'selected' : '' }}>{{ $role->name }}</option>
                                @endforeach
                            </select>
                            @error('role_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label">Password <span class="text-muted">(Leave blank to keep current)</span></label>
                            <input type="password" name="password" class="form-control @error('password') is-invalid @enderror" placeholder="Enter new password to update">
                            @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label">Profile Picture</label>
                            <div class="d-flex align-items-center gap-3">
                                <img src="{{ $employee->user->avatar_url }}" alt="" class="rounded-circle border" style="width: 50px; height: 50px; object-fit: cover;">
                                <input type="file" name="avatar" class="form-control @error('avatar') is-invalid @enderror" accept="image/*">
                            </div>
                            @error('avatar')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                        <div class="col-12 col-md-6">
                            <label class="form-label">Google Drive Folder Link</label>
                            <input type="url" name="google_drive_link" class="form-control @error('google_drive_link') is-invalid @enderror" value="{{ old('google_drive_link', $employee->google_drive_link) }}" placeholder="https://drive.google.com/drive/folders/...">
                            @error('google_drive_link')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>

                    <h6 class="text-uppercase text-primary fs-7 mb-3 border-bottom pb-2">Employment Information</h6>
                    <div class="row g-3 mb-4">
                        <div class="col-12 col-md-6">
                            <label class="form-label">Employee Code</label>
                            <input type="text" name="employee_code" class="form-control" value="{{ $employee->employee_code }}" disabled>
                            <div class="form-text">Employee code cannot be changed after creation.</div>
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label">Joining Date <span class="text-danger">*</span></label>
                            <input type="date" name="joining_date" class="form-control @error('joining_date') is-invalid @enderror" value="{{ old('joining_date', $employee->joining_date ? $employee->joining_date->format('Y-m-d') : '') }}" required>
                            @error('joining_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label">Department <span class="text-danger">*</span></label>
                            <select name="department_id" id="department_id" class="form-select @error('department_id') is-invalid @enderror" required onchange="filterDesignations()">
                                <option value="">Select Department</option>
                                @foreach($departments as $dept)
                                    <option value="{{ $dept->id }}" {{ old('department_id', $employee->department_id) == $dept->id ? 'selected' : '' }}>{{ $dept->name }}</option>
                                @endforeach
                            </select>
                            @error('department_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label">Designation</label>
                            <select name="designation_id" id="designation_id" class="form-select @error('designation_id') is-invalid @enderror">
                                <option value="">Select Designation</option>
                            </select>
                            @error('designation_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label">Team Leader / Reporting To</label>
                            <select name="team_leader_id" class="form-select @error('team_leader_id') is-invalid @enderror">
                                <option value="">Select Team Leader</option>
                                @foreach($teamLeaders as $tl)
                                    <option value="{{ $tl->id }}" {{ old('team_leader_id', $employee->team_leader_id) == $tl->id ? 'selected' : '' }}>{{ $tl->name }}</option>
                                @endforeach
                            </select>
                            @error('team_leader_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label">Salary</label>
                            <div class="input-group">
                                <span class="input-group-text">₹</span>
                                <input type="number" step="0.01" name="salary" id="salary" class="form-control @error('salary') is-invalid @enderror" value="{{ old('salary', $employee->salary) }}">
                            </div>
                            <div class="form-text text-muted mt-1" id="single-day-salary-text" style="display: none;">
                                Single Day Salary (LOP Rate): <strong>₹0.00</strong> (Monthly / 26)
                            </div>
                            @error('salary')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label">Applicable for Salary <span class="text-danger">*</span></label>
                            <select name="is_applicable_for_salary" class="form-select @error('is_applicable_for_salary') is-invalid @enderror" required>
                                <option value="1" {{ old('is_applicable_for_salary', $employee->is_applicable_for_salary) == 1 ? 'selected' : '' }}>Yes</option>
                                <option value="0" {{ old('is_applicable_for_salary', $employee->is_applicable_for_salary) == 0 ? 'selected' : '' }}>No</option>
                            </select>
                            @error('is_applicable_for_salary')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label">Show in Live Status Board <span class="text-danger">*</span></label>
                            <select name="show_in_live_status" class="form-select @error('show_in_live_status') is-invalid @enderror" required>
                                <option value="1" {{ old('show_in_live_status', $employee->show_in_live_status) == 1 ? 'selected' : '' }}>Yes</option>
                                <option value="0" {{ old('show_in_live_status', $employee->show_in_live_status) == 0 ? 'selected' : '' }}>No</option>
                            </select>
                            @error('show_in_live_status')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>

                    <div class="d-flex align-items-center justify-content-end gap-2 border-top pt-3">
                        <a href="{{ route('employees.index') }}" class="btn btn-outline-secondary">Cancel</a>
                        <button type="submit" class="btn btn-primary">Update Employee</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    const departments = @json($departments);
    const selectedDesignationId = "{{ old('designation_id', $employee->designation_id) }}";

    function filterDesignations() {
        const deptId = document.getElementById('department_id').value;
        const desigSelect = document.getElementById('designation_id');
        
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

    function updateSingleDaySalary() {
        const salaryInput = document.getElementById('salary');
        const singleDayText = document.getElementById('single-day-salary-text');

        if (!salaryInput || !singleDayText) return;
        const salaryType = "{{ $employee->salary_type ?? 'monthly' }}";
        
        if (salaryType === 'monthly') {
            const salaryVal = parseFloat(salaryInput.value) || 0;
            const singleDayVal = salaryVal / 26;
            singleDayText.style.display = 'block';
            singleDayText.innerHTML = `Single Day Salary (LOP Rate): <strong>₹${singleDayVal.toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}</strong> (Monthly / 26)`;
        } else {
            singleDayText.style.display = 'none';
        }
    }

    document.addEventListener('DOMContentLoaded', () => {
        filterDesignations();

        const salaryInput = document.getElementById('salary');
        if (salaryInput) {
            salaryInput.addEventListener('input', updateSingleDaySalary);
            updateSingleDaySalary();
        }
    });
</script>
@endpush
