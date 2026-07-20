@extends('layouts.app')

@section('title', 'Review Employee Onboarding')
@section('page-title', 'Review Employee Onboarding')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('employees.index') }}">Employees</a></li>
    <li class="breadcrumb-item"><a href="{{ route('employees.onboardings.index') }}">Onboarding Links</a></li>
    <li class="breadcrumb-item active">Review</li>
@endsection

@section('content')
<div class="row g-4">
    <!-- Header Back Navigation -->
    <div class="col-12">
        <div class="d-flex align-items-center justify-content-between">
            <div class="d-flex gap-2">
                <a href="{{ route('employees.onboardings.index') }}" class="btn btn-secondary btn-sm px-3 shadow-sm">
                    <i class="bi bi-arrow-left"></i> Back to Onboardings
                </a>
                <a href="{{ route('employees.onboardings.edit', $onboarding->id) }}" class="btn btn-primary btn-sm px-3 shadow-sm">
                    <i class="bi bi-pencil-square"></i> Edit Details
                </a>
            </div>
            <div>
                <span class="badge bg-warning text-dark px-3 py-2 rounded-pill fw-bold border border-warning-subtle">
                    <i class="bi bi-shield-fill-exclamation me-1"></i> Review Stage: Submitted
                </span>
            </div>
        </div>
    </div>

    <!-- Main Content Stack -->
    <div class="col-12">
        <!-- 1. Candidate Onboarding Questionnaire Details -->
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-white py-3">
                <h5 class="mb-0 fw-bold text-dark"><i class="bi bi-person-fill text-primary me-1.5"></i> Candidate Onboarding Questionnaire Details</h5>
            </div>
            <div class="card-body p-4">
                
                <!-- SECTION 1 & 2: PERSONAL & CONTACT -->
                <h6 class="text-primary fw-bold border-bottom pb-2 mb-3"><i class="bi bi-card-text me-1"></i> Section 1 & 2 – Personal Information & Contact</h6>
                <div class="d-flex gap-4 mb-4 align-items-start">
                    <div class="flex-grow-1">
                        <div class="row g-3">
                            <div class="col-md-12"><span class="text-muted d-block fs-8">Full Name</span><strong>{{ $onboarding->name }}</strong></div>
                            <div class="col-md-3"><span class="text-muted d-block fs-8">Gender</span><strong>{{ $onboarding->gender }}</strong></div>
                            @if($onboarding->dob)
                                <div class="col-md-3"><span class="text-muted d-block fs-8">Date of Birth</span><strong>{{ $onboarding->dob->format('d M Y') }}</strong></div>
                            @endif
                            @if($onboarding->blood_group)
                                <div class="col-md-3"><span class="text-muted d-block fs-8">Blood Group</span><strong>{{ $onboarding->blood_group }}</strong></div>
                            @endif
                            <div class="col-md-3"><span class="text-muted d-block fs-8">Marital Status</span><strong>{{ $onboarding->marital_status }}</strong></div>
                            <div class="col-md-4"><span class="text-muted d-block fs-8">Nationality</span><strong>{{ $onboarding->nationality }}</strong></div>
                            <div class="col-md-4"><span class="text-muted d-block fs-8">Mobile Number</span><strong>{{ $onboarding->phone }}</strong></div>
                            @if($onboarding->alternate_mobile)
                                <div class="col-md-4"><span class="text-muted d-block fs-8">Alternate Number</span><strong>{{ $onboarding->alternate_mobile }}</strong></div>
                            @endif
                            <div class="col-md-6"><span class="text-muted d-block fs-8">Aadhaar Number</span><strong>{{ $onboarding->aadhaar_number }}</strong></div>
                            <div class="col-md-6"><span class="text-muted d-block fs-8">PAN Number</span><strong>{{ $onboarding->pan_number }}</strong></div>
                            @if($onboarding->passport_number)
                                <div class="col-md-6"><span class="text-muted d-block fs-8">Passport Number</span><strong>{{ $onboarding->passport_number }}</strong></div>
                            @endif
                            @if($onboarding->driving_license_number)
                                <div class="col-md-6"><span class="text-muted d-block fs-8">Driving License Number</span><strong>{{ $onboarding->driving_license_number }}</strong></div>
                            @endif
                            <div class="col-12"><span class="text-muted d-block fs-8">Personal Email</span><strong>{{ $onboarding->personal_email }}</strong></div>
                        </div>
                    </div>
                    
                    <!-- Right side photo box -->
                    <div class="flex-shrink-0 text-center">
                        <span class="text-muted d-block fs-8 mb-1.5">Passport Photo</span>
                        <div style="width: 110px; height: 135px; border: 2px dashed #cbd5e1; display: flex; align-items: center; justify-content: center; text-align: center; font-size: 9px; color: #64748b; background-color: #f8fafc; overflow: hidden; border-radius: 8px;">
                            @php
                                $photoDoc = $uploadedDocs->where('title', 'Passport Size Photo')->first();
                            @endphp
                            @if($photoDoc)
                                <img src="{{ asset('storage/' . $photoDoc->file_path) }}" alt="Photo" style="width: 100%; height: 100%; object-fit: cover;">
                            @else
                                <div class="p-2">NO PHOTO<br>UPLOADED</div>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="row g-3 mb-4">
                    <div class="col-12"><span class="text-muted d-block fs-8">Current Address</span><span class="text-dark">{{ $onboarding->current_address }} (PIN: {{ $onboarding->current_pin_code }})</span></div>
                    <div class="col-12">
                        <span class="text-muted d-block fs-8">Permanent Address</span>
                        <span class="text-dark">
                            @if($onboarding->same_as_current)
                                <em class="text-secondary">(Same as Current Address)</em>
                            @else
                                {{ $onboarding->permanent_address }} (PIN: {{ $onboarding->permanent_pin_code }})
                            @endif
                        </span>
                    </div>
                </div>

                <!-- SECTION 3: EMERGENCY -->
                <h6 class="text-primary fw-bold border-bottom pb-2 mb-3"><i class="bi bi-telephone-fill me-1"></i> Section 3 – Emergency Contact Details</h6>
                <div class="row g-3 mb-4">
                    <div class="col-md-4"><span class="text-muted d-block fs-8">Emergency Contact</span><strong>{{ $onboarding->emergency_contact_person }}</strong></div>
                    <div class="col-md-4"><span class="text-muted d-block fs-8">Relationship</span><strong>{{ $onboarding->emergency_relationship }}</strong></div>
                    <div class="col-md-4"><span class="text-muted d-block fs-8">Mobile Number</span><strong>{{ $onboarding->emergency_phone }}</strong></div>
                    @if($onboarding->emergency_alternate_phone)
                        <div class="col-md-6"><span class="text-muted d-block fs-8">Alternate Mobile</span><strong>{{ $onboarding->emergency_alternate_phone }}</strong></div>
                    @endif
                    @if($onboarding->emergency_address)
                        <div class="col-md-6"><span class="text-muted d-block fs-8">Address</span><strong>{{ $onboarding->emergency_address }}</strong></div>
                    @endif
                </div>

                <!-- SECTION 5: EDUCATIONAL QUALIFICATIONS -->
                <h6 class="text-primary fw-bold border-bottom pb-2 mb-3"><i class="bi bi-mortarboard-fill me-1"></i> Section 5 – Educational Qualifications</h6>
                <div class="table-responsive mb-4">
                    <table class="table table-bordered table-sm">
                        <thead class="bg-light">
                            <tr>
                                <th>Qualification</th>
                                <th>Institution</th>
                                <th>Board / University</th>
                                <th>Year Passed</th>
                                <th>Percentage / CGPA</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($onboarding->education_qualifications ?? [] as $edu)
                                @if(!empty($edu['institution']) || !empty($edu['board_university']))
                                    <tr>
                                        <td class="fw-semibold">{{ $edu['qualification'] ?? 'N/A' }}</td>
                                        <td>{{ $edu['institution'] ?? 'N/A' }}</td>
                                        <td>{{ $edu['board_university'] ?? 'N/A' }}</td>
                                        <td>{{ $edu['year_passed'] ?? 'N/A' }}</td>
                                        <td>{{ $edu['percentage'] ?? 'N/A' }}</td>
                                    </tr>
                                @endif
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center text-muted py-2 fs-8">No educational qualifications submitted</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- SECTION 6 & 8: PROFESSIONAL & PF -->
                <h6 class="text-primary fw-bold border-bottom pb-2 mb-3"><i class="bi bi-briefcase-fill me-1"></i> Section 6 & 8 – Professional Details & PF / ESI</h6>
                <div class="row g-3 mb-4">
                    <div class="col-md-4"><span class="text-muted d-block fs-8">Total Experience</span><strong>{{ $onboarding->total_experience }}</strong></div>
                    @if($onboarding->prev_employer)
                        <div class="col-md-8"><span class="text-muted d-block fs-8">Previous Employer</span><strong>{{ $onboarding->prev_employer }}</strong></div>
                    @endif
                    @if($onboarding->prev_designation)
                        <div class="col-md-4"><span class="text-muted d-block fs-8">Designation</span><strong>{{ $onboarding->prev_designation }}</strong></div>
                    @endif
                    @if($onboarding->prev_duration)
                        <div class="col-md-4"><span class="text-muted d-block fs-8">Duration</span><strong>{{ $onboarding->prev_duration }}</strong></div>
                    @endif
                    @if($onboarding->prev_reason_for_leaving)
                        <div class="col-md-4"><span class="text-muted d-block fs-8">Reason for Leaving</span><strong>{{ $onboarding->prev_reason_for_leaving }}</strong></div>
                    @endif
                    <div class="col-12">
                        <span class="text-muted d-block fs-8">Skills & Technologies Known</span>
                        <div class="d-flex flex-wrap gap-1 mt-1">
                            @forelse($onboarding->skills ?? [] as $sk)
                                <span class="badge bg-secondary text-dark">{{ $sk }}</span>
                            @empty
                                <span class="text-muted fs-8">No skills selected</span>
                            @endforelse
                        </div>
                    </div>
                    @if($onboarding->skills_other)
                        <div class="col-12"><span class="text-muted d-block fs-8">Other Skills</span><strong>{{ $onboarding->skills_other }}</strong></div>
                    @endif
                    @if($onboarding->uan_number)
                        <div class="col-md-4"><span class="text-muted d-block fs-8">UAN Number</span><strong>{{ $onboarding->uan_number }}</strong></div>
                    @endif
                    @if($onboarding->pf_number)
                        <div class="col-md-4"><span class="text-muted d-block fs-8">PF Number</span><strong>{{ $onboarding->pf_number }}</strong></div>
                    @endif
                    @if($onboarding->esi_number)
                        <div class="col-md-4"><span class="text-muted d-block fs-8">ESI Number</span><strong>{{ $onboarding->esi_number }}</strong></div>
                    @endif
                </div>

                <!-- SECTION 7: BANK DETAILS -->
                <h6 class="text-primary fw-bold border-bottom pb-2 mb-3"><i class="bi bi-credit-card-2-front-fill me-1"></i> Section 7 – Bank Details</h6>
                <div class="row g-3 mb-4">
                    <div class="col-md-6"><span class="text-muted d-block fs-8">Account Holder Name</span><strong>{{ $onboarding->bank_account_holder }}</strong></div>
                    <div class="col-md-6"><span class="text-muted d-block fs-8">Bank Name</span><strong>{{ $onboarding->bank_name }}</strong></div>
                    <div class="col-md-4"><span class="text-muted d-block fs-8">Branch Name</span><strong>{{ $onboarding->bank_branch }}</strong></div>
                    <div class="col-md-4"><span class="text-muted d-block fs-8">Account Number</span><strong>{{ $onboarding->bank_account_number }}</strong></div>
                    <div class="col-md-4"><span class="text-muted d-block fs-8">IFSC Code</span><strong>{{ $onboarding->bank_ifsc }}</strong></div>
                    @if($onboarding->bank_upi)
                        <div class="col-md-6"><span class="text-muted d-block fs-8">UPI ID</span><strong>{{ $onboarding->bank_upi }}</strong></div>
                    @endif
                </div>

                <!-- SECTION 12 & 13: MEDICAL & DECLARATION -->
                @if($onboarding->medical_condition || $onboarding->medical_allergies || $onboarding->medical_medication)
                    <h6 class="text-primary fw-bold border-bottom pb-2 mb-3"><i class="bi bi-heart-pulse-fill me-1"></i> Section 12 – Medical Information</h6>
                    <div class="row g-3 mb-4">
                        @if($onboarding->medical_condition)
                            <div class="col-md-4"><span class="text-muted d-block fs-8">Medical Condition</span><strong>{{ $onboarding->medical_condition }}</strong></div>
                        @endif
                        @if($onboarding->medical_allergies)
                            <div class="col-md-4"><span class="text-muted d-block fs-8">Allergies</span><strong>{{ $onboarding->medical_allergies }}</strong></div>
                        @endif
                        @if($onboarding->medical_medication)
                            <div class="col-md-4"><span class="text-muted d-block fs-8">Regular Medication</span><strong>{{ $onboarding->medical_medication }}</strong></div>
                        @endif
                    </div>
                @endif

                <h6 class="text-primary fw-bold border-bottom pb-2 mb-3"><i class="bi bi-shield-check-fill me-1"></i> Section 13 – Declaration</h6>
                <div class="row g-3">
                    <div class="col-md-12">
                        <span class="text-muted d-block fs-8">Declaration Checked?</span>
                        <strong>{{ $onboarding->declaration_accepted ? 'Yes (Accepted)' : 'No' }}</strong>
                    </div>
                </div>

            </div>
        </div>

        <!-- 2. Candidate Documents Checklist Uploads -->
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-white py-3">
                <h5 class="mb-0 fw-bold text-dark"><i class="bi bi-folder-check text-primary me-1.5"></i> Uploaded Documents</h5>
            </div>
            <div class="card-body p-4">
                <div class="row g-3">
                    @forelse($uploadedDocs as $doc)
                        <div class="col-md-6 col-12">
                            <div class="p-3 border rounded-3 d-flex align-items-center justify-content-between bg-light-subtle">
                                <div>
                                    <div class="fw-semibold text-dark fs-8" style="font-size: 13.5px;"><i class="bi bi-file-earmark-check-fill text-success me-1"></i>{{ $doc->title }}</div>
                                    <div class="text-muted" style="font-size: 11px;">{{ $doc->file_name }} ({{ round($doc->file_size / 1024 / 1024, 2) }} MB)</div>
                                </div>
                                <div class="btn-group">
                                    <a href="{{ route('documents.view', $doc->id) }}" target="_blank" class="btn btn-sm btn-outline-secondary px-2.5" title="View Document"><i class="bi bi-eye"></i> View</a>
                                    <a href="{{ route('documents.download', $doc->id) }}" class="btn btn-sm btn-outline-primary px-2.5" title="Download"><i class="bi bi-download"></i> Download</a>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="col-12 text-center text-muted py-4 fs-8">
                            <i class="bi bi-files text-secondary" style="font-size: 32px;"></i>
                            <div class="mt-2">No documents uploaded.</div>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- 3. HR Actions Checklist & Approvals Form (At the bottom of the page) -->
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-white py-3">
                <h5 class="mb-0 fw-bold text-dark"><i class="bi bi-patch-check-fill text-success me-1.5"></i> HR Approval & Activation</h5>
            </div>
            <div class="card-body p-4">
                <form method="POST" action="{{ route('employees.onboardings.approve', $onboarding->id) }}">
                    @csrf
                    
                    <!-- Section 10 -->
                    <h6 class="fw-bold text-secondary mb-3 border-bottom pb-2" style="font-size: 14px;"><i class="bi bi-shield-lock me-1"></i> Section 10 – Official Access Requirements</h6>
                    <div class="row g-3 mb-4">
                        @php
                            $access = ['Official Email ID', 'Attendance System', 'HRMS Login', 'ERP Login', 'GitHub', 'GitLab', 'Server Access', 'VPN Access', 'Google Workspace', 'Microsoft Office'];
                        @endphp
                        @foreach($access as $acc)
                            <div class="col-md-3 col-sm-6 col-12">
                                <div class="d-flex align-items-center bg-light p-2 px-3 rounded border" style="min-height: 42px;">
                                    <div class="form-check form-switch mb-0">
                                        <input class="form-check-input" type="checkbox" role="switch" name="company_access_requirements[]" id="access_{{ Str::slug($acc) }}" value="{{ $acc }}"
                                            {{ is_array(old('company_access_requirements', $onboarding->company_access_requirements)) && in_array($acc, old('company_access_requirements', $onboarding->company_access_requirements)) ? 'checked' : '' }}>
                                        <label class="form-check-label fs-8 text-dark fw-semibold cursor-pointer" for="access_{{ Str::slug($acc) }}">{{ $acc }}</label>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                        <div class="col-12 mt-2">
                            <label class="form-label fs-8 fw-semibold">Other Access Requirements</label>
                            <input type="text" name="company_access_other" class="form-control" placeholder="Specify any additional system permissions or workspace accounts..." value="{{ old('company_access_other', $onboarding->company_access_other) }}">
                        </div>
                    </div>

                    <!-- Section 11 -->
                    <h6 class="fw-bold text-secondary mb-3 border-bottom pb-2" style="font-size: 14px;"><i class="bi bi-laptop me-1"></i> Section 11 – IT Asset Requirement</h6>
                    <div class="row g-3 mb-4">
                        @php
                            $assets = ['Laptop', 'Charger', 'Mouse', 'Keyboard', 'Headset', 'Monitor', 'ID Card', 'SIM Card', 'Access Card'];
                        @endphp
                        @foreach($assets as $asset)
                            <div class="col-md-2 col-sm-4 col-6">
                                <div class="d-flex align-items-center bg-light p-2 px-3 rounded border" style="min-height: 42px;">
                                    <div class="form-check mb-0">
                                        <input class="form-check-input" type="checkbox" name="assets_issued[]" id="asset_{{ Str::slug($asset) }}" value="{{ $asset }}"
                                            {{ is_array(old('assets_issued', $onboarding->assets_issued)) && in_array($asset, old('assets_issued', $onboarding->assets_issued)) ? 'checked' : '' }}>
                                        <label class="form-check-label fs-8 text-dark fw-semibold cursor-pointer" for="asset_{{ Str::slug($asset) }}">{{ $asset }}</label>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                        <div class="col-12 mt-2">
                            <label class="form-label fs-8 fw-semibold">Asset Remarks (Serial numbers, accessories)</label>
                            <textarea name="assets_remarks" class="form-control" rows="2" placeholder="Enter laptop serial number, brand details, mouse/charger models or asset tag values...">{{ old('assets_remarks', $onboarding->assets_remarks) }}</textarea>
                        </div>
                    </div>

                    <!-- HR USE ONLY -->
                    <h6 class="fw-bold text-secondary mb-3 border-bottom pb-2" style="font-size: 14px;"><i class="bi bi-file-person me-1"></i> FOR HR USE ONLY (Activates Employee & User Accounts)</h6>
                    <div class="row g-3 mb-4">
                        <div class="col-md-3 col-sm-6 col-12">
                            <label class="form-label fs-8 fw-semibold">Employee ID / Code <span class="text-danger">*</span></label>
                            @php
                                $emp_code = old('employee_code', $onboarding->employee_code ?? 'EMP' . str_pad(\App\Models\User::max('id') + 1, 4, '0', STR_PAD_LEFT));
                            @endphp
                            <input type="text" name="employee_code" class="form-control" value="{{ $emp_code }}" required>
                        </div>
                        <div class="col-md-3 col-sm-6 col-12">
                            <label class="form-label fs-8 fw-semibold">Official Email Address <span class="text-danger">*</span></label>
                            @php
                                $suggestedEmail = strtolower(str_replace(' ', '', $onboarding->name)) . '@techsoulcybersolutions.com';
                            @endphp
                            <input type="email" name="official_email" class="form-control" value="{{ old('official_email', $onboarding->official_email ?? $suggestedEmail) }}" required>
                            <span class="text-muted" style="font-size: 10px;">Will create User profile with this email.</span>
                        </div>
                        <div class="col-md-3 col-sm-6 col-12">
                            <label class="form-label fs-8 fw-semibold">Employment Type <span class="text-danger">*</span></label>
                            <select name="employment_type" class="form-select" required>
                                <option value="">Select Option</option>
                                <option value="Permanent" {{ old('employment_type', $onboarding->employment_type) === 'Permanent' ? 'selected' : '' }}>Permanent</option>
                                <option value="Probation" {{ old('employment_type', $onboarding->employment_type) === 'Probation' ? 'selected' : '' }}>Probation</option>
                                <option value="Contract" {{ old('employment_type', $onboarding->employment_type) === 'Contract' ? 'selected' : '' }}>Contract</option>
                                <option value="Internship" {{ old('employment_type', $onboarding->employment_type) === 'Internship' ? 'selected' : '' }}>Internship</option>
                            </select>
                        </div>
                        <div class="col-md-3 col-sm-6 col-12">
                            <label class="form-label fs-8 fw-semibold">Salary / Stipend <span class="text-danger">*</span></label>
                            <input type="number" name="salary" class="form-control" value="{{ old('salary', $onboarding->salary) }}" required>
                        </div>
                    </div>

                    <!-- Signatures -->
                    <div class="row g-3 mb-4">
                        <div class="col-md-4 col-12">
                            <label class="form-label fs-8 fw-semibold">Approved By Name</label>
                            <input type="text" name="approved_by" class="form-control" value="{{ old('approved_by', auth()->user()->name) }}">
                        </div>
                        <div class="col-md-4 col-6">
                            <label class="form-label fs-8 fw-semibold">HR Signature</label>
                            <input type="text" name="hr_signature" class="form-control" value="{{ old('hr_signature', 'HR APPROVED') }}">
                        </div>
                        <div class="col-md-4 col-6">
                            <label class="form-label fs-8 fw-semibold">Management Signature</label>
                            <input type="text" name="management_signature" class="form-control" value="{{ old('management_signature', 'APPROVED BY MGT') }}">
                        </div>
                    </div>

                    <!-- Submission buttons -->
                    <div class="d-flex justify-content-end gap-2 pt-3 border-top">
                        <button type="button" class="btn btn-outline-danger px-4" onclick="showRejectModal()"><i class="bi bi-x-circle-fill me-1"></i> Request Revision</button>
                        <button type="submit" class="btn btn-success px-5 fw-bold"><i class="bi bi-check-circle-fill me-1"></i> Approve & Activate Account</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Modal: Revision Feedback Request -->
<div class="modal fade" id="rejectModal" tabindex="-1" aria-labelledby="rejectModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-danger text-white py-3">
                <h5 class="modal-title fw-bold" id="rejectModalLabel">Request Revisions</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" action="{{ route('employees.onboardings.reject', $onboarding->id) }}">
                @csrf
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Feedback / Revision Details <span class="text-danger">*</span></label>
                        <textarea class="form-control" name="remarks" rows="5" placeholder="Specify precisely what details or documents the candidate needs to modify (e.g., upload clearer Aadhaar Card Copy, fix incorrect bank details)..." required></textarea>
                    </div>
                    <p class="text-muted fs-8 mb-0">This will revert the status of the form to pending, allowing the employee to correct their details using the same link.</p>
                </div>
                <div class="modal-footer bg-light border-0 py-3">
                    <button type="button" class="btn btn-outline-secondary btn-sm px-3" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger btn-sm px-4">Submit Request</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function showRejectModal() {
        const modal = new bootstrap.Modal(document.getElementById('rejectModal'));
        modal.show();
    }
</script>
@endpush
