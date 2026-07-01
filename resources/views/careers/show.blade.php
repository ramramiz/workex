<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Join Us as {{ $vacancy->title }} — {{ $vacancy->company?->name ?? 'Careers' }}</title>

    @php
        $companyLogo = \App\Models\Setting::get('company_logo');
    @endphp
    @if($companyLogo)
        <link rel="icon" type="image/x-icon" href="{{ asset('storage/' . $companyLogo) }}">
        <link rel="shortcut icon" href="{{ asset('storage/' . $companyLogo) }}">
    @else
        <link rel="icon" type="image/x-icon" href="/favicon.ico">
    @endif

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        :root {
            --primary: #4f46e5;
            --primary-light: #e0e7ff;
            --primary-dark: #3730a3;
            --dark: #0f172a;
            --light: #f8fafc;
            --gray: #64748b;
            --border-radius: 16px;
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: #f8fafc;
            color: var(--dark);
            min-height: 100vh;
        }

        /* Premium Top Nav */
        .careers-nav {
            background-color: rgba(15, 23, 42, 0.95);
            backdrop-filter: blur(10px);
            border-bottom: 1px solid rgba(255, 255, 255, 0.08);
            padding: 1rem 0;
        }

        .careers-nav .brand-name {
            font-size: 1.25rem;
            font-weight: 800;
            letter-spacing: -0.025em;
            color: #ffffff;
            text-transform: uppercase;
        }

        /* Elegant Hero Banner */
        .hero-banner {
            background: radial-gradient(circle at top right, #1e1b4b 0%, #0f172a 100%);
            color: #ffffff;
            padding: 6rem 0 9rem 0;
            position: relative;
        }

        .badge-pill {
            padding: 0.5rem 1rem;
            font-weight: 600;
            font-size: 0.85rem;
            border-radius: 50px;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }

        /* Main Content Layout with Overlap */
        .main-content {
            margin-top: -6rem;
            position: relative;
            z-index: 10;
        }

        .job-card {
            background-color: #ffffff;
            border-radius: var(--border-radius);
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.05), 0 10px 10px -5px rgba(0, 0, 0, 0.02);
            border: 1px solid rgba(226, 232, 240, 0.8);
            overflow: hidden;
        }

        /* Prevents horizontal stretching of layout due to continuous words/strings */
        .break-words {
            word-wrap: break-word;
            word-break: break-word;
            overflow-wrap: break-word;
        }

        /* Inputs & Form Styling */
        .form-label {
            font-weight: 600;
            color: #334155;
            font-size: 0.9rem;
            margin-bottom: 0.5rem;
        }

        .form-control, .form-select {
            padding: 0.75rem 1rem;
            border-radius: 10px;
            border: 1px solid #cbd5e1;
            font-size: 0.95rem;
            background-color: #ffffff;
            color: var(--dark);
            transition: all 0.2s ease;
        }

        .form-control:focus, .form-select:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 4px var(--primary-light);
        }

        /* Resume Upload Drag/Drop Box */
        .resume-upload-wrapper {
            border: 2px dashed #cbd5e1;
            border-radius: 12px;
            padding: 1.5rem 1rem;
            text-align: center;
            background-color: #f8fafc;
            cursor: pointer;
            transition: all 0.2s ease;
            position: relative;
        }

        .resume-upload-wrapper:hover {
            border-color: var(--primary);
            background-color: #f5f3ff;
        }

        .resume-upload-wrapper input[type="file"] {
            position: absolute;
            top: 0; left: 0; width: 100%; height: 100%;
            opacity: 0;
            cursor: pointer;
            z-index: 10;
        }

        .btn-apply {
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
            border: none;
            color: #ffffff;
            font-weight: 600;
            padding: 1rem 1.5rem;
            border-radius: 10px;
            transition: all 0.2s ease;
            box-shadow: 0 4px 6px -1px rgba(79, 70, 229, 0.2);
        }

        .btn-apply:hover {
            transform: translateY(-1.5px);
            box-shadow: 0 10px 15px -3px rgba(79, 70, 229, 0.35);
            background: linear-gradient(135deg, var(--primary-dark) 0%, #2e269c 100%);
            color: #ffffff;
        }

        .job-detail-badge {
            background-color: rgba(255, 255, 255, 0.1);
            color: #e2e8f0;
            border: 1px solid rgba(255, 255, 255, 0.15);
            backdrop-filter: blur(5px);
        }
    </style>
</head>
<body>

    <!-- Sticky Header Navbar -->
    <nav class="navbar careers-nav sticky-top">
        <div class="container">
            @php
                $companyLogo = \App\Models\Setting::get('company_logo');
                $companyName = \App\Models\Setting::get('company_name', 'Techsoul');
            @endphp
            <span class="brand-name d-flex align-items-center gap-2">
                @if($companyLogo)
                    <div class="brand-logo-container d-flex align-items-center justify-content-center" style="width: 32px; height: 32px; overflow: hidden; border-radius: 8px; flex-shrink: 0; background: white;">
                        <img src="{{ asset('storage/' . $companyLogo) }}" alt="Logo" style="max-width: 100%; max-height: 100%; object-fit: contain; padding: 2px;">
                    </div>
                @else
                    <i class="bi bi-briefcase-fill text-primary-light" style="color: #a5b4fc;"></i>
                @endif
                <span style="font-size: 1.15rem; font-weight: 800; letter-spacing: -0.025em; text-transform: uppercase;">
                    {{ $companyName }}
                </span>
            </span>
            <span class="badge bg-success-subtle text-success border border-success-subtle px-3 py-1.5 rounded-pill fw-semibold">
                <span class="spinner-grow spinner-grow-sm text-success me-1.5" role="status" style="width: 8px; height: 8px; vertical-align: middle;"></span>
                We are hiring
            </span>
        </div>
    </nav>

    <!-- Elegant Hero Banner -->
    <header class="hero-banner">
        <div class="container">
            <div class="row">
                <div class="col-lg-10 mx-auto text-center">
                    <h5 class="text-uppercase tracking-widest fw-bold mb-3" style="color: #818cf8; font-size: 0.85rem; letter-spacing: 0.15em;">
                        {{ $vacancy->department?->name ?? 'Career Opportunity' }}
                    </h5>
                    <h1 class="display-4 fw-extrabold mb-3 break-words" style="letter-spacing: -0.03em; line-height: 1.15;">
                        {{ $vacancy->title }}
                    </h1>
                    
                    <div class="d-flex justify-content-center align-items-center gap-2.5 flex-wrap mb-4">
                        <span class="badge badge-pill job-detail-badge">
                            <i class="bi bi-briefcase"></i> {{ $vacancy->job_type }}
                        </span>
                        <span class="badge badge-pill job-detail-badge">
                            <i class="bi bi-geo-alt"></i> {{ $vacancy->location ?? 'Office' }}
                        </span>
                    </div>

                    <div>
                        <button type="button" class="btn btn-outline-light rounded-pill px-4 py-2 fw-semibold shadow-sm" 
                                data-bs-toggle="modal" data-bs-target="#jobDetailsModal">
                            <i class="bi bi-info-circle me-1.5"></i> View Job Details
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <!-- Main Content Overlap Layout -->
    <main class="container main-content pb-5">
        <div class="row">
            
            <!-- Full Page: Submission Form (Centered 8-column layout) -->
            <div class="col-lg-8 mx-auto">
                <div class="job-card p-4 p-md-5">
                    <div class="border-bottom pb-3 mb-4">
                        <h4 class="fw-bold mb-1 text-dark" style="letter-spacing: -0.02em;">Apply for this position</h4>
                        <p class="text-muted small mb-0">Fill out all required fields below and upload your CV/resume to complete your application.</p>
                    </div>

                    <form method="POST" action="{{ route('careers.vacancy.apply', $vacancy->token) }}" enctype="multipart/form-data">
                        @csrf
                        
                        <div class="row g-3">
                            <!-- Full Name -->
                            <div class="col-md-6">
                                <label class="form-label">Full Name <span class="text-danger">*</span></label>
                                <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" 
                                       value="{{ old('name') }}" required>
                                @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <!-- Gender -->
                            <div class="col-md-6">
                                <label class="form-label">Gender <span class="text-danger">*</span></label>
                                <select name="gender" class="form-select @error('gender') is-invalid @enderror" required>
                                    <option value="">Select Gender</option>
                                    <option value="Male" {{ old('gender') === 'Male' ? 'selected' : '' }}>Male</option>
                                    <option value="Female" {{ old('gender') === 'Female' ? 'selected' : '' }}>Female</option>
                                    <option value="Other" {{ old('gender') === 'Other' ? 'selected' : '' }}>Other</option>
                                </select>
                                @error('gender')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <!-- Date of Birth -->
                            <div class="col-md-6">
                                <label class="form-label">Date Of Birth <span class="text-danger">*</span></label>
                                <input type="date" name="dob" class="form-control @error('dob') is-invalid @enderror" 
                                       value="{{ old('dob') }}" required>
                                @error('dob')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <!-- Qualification -->
                            <div class="col-md-6">
                                <label class="form-label">Qualification <span class="text-danger">*</span></label>
                                <input type="text" name="qualification" class="form-control @error('qualification') is-invalid @enderror" 
                                       value="{{ old('qualification') }}" required>
                                @error('qualification')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <!-- Email -->
                            <div class="col-md-6">
                                <label class="form-label">Email <span class="text-danger">*</span></label>
                                <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" 
                                       value="{{ old('email') }}" required>
                                @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <!-- Phone -->
                            <div class="col-md-6">
                                <label class="form-label">Phone Number <span class="text-danger">*</span></label>
                                <input type="text" name="phone" class="form-control @error('phone') is-invalid @enderror" 
                                       value="{{ old('phone') }}" required>
                                @error('phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <!-- State -->
                            <div class="col-md-4">
                                <label class="form-label">State <span class="text-danger">*</span></label>
                                <select name="state" id="stateSelect" class="form-select @error('state') is-invalid @enderror" required>
                                    <option value="">Select State</option>
                                </select>
                                @error('state')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <!-- District -->
                            <div class="col-md-4">
                                <label class="form-label">District <span class="text-danger">*</span></label>
                                <select name="district" id="districtSelect" class="form-select @error('district') is-invalid @enderror" required>
                                    <option value="">Select District</option>
                                </select>
                                @error('district')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <!-- Home Town -->
                            <div class="col-md-4">
                                <label class="form-label">Home Town <span class="text-danger">*</span></label>
                                <input type="text" name="home_town" class="form-control @error('home_town') is-invalid @enderror" 
                                       value="{{ old('home_town') }}" required>
                                @error('home_town')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <!-- Experience Years -->
                            <div class="col-md-6">
                                <label class="form-label d-flex align-items-end" style="min-height: 44px;">How many years of relevant experience do you have? <span class="text-danger">*</span></label>
                                <input type="text" name="experience_years" class="form-control @error('experience_years') is-invalid @enderror" 
                                       value="{{ old('experience_years') }}" required>
                                @error('experience_years')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <!-- Salary Expectation -->
                            <div class="col-md-6">
                                <label class="form-label d-flex align-items-end" style="min-height: 44px;">What are your salary expectations? <span class="text-danger">*</span></label>
                                <input type="text" name="salary_expectation" class="form-control @error('salary_expectation') is-invalid @enderror" 
                                       value="{{ old('salary_expectation') }}" required>
                                @error('salary_expectation')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                @if($vacancy->salary_note)
                                    <div class="form-text text-muted mt-1.5 fw-medium d-flex align-items-center gap-1.5" style="font-size: 0.82rem; color: #4f46e5 !important;">
                                        <i class="bi bi-info-circle-fill"></i>
                                        <span>{{ $vacancy->salary_note }}</span>
                                    </div>
                                @endif
                            </div>

                            <!-- Relocation -->
                            <div class="col-md-6">
                                <label class="form-label">Are you ready to relocate? <span class="text-danger">*</span></label>
                                <select name="ready_to_relocate" class="form-select @error('ready_to_relocate') is-invalid @enderror" required>
                                    <option value="">Choose An Answer</option>
                                    <option value="Yes" {{ old('ready_to_relocate') === 'Yes' ? 'selected' : '' }}>Yes</option>
                                    <option value="No" {{ old('ready_to_relocate') === 'No' ? 'selected' : '' }}>No</option>
                                </select>
                                @error('ready_to_relocate')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <!-- Linkedin Id -->
                            <div class="col-md-6">
                                <label class="form-label">Linkedin Id</label>
                                <input type="text" name="linkedin_id" class="form-control @error('linkedin_id') is-invalid @enderror" 
                                       value="{{ old('linkedin_id') }}">
                                @error('linkedin_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <!-- Resume -->
                            <div class="col-12">
                                <label class="form-label">Upload your Resume <span class="text-danger">*</span></label>
                                <div class="resume-upload-wrapper @error('resume') border-danger @enderror">
                                    <i class="bi bi-file-earmark-arrow-up text-primary display-6 d-block mb-2" style="color: var(--primary) !important;"></i>
                                    <span class="fw-bold text-dark d-block" id="fileNameDisplay" style="font-size: 0.95rem;">Upload PDF, DOC, or DOCX</span>
                                    <small class="text-muted d-block mt-1">Drag file here or click to browse (Max: 10MB)</small>
                                    <input type="file" name="resume" required accept=".pdf,.doc,.docx" id="resumeFileInput" onchange="displaySelectedFile()">
                                </div>
                                @error('resume')<div class="text-danger small mt-1.5"><i class="bi bi-exclamation-triangle-fill me-1"></i>{{ $message }}</div>@enderror
                            </div>

                            <!-- Cover Letter -->
                            <div class="col-12">
                                <label class="form-label">Cover Letter <span class="text-muted">(Optional)</span></label>
                                <textarea name="cover_letter" class="form-control @error('cover_letter') is-invalid @enderror" 
                                          rows="3"></textarea>
                                @error('cover_letter')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>

                        <div class="mt-4">
                            <button type="submit" class="btn btn-apply w-100 py-3 d-inline-flex align-items-center justify-content-center gap-2">
                                <i class="bi bi-send-fill"></i> Submit Application
                            </button>
                        </div>
                    </form>
                </div>
            </div>

        </div>
    </main>

    <!-- Job Details Modal -->
    <div class="modal fade" id="jobDetailsModal" tabindex="-1" aria-labelledby="jobDetailsModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content shadow-lg border-0" style="border-radius: 16px;">
                <div class="modal-header border-bottom py-3">
                    <h5 class="modal-title fw-bold text-dark" id="jobDetailsModalLabel">
                        <i class="bi bi-briefcase-fill text-primary me-2"></i>Job Details & Requirements
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4 p-md-5 bg-light-subtle" style="max-height: 70vh; overflow-y: auto;">
                    <div class="mb-4">
                        <h5 class="fw-bold text-dark border-bottom pb-2 mb-3">Job Description</h5>
                        <div class="text-secondary break-words" style="white-space: pre-line; line-height: 1.8; font-size: 1.025rem;">
                            {{ $vacancy->description }}
                        </div>
                    </div>

                    @if($vacancy->requirements)
                        <div class="mt-4">
                            <h5 class="fw-bold text-dark border-bottom pb-2 mb-3">Requirements & Skills</h5>
                            <div class="text-secondary break-words" style="white-space: pre-line; line-height: 1.8; font-size: 1.025rem;">
                                {{ $vacancy->requirements }}
                            </div>
                        </div>
                    @endif
                </div>
                <div class="modal-footer border-top py-2.5">
                    <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="text-center py-4 bg-white border-top text-muted small mt-5">
        <div class="container">
            &copy; {{ date('Y') }} {{ $vacancy->company?->name ?? 'WorkeX' }}. All rights reserved.
        </div>
    </footer>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function displaySelectedFile() {
            const fileInput = document.getElementById('resumeFileInput');
            const fileNameDisplay = document.getElementById('fileNameDisplay');
            if (fileInput.files.length > 0) {
                fileNameDisplay.textContent = fileInput.files[0].name;
                fileNameDisplay.className = 'fw-bold text-success d-block';
            } else {
                fileNameDisplay.textContent = 'Upload PDF, DOC, or DOCX';
                fileNameDisplay.className = 'fw-bold text-dark d-block';
            }
        }

        // Indian States & Districts Dynamic Dropdowns
        const oldState = "{{ old('state') }}";
        const oldDistrict = "{{ old('district') }}";
        let statesData = [];

        fetch('/states-districts.json')
            .then(response => response.json())
            .then(data => {
                statesData = data.states;
                const stateSelect = document.getElementById('stateSelect');
                
                statesData.forEach(item => {
                    const option = document.createElement('option');
                    option.value = item.state;
                    option.textContent = item.state;
                    if (oldState && oldState === item.state) {
                        option.selected = true;
                    }
                    stateSelect.appendChild(option);
                });

                if (oldState) {
                    populateDistricts(oldState, oldDistrict);
                }
            })
            .catch(err => console.error('Error fetching states and districts:', err));

        document.getElementById('stateSelect').addEventListener('change', function() {
            populateDistricts(this.value);
        });

        function populateDistricts(selectedState, selectDistrict = null) {
            const districtSelect = document.getElementById('districtSelect');
            districtSelect.innerHTML = '<option value="">Select District</option>';
            
            if (!selectedState) return;

            const stateObj = statesData.find(item => item.state === selectedState);
            if (stateObj && stateObj.districts) {
                stateObj.districts.forEach(district => {
                    const option = document.createElement('option');
                    option.value = district;
                    option.textContent = district;
                    if (selectDistrict && selectDistrict === district) {
                        option.selected = true;
                    }
                    districtSelect.appendChild(option);
                });
            }
        }
    </script>
</body>
</html>
