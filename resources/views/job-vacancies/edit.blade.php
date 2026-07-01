@extends('layouts.app')

@section('title', 'Edit Vacancy')
@section('page-title', 'Edit Vacancy')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('job-vacancies.index') }}">Hiring & Vacancies</a></li>
    <li class="breadcrumb-item active">Edit Vacancy</li>
@endsection

@section('content')
<div class="row">
    <div class="col-12 col-lg-10 mx-auto">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white py-3">
                <h5 class="mb-0 fw-bold text-dark">Edit Job Vacancy</h5>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('job-vacancies.update', $jobVacancy->id) }}">
                    @csrf
                    @method('PUT')
                    
                    <h6 class="text-uppercase text-primary fw-semibold fs-7 mb-3 border-bottom pb-2">Vacancy Details</h6>
                    <div class="row g-3 mb-4">
                        <div class="col-12 col-md-6">
                            <label class="form-label">Job Title <span class="text-danger">*</span></label>
                            <input type="text" name="title" class="form-control @error('title') is-invalid @enderror" value="{{ old('title', $jobVacancy->title) }}" required>
                            @error('title')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label">Department</label>
                            <select name="department_id" class="form-select @error('department_id') is-invalid @enderror">
                                <option value="">Select Department (Optional)</option>
                                @foreach($departments as $dept)
                                    <option value="{{ $dept->id }}" {{ old('department_id', $jobVacancy->department_id) == $dept->id ? 'selected' : '' }}>{{ $dept->name }}</option>
                                @endforeach
                            </select>
                            @error('department_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-12 col-md-4">
                            <label class="form-label">Job Type <span class="text-danger">*</span></label>
                            <select name="job_type" class="form-select @error('job_type') is-invalid @enderror" required>
                                <option value="Full-time" {{ old('job_type', $jobVacancy->job_type) == 'Full-time' ? 'selected' : '' }}>Full-time</option>
                                <option value="Part-time" {{ old('job_type', $jobVacancy->job_type) == 'Part-time' ? 'selected' : '' }}>Part-time</option>
                                <option value="Contract" {{ old('job_type', $jobVacancy->job_type) == 'Contract' ? 'selected' : '' }}>Contract</option>
                                <option value="Internship" {{ old('job_type', $jobVacancy->job_type) == 'Internship' ? 'selected' : '' }}>Internship</option>
                            </select>
                            @error('job_type')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-12 col-md-4">
                            <label class="form-label">Location / Workplace <span class="text-danger">*</span></label>
                            <input type="text" name="location" class="form-control @error('location') is-invalid @enderror" value="{{ old('location', $jobVacancy->location) }}" required>
                            @error('location')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-12 col-md-4">
                            <label class="form-label">Status <span class="text-danger">*</span></label>
                            <select name="status" class="form-select @error('status') is-invalid @enderror" required>
                                <option value="active" {{ old('status', $jobVacancy->status) == 'active' ? 'selected' : '' }}>Active (Visible to candidates)</option>
                                <option value="inactive" {{ old('status', $jobVacancy->status) == 'inactive' ? 'selected' : '' }}>Inactive (Closed/Hidden)</option>
                            </select>
                            @error('status')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-12">
                            <label class="form-label">Salary Note / Range <span class="text-muted">(Optional, shown below Salary Expectations on application form)</span></label>
                            <input type="text" name="salary_note" id="salary_note" class="form-control @error('salary_note') is-invalid @enderror" value="{{ old('salary_note', $jobVacancy->salary_note) }}">
                            @error('salary_note')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>

                    <h6 class="text-uppercase text-primary fw-semibold fs-7 mb-3 border-bottom pb-2">Description & Requirements</h6>
                    
                    <div class="mb-3">
                        <label class="form-label">Job Description <span class="text-danger">*</span></label>
                        <textarea name="description" class="form-control @error('description') is-invalid @enderror" rows="6" required>{{ old('description', $jobVacancy->description) }}</textarea>
                        @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="mb-4">
                        <label class="form-label">Skills & Requirements <span class="text-muted">(Optional)</span></label>
                        <textarea name="requirements" class="form-control @error('requirements') is-invalid @enderror" rows="5">{{ old('requirements', $jobVacancy->requirements) }}</textarea>
                        @error('requirements')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="d-flex align-items-center justify-content-end gap-2 border-top pt-3">
                        <a href="{{ route('job-vacancies.index') }}" class="btn btn-outline-secondary">Cancel</a>
                        <button type="submit" class="btn btn-primary px-4">Update Vacancy</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const titleInput = document.querySelector('input[name="title"]');
        const salaryNoteInput = document.getElementById('salary_note');
        
        if (titleInput && salaryNoteInput) {
            // Track if user manually edits the salary note field
            salaryNoteInput.addEventListener('input', function() {
                salaryNoteInput.dataset.manuallyEdited = 'true';
            });
            
            titleInput.addEventListener('input', function() {
                if (salaryNoteInput.dataset.manuallyEdited !== 'true') {
                    const jobTitle = this.value.trim() || 'Senior Software Engineer';
                    salaryNoteInput.value = `For ${jobTitle}, we will provide a salary in the range of 15,000 to 20,000.`;
                }
            });
        }
    });
</script>
@endpush
@endsection
