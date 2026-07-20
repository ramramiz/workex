<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Intern Onboarding Form - Techsoul Cyber Solutions</title>
    
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
            --accent-primary: #10b981;
            --accent-glow: rgba(16, 185, 129, 0.15);
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
            border-color: rgba(16, 185, 129, 0.5);
            background: #f8fafc;
        }

        .radio-card-input:checked + .radio-card {
            border-color: var(--accent-primary);
            background: rgba(16, 185, 129, 0.05);
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
            background: rgba(16, 185, 129, 0.02);
        }

        .upload-zone i {
            font-size: 28px;
            color: var(--accent-primary);
            margin-bottom: 10px;
        }

        .signature-preview {
            font-family: 'Caveat', cursive;
            font-size: 32px;
            color: #059669;
            border-bottom: 2px solid #cbd5e1;
            display: inline-block;
            min-width: 250px;
            text-align: center;
            padding: 10px;
            height: 60px;
        }

        .info-strip {
            background: rgba(16, 185, 129, 0.05);
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
            <div class="col-12 col-xl-9 mx-auto">
                
                <!-- Logo & Brand Header -->
                <div class="text-center mb-4 text-dark">
                    <h2 class="fw-extrabold mb-1 letter-spacing-tight text-dark">TECHSOUL CYBER SOLUTIONS</h2>
                    <h5 class="text-success text-uppercase fw-semibold tracking-widest" style="font-size: 13px; letter-spacing: 2px;">Intern Onboarding & Information Form</h5>
                    <p class="text-muted fs-7 mb-0">"Learn by Working on Real Projects with Experienced Professionals"</p>
                </div>

                <!-- Form Card -->
                <div class="card glass-card p-4 p-md-5">
                    
                    <!-- Pre-filled Internship Details Strip -->
                    <div class="info-strip mb-4">
                        <div class="row g-3" style="font-size: 13.5px; color: var(--text-main);">
                            <div class="col-6 col-md-3">
                                <span class="text-muted d-block">Intern ID</span>
                                <strong>TSL-{{ str_pad(14737 + $onboarding->intern->id, 6, '0', STR_PAD_LEFT) }}</strong>
                            </div>
                            <div class="col-6 col-md-3">
                                <span class="text-muted d-block">Sector</span>
                                <strong>{{ $onboarding->sector ?? 'Techsoul Technologies' }}</strong>
                            </div>
                            <div class="col-6 col-md-3">
                                <span class="text-muted d-block">Department</span>
                                <strong>{{ $onboarding->intern->department->name ?? 'N/A' }}</strong>
                            </div>
                            <div class="col-6 col-md-3">
                                <span class="text-muted d-block">Period</span>
                                <strong>{{ $onboarding->intern->joining_date ? $onboarding->intern->joining_date->format('d M Y') : 'N/A' }} to {{ $onboarding->intern->end_date ? $onboarding->intern->end_date->format('d M Y') : 'N/A' }}</strong>
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
                    <form method="POST" id="onboardingForm" action="{{ route('interns.onboard.submit', $onboarding->token) }}" enctype="multipart/form-data">
                        @csrf
                        
                        <!-- ================= STEP 1: PERSONAL INFO ================= -->
                        <div class="form-section active" data-step="1">
                            <h5 class="fw-bold border-bottom pb-2 border-light-subtle mb-4 text-success"><i class="bi bi-person-circle me-2"></i> Section 1: Personal Information</h5>
                            
                            <div class="row g-4">
                                <div class="col-12">
                                    <label class="form-label">Full Name (As per Aadhaar) <span class="text-danger">*</span></label>
                                    <input type="text" name="name" class="form-control" value="{{ old('name', $onboarding->intern->name) }}" required>
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

                                <div class="col-12 col-md-6">
                                    <label class="form-label">Aadhaar Number <span class="text-danger">*</span></label>
                                    <input type="text" name="aadhaar_number" class="form-control" value="{{ old('aadhaar_number', $onboarding->aadhaar_number) }}" placeholder="12 Digit Aadhaar Card No." required>
                                </div>

                                <div class="col-12 col-md-6">
                                    <label class="form-label">Mobile Number <span class="text-danger">*</span></label>
                                    <input type="text" name="phone" class="form-control" value="{{ old('phone', $onboarding->intern->phone) }}" placeholder="WhatsApp/Contact Mobile" required>
                                </div>

                                <div class="col-12 col-md-6">
                                    <label class="form-label">Alternate Mobile Number</label>
                                    <input type="text" name="alternate_mobile" class="form-control" value="{{ old('alternate_mobile', $onboarding->alternate_mobile) }}">
                                </div>

                                <div class="col-12 col-md-6">
                                    <label class="form-label">Personal Email Address <span class="text-danger">*</span></label>
                                    <input type="email" name="personal_email" class="form-control bg-light text-dark" value="{{ $onboarding->intern->email }}" readonly>
                                    <span class="fs-8 text-muted">Pre-filled email. Contact HR if this is incorrect.</span>
                                </div>

                                <div class="col-12 col-md-9">
                                    <label class="form-label">Current Address <span class="text-danger">*</span></label>
                                    <textarea name="current_address" class="form-control" rows="3" placeholder="Full residential address details" required>{{ old('current_address', $onboarding->current_address) }}</textarea>
                                </div>

                                <div class="col-12 col-md-3">
                                    <label class="form-label">PIN Code <span class="text-danger">*</span></label>
                                    <input type="text" name="pin_code" class="form-control" value="{{ old('pin_code', $onboarding->pin_code) }}" placeholder="6-digit Postal PIN" required>
                                </div>
                            </div>
                        </div>

                        <!-- ================= STEP 2: EDUCATIONAL INFO ================= -->
                        <div class="form-section" data-step="2">
                            <h5 class="fw-bold border-bottom pb-2 border-light-subtle mb-4 text-success"><i class="bi bi-mortarboard-fill me-2"></i> Section 2: Educational Information</h5>
                            
                            <div class="row g-4">
                                <div class="col-12">
                                    <label class="form-label">College / Institution Name <span class="text-danger">*</span></label>
                                    <input type="text" name="college_name" class="form-control" value="{{ old('college_name', $onboarding->college_name) }}" required>
                                </div>
                                <div class="col-12 col-md-6">
                                    <label class="form-label">University / Board <span class="text-danger">*</span></label>
                                    <input type="text" name="university_board" class="form-control" value="{{ old('university_board', $onboarding->university_board) }}" placeholder="e.g. VTU, Anna Univ, etc." required>
                                </div>
                                <div class="col-12 col-md-6">
                                    <label class="form-label">Course <span class="text-danger">*</span></label>
                                    <input type="text" name="course" class="form-control" value="{{ old('course', $onboarding->course) }}" placeholder="e.g. B.E, B.Tech, MCA, BCA" required>
                                </div>
                                <div class="col-12 col-md-6">
                                    <label class="form-label">Branch / Specialization <span class="text-danger">*</span></label>
                                    <input type="text" name="branch_specialization" class="form-control" value="{{ old('branch_specialization', $onboarding->branch_specialization) }}" placeholder="e.g. Computer Science, IT, Electronics" required>
                                </div>
                                <div class="col-12 col-md-6">
                                    <label class="form-label">Current Semester / Year</label>
                                    <input type="text" name="current_semester_year" class="form-control" value="{{ old('current_semester_year', $onboarding->current_semester_year) }}" placeholder="e.g. 6th Semester / 3rd Year">
                                </div>
                                <div class="col-12 col-md-6">
                                    <label class="form-label">College Roll Number / Registration Number</label>
                                    <input type="text" name="college_roll_number" class="form-control" value="{{ old('college_roll_number', $onboarding->college_roll_number) }}">
                                </div>
                                <div class="col-12 col-md-6">
                                    <label class="form-label">Expected Year of Completion</label>
                                    <input type="text" name="expected_completion_year" class="form-control" value="{{ old('expected_completion_year', $onboarding->expected_completion_year) }}" placeholder="e.g. 2027">
                                </div>
                            </div>
                        </div>

                        <!-- ================= STEP 3: FAMILY & EMERGENCY DETAILS ================= -->
                        <div class="form-section" data-step="3">
                            <h5 class="fw-bold border-bottom pb-2 border-light-subtle mb-4 text-success"><i class="bi bi-people-fill me-2"></i> Section 3 & 4: Guardian & Emergency Contact</h5>
                            
                            <h6 class="text-muted border-bottom pb-2 mb-3 border-light-subtle">Section 3 – Parent / Guardian Details</h6>
                            <div class="row g-4 mb-4">
                                <div class="col-12 col-md-6">
                                    <label class="form-label">Parent / Guardian Name <span class="text-danger">*</span></label>
                                    <input type="text" name="parent_name" class="form-control" value="{{ old('parent_name', $onboarding->parent_name) }}" required>
                                </div>
                                <div class="col-12 col-md-3">
                                    <label class="form-label">Relationship <span class="text-danger">*</span></label>
                                    <input type="text" name="parent_relationship" class="form-control" value="{{ old('parent_relationship', $onboarding->parent_relationship) }}" placeholder="Father, Mother, Uncle, etc." required>
                                </div>
                                <div class="col-12 col-md-3">
                                    <label class="form-label">Mobile Number <span class="text-danger">*</span></label>
                                    <input type="text" name="parent_phone" class="form-control" value="{{ old('parent_phone', $onboarding->parent_phone) }}" required>
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Address (If Different from Current Address)</label>
                                    <input type="text" name="parent_address" class="form-control" value="{{ old('parent_address', $onboarding->parent_address) }}" placeholder="Leave blank if same as current address">
                                </div>
                            </div>

                            <h6 class="text-muted border-bottom pb-2 mb-3 border-light-subtle">Section 4 – Emergency Contact Details</h6>
                            <div class="row g-4">
                                <div class="col-12 col-md-6">
                                    <label class="form-label">Emergency Contact Person <span class="text-danger">*</span></label>
                                    <input type="text" name="emergency_contact_person" class="form-control" value="{{ old('emergency_contact_person', $onboarding->emergency_contact_person) }}" required>
                                </div>
                                <div class="col-12 col-md-3">
                                    <label class="form-label">Relationship <span class="text-danger">*</span></label>
                                    <input type="text" name="emergency_relationship" class="form-control" value="{{ old('emergency_relationship', $onboarding->emergency_relationship) }}" required>
                                </div>
                                <div class="col-12 col-md-3">
                                    <label class="form-label">Mobile Number <span class="text-danger">*</span></label>
                                    <input type="text" name="emergency_phone" class="form-control" value="{{ old('emergency_phone', $onboarding->emergency_phone) }}" required>
                                </div>
                                <div class="col-12 col-md-6">
                                    <label class="form-label">Alternate Number</label>
                                    <input type="text" name="emergency_alternate_phone" class="form-control" value="{{ old('emergency_alternate_phone', $onboarding->emergency_alternate_phone) }}">
                                </div>
                            </div>
                        </div>

                        <!-- ================= STEP 4: INTERNSHIP & TECHNICAL SKILLS ================= -->
                        <div class="form-section" data-step="4">
                            <h5 class="fw-bold border-bottom pb-2 border-light-subtle mb-4 text-success"><i class="bi bi-briefcase-fill me-2"></i> Section 5 & 6 & 10: Internship & Technical Details</h5>
                            
                            <h6 class="text-muted border-bottom pb-2 mb-3 border-light-subtle">Section 5 – Internship Configuration</h6>
                            <div class="row g-4 mb-4">
                                <div class="col-12 col-md-6">
                                    <label class="form-label d-block">Internship Type <span class="text-danger">*</span></label>
                                    <select name="internship_type" id="internship_type" class="form-select" onchange="toggleOtherField('internship_type', 'internship_type_other_div')" required>
                                        <option value="">Select Internship Type</option>
                                        @foreach(['Academic Internship', 'Industrial Internship', 'Summer Internship', 'Project Internship', 'Skill Development Internship', 'Other'] as $type)
                                            <option value="{{ $type }}" {{ old('internship_type', $onboarding->internship_type) === $type ? 'selected' : '' }}>{{ $type }}</option>
                                        @endforeach
                                    </select>
                                    <div class="mt-2 {{ old('internship_type', $onboarding->internship_type) === 'Other' ? '' : 'd-none' }}" id="internship_type_other_div">
                                        <input type="text" name="internship_type_other" class="form-control" value="{{ old('internship_type_other', $onboarding->internship_type_other) }}" placeholder="Please specify internship type">
                                    </div>
                                </div>
                                
                                <div class="col-12 col-md-6">
                                    <label class="form-label">Internship Mode <span class="text-danger">*</span></label>
                                    <div class="d-flex gap-2">
                                        <div class="flex-grow-1">
                                            <input type="radio" class="btn-check radio-card-input" name="internship_mode" id="mode_onsite" value="On-site" {{ old('internship_mode', $onboarding->internship_mode) === 'On-site' ? 'checked' : '' }} required>
                                            <label class="radio-card d-flex align-items-center justify-content-center" for="mode_onsite">On-site</label>
                                        </div>
                                        <div class="flex-grow-1">
                                            <input type="radio" class="btn-check radio-card-input" name="internship_mode" id="mode_hybrid" value="Hybrid" {{ old('internship_mode', $onboarding->internship_mode) === 'Hybrid' ? 'checked' : '' }}>
                                            <label class="radio-card d-flex align-items-center justify-content-center" for="mode_hybrid">Hybrid</label>
                                        </div>
                                        <div class="flex-grow-1">
                                            <input type="radio" class="btn-check radio-card-input" name="internship_mode" id="mode_remote" value="Remote" {{ old('internship_mode', $onboarding->internship_mode) === 'Remote' ? 'checked' : '' }}>
                                            <label class="radio-card d-flex align-items-center justify-content-center" for="mode_remote">Remote</label>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-12 col-md-6">
                                    <label class="form-label d-block">Internship Duration <span class="text-danger">*</span></label>
                                    <select name="internship_duration" id="internship_duration" class="form-select" onchange="toggleOtherField('internship_duration', 'internship_duration_other_div')" required>
                                        <option value="">Select Duration</option>
                                        @foreach(['1 Month', '2 Months', '3 Months', '6 Months', 'Other'] as $duration)
                                            <option value="{{ $duration }}" {{ old('internship_duration', $onboarding->internship_duration) === $duration ? 'selected' : '' }}>{{ $duration }}</option>
                                        @endforeach
                                    </select>
                                    <div class="mt-2 {{ old('internship_duration', $onboarding->internship_duration) === 'Other' ? '' : 'd-none' }}" id="internship_duration_other_div">
                                        <input type="text" name="internship_duration_other" class="form-control" value="{{ old('internship_duration_other', $onboarding->internship_duration_other) }}" placeholder="Please specify duration">
                                    </div>
                                </div>

                                <div class="col-12">
                                    @php
                                        $sector = $onboarding->sector ?? 'Techsoul Technologies';

                                        if ($sector === 'Techsoul IT Solutions') {
                                            $interests = ['Hardware Support', 'Networking & Routing', 'Server Management', 'System Administration', 'Cloud Infrastructure', 'Cyber Security', 'IT Support Desk'];
                                            $languages = ['TCP/IP Protocols', 'CCNA Concepts', 'Linux Administration', 'Windows Server', 'Active Directory', 'Bash / PowerShell', 'Firewall & Security'];
                                            $tools = ['Cisco Packet Tracer', 'Wireshark', 'Putty', 'VirtualBox / VMware', 'Canva', 'Visio'];
                                            $interestLabel = 'Area of Interest';
                                            $skillsLabel = 'IT Skills / Protocols Known';
                                            $toolsLabel = 'IT Administration & Creative Tools';
                                        } elseif ($sector === 'Techsoul Solar') {
                                            $interests = ['Solar Site Assessment', 'Solar Design & PVsyst', 'Electrical Layouts', 'Renewable Energy Analysis', 'Technical Sales & Proposals', 'Project Estimations'];
                                            $languages = ['AutoCAD Design', 'PVsyst / Solar Design', 'MATLAB', 'Renewable Energy Tech', 'Electrical Drawings', 'Project Planning'];
                                            $tools = ['AutoCAD', 'PVsyst', 'SketchUp', 'MS Excel', 'Sketching / Drafting', 'Canva'];
                                            $interestLabel = 'Area of Interest';
                                            $skillsLabel = 'Engineering Skills / Software Known';
                                            $toolsLabel = 'Design & Modeling Tools';
                                        } else { // Techsoul Technologies
                                            $interests = ['Software Development', 'Web Development', 'Mobile App Development', 'Artificial Intelligence', 'UI/UX Design', 'Graphic Design', 'Cyber Security', 'Data Analytics', 'ERP Development'];
                                            $languages = ['PHP', 'Python', 'Java', 'JavaScript', 'C / C++', 'Flutter', 'React', 'Laravel', 'HTML/CSS', 'MySQL'];
                                            $tools = ['Canva', 'Photoshop', 'Illustrator', 'Figma', 'Premiere Pro', 'After Effects'];
                                            $interestLabel = 'Area of Interest';
                                            $skillsLabel = 'Programming Languages Known';
                                            $toolsLabel = 'Design & Creative Tools';
                                        }
                                        
                                        $selectedInterests = old('areas_of_interest', $onboarding->areas_of_interest ?? []);
                                        $selectedLangs = old('programming_languages', $onboarding->programming_languages ?? []);
                                        $selectedTools = old('design_tools', $onboarding->design_tools ?? []);
                                    @endphp

                                    <label class="form-label d-block mb-2">{{ $interestLabel }} <span class="text-danger">*</span></label>
                                    <div class="row g-2">
                                        @foreach($interests as $interest)
                                            <div class="col-6 col-md-4">
                                                <input type="checkbox" class="btn-check" name="areas_of_interest[]" id="interest_{{ Str::slug($interest) }}" value="{{ $interest }}" {{ in_array($interest, $selectedInterests) ? 'checked' : '' }}>
                                                <label class="btn btn-outline-secondary text-start border-light-subtle px-3 py-2 fs-8 w-100 h-100" for="interest_{{ Str::slug($interest) }}">
                                                    <i class="bi bi-circle me-1.5 active-check-icon text-muted"></i>{{ $interest }}
                                                </label>
                                            </div>
                                        @endforeach
                                        <div class="col-12 mt-2">
                                            <input type="text" name="areas_of_interest_other" class="form-control" value="{{ old('areas_of_interest_other', $onboarding->areas_of_interest_other) }}" placeholder="Other Interest (If any)">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <h6 class="text-muted border-bottom pb-2 mb-3 border-light-subtle">Section 6 – Technical Skills & Knowledge</h6>
                            <div class="row g-4 mb-4">
                                <div class="col-12 col-md-6">
                                    <label class="form-label d-block mb-2">{{ $skillsLabel }}</label>
                                    <div class="row g-2">
                                        @foreach($languages as $lang)
                                            <div class="col-6 col-md-4">
                                                <input type="checkbox" class="btn-check" name="programming_languages[]" id="lang_{{ Str::slug($lang) }}" value="{{ $lang }}" {{ in_array($lang, $selectedLangs) ? 'checked' : '' }}>
                                                <label class="btn btn-outline-secondary text-start border-light-subtle px-2.5 py-2 fs-8 w-100 h-100" for="lang_{{ Str::slug($lang) }}">
                                                    <i class="bi bi-circle me-1.5 active-check-icon text-muted"></i>{{ $lang }}
                                                </label>
                                            </div>
                                        @endforeach
                                        <div class="col-12 mt-2">
                                            <input type="text" name="programming_languages_other" class="form-control" value="{{ old('programming_languages_other', $onboarding->programming_languages_other) }}" placeholder="Other skills / languages">
                                        </div>
                                    </div>
                                </div>

                                <div class="col-12 col-md-6">
                                    <label class="form-label d-block mb-2">{{ $toolsLabel }}</label>
                                    <div class="row g-2">
                                        @foreach($tools as $tool)
                                            <div class="col-6 col-md-4">
                                                <input type="checkbox" class="btn-check" name="design_tools[]" id="tool_{{ Str::slug($tool) }}" value="{{ $tool }}" {{ in_array($tool, $selectedTools) ? 'checked' : '' }}>
                                                <label class="btn btn-outline-secondary text-start border-light-subtle px-2.5 py-2 fs-8 w-100 h-100" for="tool_{{ Str::slug($tool) }}">
                                                    <i class="bi bi-circle me-1.5 active-check-icon text-muted"></i>{{ $tool }}
                                                </label>
                                            </div>
                                        @endforeach
                                        <div class="col-12 mt-2">
                                            <input type="text" name="design_tools_other" class="form-control" value="{{ old('design_tools_other', $onboarding->design_tools_other) }}" placeholder="Other creative tools">
                                        </div>
                                    </div>
                                </div>

                                <div class="col-12">
                                    <label class="form-label">Describe Any Projects Completed</label>
                                    <textarea name="completed_projects" class="form-control" rows="3" placeholder="Briefly explain any college or personal projects you have developed">{{ old('completed_projects', $onboarding->completed_projects) }}</textarea>
                                </div>
                            </div>

                            <h6 class="text-muted border-bottom pb-2 mb-3 border-light-subtle">Section 10 – Learning Objectives</h6>
                            <div class="row g-4">
                                <div class="col-12 col-md-6">
                                    <label class="form-label">What do you expect to learn from this internship? <span class="text-danger">*</span></label>
                                    <textarea name="learning_expectations" class="form-control" rows="3" required>{{ old('learning_expectations', $onboarding->learning_expectations) }}</textarea>
                                </div>
                                <div class="col-12 col-md-6">
                                    <label class="form-label">Career Goal <span class="text-danger">*</span></label>
                                    <textarea name="career_goal" class="form-control" rows="3" required>{{ old('career_goal', $onboarding->career_goal) }}</textarea>
                                </div>
                            </div>
                        </div>

                        <!-- ================= STEP 5: DOCUMENT CHECKLIST & DECLARATION ================= -->
                        <div class="form-section" data-step="5">
                            <h5 class="fw-bold border-bottom pb-2 border-light-subtle mb-4 text-success"><i class="bi bi-file-earmark-check-fill me-2"></i> Section 7 & 11: Uploads & Declaration</h5>
                            
                            <h6 class="text-muted border-bottom pb-2 mb-3 border-light-subtle">Section 7 – Document Checklist (Max file size: 5MB per document)</h6>
                            <div class="row g-4 mb-4">
                                <div class="col-12 col-md-4">
                                    <label class="form-label">Passport Size Photo <span class="text-danger">*</span></label>
                                    <input type="file" name="photo" id="photoInput" class="form-control form-control-sm" accept="image/*" required>
                                    <div class="form-text fs-8 text-muted" style="font-size: 11px;">Supported format: Image. Max: 2MB</div>
                                </div>

                                <div class="col-12 col-md-4">
                                    <label class="form-label">Aadhaar Card copy <span class="text-danger">*</span></label>
                                    <input type="file" name="doc_aadhaar" class="form-control form-control-sm" accept=".pdf,.jpg,.jpeg,.png" required>
                                    <div class="form-text fs-8 text-muted" style="font-size: 11px;">PDF or Image</div>
                                </div>

                                <div class="col-12 col-md-4">
                                    <label class="form-label">Resume / CV <span class="text-danger">*</span></label>
                                    <input type="file" name="doc_resume" class="form-control form-control-sm" accept=".pdf,.doc,.docx" required>
                                    <div class="form-text fs-8 text-muted" style="font-size: 11px;">PDF or Word</div>
                                </div>

                                <div class="col-12 col-md-4">
                                    <label class="form-label">College ID Card</label>
                                    <input type="file" name="doc_college_id" class="form-control form-control-sm" accept=".pdf,.jpg,.jpeg,.png">
                                    <div class="form-text fs-8 text-muted" style="font-size: 11px;">PDF or Image (Optional)</div>
                                </div>

                                <div class="col-12 col-md-4">
                                    <label class="form-label">Bonafide Certificate</label>
                                    <input type="file" name="doc_bonafide" class="form-control form-control-sm" accept=".pdf,.jpg,.jpeg,.png">
                                    <div class="form-text fs-8 text-muted" style="font-size: 11px;">Optional</div>
                                </div>

                                <div class="col-12 col-md-4">
                                    <label class="form-label">Internship Request Letter</label>
                                    <input type="file" name="doc_request_letter" class="form-control form-control-sm" accept=".pdf,.jpg,.jpeg,.png">
                                    <div class="form-text fs-8 text-muted" style="font-size: 11px;">Optional</div>
                                </div>

                                <div class="col-12 col-md-6">
                                    <label class="form-label">Academic Project Details</label>
                                    <input type="file" name="doc_project_details" class="form-control form-control-sm" accept=".pdf,.doc,.docx,.txt">
                                    <div class="form-text fs-8 text-muted" style="font-size: 11px;">Optional</div>
                                </div>

                                <div class="col-12 col-md-6">
                                    <label class="form-label">Previous Internship Certificates</label>
                                    <input type="file" name="doc_prev_certificate" class="form-control form-control-sm" accept=".pdf,.jpg,.jpeg,.png">
                                    <div class="form-text fs-8 text-muted" style="font-size: 11px;">Optional</div>
                                </div>
                            </div>

                            <h6 class="text-muted border-bottom pb-2 mb-3 border-light-subtle">Section 11 – Intern Declaration</h6>
                            <div class="row g-3">
                                <div class="col-12">
                                    <div class="p-3 bg-light rounded border border-secondary-subtle" style="font-size: 12.5px; line-height: 1.6; color: #334155; max-height: 160px; overflow-y: auto;">
                                        <p class="mb-2">I hereby confirm that the information provided by me is true and accurate.</p>
                                        <p class="mb-2">I understand that:</p>
                                        <ul class="ps-3 mb-0">
                                            <li class="mb-1">This internship is primarily a learning and skill-development program.</li>
                                            <li class="mb-1">I shall comply with all Techsoul Cyber Solutions policies and procedures.</li>
                                            <li class="mb-1">I shall maintain confidentiality regarding company projects, source code, client information, and internal operations.</li>
                                            <li class="mb-1">All work, software, designs, content, documentation, and projects developed during the internship shall remain the property of Techsoul Cyber Solutions.</li>
                                            <li class="mb-0">Completion Certificate and Internship Certificate shall be issued subject to satisfactory performance, attendance, discipline, and successful completion of assigned tasks.</li>
                                        </ul>
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
        const totalSteps = 5;

        function showStep(step) {
            // Hide all sections
            document.querySelectorAll('.form-section').forEach(sec => {
                sec.classList.remove('active');
            });

            // Show active section
            document.querySelector(`.form-section[data-step="${step}"]`).classList.add('active');

            // Update dots
            document.querySelectorAll('.step-dot').forEach(dot => {
                const s = parseInt(dot.getAttribute('data-step'));
                dot.classList.remove('active', 'completed');
                if (s === step) {
                    dot.classList.add('active');
                } else if (s < step) {
                    dot.classList.add('completed');
                }
            });

            // Update Progress Bar
            const progress = ((step - 1) / (totalSteps - 1)) * 100;
            document.getElementById('progressBar').style.width = `${progress}%`;

            // Update Buttons
            const prevBtn = document.getElementById('prevBtn');
            const nextBtn = document.getElementById('nextBtn');

            if (step === 1) {
                prevBtn.disabled = true;
            } else {
                prevBtn.disabled = false;
            }

            if (step === totalSteps) {
                nextBtn.innerHTML = 'Submit Onboarding Form <i class="bi bi-send-fill ms-1"></i>';
                nextBtn.classList.remove('btn-success');
                nextBtn.classList.add('btn-primary');
            } else {
                nextBtn.innerHTML = 'Next Step <i class="bi bi-arrow-right-short ms-0.5"></i>';
                nextBtn.classList.remove('btn-primary');
                nextBtn.classList.add('btn-success');
            }
            
            // Scroll to card top smoothly
            window.scrollTo({ top: 120, behavior: 'smooth' });
        }

        function validateStepFields(step) {
            const section = document.querySelector(`.form-section[data-step="${step}"]`);
            const inputs = section.querySelectorAll('input[required], select[required], textarea[required]');
            let isValid = true;

            inputs.forEach(input => {
                if (input.type === 'radio') {
                    // Check if group is checked
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
                    // Handled if required
                } else {
                    if (!input.value.trim()) {
                        isValid = false;
                        input.classList.add('is-invalid');
                    } else {
                        input.classList.remove('is-invalid');
                    }
                }
            });

            // Specific check for Section 5: Area of interest checkboxes (at least one is required)
            if (step === 4) {
                const interestChecked = section.querySelectorAll('input[name="areas_of_interest[]"]:checked');
                if (interestChecked.length === 0) {
                    isValid = false;
                    alert('Please select at least one Area of Interest.');
                }
            }

            return isValid;
        }

        function navigateStep(direction) {
            if (direction === 1) {
                if (!validateStepFields(currentStep)) {
                    // Trigger native HTML5 validation UI or custom alerts
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
                    // Submit form
                    document.getElementById('onboardingForm').submit();
                }
            } else {
                if (currentStep > 1) {
                    currentStep--;
                    showStep(currentStep);
                }
            }
        }

        function toggleOtherField(selectId, targetId) {
            const select = document.getElementById(selectId);
            const div = document.getElementById(targetId);
            if (select.value === 'Other') {
                div.classList.remove('d-none');
                div.querySelector('input').setAttribute('required', 'required');
            } else {
                div.classList.add('d-none');
                div.querySelector('input').removeAttribute('required');
            }
        }

        function syncSignature(val) {
            const signatureDisplay = document.getElementById('signatureDisplay');
            if (val.trim()) {
                signatureDisplay.textContent = val;
            } else {
                signatureDisplay.textContent = 'Cursive Signature';
            }
        }

        // Add icons dynamic checks
        document.querySelectorAll('input[type="checkbox"]').forEach(checkbox => {
            checkbox.addEventListener('change', function() {
                const label = document.querySelector(`label[for="${this.id}"]`);
                const icon = label.querySelector('.active-check-icon');
                if (icon) {
                    if (this.checked) {
                        icon.classList.remove('bi-circle');
                        icon.classList.add('bi-check-circle-fill', 'text-success');
                        label.classList.add('border-success', 'bg-success-subtle', 'text-success');
                    } else {
                        icon.classList.remove('bi-check-circle-fill', 'text-success');
                        icon.classList.add('bi-circle');
                        label.classList.remove('border-success', 'bg-success-subtle', 'text-success');
                    }
                }
            });
        });
    </script>
</body>
</html>
