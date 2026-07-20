<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Completed Onboarding Form - {{ $onboarding->employee_code ?? 'Pending' }}</title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background: #f8fafc;
            color: #1e293b;
            padding: 20px 0;
        }

        .document-page {
            background: #ffffff;
            width: 210mm;
            min-height: 297mm;
            margin: 0 auto;
            padding: 20mm;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
            position: relative;
            border: 1px solid #e2e8f0;
        }

        h1, h2, h3, h4, h5, h6 {
            color: #0f172a;
            font-weight: 700;
        }

        .section-header {
            background: #f1f5f9;
            padding: 6px 12px;
            font-size: 11px;
            font-weight: 700;
            letter-spacing: 0.5px;
            text-transform: uppercase;
            border-left: 4px solid #0f172a;
            margin: 20px 0 12px 0;
        }

        .form-field-label {
            font-size: 10.5px;
            color: #64748b;
            text-transform: uppercase;
            font-weight: 600;
            margin-bottom: 2px;
        }

        .form-field-value {
            font-size: 13.5px;
            color: #0f172a;
            font-weight: 600;
            border-bottom: 1px dashed #cbd5e1;
            padding-bottom: 3px;
            min-height: 22px;
        }

        .checkbox-container {
            display: flex;
            align-items: center;
            gap: 6px;
            font-size: 12px;
            margin-bottom: 6px;
        }

        .custom-checkbox {
            width: 14px;
            height: 14px;
            border: 1.5px solid #0f172a;
            display: inline-block;
            text-align: center;
            line-height: 10px;
            font-weight: bold;
            font-size: 10px;
        }

        .section-block {
            page-break-inside: avoid;
            break-inside: avoid;
        }

        .office-use-box {
            border: 2px solid #0f172a;
            padding: 15px;
            margin-top: 30px;
            background: #f8fafc;
        }

        @media print {
            @page {
                size: A4;
                margin: 25mm 12mm 15mm 12mm;
            }
            body {
                background: none;
                padding: 0;
            }
            .document-page {
                box-shadow: none;
                border: none;
                margin: 0;
                padding: 20mm 15mm;
                width: 100%;
                min-height: auto;
            }
            .no-print {
                display: none !important;
            }
        }
    </style>
