<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Completed Intern Onboarding Form - TSL-{{ str_pad(14737 + $onboarding->intern->id, 6, '0', STR_PAD_LEFT) }}</title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Caveat:wght@700&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    
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

        .header-title {
            font-size: 20px;
            font-weight: 800;
            letter-spacing: 0.5px;
            text-align: center;
        }

        .header-subtitle {
            font-size: 14px;
            font-weight: 700;
            text-align: center;
            border-bottom: 2px solid #000;
            padding-bottom: 10px;
            margin-bottom: 20px;
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

        .photo-box {
            width: 100px;
            height: 125px;
            border: 2px dashed #94a3b8;
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
            font-size: 10px;
            color: #64748b;
            position: absolute;
            top: 20mm;
            right: 20mm;
            background: #f8fafc;
            overflow: hidden;
        }

        .photo-box img {
            width: 100%;
            height: 100%;
            object-fit: cover;
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

        .signature-line {
            border-bottom: 1px solid #0f172a;
            margin-top: 40px;
            text-align: center;
            font-size: 12px;
            font-weight: 600;
            padding-bottom: 5px;
        }

        .office-use-box {
            border: 2px solid #0f172a;
            padding: 15px;
            margin-top: 30px;
            background: #f8fafc;
        }

        .section-block {
            page-break-inside: avoid;
            break-inside: avoid;
        }

        @media print {
            @page {
                margin: 0;
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
            <a href="{{ route('interns.onboardings.index') }}" class="btn btn-secondary btn-sm px-3">
                <i class="bi bi-arrow-left"></i> Back to Onboardings
            </a>
            <button onclick="window.print()" class="btn btn-primary btn-sm px-4">
                <i class="bi bi-printer"></i> Print Document
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
                <div style="font-size: 13px; font-weight: 700; text-transform: uppercase; color: #475569; margin-top: 5px;">Intern Onboarding & Information Form</div>
            </div>

            <!-- Right Photo slot -->
            <div style="width: 100px; height: 120px; border: 2px dashed #cbd5e1; display: flex; align-items: center; justify-content: center; text-align: center; font-size: 9px; color: #64748b; background-color: #f8fafc; overflow: hidden; flex-shrink: 0;">
                @if($onboarding->intern->photo)
                    <img src="{{ asset('storage/' . $onboarding->intern->photo) }}" alt="Photo" style="width: 100%; height: 100%; object-fit: cover;">
                @else
                    PASTE RECENT<br>PASSPORT SIZE<br>PHOTOGRAPH HERE
                @endif
            </div>
        </div>

        <!-- Meta Details Box -->
        <table class="table table-bordered mb-4 mt-3" style="font-size: 11px; border-color: #cbd5e1; border-collapse: collapse; width: 100%;">
            <tbody>
                <tr>
                    <td style="width: 33.33%; padding: 8px 12px; background-color: #f8fafc; border: 1px solid #cbd5e1;">
                        <span class="text-muted d-block text-uppercase fw-bold" style="font-size: 9px; letter-spacing: 0.3px; margin-bottom: 2px;">Intern ID</span>
                        <strong style="font-size: 12px; color: #0f172a;">TSL-{{ str_pad(14737 + $onboarding->intern->id, 6, '0', STR_PAD_LEFT) }}</strong>
                    </td>
                    <td style="width: 33.33%; padding: 8px 12px; border: 1px solid #cbd5e1;">
                        <span class="text-muted d-block text-uppercase fw-bold" style="font-size: 9px; letter-spacing: 0.3px; margin-bottom: 2px;">Internship Start Date</span>
                        <strong style="font-size: 12px; color: #0f172a;">{{ $onboarding->intern->joining_date ? $onboarding->intern->joining_date->format('d / m / Y') : '____ / ____ / ________' }}</strong>
                    </td>
                    <td style="width: 33.33%; padding: 8px 12px; border: 1px solid #cbd5e1;">
                        <span class="text-muted d-block text-uppercase fw-bold" style="font-size: 9px; letter-spacing: 0.3px; margin-bottom: 2px;">Internship End Date</span>
                        <strong style="font-size: 12px; color: #0f172a;">{{ $onboarding->intern->end_date ? $onboarding->intern->end_date->format('d / m / Y') : '____ / ____ / ________' }}</strong>
                    </td>
                </tr>
                <tr>
                    <td style="padding: 8px 12px; background-color: #f8fafc; border: 1px solid #cbd5e1;">
                        <span class="text-muted d-block text-uppercase fw-bold" style="font-size: 9px; letter-spacing: 0.3px; margin-bottom: 2px;">Department</span>
                        <strong style="font-size: 12px; color: #0f172a;">{{ $onboarding->intern->department->name ?? 'N/A' }}</strong>
                    </td>
                    <td style="padding: 8px 12px; border: 1px solid #cbd5e1;">
                        <span class="text-muted d-block text-uppercase fw-bold" style="font-size: 9px; letter-spacing: 0.3px; margin-bottom: 2px;">Internship Sector</span>
                        <strong style="font-size: 12px; color: #0f172a;">{{ $onboarding->sector ?? 'Techsoul Technologies' }}</strong>
                    </td>
                    <td style="padding: 8px 12px; border: 1px solid #cbd5e1;">
                        <span class="text-muted d-block text-uppercase fw-bold" style="font-size: 9px; letter-spacing: 0.3px; margin-bottom: 2px;">Mentor / Supervisor</span>
                        <strong style="font-size: 12px; color: #0f172a;">{{ $onboarding->mentor_supervisor ?? 'N/A' }}</strong>
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
                    <div class="form-field-value">{{ $onboarding->intern->name }}</div>
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
                <div class="col-4">
                    <div class="form-field-label">Date of Birth</div>
                    <div class="form-field-value">{{ $onboarding->dob ? $onboarding->dob->format('d / m / Y') : '____ / ____ / ________' }}</div>
                </div>
                <div class="col-4">
                    <div class="form-field-label">Blood Group</div>
                    <div class="form-field-value">{{ $onboarding->blood_group ?? 'N/A' }}</div>
                </div>
                <div class="col-6">
                    <div class="form-field-label">Aadhaar Number</div>
                    <div class="form-field-value">{{ $onboarding->aadhaar_number }}</div>
                </div>
                <div class="col-6">
                    <div class="form-field-label">Mobile Number</div>
                    <div class="form-field-value">{{ $onboarding->intern->phone ?? 'N/A' }}</div>
                </div>
                <div class="col-6">
                    <div class="form-field-label">Alternate Mobile Number</div>
                    <div class="form-field-value">{{ $onboarding->alternate_mobile ?? 'N/A' }}</div>
                </div>
                <div class="col-6">
                    <div class="form-field-label">Personal Email Address</div>
                    <div class="form-field-value">{{ $onboarding->intern->email }}</div>
                </div>
                <div class="col-9">
                    <div class="form-field-label">Current Address</div>
                    <div class="form-field-value">{{ $onboarding->current_address }}</div>
                </div>
                <div class="col-3">
                    <div class="form-field-label">PIN Code</div>
                    <div class="form-field-value">{{ $onboarding->pin_code }}</div>
                </div>
            </div>
        </div>

        <!-- SECTION 2 -->
        <div class="section-block">
            <div class="section-header">Section 2 – Educational Information</div>
            <div class="row g-3 mb-2">
                <div class="col-12">
                    <div class="form-field-label">College / Institution Name</div>
                    <div class="form-field-value">{{ $onboarding->college_name }}</div>
                </div>
                <div class="col-6">
                    <div class="form-field-label">University / Board</div>
                    <div class="form-field-value">{{ $onboarding->university_board }}</div>
                </div>
                <div class="col-6">
                    <div class="form-field-label">Course</div>
                    <div class="form-field-value">{{ $onboarding->course }}</div>
                </div>
                <div class="col-6">
                    <div class="form-field-label">Branch / Specialization</div>
                    <div class="form-field-value">{{ $onboarding->branch_specialization }}</div>
                </div>
                <div class="col-6">
                    <div class="form-field-label">Current Semester / Year</div>
                    <div class="form-field-value">{{ $onboarding->current_semester_year }}</div>
                </div>
                <div class="col-6">
                    <div class="form-field-label">College Roll Number / Registration Number</div>
                    <div class="form-field-value">{{ $onboarding->college_roll_number }}</div>
                </div>
                <div class="col-6">
                    <div class="form-field-label">Expected Year of Completion</div>
                    <div class="form-field-value">{{ $onboarding->expected_completion_year }}</div>
                </div>
            </div>
        </div>

        <!-- SECTION 3 & 4 -->
        <div class="section-block">
            <div class="row g-4">
                <div class="col-6">
                    <div class="section-header">Section 3 – Parent / Guardian Details</div>
                    <div class="row g-2">
                        <div class="col-12">
                            <div class="form-field-label">Parent / Guardian Name</div>
                            <div class="form-field-value">{{ $onboarding->parent_name }}</div>
                        </div>
                        <div class="col-6">
                            <div class="form-field-label">Relationship</div>
                            <div class="form-field-value">{{ $onboarding->parent_relationship }}</div>
                        </div>
                        <div class="col-6">
                            <div class="form-field-label">Mobile Number</div>
                            <div class="form-field-value">{{ $onboarding->parent_phone }}</div>
                        </div>

                        <div class="col-12">
                            <div class="form-field-label">Address (If Different)</div>
                            <div class="form-field-value">{{ $onboarding->parent_address ?? 'Same as current address' }}</div>
                        </div>
                    </div>
                </div>
                <div class="col-6">
                    <div class="section-header">Section 4 – Emergency Contact Details</div>
                    <div class="row g-2">
                        <div class="col-12">
                            <div class="form-field-label">Emergency Contact Person</div>
                            <div class="form-field-value">{{ $onboarding->emergency_contact_person }}</div>
                        </div>
                        <div class="col-6">
                            <div class="form-field-label">Relationship</div>
                            <div class="form-field-value">{{ $onboarding->emergency_relationship }}</div>
                        </div>
                        <div class="col-6">
                            <div class="form-field-label">Mobile Number</div>
                            <div class="form-field-value">{{ $onboarding->emergency_phone }}</div>
                        </div>
                        <div class="col-12">
                            <div class="form-field-label">Alternate Number</div>
                            <div class="form-field-value">{{ $onboarding->emergency_alternate_phone ?? 'N/A' }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- SECTION 5 -->
        <div class="section-block">
            <div class="section-header">Section 5 – Internship Details</div>
            <div class="row g-3 mb-2">
                <div class="col-4">
                    <div class="form-field-label">Internship Type</div>
                    <div class="form-field-value">{{ $onboarding->internship_type === 'Other' ? $onboarding->internship_type_other : $onboarding->internship_type }}</div>
                </div>
                <div class="col-4">
                    <div class="form-field-label">Internship Mode</div>
                    <div class="form-field-value">{{ $onboarding->internship_mode ?? 'N/A' }}</div>
                </div>
                <div class="col-4">
                    <div class="form-field-label">Internship Duration</div>
                    <div class="form-field-value">{{ $onboarding->internship_duration === 'Other' ? $onboarding->internship_duration_other : $onboarding->internship_duration }}</div>
                </div>

                <div class="col-12">
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
                        
                        $selectedInts = $onboarding->areas_of_interest ?? [];
                        if ($onboarding->areas_of_interest_other) {
                            $selectedInts[] = $onboarding->areas_of_interest_other;
                        }
                        $intsString = implode(', ', $selectedInts);
                    @endphp

                    <div class="form-field-label">{{ $interestLabel }}</div>
                    <div class="form-field-value">{{ $intsString ?: 'None selected.' }}</div>
                </div>
            </div>
        </div>

        <!-- SECTION 6 -->
        <div class="section-block">
            <div class="section-header">Section 6 – Technical Skills & Knowledge</div>
            <div class="row g-3 mb-2">
                <div class="col-6">
                    @php
                        $selectedLangs = $onboarding->programming_languages ?? [];
                        if ($onboarding->programming_languages_other) {
                            $selectedLangs[] = $onboarding->programming_languages_other;
                        }
                        $langsString = implode(', ', $selectedLangs);
                    @endphp
                    <div class="form-field-label">{{ $skillsLabel }}</div>
                    <div class="form-field-value">{{ $langsString ?: 'None listed.' }}</div>
                </div>

                <div class="col-6">
                    @php
                        $selectedTools = $onboarding->design_tools ?? [];
                        if ($onboarding->design_tools_other) {
                            $selectedTools[] = $onboarding->design_tools_other;
                        }
                        $toolsString = implode(', ', $selectedTools);
                    @endphp
                    <div class="form-field-label">{{ $toolsLabel }}</div>
                    <div class="form-field-value">{{ $toolsString ?: 'None listed.' }}</div>
                </div>

                <div class="col-12">
                    <div class="form-field-label">Describe Any Projects Completed</div>
                    <div class="form-field-value" style="border-bottom: none; background: #f8fafc; padding: 10px; border-radius: 4px; min-height: 50px;">
                        {{ $onboarding->completed_projects ?? 'None listed.' }}
                    </div>
                </div>
            </div>
        </div>

        <!-- SECTION 7 -->
        <div class="section-block">
            <div class="section-header">Section 7 – Document Checklist</div>
            <div class="row g-2 mb-2">
                @php
                    $uploadedDocs = $onboarding->intern->uploadedDocuments->pluck('title')->toArray();
                    if ($onboarding->intern->photo && !in_array('Passport Size Photograph', $uploadedDocs)) {
                        $uploadedDocs[] = 'Passport Size Photograph';
                    }
                    $checklist = ['Aadhaar Card', 'Passport Size Photograph', 'Resume / CV', 'College ID Card', 'Bonafide Certificate', 'Internship Request Letter', 'Academic Project Details', 'Previous Internship Certificates'];
                    $tickedDocs = array_filter($checklist, function($doc) use ($uploadedDocs) {
                        return in_array($doc, $uploadedDocs);
                    });
                    $docsString = implode(', ', $tickedDocs);
                @endphp
                <div class="col-12">
                    <div class="form-field-label">Attached & Confirmed Documents</div>
                    <div class="form-field-value">{{ $docsString ?: 'None confirmed.' }}</div>
                </div>
            </div>
        </div>

        <!-- SECTION 8 -->
        <div class="section-block">
            <div class="section-header">Section 8 – Company Access Requirements (Completed by HR / Mentor)</div>
            <div class="row g-2 mb-2">
                @php
                    $selectedAccess = $onboarding->company_access_requirements ?? [];
                    if ($onboarding->company_access_other) {
                        $selectedAccess[] = $onboarding->company_access_other;
                    }
                    $accessString = implode(', ', $selectedAccess);
                @endphp
                <div class="col-12">
                    <div class="form-field-label">Granted Company Access</div>
                    <div class="form-field-value">{{ $accessString ?: 'None.' }}</div>
                </div>
            </div>
        </div>

        <!-- SECTION 9 -->
        <div class="section-block">
            <div class="section-header">Section 9 – Asset Requirement</div>
            <div class="row g-2 mb-2">
                @php
                    $selectedAssets = $onboarding->assets_issued ?? [];
                    $assetsString = implode(', ', $selectedAssets);
                @endphp
                <div class="col-12">
                    <div class="form-field-label">Assets Issued</div>
                    <div class="form-field-value">{{ $assetsString ?: 'None.' }}</div>
                </div>
                @if($onboarding->assets_remarks)
                    <div class="col-12 mt-2">
                        <div class="form-field-label">Remarks / Notes</div>
                        <div class="form-field-value">{{ $onboarding->assets_remarks }}</div>
                    </div>
                @endif
            </div>
        </div>

        <!-- Page break to keep Section 10 and 11 on the same page -->
        <div style="page-break-after: always;"></div>

        <!-- SECTION 10 -->
        <div class="section-block">
            <div class="section-header">Section 10 – Learning Objectives</div>
            <div class="row g-3 mb-2">
                <div class="col-12">
                    <div class="form-field-label">What do you expect to learn from this internship?</div>
                    <div class="form-field-value" style="border-bottom: none; background: #f8fafc; padding: 10px; border-radius: 4px;">
                        {{ $onboarding->learning_expectations }}
                    </div>
                </div>
                <div class="col-12">
                    <div class="form-field-label">Career Goal</div>
                    <div class="form-field-value" style="border-bottom: none; background: #f8fafc; padding: 10px; border-radius: 4px;">
                        {{ $onboarding->career_goal }}
                    </div>
                </div>
            </div>
        </div>

        <!-- SECTION 11 -->
        <div class="section-block">
            <div class="section-header">Section 11 – Intern Declaration</div>
            <div style="font-size: 10px; line-height: 1.5; text-align: justify; color: #475569;" class="mb-3">
                I hereby confirm that the information provided by me is true and accurate. I understand that:
                (1) This internship is primarily a learning and skill-development program.
                (2) I shall comply with all Techsoul Cyber Solutions policies and procedures.
                (3) I shall maintain confidentiality regarding company projects, source code, client information, and internal operations.
                (4) All work, software, designs, content, documentation, and projects developed during the internship shall remain the property of Techsoul Cyber Solutions.
                (5) Completion Certificate and Internship Certificate shall be issued subject to satisfactory performance, attendance, discipline, and successful completion of assigned tasks.
            </div>
            <div class="row g-4 mt-1 mb-3">
                <div class="col-5">
                    <div class="form-field-label">Intern Name</div>
                    <div class="form-field-value">{{ $onboarding->intern->name }}</div>
                </div>
                <div class="col-4">
                    <div class="form-field-label">Signature</div>
                    <div class="form-field-value" style="border-bottom: 1px solid #000; height: 24px;">&nbsp;</div>
                </div>
                <div class="col-3">
                    <div class="form-field-label">Date</div>
                    <div class="form-field-value">{{ $onboarding->signature_date ? $onboarding->signature_date->format('d / m / Y') : '____ / ____ / ________' }}</div>
                </div>
            </div>
        </div>

        <!-- OFFICE USE BOX -->
        <div class="office-use-box section-block">
            <div class="text-uppercase fw-bold border-bottom pb-1 mb-3 border-dark" style="font-size: 12px; letter-spacing: 0.5px;">For Office Use Only</div>
            <div class="row g-3">
                <div class="col-4">
                    <div class="form-field-label">Intern ID</div>
                    <div class="form-field-value">TSL-{{ str_pad(14737 + $onboarding->intern->id, 6, '0', STR_PAD_LEFT) }}</div>
                </div>
                <div class="col-4">
                    <div class="form-field-label">Department</div>
                    <div class="form-field-value">{{ $onboarding->intern->department->name ?? 'N/A' }}</div>
                </div>
                <div class="col-4">
                    <div class="form-field-label">Internship Domain</div>
                    <div class="form-field-value">{{ $onboarding->office_use_domain ?? 'N/A' }}</div>
                </div>
                <div class="col-6">
                    <div class="form-field-label">Mentor Assigned</div>
                    <div class="form-field-value">{{ $onboarding->office_use_mentor_assigned ?? 'N/A' }}</div>
                </div>
                <div class="col-6">
                    <div class="form-field-label">Internship Period</div>
                    <div class="form-field-value">
                        From: {{ $onboarding->intern->joining_date ? $onboarding->intern->joining_date->format('d/m/Y') : '___/___/___' }} 
                        To: {{ $onboarding->intern->end_date ? $onboarding->intern->end_date->format('d/m/Y') : '___/___/___' }}
                    </div>
                </div>
                <div class="col-4">
                    <div class="form-field-label">Certificate Eligible</div>
                    <div class="d-flex gap-3 pt-1">
                        <div class="checkbox-container">
                            <span class="custom-checkbox">{{ $onboarding->office_use_certificate_eligible === true ? '✓' : '' }}</span> Yes
                        </div>
                        <div class="checkbox-container">
                            <span class="custom-checkbox">{{ $onboarding->office_use_certificate_eligible === false ? '✓' : '' }}</span> No
                        </div>
                    </div>
                </div>
                <div class="col-8">
                    <div class="row g-2">
                        <div class="col-4">
                            <div class="signature-line">&nbsp;</div>
                            <div class="text-center" style="font-size: 9px; font-weight: 600; text-transform: uppercase; color: #64748b; letter-spacing: 0.3px; margin-top: 5px;">HR Signature</div>
                        </div>
                        <div class="col-4">
                            <div class="signature-line">&nbsp;</div>
                            <div class="text-center" style="font-size: 9px; font-weight: 600; text-transform: uppercase; color: #64748b; letter-spacing: 0.3px; margin-top: 5px;">Mentor Signature</div>
                        </div>
                        <div class="col-4">
                            <div class="signature-line">&nbsp;</div>
                            <div class="text-center" style="font-size: 9px; font-weight: 600; text-transform: uppercase; color: #64748b; letter-spacing: 0.3px; margin-top: 5px;">Management Approval</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Footer Motto -->
        <div class="text-center mt-4 border-top pt-2" style="font-size: 11px;">
            <strong>TECHSOUL CYBER SOLUTIONS</strong> &ndash; <em>"Learn by Working on Real Projects with Experienced Professionals"</em>
        </div>

    </div>

</body>
</html>
