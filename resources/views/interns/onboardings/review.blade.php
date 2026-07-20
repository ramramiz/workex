@extends('layouts.app')

@section('title', 'Review Onboarding Submission')
@section('page-title', 'Review Onboarding Form')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('interns.index') }}">Interns</a></li>
    <li class="breadcrumb-item"><a href="{{ route('interns.onboardings.index') }}">Onboardings</a></li>
    <li class="breadcrumb-item active">Review</li>
@endsection

@section('content')
<div class="row g-4">
    <!-- Candidate Overview Card -->
    <div class="col-12">
        <div class="card shadow-sm border-0">
            <div class="card-body p-4">
                <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
                    <div class="d-flex align-items-center gap-3">
                        @if($onboarding->intern->photo)
                            <img src="{{ asset('storage/' . $onboarding->intern->photo) }}" alt="Photo" class="avatar-circle border" style="width: 70px; height: 70px; border-radius: 50%; object-fit: cover;">
                        @else
                            <img src="https://ui-avatars.com/api/?name={{ urlencode($onboarding->intern->name) }}&background=10b981&color=fff&size=70" alt="Photo" class="avatar-circle" style="width: 70px; height: 70px; border-radius: 50%;">
                        @endif
                        <div>
                            <h4 class="mb-1 fw-bold text-dark">{{ $onboarding->intern->name }}</h4>
                            <p class="text-muted mb-0 fs-7">
                                <i class="bi bi-envelope-fill me-1"></i> {{ $onboarding->intern->email }} | 
                                <i class="bi bi-telephone-fill me-1"></i> {{ $onboarding->intern->phone ?? 'N/A' }}
                            </p>
                        </div>
                    </div>
                    <div>
                        <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle px-3 py-1.5 rounded-pill fs-7">
                            <i class="bi bi-clock-fill me-1"></i> Pending Approval
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content Details (Left) and Approvals (Right) -->
    <div class="col-12 col-lg-8">
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-white py-3">
                <h5 class="mb-0 fw-bold text-dark"><i class="bi bi-file-text-fill text-primary me-2"></i>Submitted Onboarding Details</h5>
            </div>
            <div class="card-body p-4">
                <!-- SECTION 1: PERSONAL INFORMATION -->
                <h6 class="text-uppercase text-primary border-bottom pb-2 fw-semibold mb-3 fs-7">Section 1 – Personal Information</h6>
                <div class="row g-3 mb-4">

                    <div class="col-6 col-md-4">
                        <span class="text-muted fs-8 d-block">Gender</span>
                        <strong class="text-dark">{{ $onboarding->gender ?? 'N/A' }}</strong>
                    </div>
                    <div class="col-6 col-md-4">
                        <span class="text-muted fs-8 d-block">Date of Birth</span>
                        <strong class="text-dark">{{ $onboarding->dob ? $onboarding->dob->format('d M Y') : 'N/A' }}</strong>
                    </div>
                    <div class="col-6 col-md-4">
                        <span class="text-muted fs-8 d-block">Blood Group</span>
                        <strong class="text-dark">{{ $onboarding->blood_group ?? 'N/A' }}</strong>
                    </div>
                    <div class="col-6 col-md-4">
                        <span class="text-muted fs-8 d-block">Aadhaar Number</span>
                        <strong class="text-dark">{{ $onboarding->aadhaar_number ?? 'N/A' }}</strong>
                    </div>
                    <div class="col-6 col-md-4">
                        <span class="text-muted fs-8 d-block">Alternate Mobile</span>
                        <strong class="text-dark">{{ $onboarding->alternate_mobile ?? 'N/A' }}</strong>
                    </div>
                    <div class="col-12">
                        <span class="text-muted fs-8 d-block">Current Address</span>
                        <strong class="text-dark">{{ $onboarding->current_address ?? 'N/A' }} (PIN: {{ $onboarding->pin_code ?? 'N/A' }})</strong>
                    </div>
                </div>

                <!-- SECTION 2: EDUCATIONAL INFORMATION -->
                <h6 class="text-uppercase text-primary border-bottom pb-2 fw-semibold mb-3 fs-7">Section 2 – Educational Information</h6>
                <div class="row g-3 mb-4">
                    <div class="col-12">
                        <span class="text-muted fs-8 d-block">College / Institution Name</span>
                        <strong class="text-dark">{{ $onboarding->college_name ?? 'N/A' }}</strong>
                    </div>
                    <div class="col-6 col-md-4">
                        <span class="text-muted fs-8 d-block">University / Board</span>
                        <strong class="text-dark">{{ $onboarding->university_board ?? 'N/A' }}</strong>
                    </div>
                    <div class="col-6 col-md-4">
                        <span class="text-muted fs-8 d-block">Course</span>
                        <strong class="text-dark">{{ $onboarding->course ?? 'N/A' }}</strong>
                    </div>
                    <div class="col-6 col-md-4">
                        <span class="text-muted fs-8 d-block">Branch / Specialization</span>
                        <strong class="text-dark">{{ $onboarding->branch_specialization ?? 'N/A' }}</strong>
                    </div>
                    <div class="col-6 col-md-4">
                        <span class="text-muted fs-8 d-block">Current Semester / Year</span>
                        <strong class="text-dark">{{ $onboarding->current_semester_year ?? 'N/A' }}</strong>
                    </div>
                    <div class="col-6 col-md-4">
                        <span class="text-muted fs-8 d-block">Roll / Registration Number</span>
                        <strong class="text-dark">{{ $onboarding->college_roll_number ?? 'N/A' }}</strong>
                    </div>
                    <div class="col-6 col-md-4">
                        <span class="text-muted fs-8 d-block">Expected Year of Completion</span>
                        <strong class="text-dark">{{ $onboarding->expected_completion_year ?? 'N/A' }}</strong>
                    </div>
                </div>

                <!-- SECTION 3: PARENT / GUARDIAN DETAILS -->
                <h6 class="text-uppercase text-primary border-bottom pb-2 fw-semibold mb-3 fs-7">Section 3 – Parent / Guardian Details</h6>
                <div class="row g-3 mb-4">
                    <div class="col-6 col-md-4">
                        <span class="text-muted fs-8 d-block">Parent / Guardian Name</span>
                        <strong class="text-dark">{{ $onboarding->parent_name ?? 'N/A' }}</strong>
                    </div>
                    <div class="col-6 col-md-4">
                        <span class="text-muted fs-8 d-block">Relationship</span>
                        <strong class="text-dark">{{ $onboarding->parent_relationship ?? 'N/A' }}</strong>
                    </div>
                    <div class="col-6 col-md-4">
                        <span class="text-muted fs-8 d-block">Mobile Number</span>
                        <strong class="text-dark">{{ $onboarding->parent_phone ?? 'N/A' }}</strong>
                    </div>

                    <div class="col-12">
                        <span class="text-muted fs-8 d-block">Parent Address</span>
                        <strong class="text-dark">{{ $onboarding->parent_address ?? 'Same as current address' }}</strong>
                    </div>
                </div>

                <!-- SECTION 4: EMERGENCY CONTACT DETAILS -->
                <h6 class="text-uppercase text-primary border-bottom pb-2 fw-semibold mb-3 fs-7">Section 4 – Emergency Contact Details</h6>
                <div class="row g-3 mb-4">
                    <div class="col-6 col-md-4">
                        <span class="text-muted fs-8 d-block">Emergency Contact Person</span>
                        <strong class="text-dark">{{ $onboarding->emergency_contact_person ?? 'N/A' }}</strong>
                    </div>
                    <div class="col-6 col-md-4">
                        <span class="text-muted fs-8 d-block">Relationship</span>
                        <strong class="text-dark">{{ $onboarding->emergency_relationship ?? 'N/A' }}</strong>
                    </div>
                    <div class="col-6 col-md-4">
                        <span class="text-muted fs-8 d-block">Mobile Number</span>
                        <strong class="text-dark">{{ $onboarding->emergency_phone ?? 'N/A' }}</strong>
                    </div>
                    <div class="col-6 col-md-4">
                        <span class="text-muted fs-8 d-block">Alternate Number</span>
                        <strong class="text-dark">{{ $onboarding->emergency_alternate_phone ?? 'N/A' }}</strong>
                    </div>
                </div>

                <!-- SECTION 5: INTERNSHIP DETAILS -->
                <h6 class="text-uppercase text-primary border-bottom pb-2 fw-semibold mb-3 fs-7">Section 5 – Internship Details</h6>
                <div class="row g-3 mb-4">
                    <div class="col-6 col-md-4">
                        <span class="text-muted fs-8 d-block">Internship Type</span>
                        <strong class="text-dark">{{ $onboarding->internship_type === 'Other' ? $onboarding->internship_type_other : $onboarding->internship_type }}</strong>
                    </div>
                    <div class="col-6 col-md-4">
                        <span class="text-muted fs-8 d-block">Internship Mode</span>
                        <strong class="text-dark">{{ $onboarding->internship_mode ?? 'N/A' }}</strong>
                    </div>
                    <div class="col-6 col-md-4">
                        <span class="text-muted fs-8 d-block">Internship Duration</span>
                        <strong class="text-dark">{{ $onboarding->internship_duration === 'Other' ? $onboarding->internship_duration_other : $onboarding->internship_duration }}</strong>
                    </div>
                    @php
                        $sector = $onboarding->sector ?? 'Techsoul Technologies';

                        if ($sector === 'Techsoul IT Solutions') {
                            $interestLabel = 'Area of Interest';
                            $skillsLabel = 'IT Skills / Protocols Known';
                            $toolsLabel = 'IT Administration & Creative Tools';
                        } elseif ($sector === 'Techsoul Solar') {
                            $interestLabel = 'Area of Interest';
                            $skillsLabel = 'Engineering Skills / Software Known';
                            $toolsLabel = 'Design & Modeling Tools';
                        } else { // Techsoul Technologies
                            $interestLabel = 'Area of Interest';
                            $skillsLabel = 'Programming Languages Known';
                            $toolsLabel = 'Design & Creative Tools';
                        }
                    @endphp

                    <div class="col-12">
                        <span class="text-muted fs-8 d-block mb-1">{{ $interestLabel }}</span>
                        <div class="d-flex flex-wrap gap-1.5">
                            @if(!empty($onboarding->areas_of_interest))
                                @foreach($onboarding->areas_of_interest as $interest)
                                    <span class="badge bg-secondary-subtle text-secondary-emphasis px-2 py-1 fs-9">{{ $interest }}</span>
                                @endforeach
                            @endif
                            @if($onboarding->areas_of_interest_other)
                                <span class="badge bg-secondary-subtle text-secondary-emphasis px-2 py-1 fs-9">{{ $onboarding->areas_of_interest_other }}</span>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- SECTION 6: TECHNICAL SKILLS & KNOWLEDGE -->
                <h6 class="text-uppercase text-primary border-bottom pb-2 fw-semibold mb-3 fs-7">Section 6 – Technical Skills & Knowledge</h6>
                <div class="row g-3 mb-4">
                    <div class="col-12 col-md-6">
                        <span class="text-muted fs-8 d-block mb-1">{{ $skillsLabel }}</span>
                        <div class="d-flex flex-wrap gap-1.5">
                            @if(!empty($onboarding->programming_languages))
                                @foreach($onboarding->programming_languages as $lang)
                                    <span class="badge bg-info-subtle text-info-emphasis px-2 py-1 fs-9">{{ $lang }}</span>
                                @endforeach
                            @endif
                            @if($onboarding->programming_languages_other)
                                <span class="badge bg-info-subtle text-info-emphasis px-2 py-1 fs-9">{{ $onboarding->programming_languages_other }}</span>
                            @endif
                        </div>
                    </div>
                    <div class="col-12 col-md-6">
                        <span class="text-muted fs-8 d-block mb-1">{{ $toolsLabel }}</span>
                        <div class="d-flex flex-wrap gap-1.5">
                            @if(!empty($onboarding->design_tools))
                                @foreach($onboarding->design_tools as $tool)
                                    <span class="badge bg-success-subtle text-success-emphasis px-2 py-1 fs-9">{{ $tool }}</span>
                                @endforeach
                            @endif
                            @if($onboarding->design_tools_other)
                                <span class="badge bg-success-subtle text-success-emphasis px-2 py-1 fs-9">{{ $onboarding->design_tools_other }}</span>
                            @endif
                        </div>
                    </div>
                    <div class="col-12">
                        <span class="text-muted fs-8 d-block">Projects Completed</span>
                        <p class="text-dark mb-0 fs-7 bg-light p-2.5 rounded border border-light-subtle">{{ $onboarding->completed_projects ?? 'None detailed.' }}</p>
                    </div>
                </div>

                <!-- SECTION 10: LEARNING OBJECTIVES -->
                <h6 class="text-uppercase text-primary border-bottom pb-2 fw-semibold mb-3 fs-7">Section 10 – Learning Objectives</h6>
                <div class="row g-3 mb-4">
                    <div class="col-12 col-md-6">
                        <span class="text-muted fs-8 d-block">Learning Expectations</span>
                        <p class="text-dark mb-0 fs-7 bg-light p-2.5 rounded border border-light-subtle">{{ $onboarding->learning_expectations }}</p>
                    </div>
                    <div class="col-12 col-md-6">
                        <span class="text-muted fs-8 d-block">Career Goal</span>
                        <p class="text-dark mb-0 fs-7 bg-light p-2.5 rounded border border-light-subtle">{{ $onboarding->career_goal }}</p>
                    </div>
                </div>

                <!-- SECTION 11: DECLARATION & DIGITAL SIGNATURE -->
                <h6 class="text-uppercase text-primary border-bottom pb-2 fw-semibold mb-3 fs-7">Section 11 – Intern Declaration & Signature</h6>
                <div class="row g-3">
                    <div class="col-12 col-md-8">
                        <span class="text-muted fs-8 d-block">Cursive Digital Signature</span>
                        <span class="signature-text" style="font-family: 'Caveat', cursive; font-size: 32px; color: #6366f1; border-bottom: 2px solid #e2e8f0; display: inline-block; min-width: 200px; padding: 5px;">{{ $onboarding->signature_name }}</span>
                    </div>
                    <div class="col-12 col-md-4">
                        <span class="text-muted fs-8 d-block">Declaration Date</span>
                        <strong class="text-dark">{{ $onboarding->signature_date ? $onboarding->signature_date->format('d M Y') : 'N/A' }}</strong>
                    </div>
                </div>
            </div>
        </div>

        <!-- SECTION 7: UPLOADED DOCUMENTS -->
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-white py-3">
                <h5 class="mb-0 fw-bold text-dark"><i class="bi bi-paperclip text-primary me-2"></i>Section 7 – Document Checklist & Attachments</h5>
            </div>
            <div class="card-body p-4">
                <div class="row g-3">
                    @forelse($onboarding->intern->uploadedDocuments as $doc)
                        <div class="col-12 col-md-6">
                            <div class="border rounded p-3 d-flex align-items-center justify-content-between">
                                <div class="d-flex align-items-center gap-2.5">
                                    <i class="bi bi-file-earmark-pdf-fill text-danger fs-3"></i>
                                    <div>
                                        <div class="fw-semibold text-dark fs-7">{{ $doc->title }}</div>
                                        <div class="text-muted fs-9">{{ $doc->file_name }} ({{ $doc->file_size_human }})</div>
                                    </div>
                                </div>
                                <div class="d-inline-flex gap-1.5">
                                    <a href="{{ route('documents.view', $doc->id) }}" target="_blank" class="btn btn-sm btn-outline-secondary" title="View in browser">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <a href="{{ route('documents.download', $doc->id) }}" class="btn btn-sm btn-outline-primary" title="Download">
                                        <i class="bi bi-download"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="col-12 text-center py-4 text-muted fs-7">
                            No document attachments uploaded.
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    <!-- Approvals & Office Use Panel (Right) -->
    <div class="col-12 col-lg-4">
        <div class="card shadow-sm border-0 mb-4 sticky-lg-top" style="top: 80px; z-index: 900;">
            <div class="card-header bg-primary text-white py-3">
                <h5 class="mb-0 fw-bold"><i class="bi bi-shield-check-fill me-2"></i>Review & Process Onboarding</h5>
            </div>
            
            <form method="POST" action="{{ route('interns.onboardings.approve', $onboarding->id) }}">
                @csrf
                <div class="card-body p-4" style="max-height: 70vh; overflow-y: auto;">
                    
                    <!-- SECTION 8: ACCESS REQUIREMENTS -->
                    <h6 class="text-uppercase text-primary border-bottom pb-2 fw-semibold mb-3 fs-8">Section 8 – Access Requirements</h6>
                    <div class="row g-2 mb-4">
                        @php
                            $requirements = ['Official Email ID', 'Attendance System Access', 'Project Management Access', 'GitHub Access', 'Development Server Access', 'Learning Portal Access', 'Company ID Card'];
                        @endphp
                        @foreach($requirements as $req)
                            <div class="col-12">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="company_access_requirements[]" id="req_{{ Str::slug($req) }}" value="{{ $req }}" {{ is_array(old('company_access_requirements')) && in_array($req, old('company_access_requirements')) ? 'checked' : '' }}>
                                    <label class="form-check-label fs-8 text-dark" for="req_{{ Str::slug($req) }}">
                                        {{ $req }}
                                    </label>
                                </div>
                            </div>
                        @endforeach
                        <div class="col-12 mt-2">
                            <input type="text" name="company_access_other" class="form-control form-control-sm" placeholder="Other Access requirements" value="{{ old('company_access_other') }}">
                        </div>
                    </div>

                    <!-- SECTION 9: ASSET REQUIREMENTS -->
                    <h6 class="text-uppercase text-primary border-bottom pb-2 fw-semibold mb-3 fs-8">Section 9 – Asset Requirement</h6>
                    <div class="row g-2 mb-4">
                        @php
                            $assets = ['Laptop', 'Charger', 'Mouse', 'Headset', 'Company Email', 'ID Card', 'Access Card'];
                        @endphp
                        @foreach($assets as $asset)
                            <div class="col-12">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="assets_issued[]" id="asset_{{ Str::slug($asset) }}" value="{{ $asset }}" {{ is_array(old('assets_issued')) && in_array($asset, old('assets_issued')) ? 'checked' : '' }}>
                                    <label class="form-check-label fs-8 text-dark" for="asset_{{ Str::slug($asset) }}">
                                        {{ $asset }} (Issued)
                                    </label>
                                </div>
                            </div>
                        @endforeach
                        <div class="col-12 mt-2">
                            <textarea name="assets_remarks" class="form-control form-control-sm" rows="2" placeholder="Remarks/Assets notes">{{ old('assets_remarks') }}</textarea>
                        </div>
                    </div>

                    <!-- FOR OFFICE USE ONLY -->
                    <h6 class="text-uppercase text-primary border-bottom pb-2 fw-semibold mb-3 fs-8">For Office Use Only</h6>
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label fs-8">Intern ID</label>
                            <input type="text" class="form-control form-control-sm bg-light" value="TSL-{{ str_pad(14737 + $onboarding->intern->id, 6, '0', STR_PAD_LEFT) }}" readonly>
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label fs-8">Department</label>
                            <input type="text" class="form-control form-control-sm bg-light" value="{{ $onboarding->intern->department->name ?? 'N/A' }}" readonly>
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label fs-8">Internship Domain</label>
                            <input type="text" name="office_use_domain" class="form-control form-control-sm" value="{{ old('office_use_domain', $onboarding->intern->designation->name ?? '') }}" placeholder="e.g. Full Stack Developer">
                        </div>
                        <div class="col-12">
                            <label class="form-label fs-8">Mentor Assigned</label>
                            <select name="office_use_mentor_assigned" class="form-select form-select-sm">
                                <option value="">Select Mentor</option>
                                @foreach($teamLeaders as $leader)
                                    <option value="{{ $leader->name }}" {{ old('office_use_mentor_assigned', $onboarding->mentor_supervisor ?? '') == $leader->name ? 'selected' : '' }}>{{ $leader->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label fs-8 d-block">Certificate Eligible</label>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="office_use_certificate_eligible" id="eligible_y" value="yes" checked>
                                <label class="form-check-label fs-8 text-dark" for="eligible_y">Yes</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="office_use_certificate_eligible" id="eligible_n" value="no">
                                <label class="form-check-label fs-8 text-dark" for="eligible_n">No</label>
                            </div>
                        </div>
                        <div class="col-12">
                            <label class="form-label fs-8">HR Signature Name</label>
                            <input type="text" name="office_use_hr_signature" class="form-control form-control-sm" value="{{ old('office_use_hr_signature', auth()->user()->name) }}">
                        </div>
                        <div class="col-12">
                            <label class="form-label fs-8">Mentor Signature Name</label>
                            <input type="text" name="office_use_mentor_signature" class="form-control form-control-sm" value="{{ old('office_use_mentor_signature', $onboarding->mentor_supervisor ?? '') }}">
                        </div>
                        <div class="col-12">
                            <label class="form-label fs-8">Management Approval Person</label>
                            <input type="text" name="office_use_management_approval" class="form-control form-control-sm" value="{{ old('office_use_management_approval', 'Management Signatory') }}">
                        </div>
                    </div>

                </div>
                <div class="card-footer bg-light border-0 p-4">
                    <button type="submit" class="btn btn-primary w-100 mb-2 py-2 fw-semibold d-inline-flex align-items-center justify-content-center gap-1.5 shadow-sm">
                        <i class="bi bi-check-circle-fill"></i> Approve & Activate Intern
                    </button>
                    
                    <button type="button" class="btn btn-outline-danger w-100 py-2 d-inline-flex align-items-center justify-content-center gap-1.5" data-bs-toggle="collapse" data-bs-target="#rejectCollapse">
                        <i class="bi bi-arrow-counterclockwise"></i> Request Revision
                    </button>

                    <!-- Reject/Revision Form (Collapsible) -->
                    <div class="collapse mt-3" id="rejectCollapse">
                        <div class="border rounded p-3 bg-white">
                            <div class="mb-3">
                                <label class="form-label fs-8 fw-semibold text-danger">Revision Instructions to Candidate</label>
                                <textarea name="reject_remarks" id="reject_remarks" class="form-control form-control-sm" rows="3" placeholder="Provide instructions on what columns or documents need correcting..."></textarea>
                            </div>
                            <button type="button" onclick="submitRevisionRequest()" class="btn btn-danger btn-sm w-100">Send Revision Request</button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Form helper for revision request -->
<form id="rejectForm" action="{{ route('interns.onboardings.reject', $onboarding->id) }}" method="POST" style="display:none;">
    @csrf
    <input type="hidden" name="remarks" id="reject_remarks_hidden">
</form>

@endsection

@push('scripts')
<script>
    function submitRevisionRequest() {
        const remarks = document.getElementById('reject_remarks').value;
        if (!remarks.trim()) {
            alert('Please enter revision comments.');
            return;
        }
        document.getElementById('reject_remarks_hidden').value = remarks;
        document.getElementById('rejectForm').submit();
    }
</script>
@endpush