</head>
<body>

    <!-- Print / Back Navigation panel -->
    <div class="container text-center my-3 no-print">
        <div class="d-inline-flex gap-2">
            <a href="{{ route('employees.onboardings.index') }}" class="btn btn-secondary btn-sm px-3">
                <i class="bi bi-arrow-left"></i> Back to Onboardings
            </a>
            <a href="{{ route('employees.onboardings.edit', $onboarding->id) }}" class="btn btn-warning text-dark btn-sm px-3">
                <i class="bi bi-pencil-square"></i> Edit Details
            </a>
            <button onclick="window.print()" class="btn btn-primary btn-sm px-4">
                <i class="bi bi-printer"></i> Print / Save as PDF
            </button>
        </div>
    </div>

    <!-- Printable Paper Form Sheet -->
    <div class="document-page shadow">
        
        <!-- Centered Header and Photo Box Row -->
        <div class="d-flex align-items-center justify-content-between pb-3 mb-4" style="border-bottom: 2px solid #0f172a;">
            <!-- Left buffer for centering -->
            <div style="width: 100px;" class="no-print-empty"></div>
            
            <!-- Centered Titles -->
            <div class="text-center flex-grow-1">
                <div style="font-size: 22px; font-weight: 800; letter-spacing: 0.5px; color: #0f172a;">TECHSOUL CYBER SOLUTIONS</div>
                <div style="font-size: 13px; font-weight: 700; text-transform: uppercase; color: #475569; margin-top: 5px;">Employee Onboarding & Personal Information Form</div>
                <div style="font-size: 10px; color: #64748b; margin-top: 2px;">Techsoul Technologies • Techsoul IT Solutions • Techsoul Solar</div>
            </div>

            <!-- Right Photo slot -->
            <div style="width: 100px; height: 120px; border: 2px dashed #cbd5e1; display: flex; align-items: center; justify-content: center; text-align: center; font-size: 9px; color: #64748b; background-color: #f8fafc; overflow: hidden; flex-shrink: 0;">
                @php
                    $photoDoc = $uploadedDocs->where('title', 'Passport Size Photo')->first();
                @endphp
                @if($photoDoc)
                    <img src="{{ asset('storage/' . $photoDoc->file_path) }}" alt="Photo" style="width: 100%; height: 100%; object-fit: cover;">
                @else
                    PASTE RECENT<br>PASSPORT SIZE<br>PHOTOGRAPH HERE
                @endif
            </div>
        </div>

        <!-- Meta Details Box -->
        <table class="table table-bordered mb-4 mt-3" style="font-size: 11px; border-color: #cbd5e1; border-collapse: collapse; width: 100%;">
            <tbody>
                <tr>
                    <td style="width: 25%; padding: 8px 12px; background-color: #f8fafc; border: 1px solid #cbd5e1;">
                        <span class="text-muted d-block text-uppercase fw-bold" style="font-size: 9px; letter-spacing: 0.3px; margin-bottom: 2px;">Employee ID</span>
                        <strong style="font-size: 12px; color: #0f172a;">{{ $onboarding->employee_code ?? 'Pending' }}</strong>
                    </td>
                    <td style="width: 25%; padding: 8px 12px; border: 1px solid #cbd5e1;">
                        <span class="text-muted d-block text-uppercase fw-bold" style="font-size: 9px; letter-spacing: 0.3px; margin-bottom: 2px;">Department</span>
                        <strong style="font-size: 12px; color: #0f172a;">{{ $onboarding->department->name ?? 'N/A' }}</strong>
                    </td>
                    <td style="width: 25%; padding: 8px 12px; border: 1px solid #cbd5e1;">
                        <span class="text-muted d-block text-uppercase fw-bold" style="font-size: 9px; letter-spacing: 0.3px; margin-bottom: 2px;">Designation</span>
                        <strong style="font-size: 12px; color: #0f172a;">{{ $onboarding->designation->name ?? 'N/A' }}</strong>
                    </td>
                    <td style="width: 25%; padding: 8px 12px; border: 1px solid #cbd5e1;">
                        <span class="text-muted d-block text-uppercase fw-bold" style="font-size: 9px; letter-spacing: 0.3px; margin-bottom: 2px;">Date of Joining</span>
                        <strong style="font-size: 12px; color: #0f172a;">{{ $onboarding->joining_date ? $onboarding->joining_date->format('d M Y') : 'N/A' }}</strong>
                    </td>
                </tr>
            </tbody>
        </table>

        <!-- SECTION 1 -->
        <div class="section-block">
            <div class="section-header">Section 1 – Personal Information</div>
            <div class="row g-3 mb-2">
                <div class="col-12">
                    <div class="form-field-label">Full Name (As per Aadhaar)</div>
                    <div class="form-field-value">{{ $onboarding->name }}</div>
                </div>
                <div class="col-4">
                    <div class="form-field-label">Gender</div>
                    <div class="d-flex gap-3 pt-1">
                        <div class="checkbox-container">
                            <span class="custom-checkbox">{{ $onboarding->gender === 'Male' ? '✓' : '' }}</span> Male
                        </div>
                        <div class="checkbox-container">
                            <span class="custom-checkbox">{{ $onboarding->gender === 'Female' ? '✓' : '' }}</span> Female
                        </div>
                        <div class="checkbox-container">
                            <span class="custom-checkbox">{{ $onboarding->gender === 'Other' ? '✓' : '' }}</span> Other
                        </div>
                    </div>
                </div>
                @if($onboarding->dob)
                <div class="col-4">
                    <div class="form-field-label">Date of Birth</div>
                    <div class="form-field-value">{{ $onboarding->dob->format('d M Y') }}</div>
                </div>
                @endif
                @if($onboarding->blood_group)
                <div class="col-4">
                    <div class="form-field-label">Blood Group</div>
                    <div class="form-field-value">{{ $onboarding->blood_group }}</div>
                </div>
                @endif
                <div class="col-6">
                    <div class="form-field-label">Marital Status</div>
                    <div class="d-flex gap-3 pt-1">
                        <div class="checkbox-container">
                            <span class="custom-checkbox">{{ $onboarding->marital_status === 'Single' ? '✓' : '' }}</span> Single
                        </div>
                        <div class="checkbox-container">
                            <span class="custom-checkbox">{{ $onboarding->marital_status === 'Married' ? '✓' : '' }}</span> Married
                        </div>
                        <div class="checkbox-container">
                            <span class="custom-checkbox">{{ $onboarding->marital_status === 'Divorced' ? '✓' : '' }}</span> Divorced
                        </div>
                        <div class="checkbox-container">
                            <span class="custom-checkbox">{{ $onboarding->marital_status === 'Widowed' ? '✓' : '' }}</span> Widowed
                        </div>
                    </div>
                </div>
                <div class="col-6">
                    <div class="form-field-label">Nationality</div>
                    <div class="form-field-value">{{ $onboarding->nationality }}</div>
                </div>
                <div class="col-6">
                    <div class="form-field-label">Aadhaar Number</div>
                    <div class="form-field-value">{{ $onboarding->aadhaar_number }}</div>
                </div>
                <div class="col-6">
                    <div class="form-field-label">PAN Number</div>
                    <div class="form-field-value">{{ $onboarding->pan_number }}</div>
                </div>
                @if($onboarding->passport_number)
                <div class="col-6">
                    <div class="form-field-label">Passport Number</div>
                    <div class="form-field-value">{{ $onboarding->passport_number }}</div>
                </div>
                @endif
                @if($onboarding->driving_license_number)
                <div class="col-6">
                    <div class="form-field-label">Driving License Number</div>
                    <div class="form-field-value">{{ $onboarding->driving_license_number }}</div>
                </div>
                @endif
            </div>
        </div>

        <!-- SECTION 2 -->
        <div class="section-block">
            <div class="section-header">Section 2 – Contact Details</div>
            <div class="row g-3 mb-2">
                <div class="col-6">
                    <div class="form-field-label">Mobile Number</div>
                    <div class="form-field-value">{{ $onboarding->phone }}</div>
                </div>
                @if($onboarding->alternate_mobile)
                <div class="col-6">
                    <div class="form-field-label">Alternate Mobile Number</div>
                    <div class="form-field-value">{{ $onboarding->alternate_mobile }}</div>
                </div>
                @endif
                <div class="col-12">
                    <div class="form-field-label">Personal Email Address</div>
                    <div class="form-field-value">{{ $onboarding->personal_email }}</div>
                </div>
                <div class="col-12">
                    <div class="form-field-label">Current Residential Address</div>
                    <div class="form-field-value">{{ $onboarding->current_address }} (PIN: {{ $onboarding->current_pin_code }})</div>
                </div>
                <div class="col-12">
                    <div class="form-field-label">Permanent Address</div>
                    <div class="form-field-value">
                        @if($onboarding->same_as_current)
                            Same as Current Address
                        @else
                            {{ $onboarding->permanent_address }} (PIN: {{ $onboarding->permanent_pin_code }})
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- SECTION 3 -->
        <div class="section-block">
            <div class="section-header">Section 3 – Emergency Contact Details</div>
            <div class="row g-3 mb-2">
                <div class="col-4">
                    <div class="form-field-label">Emergency Contact Person</div>
                    <div class="form-field-value">{{ $onboarding->emergency_contact_person }}</div>
                </div>
                <div class="col-4">
                    <div class="form-field-label">Relationship</div>
                    <div class="form-field-value">{{ $onboarding->emergency_relationship }}</div>
                </div>
                <div class="col-4">
                    <div class="form-field-label">Mobile Number</div>
                    <div class="form-field-value">{{ $onboarding->emergency_phone }}</div>
                </div>
                @if($onboarding->emergency_alternate_phone)
                <div class="col-6">
                    <div class="form-field-label">Alternate Mobile Number</div>
                    <div class="form-field-value">{{ $onboarding->emergency_alternate_phone }}</div>
                </div>
                @endif
                @if($onboarding->emergency_address)
                <div class="col-6">
                    <div class="form-field-label">Address</div>
                    <div class="form-field-value">{{ $onboarding->emergency_address }}</div>
                </div>
                @endif
            </div>
        </div>

        <!-- SECTION 5 -->
        <div class="section-block">
            <div class="section-header">Section 5 – Educational Qualifications</div>
            <div class="table-responsive my-2">
                <table class="table table-bordered table-sm align-middle mb-0" style="font-size: 12px; border-color: #cbd5e1; width: 100%;">
                    <thead class="bg-light">
                        <tr>
                            <th style="padding: 6px 10px;">Qualification</th>
                            <th style="padding: 6px 10px;">Institution</th>
                            <th style="padding: 6px 10px;">Board / University</th>
                            <th style="padding: 6px 10px;">Year Passed</th>
                            <th style="padding: 6px 10px;">Percentage / CGPA</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($onboarding->education_qualifications ?? [] as $edu)
                            @if(!empty($edu['institution']) || !empty($edu['board_university']))
                                <tr>
                                    <td class="fw-semibold bg-light-subtle" style="padding: 6px 10px;">{{ $edu['qualification'] ?? 'N/A' }}</td>
                                    <td style="padding: 6px 10px;">{{ $edu['institution'] ?? 'N/A' }}</td>
                                    <td style="padding: 6px 10px;">{{ $edu['board_university'] ?? 'N/A' }}</td>
                                    <td style="padding: 6px 10px;">{{ $edu['year_passed'] ?? 'N/A' }}</td>
                                    <td style="padding: 6px 10px;">{{ $edu['percentage'] ?? 'N/A' }}</td>
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
        </div>

        <!-- SECTION 6 -->
        <div class="section-block">
            <div class="section-header">Section 6 – Professional Details</div>
            <div class="row g-3 mb-2">
                <div class="col-4">
                    <div class="form-field-label">Total Experience</div>
                    <div class="form-field-value">{{ $onboarding->total_experience }}</div>
                </div>
                @if($onboarding->prev_employer)
                <div class="col-8">
                    <div class="form-field-label">Previous Employer</div>
                    <div class="form-field-value">{{ $onboarding->prev_employer }}</div>
                </div>
                @endif
                @if($onboarding->prev_designation)
                <div class="col-4">
                    <div class="form-field-label">Designation</div>
                    <div class="form-field-value">{{ $onboarding->prev_designation }}</div>
                </div>
                @endif
                @if($onboarding->prev_duration)
                <div class="col-4">
                    <div class="form-field-label">Duration</div>
                    <div class="form-field-value">{{ $onboarding->prev_duration }}</div>
                </div>
                @endif
                @if($onboarding->prev_reason_for_leaving)
                <div class="col-4">
                    <div class="form-field-label">Reason for Leaving</div>
                    <div class="form-field-value">{{ $onboarding->prev_reason_for_leaving }}</div>
                </div>
                @endif
                <div class="col-12">
                    <div class="form-field-label">Skills & Technologies Known</div>
                    <div class="d-flex flex-wrap gap-1 mt-1">
                        @forelse($onboarding->skills ?? [] as $sk)
                            <span class="badge bg-secondary text-dark border">{{ $sk }}</span>
                        @empty
                            <span class="text-muted fs-8">No skills selected</span>
                        @endforelse
                    </div>
                </div>
                @if($onboarding->skills_other)
                <div class="col-12">
                    <div class="form-field-label">Other Skills</div>
                    <div class="form-field-value">{{ $onboarding->skills_other }}</div>
                </div>
                @endif
            </div>
        </div>

        <!-- SECTION 7 -->
        <div class="section-block">
            <div class="section-header">Section 7 – Bank Details</div>
            <div class="row g-3 mb-2">
                <div class="col-6">
                    <div class="form-field-label">Account Holder Name</div>
                    <div class="form-field-value">{{ $onboarding->bank_account_holder }}</div>
                </div>
                <div class="col-6">
                    <div class="form-field-label">Bank Name</div>
                    <div class="form-field-value">{{ $onboarding->bank_name }}</div>
                </div>
                <div class="col-4">
                    <div class="form-field-label">Branch Name</div>
                    <div class="form-field-value">{{ $onboarding->bank_branch }}</div>
                </div>
                <div class="col-4">
                    <div class="form-field-label">Account Number</div>
                    <div class="form-field-value">{{ $onboarding->bank_account_number }}</div>
                </div>
                <div class="col-4">
                    <div class="form-field-label">IFSC Code</div>
                    <div class="form-field-value">{{ $onboarding->bank_ifsc }}</div>
                </div>
                @if($onboarding->bank_upi)
                <div class="col-12">
                    <div class="form-field-label">UPI ID</div>
                    <div class="form-field-value">{{ $onboarding->bank_upi }}</div>
                </div>
                @endif
            </div>
        </div>

        <!-- SECTION 8 -->
        @if($onboarding->uan_number || $onboarding->pf_number || $onboarding->esi_number)
        <div class="section-block">
            <div class="section-header">Section 8 – PF / ESI Details</div>
            <div class="row g-3 mb-2">
                @if($onboarding->uan_number)
                <div class="col-4">
                    <div class="form-field-label">UAN Number</div>
                    <div class="form-field-value">{{ $onboarding->uan_number }}</div>
                </div>
                @endif
                @if($onboarding->pf_number)
                <div class="col-4">
                    <div class="form-field-label">PF Number</div>
                    <div class="form-field-value">{{ $onboarding->pf_number }}</div>
                </div>
                @endif
                @if($onboarding->esi_number)
                <div class="col-4">
                    <div class="form-field-label">ESI Number</div>
                    <div class="form-field-value">{{ $onboarding->esi_number }}</div>
                </div>
                @endif
            </div>
        </div>
        @endif

        <!-- SECTION 10 & 11 -->
        <div class="section-block">
            <div class="section-header">Section 10 & 11 – Access & Assets Requirements</div>
            <div class="row g-3 mb-2">
                <div class="col-6">
                    <div class="form-field-label">Official Access Granted</div>
                    <div class="d-flex flex-wrap gap-1 mt-1">
                        @forelse($onboarding->company_access_requirements ?? [] as $acc)
                            <span class="badge bg-light text-dark border">{{ $acc }}</span>
                        @empty
                            <span class="text-muted fs-8">No access requirements set</span>
                        @endforelse
                    </div>
                    @if($onboarding->company_access_other)
                        <div class="form-field-label mt-2">Other Access</div>
                        <div class="form-field-value">{{ $onboarding->company_access_other }}</div>
                    @endif
                </div>
                <div class="col-6">
                    <div class="form-field-label">IT Assets Issued</div>
                    <div class="d-flex flex-wrap gap-1 mt-1">
                        @forelse($onboarding->assets_issued ?? [] as $asset)
                            <span class="badge bg-light text-dark border">{{ $asset }}</span>
                        @empty
                            <span class="text-muted fs-8">No assets issued</span>
                        @endforelse
                    </div>
                    @if($onboarding->assets_remarks)
                        <div class="form-field-label mt-2">Asset Remarks / Serial Numbers</div>
                        <div class="form-field-value">{{ $onboarding->assets_remarks }}</div>
                    @endif
                </div>
            </div>
        </div>

        <!-- SECTION 12 -->
        @if($onboarding->medical_condition || $onboarding->medical_allergies || $onboarding->medical_medication)
        <div class="section-block">
            <div class="section-header">Section 12 – Medical Information (Optional)</div>
            <div class="row g-3 mb-2">
                @if($onboarding->medical_condition)
                <div class="col-4">
                    <div class="form-field-label">Medical Condition</div>
                    <div class="form-field-value">{{ $onboarding->medical_condition }}</div>
                </div>
                @endif
                @if($onboarding->medical_allergies)
                <div class="col-4">
                    <div class="form-field-label">Allergies</div>
                    <div class="form-field-value">{{ $onboarding->medical_allergies }}</div>
                </div>
                @endif
                @if($onboarding->medical_medication)
                <div class="col-4">
                    <div class="form-field-label">Regular Medication</div>
                    <div class="form-field-value">{{ $onboarding->medical_medication }}</div>
                </div>
                @endif
            </div>
        </div>
        @endif

        <!-- SECTION 9 -->
        <div class="section-block">
            <div class="section-header">Section 9 – Document Submission Summary</div>
            <div class="row g-2 mb-3">
                @forelse($uploadedDocs as $doc)
                    <div class="col-6" style="font-size: 12px; color: #475569;">
                        <i class="bi bi-check-circle-fill text-success me-2"></i><strong>{{ $doc->title }}</strong>: <span class="text-muted">{{ $doc->file_name }}</span>
                    </div>
                @empty
                    <div class="col-12 text-muted">No documents uploaded.</div>
                @endforelse
            </div>
        </div>

        <!-- SECTION 13 -->
        <div class="section-block">
            <div class="section-header">Section 13 – Declaration by Employee</div>
            <p style="font-size: 12px; line-height: 1.5; color: #475569; text-align: justify;">I hereby certify that all information provided by me in this Employee Onboarding Form is true, complete, and accurate. I understand that submission of false information may result in disciplinary action, including termination of employment. I agree to comply with all policies, confidentiality requirements, and employment conditions of Techsoul Cyber Solutions.</p>
            
            <div class="row g-4 my-2 text-center">
                <div class="col-6">
                    <div style="border-bottom: 1px solid #0f172a; height: 35px; line-height: 35px;">&nbsp;</div>
                    <div style="font-size: 10px; color: #64748b; margin-top: 5px;">Employee Signature</div>
                </div>
                <div class="col-6">
                    <div style="border-bottom: 1px solid #0f172a; height: 35px; line-height: 35px;">&nbsp;</div>
                    <div style="font-size: 10px; color: #64748b; margin-top: 5px;">Date</div>
                </div>
            </div>

        </div>

        <!-- HR USE ONLY PRINT DETAILS -->
        <div class="section-block office-use-box">
            <h6 class="fw-bold text-center text-uppercase text-secondary tracking-widest mb-3" style="font-size: 11px;">For Office Use Only</h6>
            <div class="row g-4 text-center">
                <div class="col-4">
                    <div style="border-bottom: 1px solid #0f172a; height: 35px; line-height: 35px;">&nbsp;</div>
                    <div style="font-size: 10px; color: #64748b; margin-top: 5px;">Approved By: <strong>{{ $onboarding->approved_by ?? 'N/A' }}</strong></div>
                </div>
                <div class="col-4">
                    <div style="border-bottom: 1px solid #0f172a; height: 35px; line-height: 35px;">&nbsp;</div>
                    <div style="font-size: 10px; color: #64748b; margin-top: 5px;">HR Signature: <strong>{{ $onboarding->hr_signature ?? 'N/A' }}</strong></div>
                </div>
                <div class="col-4">
                    <div style="border-bottom: 1px solid #0f172a; height: 35px; line-height: 35px;">&nbsp;</div>
                    <div style="font-size: 10px; color: #64748b; margin-top: 5px;">Management Approval: <strong>{{ $onboarding->management_signature ?? 'N/A' }}</strong></div>
                </div>
            </div>
        </div>

    </div>
</body>
</html>
