<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Employee Onboarding Form - Techsoul Cyber Solutions</title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Caveat:wght@700&family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    
    <style>
        :root {
            --bg-color: #f8fafc;
            --card-bg: #ffffff;
            --border-color: #e2e8f0;
            --accent-primary: #6366f1;
            --accent-glow: rgba(99, 102, 241, 0.15);
            --text-main: #0f172a;
            --text-muted: #64748b;
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--bg-color);
            color: var(--text-main);
            min-height: 100vh;
            padding: 40px 0;
            overflow-x: hidden;
        }

        .glass-card {
            background: var(--card-bg);
            border: 1px solid var(--border-color);
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(15, 23, 42, 0.04);
        }

        .step-indicator {
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: relative;
            margin-bottom: 40px;
        }
        
        .step-indicator::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 0;
            right: 0;
            height: 2px;
            background: #e2e8f0;
            z-index: 1;
            transform: translateY(-50%);
        }

        .step-indicator-bar {
            position: absolute;
            top: 50%;
            left: 0;
            height: 2px;
            background: var(--accent-primary);
            z-index: 2;
            transform: translateY(-50%);
            transition: width 0.4s ease;
            width: 0%;
        }

        .step-dot {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: #ffffff;
            border: 2px solid #e2e8f0;
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 3;
            font-weight: 600;
            transition: all 0.3s ease;
            color: var(--text-muted);
        }

        .step-dot.active {
            background: #ffffff;
            border-color: var(--accent-primary);
            color: var(--accent-primary);
            box-shadow: 0 0 10px var(--accent-glow);
        }

        .step-dot.completed {
            background: var(--accent-primary);
            border-color: var(--accent-primary);
            color: white;
        }

        .form-section {
            display: none;
        }

        .form-section.active {
            display: block;
            animation: fadeIn 0.5s ease;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .form-label {
            font-weight: 500;
            color: #334155;
            font-size: 14px;
        }

        .form-control, .form-select {
            background: #ffffff;
            border: 1px solid #cbd5e1;
            color: #0f172a;
            border-radius: 10px;
            padding: 11px 15px;
            transition: all 0.2s ease;
        }

        .form-control:focus, .form-select:focus {
            background: #ffffff;
            border-color: var(--accent-primary);
            box-shadow: 0 0 8px var(--accent-glow);
            color: #0f172a;
        }

        .form-control::placeholder {
            color: #94a3b8;
        }

        .radio-card {
            border: 1px solid #cbd5e1;
            background: #ffffff;
            border-radius: 12px;
            padding: 15px;
            cursor: pointer;
            transition: all 0.25s ease;
            display: flex;
            align-items: center;
            gap: 12px;
            color: #0f172a;
        }

        .radio-card:hover {
            border-color: rgba(99, 102, 241, 0.5);
            background: #f8fafc;
        }

        .radio-card-input:checked + .radio-card {
            border-color: var(--accent-primary);
            background: rgba(99, 102, 241, 0.05);
            box-shadow: 0 0 8px var(--accent-glow);
        }

        .upload-zone {
            border: 2px dashed #cbd5e1;
            border-radius: 12px;
            padding: 20px;
            text-align: center;
            background: #ffffff;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .upload-zone:hover {
            border-color: var(--accent-primary);
            background: rgba(99, 102, 241, 0.02);
        }

        .upload-zone i {
            font-size: 28px;
            color: var(--accent-primary);
            margin-bottom: 10px;
        }

        .signature-preview {
            font-family: 'Caveat', cursive;
            font-size: 32px;
            color: #4f46e5;
            border-bottom: 2px solid #cbd5e1;
            display: inline-block;
            min-width: 250px;
            text-align: center;
            padding: 10px;
            height: 60px;
        }

        .info-strip {
            background: rgba(99, 102, 241, 0.05);
            border-left: 4px solid var(--accent-primary);
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 30px;
        }
    </style>
</head>
<body>
    <div class="container py-3">
        <div class="row">
            <div class="col-12 col-xl-10 mx-auto">
                
                <!-- Logo & Brand Header -->
                <div class="text-center mb-4 text-dark">
                    <h2 class="fw-extrabold mb-1 text-dark">TECHSOUL CYBER SOLUTIONS</h2>
                    <h5 class="text-primary text-uppercase fw-semibold" style="font-size: 13px; letter-spacing: 2px;">Employee Onboarding & Personal Information Form</h5>
                    <p class="text-muted fs-7 mb-0">Join us in securing, designing, and building the future of IT infrastructure.</p>
                </div>

                <!-- Form Card -->
                <div class="card glass-card p-4 p-md-5">
                    
                    <!-- Pre-filled Details Strip -->
                    <div class="info-strip mb-4">
                        <div class="row g-3" style="font-size: 13.5px; color: var(--text-main);">
                            <div class="col-6 col-md-3">
                                <span class="text-muted d-block">Department</span>
                                <strong>{{ $onboarding->department->name ?? 'N/A' }}</strong>
                            </div>
                            <div class="col-6 col-md-3">
                                <span class="text-muted d-block">Designation</span>
                                <strong>{{ $onboarding->designation->name ?? 'N/A' }}</strong>
                            </div>
                            <div class="col-6 col-md-3">
                                <span class="text-muted d-block">Reporting Manager</span>
                                <strong>{{ $onboarding->teamLeader->name ?? 'N/A' }}</strong>
                            </div>
                            <div class="col-6 col-md-3">
                                <span class="text-muted d-block">Date of Joining</span>
                                <strong>{{ $onboarding->joining_date ? $onboarding->joining_date->format('d M Y') : 'N/A' }}</strong>
                            </div>
                        </div>
                    </div>

                    @if($onboarding->status === 'pending' && $onboarding->assets_remarks && str_contains($onboarding->assets_remarks, 'Revision Requested:'))
                        <div class="alert alert-warning border-0 bg-warning-subtle text-warning-emphasis mb-4 rounded-3 d-flex gap-2.5">
                            <i class="bi bi-exclamation-triangle-fill"></i>
                            <div>
                                <h6 class="fw-bold mb-1">Revision Requested by HR:</h6>
                                <p class="mb-0 fs-7">{{ str_replace('Revision Requested: ', '', $onboarding->assets_remarks) }}</p>
                            </div>
                        </div>
                    @endif

                    <!-- Step Progress Indicator -->
                    <div class="step-indicator">
                        <div class="step-indicator-bar" id="progressBar"></div>
                        <div class="step-dot active" data-step="1">1</div>
                        <div class="step-dot" data-step="2">2</div>
                        <div class="step-dot" data-step="3">3</div>
                        <div class="step-dot" data-step="4">4</div>
                        <div class="step-dot" data-step="5">5</div>
                        <div class="step-dot" data-step="6">6</div>
                    </div>

                    @if ($errors->any())
                        <div class="alert alert-danger border-0 bg-danger-subtle text-danger mb-4 rounded-3">
                            <h6 class="fw-bold mb-2">Please correct the following validation errors:</h6>
                            <ul class="mb-0 fs-7">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <!-- Form Body -->
                    <form method="POST" id="onboardingForm" action="{{ route('employees.onboard.submit', $onboarding->token) }}" enctype="multipart/form-data">
                        @csrf
                        
                        <!-- ================= STEP 1: PERSONAL & CONTACT INFO ================= -->
                        <div class="form-section active" data-step="1">
                            <h5 class="fw-bold border-bottom pb-2 border-light-subtle mb-4 text-primary"><i class="bi bi-person-circle me-2"></i> Section 1 & 2: Personal Information & Contact Details</h5>
                            
                            <div class="row g-4">
                                <div class="col-12">
                                    <label class="form-label">Full Name (As per Aadhaar) <span class="text-danger">*</span></label>
                                    <input type="text" name="name" class="form-control" value="{{ old('name', $onboarding->name) }}" required>
                                </div>
                                
                                <div class="col-12 col-md-4">
                                    <label class="form-label">Gender <span class="text-danger">*</span></label>
                                    <div class="d-flex gap-2">
                                        <div class="flex-grow-1">
                                            <input type="radio" class="btn-check radio-card-input" name="gender" id="gender_m" value="Male" {{ old('gender', $onboarding->gender) === 'Male' ? 'checked' : '' }} required>
                                            <label class="radio-card d-flex align-items-center justify-content-center" for="gender_m">Male</label>
                                        </div>
                                        <div class="flex-grow-1">
                                            <input type="radio" class="btn-check radio-card-input" name="gender" id="gender_f" value="Female" {{ old('gender', $onboarding->gender) === 'Female' ? 'checked' : '' }}>
                                            <label class="radio-card d-flex align-items-center justify-content-center" for="gender_f">Female</label>
                                        </div>
                                        <div class="flex-grow-1">
                                            <input type="radio" class="btn-check radio-card-input" name="gender" id="gender_o" value="Other" {{ old('gender', $onboarding->gender) === 'Other' ? 'checked' : '' }}>
                                            <label class="radio-card d-flex align-items-center justify-content-center" for="gender_o">Other</label>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-12 col-md-4">
                                    <label class="form-label">Date of Birth <span class="text-danger">*</span></label>
                                    <input type="date" name="dob" class="form-control" value="{{ old('dob', $onboarding->dob ? $onboarding->dob->format('Y-m-d') : '') }}" required>
                                </div>
                                <div class="col-12 col-md-4">
                                    <label class="form-label">Blood Group</label>
                                    <select name="blood_group" class="form-select">
                                        <option value="">Select Blood Group</option>
                                        @foreach(['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'] as $bg)
                                            <option value="{{ $bg }}" {{ old('blood_group', $onboarding->blood_group) === $bg ? 'selected' : '' }}>{{ $bg }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-12 col-md-4">
                                    <label class="form-label">Marital Status <span class="text-danger">*</span></label>
                                    <select name="marital_status" class="form-select" required>
                                        <option value="">Select Status</option>
                                        @foreach(['Single', 'Married', 'Divorced', 'Widowed'] as $ms)
                                            <option value="{{ $ms }}" {{ old('marital_status', $onboarding->marital_status) === $ms ? 'selected' : '' }}>{{ $ms }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-12 col-md-4">
                                    <label class="form-label">Nationality <span class="text-danger">*</span></label>
                                    <input type="text" name="nationality" class="form-control" value="{{ old('nationality', $onboarding->nationality ?? 'Indian') }}" required>
                                </div>
                                <div class="col-12 col-md-4">
                                    <label class="form-label">Mobile Number <span class="text-danger">*</span></label>
                                    <input type="text" name="phone" class="form-control" value="{{ old('phone', $onboarding->phone) }}" placeholder="WhatsApp/Contact Mobile" required>
                                </div>

                                <div class="col-12 col-md-6">
                                    <label class="form-label">Aadhaar Number <span class="text-danger">*</span></label>
                                    <input type="text" name="aadhaar_number" class="form-control" value="{{ old('aadhaar_number', $onboarding->aadhaar_number) }}" placeholder="12 Digit Aadhaar Card No." required>
                                </div>
                                <div class="col-12 col-md-6">
                                    <label class="form-label">PAN Number <span class="text-danger">*</span></label>
                                    <input type="text" name="pan_number" class="form-control" value="{{ old('pan_number', $onboarding->pan_number) }}" placeholder="10 Digit PAN Card No." required>
                                </div>
                                <div class="col-12 col-md-6">
                                    <label class="form-label">Passport Number (If Available)</label>
                                    <input type="text" name="passport_number" class="form-control" value="{{ old('passport_number', $onboarding->passport_number) }}">
                                </div>
                                <div class="col-12 col-md-6">
                                    <label class="form-label">Driving License Number (If Available)</label>
                                    <input type="text" name="driving_license_number" class="form-control" value="{{ old('driving_license_number', $onboarding->driving_license_number) }}">
                                </div>

                                <div class="col-12 col-md-6">
                                    <label class="form-label">Alternate Mobile Number</label>
                                    <input type="text" name="alternate_mobile" class="form-control" value="{{ old('alternate_mobile', $onboarding->alternate_mobile) }}">
                                </div>
                                <div class="col-12 col-md-6">
                                    <label class="form-label">Personal Email Address <span class="text-danger">*</span></label>
                                    <input type="email" name="personal_email" class="form-control" value="{{ old('personal_email', $onboarding->personal_email ?? $onboarding->email) }}" required>
                                </div>

                                <div class="col-12"><hr class="my-1 border-light-subtle"></div>

                                <div class="col-12 col-md-8">
                                    <label class="form-label">Current Residential Address <span class="text-danger">*</span></label>
                                    <textarea name="current_address" id="current_address" class="form-control" rows="3" required>{{ old('current_address', $onboarding->current_address) }}</textarea>
                                </div>
                                <div class="col-12 col-md-4">
                                    <label class="form-label">PIN Code <span class="text-danger">*</span></label>
                                    <input type="text" name="current_pin_code" id="current_pin_code" class="form-control" value="{{ old('current_pin_code', $onboarding->current_pin_code) }}" required>
                                </div>

                                <div class="col-12">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="same_as_current" id="same_as_current" value="1" {{ old('same_as_current', $onboarding->same_as_current) ? 'checked' : '' }} onchange="syncPermanentAddress()">
                                        <label class="form-check-label fs-8 text-dark" for="same_as_current">
                                            Permanent Address is same as Current Address
                                        </label>
                                    </div>
                                </div>

                                <div class="col-12 col-md-8" id="permanent_address_div">
                                    <label class="form-label">Permanent Address <span class="text-danger">*</span></label>
                                    <textarea name="permanent_address" id="permanent_address" class="form-control" rows="3" required>{{ old('permanent_address', $onboarding->permanent_address) }}</textarea>
                                </div>
                                <div class="col-12 col-md-4" id="permanent_pin_code_div">
                                    <label class="form-label">PIN Code <span class="text-danger">*</span></label>
                                    <input type="text" name="permanent_pin_code" id="permanent_pin_code" class="form-control" value="{{ old('permanent_pin_code', $onboarding->permanent_pin_code) }}" required>
                                </div>
                            </div>
                        </div>

                        <!-- ================= STEP 2: EMERGENCY DETAILS ================= -->
                        <div class="form-section" data-step="2">
                            <h5 class="fw-bold border-bottom pb-2 border-light-subtle mb-4 text-primary"><i class="bi bi-people-fill me-2"></i> Section 3: Emergency Contact Details</h5>
                            
                            <div class="row g-4">
                                <div class="col-12 col-md-6">
                                    <label class="form-label">Emergency Contact Person <span class="text-danger">*</span></label>
                                    <input type="text" name="emergency_contact_person" class="form-control" value="{{ old('emergency_contact_person', $onboarding->emergency_contact_person) }}" required>
                                </div>
                                <div class="col-12 col-md-3">
                                    <label class="form-label">Relationship <span class="text-danger">*</span></label>
                                    <input type="text" name="emergency_relationship" class="form-control" value="{{ old('emergency_relationship', $onboarding->emergency_relationship) }}" placeholder="Father, Spouse, etc." required>
                                </div>
                                <div class="col-12 col-md-3">
                                    <label class="form-label">Mobile Number <span class="text-danger">*</span></label>
                                    <input type="text" name="emergency_phone" class="form-control" value="{{ old('emergency_phone', $onboarding->emergency_phone) }}" required>
                                </div>
                                <div class="col-12 col-md-6">
                                    <label class="form-label">Alternate Number</label>
                                    <input type="text" name="emergency_alternate_phone" class="form-control" value="{{ old('emergency_alternate_phone', $onboarding->emergency_alternate_phone) }}">
                                </div>
                                <div class="col-12 col-md-6">
                                    <label class="form-label">Address</label>
                                    <input type="text" name="emergency_address" class="form-control" value="{{ old('emergency_address', $onboarding->emergency_address) }}">
                                </div>
                            </div>
                        </div>

                        <!-- ================= STEP 3: EDUCATIONAL QUALIFICATIONS ================= -->
                        <div class="form-section" data-step="3">
                            <h5 class="fw-bold border-bottom pb-2 border-light-subtle mb-4 text-primary"><i class="bi bi-mortarboard-fill me-2"></i> Section 5: Educational Qualifications</h5>
                            
                            <p class="text-muted fs-8">Please enter details of your educational credentials below starting from SSLC:</p>
                            <div class="table-responsive">
                                <table class="table table-bordered align-middle">
                                    <thead class="bg-light fs-8 text-secondary">
                                        <tr>
                                            <th style="width: 200px;">Qualification</th>
                                            <th>Institution</th>
                                            <th>Board / University</th>
                                            <th>Year Passed</th>
                                            <th>Percentage / CGPA</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @php
                                            $quals = ['SSLC', 'Plus Two', 'Diploma', 'Degree', 'Post Graduation', 'Other'];
                                        @endphp
                                        @foreach($quals as $index => $q)
                                            @php
                                                $isMandatory = in_array($q, ['SSLC', 'Plus Two']);
                                            @endphp
                                            <tr>
                                                <td class="fw-medium text-dark bg-light-subtle">
                                                    {{ $q }} @if($isMandatory) <span class="text-danger">*</span> @endif
                                                    <input type="hidden" name="education_qualifications[{{ $index }}][qualification]" value="{{ $q }}">
                                                </td>
                                                <td>
                                                    <input type="text" name="education_qualifications[{{ $index }}][institution]" class="form-control form-control-sm py-1" placeholder="School/College Name" {{ $isMandatory ? 'required' : '' }}>
                                                </td>
                                                <td>
                                                    <input type="text" name="education_qualifications[{{ $index }}][board_university]" class="form-control form-control-sm py-1" placeholder="e.g. CBSE, State Board, VTU" {{ $isMandatory ? 'required' : '' }}>
                                                </td>
                                                <td>
                                                    <input type="text" name="education_qualifications[{{ $index }}][year_passed]" class="form-control form-control-sm py-1" placeholder="e.g. 2018" {{ $isMandatory ? 'required' : '' }}>
                                                </td>
                                                <td>
                                                    <input type="text" name="education_qualifications[{{ $index }}][percentage]" class="form-control form-control-sm py-1" placeholder="e.g. 85% or 8.5" {{ $isMandatory ? 'required' : '' }}>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- ================= STEP 4: PROFESSIONAL & BANK DETAILS ================= -->
                        <div class="form-section" data-step="4">
                            <h5 class="fw-bold border-bottom pb-2 border-light-subtle mb-4 text-primary"><i class="bi bi-briefcase-fill me-2"></i> Section 6 & 7 & 8: Professional, Bank & PF Details</h5>
                            
                            <h6 class="text-muted border-bottom pb-2 mb-3 border-light-subtle">Section 6 – Professional Details</h6>
                            <div class="row g-4 mb-4">
                                <div class="col-12 col-md-4">
                                    <label class="form-label">Total Experience (Years) <span class="text-danger">*</span></label>
                                    <input type="text" name="total_experience" class="form-control" value="{{ old('total_experience', $onboarding->total_experience) }}" placeholder="e.g. 2.5 Years (0 for fresher)" required>
                                </div>
                                <div class="col-12 col-md-8">
                                    <label class="form-label">Previous Employer (If Applicable)</label>
                                    <input type="text" name="prev_employer" class="form-control" value="{{ old('prev_employer', $onboarding->prev_employer) }}">
                                </div>
                                <div class="col-12 col-md-4">
                                    <label class="form-label">Previous Designation</label>
                                    <input type="text" name="prev_designation" class="form-control" value="{{ old('prev_designation', $onboarding->prev_designation) }}">
                                </div>
                                <div class="col-12 col-md-4">
                                    <label class="form-label">Previous Duration</label>
                                    <input type="text" name="prev_duration" class="form-control" value="{{ old('prev_duration', $onboarding->prev_duration) }}" placeholder="e.g. Jan 2024 - Dec 2025">
                                </div>
                                <div class="col-12 col-md-4">
                                    <label class="form-label">Reason for Leaving</label>
                                    <input type="text" name="prev_reason_for_leaving" class="form-control" value="{{ old('prev_reason_for_leaving', $onboarding->prev_reason_for_leaving) }}">
                                </div>

                                <div class="col-12">
                                    <label class="form-label d-block">Skills & Technologies Known <span class="text-danger">*</span></label>
                                    <div class="row g-2 mt-1">
                                        @php
                                            $skills = ['PHP', 'Laravel', 'Flutter', 'React', 'React Native', 'Python', 'Java', 'MySQL', 'PostgreSQL', 'HTML/CSS', 'JavaScript', 'Digital Marketing', 'Graphic Design', 'Video Editing', 'Networking', 'Hardware Support', 'CCTV Installation', 'AI & Automation'];
                                        @endphp
                                        @foreach($skills as $skill)
                                            <div class="col-6 col-sm-4 col-md-3">
                                                <input class="form-check-input" type="checkbox" name="skills[]" id="skill_{{ Str::slug($skill) }}" value="{{ $skill }}">
                                                <label class="form-check-label fs-8 text-dark ms-1" for="skill_{{ Str::slug($skill) }}">{{ $skill }}</label>
                                            </div>
                                        @endforeach
                                        <div class="col-12 mt-3">
                                            <label class="form-label fs-8 text-secondary">Other Skills & Technologies:</label>
                                            <input type="text" name="skills_other" class="form-control" value="{{ old('skills_other', $onboarding->skills_other) }}" placeholder="Specify other technical expertise...">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <h6 class="text-muted border-bottom pb-2 mb-3 border-light-subtle">Section 7 – Bank Details</h6>
                            <div class="row g-4 mb-4">
                                <div class="col-12 col-md-6">
                                    <label class="form-label">Account Holder Name <span class="text-danger">*</span></label>
                                    <input type="text" name="bank_account_holder" class="form-control" value="{{ old('bank_account_holder', $onboarding->bank_account_holder) }}" placeholder="As per bank passbook" required>
                                </div>
                                <div class="col-12 col-md-6">
                                    <label class="form-label">Bank Name <span class="text-danger">*</span></label>
                                    <input type="text" name="bank_name" class="form-control" value="{{ old('bank_name', $onboarding->bank_name) }}" required>
                                </div>
                                <div class="col-12 col-md-4">
                                    <label class="form-label">Branch Name <span class="text-danger">*</span></label>
                                    <input type="text" name="bank_branch" class="form-control" value="{{ old('bank_branch', $onboarding->bank_branch) }}" required>
                                </div>
                                <div class="col-12 col-md-4">
                                    <label class="form-label">Account Number <span class="text-danger">*</span></label>
                                    <input type="text" name="bank_account_number" class="form-control" value="{{ old('bank_account_number', $onboarding->bank_account_number) }}" required>
                                </div>
                                <div class="col-12 col-md-4">
                                    <label class="form-label">IFSC Code <span class="text-danger">*</span></label>
                                    <input type="text" name="bank_ifsc" class="form-control" value="{{ old('bank_ifsc', $onboarding->bank_ifsc) }}" placeholder="e.g. SBIN0001234" required>
                                </div>
                                <div class="col-12 col-md-6">
                                    <label class="form-label">UPI ID</label>
                                    <input type="text" name="bank_upi" class="form-control" value="{{ old('bank_upi', $onboarding->bank_upi) }}" placeholder="e.g. name@upi">
                                </div>
                            </div>

                            <h6 class="text-muted border-bottom pb-2 mb-3 border-light-subtle">Section 8 – PF / ESI Details (If Available)</h6>
                            <div class="row g-4">
                                <div class="col-12 col-md-4">
                                    <label class="form-label">UAN Number</label>
                                    <input type="text" name="uan_number" class="form-control" value="{{ old('uan_number', $onboarding->uan_number) }}" placeholder="12 Digit UAN">
                                </div>
                                <div class="col-12 col-md-4">
                                    <label class="form-label">PF Number</label>
                                    <input type="text" name="pf_number" class="form-control" value="{{ old('pf_number', $onboarding->pf_number) }}">
                                </div>
                                <div class="col-12 col-md-4">
                                    <label class="form-label">ESI Number</label>
                                    <input type="text" name="esi_number" class="form-control" value="{{ old('esi_number', $onboarding->esi_number) }}">
                                </div>
                            </div>
                        </div>

                        <!-- ================= STEP 5: DOCUMENTS, MEDICAL, DECLARATION ================= -->
                        <div class="form-section" data-step="5">
                            <h5 class="fw-bold border-bottom pb-2 border-light-subtle mb-4 text-primary"><i class="bi bi-file-earmark-arrow-up-fill me-2"></i> Section 9 & 12 & 13: Files, Medical & Declaration</h5>
                            
                            <h6 class="text-muted border-bottom pb-2 mb-3 border-light-subtle">Section 9 – Document Submission Checklist (Max 5MB per file, Photo max 2MB)</h6>
                            <div class="row g-4 mb-4">
                                <div class="col-12 col-md-6">
                                    <label class="form-label">Passport Size Photo <span class="text-danger">*</span></label>
                                    <div class="upload-zone" onclick="document.getElementById('photo').click()">
                                        <i class="bi bi-image"></i>
                                        <div class="fs-8 fw-semibold text-secondary" id="photo_label">Click to Upload JPG/PNG Photo</div>
                                        <input type="file" name="photo" id="photo" class="d-none" accept="image/*" onchange="updateFileLabel('photo', 'photo_label')" required>
                                    </div>
                                </div>
                                <div class="col-12 col-md-6">
                                    <label class="form-label">Aadhaar Card Copy <span class="text-danger">*</span></label>
                                    <div class="upload-zone" onclick="document.getElementById('doc_aadhaar').click()">
                                        <i class="bi bi-file-pdf"></i>
                                        <div class="fs-8 fw-semibold text-secondary" id="doc_aadhaar_label">Click to Upload PDF/Image</div>
                                        <input type="file" name="doc_aadhaar" id="doc_aadhaar" class="d-none" accept=".pdf,image/*" onchange="updateFileLabel('doc_aadhaar', 'doc_aadhaar_label')" required>
                                    </div>
                                </div>
                                <div class="col-12 col-md-6">
                                    <label class="form-label">PAN Card Copy <span class="text-danger">*</span></label>
                                    <div class="upload-zone" onclick="document.getElementById('doc_pan').click()">
                                        <i class="bi bi-file-pdf"></i>
                                        <div class="fs-8 fw-semibold text-secondary" id="doc_pan_label">Click to Upload PDF/Image</div>
                                        <input type="file" name="doc_pan" id="doc_pan" class="d-none" accept=".pdf,image/*" onchange="updateFileLabel('doc_pan', 'doc_pan_label')" required>
                                    </div>
                                </div>
                                <div class="col-12 col-md-6">
                                    <label class="form-label">Resume / CV <span class="text-danger">*</span></label>
                                    <div class="upload-zone" onclick="document.getElementById('doc_resume').click()">
                                        <i class="bi bi-file-text"></i>
                                        <div class="fs-8 fw-semibold text-secondary" id="doc_resume_label">Click to Upload CV (PDF/Word)</div>
                                        <input type="file" name="doc_resume" id="doc_resume" class="d-none" accept=".pdf,.doc,.docx" onchange="updateFileLabel('doc_resume', 'doc_resume_label')" required>
                                    </div>
                                </div>
                                <div class="col-12 col-md-6">
                                    <label class="form-label">Educational Certificates <span class="text-danger">*</span></label>
                                    <div class="upload-zone" onclick="document.getElementById('doc_education').click()">
                                        <i class="bi bi-file-pdf"></i>
                                        <div class="fs-8 fw-semibold text-secondary" id="doc_education_label">Click to Upload Combined Degrees/Marksheets</div>
                                        <input type="file" name="doc_education" id="doc_education" class="d-none" accept=".pdf,image/*" onchange="updateFileLabel('doc_education', 'doc_education_label')" required>
                                    </div>
                                </div>
                                <div class="col-12 col-md-6">
                                    <label class="form-label">Bank Proof (Passbook/Cheque) <span class="text-danger">*</span></label>
                                    <div class="upload-zone" onclick="document.getElementById('doc_bank_proof').click()">
                                        <i class="bi bi-file-pdf"></i>
                                        <div class="fs-8 fw-semibold text-secondary" id="doc_bank_proof_label">Upload Cancelled Cheque/Passbook</div>
                                        <input type="file" name="doc_bank_proof" id="doc_bank_proof" class="d-none" accept=".pdf,image/*" onchange="updateFileLabel('doc_bank_proof', 'doc_bank_proof_label')" required>
                                    </div>
                                </div>

                                <div class="col-12 col-md-4">
                                    <label class="form-label">Experience Certificates (Optional)</label>
                                    <div class="upload-zone" onclick="document.getElementById('doc_experience').click()">
                                        <i class="bi bi-file-pdf"></i>
                                        <div class="fs-8 text-secondary" id="doc_experience_label">Upload PDF</div>
                                        <input type="file" name="doc_experience" id="doc_experience" class="d-none" accept=".pdf,image/*" onchange="updateFileLabel('doc_experience', 'doc_experience_label')">
                                    </div>
                                </div>
                                <div class="col-12 col-md-4">
                                    <label class="form-label">Relieving Letter (Optional)</label>
                                    <div class="upload-zone" onclick="document.getElementById('doc_relieving').click()">
                                        <i class="bi bi-file-pdf"></i>
                                        <div class="fs-8 text-secondary" id="doc_relieving_label">Upload PDF</div>
                                        <input type="file" name="doc_relieving" id="doc_relieving" class="d-none" accept=".pdf,image/*" onchange="updateFileLabel('doc_relieving', 'doc_relieving_label')">
                                    </div>
                                </div>
                                <div class="col-12 col-md-4">
                                    <label class="form-label">Salary Slip Last 3 Months (Optional)</label>
                                    <div class="upload-zone" onclick="document.getElementById('doc_salary').click()">
                                        <i class="bi bi-file-pdf"></i>
                                        <div class="fs-8 text-secondary" id="doc_salary_label">Upload PDF</div>
                                        <input type="file" name="doc_salary" id="doc_salary" class="d-none" accept=".pdf,image/*" onchange="updateFileLabel('doc_salary', 'doc_salary_label')">
                                    </div>
                                </div>
                                <div class="col-12 col-md-6">
                                    <label class="form-label">Passport Copy (Optional)</label>
                                    <div class="upload-zone" onclick="document.getElementById('doc_passport').click()">
                                        <i class="bi bi-file-pdf"></i>
                                        <div class="fs-8 text-secondary" id="doc_passport_label">Upload PDF</div>
                                        <input type="file" name="doc_passport" id="doc_passport" class="d-none" accept=".pdf,image/*" onchange="updateFileLabel('doc_passport', 'doc_passport_label')">
                                    </div>
                                </div>
                                <div class="col-12 col-md-6">
                                    <label class="form-label">Driving License Copy (Optional)</label>
                                    <div class="upload-zone" onclick="document.getElementById('doc_driving').click()">
                                        <i class="bi bi-file-pdf"></i>
                                        <div class="fs-8 text-secondary" id="doc_driving_label">Upload PDF</div>
                                        <input type="file" name="doc_driving" id="doc_driving" class="d-none" accept=".pdf,image/*" onchange="updateFileLabel('doc_driving', 'doc_driving_label')">
                                    </div>
                                </div>
                            </div>

                            <h6 class="text-muted border-bottom pb-2 mb-3 border-light-subtle">Section 12 – Medical Information (Optional)</h6>
                            <div class="row g-4 mb-4">
                                <div class="col-12 col-md-4">
                                    <label class="form-label">Any Medical Condition</label>
                                    <input type="text" name="medical_condition" class="form-control" value="{{ old('medical_condition', $onboarding->medical_condition) }}">
                                </div>
                                <div class="col-12 col-md-4">
                                    <label class="form-label">Any Allergies</label>
                                    <input type="text" name="medical_allergies" class="form-control" value="{{ old('medical_allergies', $onboarding->medical_allergies) }}">
                                </div>
                                <div class="col-12 col-md-4">
                                    <label class="form-label">Regular Medication</label>
                                    <input type="text" name="medical_medication" class="form-control" value="{{ old('medical_medication', $onboarding->medical_medication) }}">
                                </div>
                            </div>

                            <h6 class="text-muted border-bottom pb-2 mb-3 border-light-subtle">Section 13 – Declaration by Employee</h6>
                            <div class="row g-4">
                                <div class="col-12">
                                    <div class="p-3 bg-light rounded border border-secondary-subtle" style="font-size: 12.5px; line-height: 1.6; color: #334155; max-height: 180px; overflow-y: auto;">
                                        <p class="mb-2">I hereby certify that all information provided by me in this Employee Onboarding Form is true, complete, and accurate to the best of my knowledge.</p>
                                        <p class="mb-2">I understand that submission of false information may result in disciplinary action, including termination of employment.</p>
                                        <p class="mb-0">I agree to comply with all policies, procedures, regulations, confidentiality requirements, and employment conditions of Techsoul Cyber Solutions.</p>
                                    </div>
                                    <div class="form-check mt-3">
                                        <input class="form-check-input" type="checkbox" name="declaration_accepted" id="declaration_check" value="1" required>
                                        <label class="form-check-label fs-8 text-dark" for="declaration_check">
                                            I agree to the declaration details above. <span class="text-danger">*</span>
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- ================= STEP 6: OFFICE CODE OF CONDUCT ================= -->
                        <div class="form-section" data-step="6">
                            <h5 class="fw-bold border-bottom pb-2 border-light-subtle mb-4 text-primary"><i class="bi bi-file-earmark-ruled-fill me-2"></i> Techsoul Cyber Solutions - Office Code of Conduct</h5>
                            
                            <div class="p-4 bg-light rounded border border-secondary-subtle mb-4 text-start" style="font-size: 15px; line-height: 1.6; color: #1e293b; max-height: 380px; overflow-y: auto;">
                                <h5 class="fw-bold text-center mb-1 text-danger">TECHSOUL CYBER SOLUTIONS</h5>
                                <h6 class="fw-bold text-center mb-4 text-danger" style="font-size: 13.5px; letter-spacing: 1px;">OFFICE CODE OF CONDUCT</h6>
                                
                                <p>Welcome to Techsoul Cyber Solutions. We are committed to maintaining a professional, respectful, secure, and productive workplace. Every employee and intern is expected to comply with the following Code of Conduct.</p>
                                
                                <ol class="ps-3 mb-4">
                                    <li class="mb-3">
                                        <strong class="text-danger">PROFESSIONALISM</strong><br>
                                        Maintain honesty, integrity, and professionalism at all times.<br>
                                        Treat colleagues, clients, mentors, and visitors with respect and courtesy.<br>
                                        Follow all lawful instructions issued by your reporting manager or management.
                                    </li>
                                    <li class="mb-3">
                                        <strong class="text-danger">PUNCTUALITY & ATTENDANCE</strong><br>
                                        Report to work on time every working day.<br>
                                        Inform your Mentor or HR immediately if you are unable to attend work.<br>
                                        Unauthorized absence or repeated late attendance may result in disciplinary action.
                                    </li>
                                    <li class="mb-3">
                                        <strong class="text-danger">MOBILE PHONE USAGE</strong><br>
                                        <strong class="text-danger">Personal mobile phone usage during working hours is strictly prohibited.</strong><br>
                                        If you receive an emergency call, kindly step outside the office to attend the call.<br>
                                        Mobile phones must not interfere with your work responsibilities.
                                    </li>
                                    <li class="mb-3">
                                        <strong class="text-danger">CONFIDENTIALITY & DATA SECURITY</strong><br>
                                        Protect all Company and client information.<br>
                                        Do not copy, transfer, email, upload, photograph, or share any official documents, source code, databases, credentials, or project files without written authorization.<br>
                                        Do not store Company data on personal devices or cloud storage.
                                    </li>
                                    <li class="mb-3">
                                        <strong class="text-danger">USE OF COMPANY ASSETS</strong><br>
                                        Company laptops, computers, software, internet, and other resources are provided for official business only.<br>
                                        Do not install unauthorized software or connect unauthorized storage devices.<br>
                                        Take proper care of all Company property and immediately report any loss or damage.
                                    </li>
                                    <li class="mb-3">
                                        <strong class="text-danger">WORKPLACE BEHAVIOUR</strong><br>
                                        Maintain a clean, safe, and organized workspace.<br>
                                        Avoid loud conversations, inappropriate language, harassment, discrimination, or any behaviour that disrupts the workplace.<br>
                                        Smoking, alcohol, illegal drugs, and gambling are strictly prohibited within Company premises.
                                    </li>
                                    <li class="mb-3">
                                        <strong class="text-danger">LEAVE POLICY</strong><br>
                                        Employees and interns are expected to maintain regular attendance.<br>
                                        Leave should be requested and approved in advance whenever possible.<br>
                                        Emergency leave must be immediately communicated to the HR Department or Reporting Manager.
                                    </li>
                                    <li class="mb-3">
                                        <strong class="text-danger">CONFLICT OF INTEREST</strong><br>
                                        Do not engage in freelance work, competing business activities, or outside employment that conflicts with your responsibilities at Techsoul Cyber Solutions without prior written approval.<br>
                                        Do not misuse Company time or resources for personal commercial activities.
                                    </li>
                                    <li class="mb-3">
                                        <strong class="text-danger">COMPLIANCE</strong><br>
                                        Failure to comply with this Code of Conduct or other Company policies may result in disciplinary action, including warning, suspension, termination of employment/internship, legal action, or recovery of damages where applicable.
                                    </li>
                                </ol>
                                
                                <h6 class="fw-bold text-danger border-top pt-3">EMPLOYEE / INTERN ACKNOWLEDGEMENT</h6>
                                <p class="mb-0">I acknowledge that I have read, understood, and agree to comply with the Office Code of Conduct of Techsoul Cyber Solutions. I understand that adherence to these rules is a condition of my employment or internship.</p>
                            </div>
                            
                            <div class="alert alert-info border-0 bg-info-subtle text-info-emphasis rounded-3 mb-3 d-flex align-items-center gap-2">
                                <i class="bi bi-info-circle-fill"></i>
                                <div id="timerText" class="fw-semibold">Please read the Code of Conduct. Checkbox will unlock in 20 seconds.</div>
                            </div>
                            
                            <div class="form-check text-start">
                                <input class="form-check-input" type="checkbox" name="code_of_conduct_accepted" id="code_of_conduct_check" value="1" onchange="toggleConductSubmitButton()" required disabled>
                                <label class="form-check-label fs-8 text-dark fw-bold" for="code_of_conduct_check">
                                    I acknowledge and agree to comply with the Office Code of Conduct. <span class="text-danger">*</span>
                                </label>
                            </div>
                        </div>

                        <!-- Form Navigation Buttons -->
                        <div class="d-flex align-items-center justify-content-between border-top border-light-subtle pt-4 mt-4">
                            <button type="button" class="btn btn-outline-secondary btn-sm px-4" id="prevBtn" onclick="navigateStep(-1)" disabled>Previous</button>
                            <button type="button" class="btn btn-success btn-sm px-4" id="nextBtn" onclick="navigateStep(1)">Next Step</button>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap 5 Bundle JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Wizard Javascript -->
    <script>
        let currentStep = 1;
        const totalSteps = 6;

        // Initialize sync on load
        document.addEventListener("DOMContentLoaded", function() {
            syncPermanentAddress();
        });

        function syncPermanentAddress() {
            const checkbox = document.getElementById('same_as_current');
            const permDiv = document.getElementById('permanent_address_div');
            const permPinDiv = document.getElementById('permanent_pin_code_div');
            
            const curAddr = document.getElementById('current_address');
            const curPin = document.getElementById('current_pin_code');
            const permAddr = document.getElementById('permanent_address');
            const permPin = document.getElementById('permanent_pin_code');

            if (checkbox.checked) {
                permAddr.value = curAddr.value;
                permPin.value = curPin.value;
                permDiv.classList.add('d-none');
                permPinDiv.classList.add('d-none');
                permAddr.removeAttribute('required');
                permPin.removeAttribute('required');
            } else {
                permDiv.classList.remove('d-none');
                permPinDiv.classList.remove('d-none');
                permAddr.setAttribute('required', 'required');
                permPin.setAttribute('required', 'required');
            }
        }

        // Attach listeners to current address to sync live if same is checked
        document.getElementById('current_address').addEventListener('input', function() {
            if (document.getElementById('same_as_current').checked) {
                document.getElementById('permanent_address').value = this.value;
            }
        });
        document.getElementById('current_pin_code').addEventListener('input', function() {
            if (document.getElementById('same_as_current').checked) {
                document.getElementById('permanent_pin_code').value = this.value;
            }
        });

        function updateFileLabel(inputId, labelId) {
            const fileInput = document.getElementById(inputId);
            const label = document.getElementById(labelId);
            if (fileInput.files.length > 0) {
                label.textContent = fileInput.files[0].name;
                label.classList.remove('text-secondary');
                label.classList.add('text-success', 'fw-bold');
            }
        }

        let timerInterval = null;
        let secondsLeft = 20;

        function showStep(step) {
            document.querySelectorAll('.form-section').forEach(sec => {
                sec.classList.remove('active');
            });

            document.querySelector(`.form-section[data-step="${step}"]`).classList.add('active');

            document.querySelectorAll('.step-dot').forEach(dot => {
                const s = parseInt(dot.getAttribute('data-step'));
                dot.classList.remove('active', 'completed');
                if (s === step) {
                    dot.classList.add('active');
                } else if (s < step) {
                    dot.classList.add('completed');
                }
            });

            const progress = ((step - 1) / (totalSteps - 1)) * 100;
            document.getElementById('progressBar').style.width = `${progress}%`;

            const prevBtn = document.getElementById('prevBtn');
            const nextBtn = document.getElementById('nextBtn');

            if (step === 1) {
                prevBtn.disabled = true;
            } else {
                prevBtn.disabled = false;
            }

            if (step === totalSteps) {
                nextBtn.innerHTML = 'Acknowledge & Save <i class="bi bi-check-circle-fill ms-1"></i>';
                nextBtn.classList.remove('btn-success');
                nextBtn.classList.add('btn-primary');
                startCodeOfConductTimer();
            } else {
                nextBtn.innerHTML = 'Next Step <i class="bi bi-arrow-right-short ms-0.5"></i>';
                nextBtn.classList.remove('btn-primary');
                nextBtn.classList.add('btn-success');
                nextBtn.disabled = false;
            }
            
            window.scrollTo({ top: 120, behavior: 'smooth' });
        }

        function startCodeOfConductTimer() {
            const nextBtn = document.getElementById('nextBtn');
            const checkbox = document.getElementById('code_of_conduct_check');
            const timerText = document.getElementById('timerText');
            
            if (secondsLeft <= 0) {
                checkbox.disabled = false;
                toggleConductSubmitButton();
                return;
            }
            
            checkbox.disabled = true;
            nextBtn.disabled = true;
            
            if (timerInterval) {
                clearInterval(timerInterval);
            }
            
            timerInterval = setInterval(() => {
                secondsLeft--;
                if (secondsLeft > 0) {
                    timerText.innerHTML = `Please read the Code of Conduct. Checkbox will unlock in ${secondsLeft} seconds.`;
                } else {
                    clearInterval(timerInterval);
                    timerText.innerHTML = `You can now acknowledge the Code of Conduct.`;
                    checkbox.disabled = false;
                    toggleConductSubmitButton();
                }
            }, 1000);
        }

        function toggleConductSubmitButton() {
            const checkbox = document.getElementById('code_of_conduct_check');
            const nextBtn = document.getElementById('nextBtn');
            
            if (currentStep === totalSteps) {
                if (checkbox.checked && secondsLeft <= 0) {
                    nextBtn.disabled = false;
                } else {
                    nextBtn.disabled = true;
                }
            }
        }

        function validateStepFields(step) {
            const section = document.querySelector(`.form-section[data-step="${step}"]`);
            const inputs = section.querySelectorAll('input[required], select[required], textarea[required]');
            let isValid = true;

            inputs.forEach(input => {
                if (input.type === 'radio') {
                    const name = input.name;
                    const checkedRadio = section.querySelector(`input[name="${name}"]:checked`);
                    if (!checkedRadio) {
                        isValid = false;
                        const radioContainer = input.closest('.d-flex');
                        if (radioContainer) radioContainer.style.border = '1px solid #ef4444';
                    } else {
                        const radioContainer = input.closest('.d-flex');
                        if (radioContainer) radioContainer.style.border = '';
                    }
                } else if (input.type === 'checkbox') {
                    if (!input.checked) {
                        isValid = false;
                        input.classList.add('is-invalid');
                    } else {
                        input.classList.remove('is-invalid');
                    }
                } else {
                    if (!input.value.trim()) {
                        isValid = false;
                        input.classList.add('is-invalid');
                    } else {
                        input.classList.remove('is-invalid');
                    }
                }
            });

            // Specific check for Step 4: Skills checkboxes (at least one is required)
            if (step === 4) {
                const checkedSkills = section.querySelectorAll('input[name="skills[]"]:checked');
                if (checkedSkills.length === 0) {
                    isValid = false;
                    alert('Please select at least one Skill / Technology Known.');
                }
            }

            return isValid;
        }

        function navigateStep(direction) {
            if (direction === 1) {
                if (!validateStepFields(currentStep)) {
                    const section = document.querySelector(`.form-section[data-step="${currentStep}"]`);
                    const invalidInput = section.querySelector('.is-invalid, input:invalid, select:invalid, textarea:invalid');
                    if (invalidInput) {
                        invalidInput.focus();
                        invalidInput.reportValidity();
                    }
                    return;
                }
                
                if (currentStep < totalSteps) {
                    currentStep++;
                    showStep(currentStep);
                } else {
                    // Pre-submit address syncing check
                    syncPermanentAddress();
                    document.getElementById('onboardingForm').submit();
                }
            } else {
                if (currentStep > 1) {
                    currentStep--;
                    showStep(currentStep);
                }
            }
        }

    </script>
</body>
</html>
