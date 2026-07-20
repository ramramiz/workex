@extends('layouts.app')

@section('title', 'Edit Onboarding Record')
@section('page-title', 'Edit Onboarding Record')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('interns.onboardings.index') }}">Onboarding Links</a></li>
    <li class="breadcrumb-item active">Edit Onboarding</li>
@endsection

@section('content')
<div class="row">
    <div class="col-12 mx-auto" style="max-width: 1100px;">
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between">
                <h5 class="mb-0 fw-bold text-dark"><i class="bi bi-pencil-square me-2 text-primary"></i>Edit Onboarding Record</h5>
                <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle px-2.5 py-1 rounded-pill">Status: {{ ucfirst($onboarding->status) }}</span>
            </div>
            
            <div class="card-body p-4">
                @if ($errors->any())
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <ul class="mb-0">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                <form method="POST" action="{{ route('interns.onboardings.update', $onboarding->id) }}" id="editOnboardingForm">
                    @csrf
                    @method('PUT')

                    <!-- Custom Tabbed Navigation -->
                    <ul class="nav nav-pills nav-fill bg-light p-1 rounded mb-4" id="editOnboardingTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active py-2.5 fw-medium" id="tab-placement" data-bs-toggle="tab" data-bs-target="#pane-placement" type="button" role="tab">1. Placement Info</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link py-2.5 fw-medium" id="tab-personal" data-bs-toggle="tab" data-bs-target="#pane-personal" type="button" role="tab">2. Personal Details</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link py-2.5 fw-medium" id="tab-education" data-bs-toggle="tab" data-bs-target="#pane-education" type="button" role="tab">3. Education</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link py-2.5 fw-medium" id="tab-guardian" data-bs-toggle="tab" data-bs-target="#pane-guardian" type="button" role="tab">4. Guardian & Emergency</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link py-2.5 fw-medium" id="tab-skills" data-bs-toggle="tab" data-bs-target="#pane-skills" type="button" role="tab">5. Internship & Skills</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link py-2.5 fw-medium" id="tab-office" data-bs-toggle="tab" data-bs-target="#pane-office" type="button" role="tab">6. Access & Office Use</button>
                        </li>
                    </ul>

                    <div class="tab-content" id="editOnboardingTabsContent">
                        
                        <!-- TAB 1: PLACEMENT INFO -->
                        <div class="tab-pane fade show active" id="pane-placement" role="tabpanel">
                            <h6 class="text-uppercase text-primary fs-7 mb-3 fw-bold border-bottom pb-2">Internship Placement Settings</h6>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label fw-medium">Full Name <span class="text-danger">*</span></label>
                                    <input type="text" name="name" class="form-control" value="{{ old('name', $onboarding->intern->name) }}" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-medium">Email Address <span class="text-danger">*</span></label>
                                    <input type="email" name="email" class="form-control" value="{{ old('email', $onboarding->intern->email) }}" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-medium">Department <span class="text-danger">*</span></label>
                                    <select name="department_id" class="form-select" required>
                                        <option value="">Select Department</option>
                                        @foreach($departments as $dept)
                                            <option value="{{ $dept->id }}" {{ old('department_id', $onboarding->intern->department_id) == $dept->id ? 'selected' : '' }}>{{ $dept->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-medium">Company Sector <span class="text-danger">*</span></label>
                                    <select name="sector" id="sector" class="form-select" onchange="updateSectorFields()" required>
                                        <option value="Techsoul Technologies" {{ old('sector', $onboarding->sector) === 'Techsoul Technologies' ? 'selected' : '' }}>Techsoul Technologies (Development Related)</option>
                                        <option value="Techsoul IT Solutions" {{ old('sector', $onboarding->sector) === 'Techsoul IT Solutions' ? 'selected' : '' }}>Techsoul IT Solutions (IT Hardware and Networking)</option>
                                        <option value="Techsoul Solar" {{ old('sector', $onboarding->sector) === 'Techsoul Solar' ? 'selected' : '' }}>Techsoul Solar (Solar and energy related)</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-medium">Mentor / Supervisor Assigned</label>
                                    <input type="text" name="mentor_supervisor" class="form-control" value="{{ old('mentor_supervisor', $onboarding->mentor_supervisor) }}" placeholder="Mentor's Name">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label fw-medium">Start Date <span class="text-danger">*</span></label>
                                    <input type="date" name="joining_date" class="form-control" value="{{ old('joining_date', $onboarding->intern->joining_date ? $onboarding->intern->joining_date->format('Y-m-d') : '') }}" required>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label fw-medium">End Date <span class="text-danger">*</span></label>
                                    <input type="date" name="end_date" class="form-control" value="{{ old('end_date', $onboarding->intern->end_date ? $onboarding->intern->end_date->format('Y-m-d') : '') }}" required>
                                </div>
                            </div>
                        </div>

                        <!-- TAB 2: PERSONAL DETAILS -->
                        <div class="tab-pane fade" id="pane-personal" role="tabpanel">
                            <h6 class="text-uppercase text-primary fs-7 mb-3 fw-bold border-bottom pb-2">Section 1: Personal Details</h6>
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label class="form-label fw-medium">Gender</label>
                                    <select name="gender" class="form-select">
                                        <option value="">Select Gender</option>
                                        <option value="Male" {{ old('gender', $onboarding->gender) === 'Male' ? 'selected' : '' }}>Male</option>
                                        <option value="Female" {{ old('gender', $onboarding->gender) === 'Female' ? 'selected' : '' }}>Female</option>
                                        <option value="Other" {{ old('gender', $onboarding->gender) === 'Other' ? 'selected' : '' }}>Other</option>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-medium">Date of Birth</label>
                                    <input type="date" name="dob" class="form-control" value="{{ old('dob', $onboarding->dob ? $onboarding->dob->format('Y-m-d') : '') }}">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-medium">Blood Group</label>
                                    <select name="blood_group" class="form-select">
                                        <option value="">Select Blood Group</option>
                                        @foreach(['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'] as $bg)
                                            <option value="{{ $bg }}" {{ old('blood_group', $onboarding->blood_group) === $bg ? 'selected' : '' }}>{{ $bg }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-medium">Aadhaar Number</label>
                                    <input type="text" name="aadhaar_number" class="form-control" value="{{ old('aadhaar_number', $onboarding->aadhaar_number) }}" placeholder="12 Digit Aadhaar Card No.">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-medium">Alternate Mobile Number</label>
                                    <input type="text" name="alternate_mobile" class="form-control" value="{{ old('alternate_mobile', $onboarding->alternate_mobile) }}">
                                </div>
                                <div class="col-md-9">
                                    <label class="form-label fw-medium">Current Address</label>
                                    <input type="text" name="current_address" class="form-control" value="{{ old('current_address', $onboarding->current_address) }}">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label fw-medium">PIN Code</label>
                                    <input type="text" name="pin_code" class="form-control" value="{{ old('pin_code', $onboarding->pin_code) }}">
                                </div>
                            </div>
                        </div>

                        <!-- TAB 3: EDUCATION -->
                        <div class="tab-pane fade" id="pane-education" role="tabpanel">
                            <h6 class="text-uppercase text-primary fs-7 mb-3 fw-bold border-bottom pb-2">Section 2: Educational Details</h6>
                            <div class="row g-3">
                                <div class="col-12">
                                    <label class="form-label fw-medium">College / Institution Name</label>
                                    <input type="text" name="college_name" class="form-control" value="{{ old('college_name', $onboarding->college_name) }}">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-medium">University / Board</label>
                                    <input type="text" name="university_board" class="form-control" value="{{ old('university_board', $onboarding->university_board) }}">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-medium">Course / Program</label>
                                    <input type="text" name="course" class="form-control" value="{{ old('course', $onboarding->course) }}">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-medium">Branch / Specialization</label>
                                    <input type="text" name="branch_specialization" class="form-control" value="{{ old('branch_specialization', $onboarding->branch_specialization) }}">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-medium">Current Semester / Year</label>
                                    <input type="text" name="current_semester_year" class="form-control" value="{{ old('current_semester_year', $onboarding->current_semester_year) }}" placeholder="e.g. 6th Semester / 3rd Year">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-medium">College Roll Number / Registration Number</label>
                                    <input type="text" name="college_roll_number" class="form-control" value="{{ old('college_roll_number', $onboarding->college_roll_number) }}">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-medium">Expected Year of Completion</label>
                                    <input type="text" name="expected_completion_year" class="form-control" value="{{ old('expected_completion_year', $onboarding->expected_completion_year) }}" placeholder="e.g. 2027">
                                </div>
                            </div>
                        </div>

                        <!-- TAB 4: GUARDIAN & EMERGENCY -->
                        <div class="tab-pane fade" id="pane-guardian" role="tabpanel">
                            <div class="row">
                                <div class="col-md-6 pe-md-4 border-end">
                                    <h6 class="text-uppercase text-primary fs-7 mb-3 fw-bold border-bottom pb-2">Section 3: Parent / Guardian Details</h6>
                                    <div class="row g-3">
                                        <div class="col-12">
                                            <label class="form-label fw-medium">Parent / Guardian Name</label>
                                            <input type="text" name="parent_name" class="form-control" value="{{ old('parent_name', $onboarding->parent_name) }}">
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label fw-medium">Relationship</label>
                                            <input type="text" name="parent_relationship" class="form-control" value="{{ old('parent_relationship', $onboarding->parent_relationship) }}">
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label fw-medium">Mobile Number</label>
                                            <input type="text" name="parent_phone" class="form-control" value="{{ old('parent_phone', $onboarding->parent_phone) }}">
                                        </div>
                                        <div class="col-12">
                                            <label class="form-label fw-medium">Address (If Different)</label>
                                            <input type="text" name="parent_address" class="form-control" value="{{ old('parent_address', $onboarding->parent_address) }}">
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-6 ps-md-4">
                                    <h6 class="text-uppercase text-primary fs-7 mb-3 fw-bold border-bottom pb-2">Section 4: Emergency Contact Details</h6>
                                    <div class="row g-3">
                                        <div class="col-12">
                                            <label class="form-label fw-medium">Emergency Contact Person</label>
                                            <input type="text" name="emergency_contact_person" class="form-control" value="{{ old('emergency_contact_person', $onboarding->emergency_contact_person) }}">
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label fw-medium">Relationship</label>
                                            <input type="text" name="emergency_relationship" class="form-control" value="{{ old('emergency_relationship', $onboarding->emergency_relationship) }}">
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label fw-medium">Mobile Number</label>
                                            <input type="text" name="emergency_phone" class="form-control" value="{{ old('emergency_phone', $onboarding->emergency_phone) }}">
                                        </div>
                                        <div class="col-12">
                                            <label class="form-label fw-medium">Alternate Number</label>
                                            <input type="text" name="emergency_alternate_phone" class="form-control" value="{{ old('emergency_alternate_phone', $onboarding->emergency_alternate_phone) }}">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- TAB 5: INTERNSHIP & SKILLS -->
                        <div class="tab-pane fade" id="pane-skills" role="tabpanel">
                            <h6 class="text-uppercase text-primary fs-7 mb-3 fw-bold border-bottom pb-2">Section 5 & 6 & 10: Internship & Technical Details</h6>
                            <div class="row g-3 mb-4">
                                <div class="col-md-4">
                                    <label class="form-label fw-medium">Internship Type</label>
                                    <input type="text" name="internship_type" class="form-control" value="{{ old('internship_type', $onboarding->internship_type) }}">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-medium">Internship Mode</label>
                                    <input type="text" name="internship_mode" class="form-control" value="{{ old('internship_mode', $onboarding->internship_mode) }}">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-medium">Internship Duration</label>
                                    <input type="text" name="internship_duration" class="form-control" value="{{ old('internship_duration', $onboarding->internship_duration) }}">
                                </div>
                            </div>

                            @php
                                $sector = $onboarding->sector ?? 'Techsoul Technologies';

                                $techInterests = ['Software Development', 'Web Development', 'Mobile App Development', 'Artificial Intelligence', 'UI/UX Design', 'Graphic Design', 'Cyber Security', 'Data Analytics', 'ERP Development'];
                                $techLangs = ['PHP', 'Python', 'Java', 'JavaScript', 'C / C++', 'Flutter', 'React', 'Laravel', 'HTML/CSS', 'MySQL'];
                                $techTools = ['Canva', 'Photoshop', 'Illustrator', 'Figma', 'Premiere Pro', 'After Effects'];

                                $itInterests = ['Hardware Support', 'Networking & Routing', 'Server Management', 'System Administration', 'Cloud Infrastructure', 'Cyber Security', 'IT Support Desk'];
                                $itLangs = ['TCP/IP Protocols', 'CCNA Concepts', 'Linux Administration', 'Windows Server', 'Active Directory', 'Bash / PowerShell', 'Firewall & Security'];
                                $itTools = ['Cisco Packet Tracer', 'Wireshark', 'Putty', 'VirtualBox / VMware', 'Canva', 'Visio'];

                                $solarInterests = ['Solar Site Assessment', 'Solar Design & PVsyst', 'Electrical Layouts', 'Renewable Energy Analysis', 'Technical Sales & Proposals', 'Project Estimations'];
                                $solarLangs = ['AutoCAD Design', 'PVsyst / Solar Design', 'MATLAB', 'Renewable Energy Tech', 'Electrical Drawings', 'Project Planning'];
                                $solarTools = ['AutoCAD', 'PVsyst', 'SketchUp', 'MS Excel', 'Sketching / Drafting', 'Canva'];

                                $selectedInterests = old('areas_of_interest', $onboarding->areas_of_interest ?? []);
                                $selectedLangs = old('programming_languages', $onboarding->programming_languages ?? []);
                                $selectedTools = old('design_tools', $onboarding->design_tools ?? []);
                            @endphp

                            <!-- DYNAMIC CHECKS BY SECTOR -->
                            <div class="bg-light p-3 rounded mb-4">
                                <!-- TECHSOUL TECHNOLOGIES -->
                                <div id="tech_fields" class="sector-fields">
                                    <div class="row">
                                        <div class="col-md-4">
                                            <label class="form-label fw-bold">Area of Interest</label>
                                            <div class="overflow-y-auto" style="max-height: 200px;">
                                                @foreach($techInterests as $interest)
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="checkbox" name="areas_of_interest[]" value="{{ $interest }}" id="tech_int_{{ Str::slug($interest) }}" {{ in_array($interest, $selectedInterests) ? 'checked' : '' }}>
                                                        <label class="form-check-label text-secondary fs-8" for="tech_int_{{ Str::slug($interest) }}">{{ $interest }}</label>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label fw-bold">Programming Languages</label>
                                            <div class="overflow-y-auto" style="max-height: 200px;">
                                                @foreach($techLangs as $lang)
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="checkbox" name="programming_languages[]" value="{{ $lang }}" id="tech_lang_{{ Str::slug($lang) }}" {{ in_array($lang, $selectedLangs) ? 'checked' : '' }}>
                                                        <label class="form-check-label text-secondary fs-8" for="tech_lang_{{ Str::slug($lang) }}">{{ $lang }}</label>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label fw-bold">Design & Creative Tools</label>
                                            <div class="overflow-y-auto" style="max-height: 200px;">
                                                @foreach($techTools as $tool)
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="checkbox" name="design_tools[]" value="{{ $tool }}" id="tech_tool_{{ Str::slug($tool) }}" {{ in_array($tool, $selectedTools) ? 'checked' : '' }}>
                                                        <label class="form-check-label text-secondary fs-8" for="tech_tool_{{ Str::slug($tool) }}">{{ $tool }}</label>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- TECHSOUL IT SOLUTIONS -->
                                <div id="it_fields" class="sector-fields d-none">
                                    <div class="row">
                                        <div class="col-md-4">
                                            <label class="form-label fw-bold">Area of Interest</label>
                                            <div class="overflow-y-auto" style="max-height: 200px;">
                                                @foreach($itInterests as $interest)
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="checkbox" name="areas_of_interest[]" value="{{ $interest }}" id="it_int_{{ Str::slug($interest) }}" {{ in_array($interest, $selectedInterests) ? 'checked' : '' }}>
                                                        <label class="form-check-label text-secondary fs-8" for="it_int_{{ Str::slug($interest) }}">{{ $interest }}</label>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label fw-bold">IT Skills & Protocols</label>
                                            <div class="overflow-y-auto" style="max-height: 200px;">
                                                @foreach($itLangs as $lang)
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="checkbox" name="programming_languages[]" value="{{ $lang }}" id="it_lang_{{ Str::slug($lang) }}" {{ in_array($lang, $selectedLangs) ? 'checked' : '' }}>
                                                        <label class="form-check-label text-secondary fs-8" for="it_lang_{{ Str::slug($lang) }}">{{ $lang }}</label>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label fw-bold">IT Administration & Tools</label>
                                            <div class="overflow-y-auto" style="max-height: 200px;">
                                                @foreach($itTools as $tool)
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="checkbox" name="design_tools[]" value="{{ $tool }}" id="it_tool_{{ Str::slug($tool) }}" {{ in_array($tool, $selectedTools) ? 'checked' : '' }}>
                                                        <label class="form-check-label text-secondary fs-8" for="it_tool_{{ Str::slug($tool) }}">{{ $tool }}</label>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- TECHSOUL SOLAR -->
                                <div id="solar_fields" class="sector-fields d-none">
                                    <div class="row">
                                        <div class="col-md-4">
                                            <label class="form-label fw-bold">Area of Interest</label>
                                            <div class="overflow-y-auto" style="max-height: 200px;">
                                                @foreach($solarInterests as $interest)
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="checkbox" name="areas_of_interest[]" value="{{ $interest }}" id="solar_int_{{ Str::slug($interest) }}" {{ in_array($interest, $selectedInterests) ? 'checked' : '' }}>
                                                        <label class="form-check-label text-secondary fs-8" for="solar_int_{{ Str::slug($interest) }}">{{ $interest }}</label>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label fw-bold">Engineering Skills / Software</label>
                                            <div class="overflow-y-auto" style="max-height: 200px;">
                                                @foreach($solarLangs as $lang)
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="checkbox" name="programming_languages[]" value="{{ $lang }}" id="solar_lang_{{ Str::slug($lang) }}" {{ in_array($lang, $selectedLangs) ? 'checked' : '' }}>
                                                        <label class="form-check-label text-secondary fs-8" for="solar_lang_{{ Str::slug($lang) }}">{{ $lang }}</label>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label fw-bold">Design & Modeling Tools</label>
                                            <div class="overflow-y-auto" style="max-height: 200px;">
                                                @foreach($solarTools as $tool)
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="checkbox" name="design_tools[]" value="{{ $tool }}" id="solar_tool_{{ Str::slug($tool) }}" {{ in_array($tool, $selectedTools) ? 'checked' : '' }}>
                                                        <label class="form-check-label text-secondary fs-8" for="solar_tool_{{ Str::slug($tool) }}">{{ $tool }}</label>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="row g-3">
                                <div class="col-12">
                                    <label class="form-label fw-medium">Projects Completed</label>
                                    <textarea name="completed_projects" class="form-control" rows="3">{{ old('completed_projects', $onboarding->completed_projects) }}</textarea>
                                </div>
                            </div>
                        </div>

                        <!-- TAB 6: ACCESS & OFFICE USE -->
                        <div class="tab-pane fade" id="pane-office" role="tabpanel">
                            <h6 class="text-uppercase text-primary fs-7 mb-3 fw-bold border-bottom pb-2">Section 8 & 9: Assets, Access & Objectives</h6>
                            <div class="row g-3 mb-4">
                                <div class="col-md-6">
                                    <label class="form-label fw-medium">Granted Company Access (Check values)</label>
                                    @php
                                        $accessList = ['Office Entry Card', 'Local Network Access', 'Official Email Address', 'Figma Shared Workspace', 'GitHub Organization Access', 'Slack Workspace Invite'];
                                        $selectedAccess = old('company_access_requirements', $onboarding->company_access_requirements ?? []);
                                    @endphp
                                    <div class="row">
                                        @foreach($accessList as $access)
                                            <div class="col-6">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" name="company_access_requirements[]" value="{{ $access }}" id="access_{{ Str::slug($access) }}" {{ in_array($access, $selectedAccess) ? 'checked' : '' }}>
                                                    <label class="form-check-label text-secondary fs-8" for="access_{{ Str::slug($access) }}">{{ $access }}</label>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                    <div class="mt-2">
                                        <input type="text" name="company_access_other" class="form-control form-control-sm" value="{{ old('company_access_other', $onboarding->company_access_other) }}" placeholder="Other Access Requirements">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-medium">Assets Issued</label>
                                    @php
                                        $assetList = ['Laptop', 'Desktop Computer', 'Test Mobile Device', 'Corporate SIM Card', 'External Hard Drive / USB', 'Workspace Pedestal Key'];
                                        $selectedAssets = old('assets_issued', $onboarding->assets_issued ?? []);
                                    @endphp
                                    <div class="row">
                                        @foreach($assetList as $asset)
                                            <div class="col-6">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" name="assets_issued[]" value="{{ $asset }}" id="asset_{{ Str::slug($asset) }}" {{ in_array($asset, $selectedAssets) ? 'checked' : '' }}>
                                                    <label class="form-check-label text-secondary fs-8" for="asset_{{ Str::slug($asset) }}">{{ $asset }}</label>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                    <div class="mt-2">
                                        <input type="text" name="assets_remarks" class="form-control form-control-sm" value="{{ old('assets_remarks', $onboarding->assets_remarks) }}" placeholder="Asset remarks / serial numbers">
                                    </div>
                                </div>
                            </div>

                            <h6 class="text-uppercase text-primary fs-7 mb-3 fw-bold border-bottom pb-2">Section 10: Learning Expectations & Goals</h6>
                            <div class="row g-3 mb-4">
                                <div class="col-md-6">
                                    <label class="form-label fw-medium">What do you expect to learn from this internship?</label>
                                    <textarea name="learning_expectations" class="form-control" rows="2">{{ old('learning_expectations', $onboarding->learning_expectations) }}</textarea>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-medium">Career Goal</label>
                                    <textarea name="career_goal" class="form-control" rows="2">{{ old('career_goal', $onboarding->career_goal) }}</textarea>
                                </div>
                            </div>

                            <h6 class="text-uppercase text-primary fs-7 mb-3 fw-bold border-bottom pb-2">For Office Use Only</h6>
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label class="form-label fw-medium">Domain Assigned</label>
                                    <input type="text" name="office_use_domain" class="form-control" value="{{ old('office_use_domain', $onboarding->office_use_domain) }}" placeholder="e.g. Backend Development">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-medium">Mentor Assigned (Administrative)</label>
                                    <input type="text" name="office_use_mentor_assigned" class="form-control" value="{{ old('office_use_mentor_assigned', $onboarding->office_use_mentor_assigned) }}" placeholder="e.g. Sreejith S">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-medium">Certificate Eligibility</label>
                                    <select name="office_use_certificate_eligible" class="form-select">
                                        <option value="yes" {{ old('office_use_certificate_eligible', $onboarding->office_use_certificate_eligible) == true ? 'selected' : '' }}>Eligible (Yes)</option>
                                        <option value="no" {{ old('office_use_certificate_eligible', $onboarding->office_use_certificate_eligible) == false ? 'selected' : '' }}>Not Eligible (No)</option>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-medium">Signature Date</label>
                                    <input type="date" name="signature_date" class="form-control" value="{{ old('signature_date', $onboarding->signature_date ? $onboarding->signature_date->format('Y-m-d') : '') }}">
                                </div>
                            </div>
                        </div>

                    </div>

                    <div class="d-flex align-items-center justify-content-end gap-2 border-top pt-3 mt-4">
                        <a href="{{ route('interns.onboardings.index') }}" class="btn btn-outline-secondary py-2 px-4"><i class="bi bi-x-circle me-1.5"></i>Cancel</a>
                        <button type="submit" class="btn btn-primary py-2 px-4"><i class="bi bi-save me-1.5"></i>Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    function updateSectorFields() {
        const sector = document.getElementById('sector').value;
        
        // Hide all fields first
        document.querySelectorAll('.sector-fields').forEach(div => {
            div.classList.add('d-none');
            // Disable inputs so they don't submit if hidden (optional, but keep checked state intact)
        });
        
        // Show correct fields
        if (sector === 'Techsoul IT Solutions') {
            document.getElementById('it_fields').classList.remove('d-none');
        } else if (sector === 'Techsoul Solar') {
            document.getElementById('solar_fields').classList.remove('d-none');
        } else {
            document.getElementById('tech_fields').classList.remove('d-none');
        }
    }

    // Call updateSectorFields once on load
    document.addEventListener('DOMContentLoaded', function() {
        updateSectorFields();
    });
</script>
@endsection
