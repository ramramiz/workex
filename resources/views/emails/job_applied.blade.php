<!DOCTYPE html>
<!-- version: 1.0.4 -->
<html>
<head>
    <meta charset="utf-8">
    <title>Application Received</title>
</head>
<body style="font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; background-color: #0f172a; margin: 0; padding: 40px 0; color: #e2e8f0; -webkit-font-smoothing: antialiased;">
    <table align="center" border="0" cellpadding="0" cellspacing="0" width="100%" style="max-width: 600px; background-color: #1e293b; border-radius: 16px; border: 1px solid rgba(255,255,255,0.06); box-shadow: 0 10px 25px -5px rgba(0,0,0,0.3); overflow: hidden;">
        <!-- Header -->
        <tr>
            <td style="padding: 40px 40px 20px 40px; text-align: center;">
                @php
                    $logo = \App\Models\Setting::get('company_logo');
                @endphp
                @if($logo)
                    <div style="margin-bottom: 20px; text-align: center;">
                        <img src="{{ url('storage/' . $logo) }}" alt="Logo" style="max-height: 60px; object-fit: contain;">
                    </div>
                @else
                    <div style="display: inline-block; background: linear-gradient(135deg, #6366f1, #8b5cf6); padding: 12px; border-radius: 14px; margin-bottom: 20px;">
                        <span style="font-size: 24px; font-weight: 800; color: #ffffff; letter-spacing: 0.5px;">T</span>
                    </div>
                @endif
                <h1 style="font-size: 24px; font-weight: 800; margin: 0; color: #ffffff; letter-spacing: -0.5px;">Application Received</h1>
                <p style="font-size: 14px; color: #94a3b8; margin: 8px 0 0 0;">Techsoul Careers</p>
            </td>
        </tr>

        <!-- Divider -->
        <tr>
            <td style="padding: 0 40px;">
                <hr style="border: 0; border-top: 1px solid rgba(255,255,255,0.06); margin: 0;">
            </td>
        </tr>

        <!-- Content -->
        <tr>
            <td style="padding: 30px 40px;">
                <p style="font-size: 16px; line-height: 1.6; margin-top: 0; color: #cbd5e1;">Dear <strong>{{ $application->name }}</strong>,</p>
                <p style="font-size: 15px; line-height: 1.6; color: #cbd5e1;">Thank you for your interest in joining <strong>Techsoul</strong>. We have successfully received your application for the <strong>{{ $application->vacancy?->title ?? 'Position' }}</strong> role.</p>
                
                <p style="font-size: 15px; line-height: 1.6; color: #cbd5e1;">Our recruitment team is currently reviewing your qualifications and experience. If your profile aligns with our requirements, a member of our HR team will reach out to you for the next steps in our hiring process.</p>
                
                <!-- Application Responses Summary -->
                <h3 style="font-size: 15px; font-weight: 700; color: #ffffff; margin: 30px 0 12px 0; letter-spacing: -0.2px;">Submitted Application Details</h3>
                <table border="0" cellpadding="0" cellspacing="0" width="100%" style="background-color: #0f172a; border-radius: 12px; border: 1px solid rgba(255,255,255,0.08); margin-bottom: 24px; padding: 20px;">
                    <tr>
                        <td style="padding-bottom: 12px; width: 50%; vertical-align: top;">
                            <span style="font-size: 11px; color: #64748b; text-transform: uppercase; font-weight: 700; letter-spacing: 0.5px;">Full Name</span>
                            <div style="font-size: 14px; font-weight: 600; color: #ffffff; margin-top: 4px;">{{ $application->name }}</div>
                        </td>
                        <td style="padding-bottom: 12px; width: 50%; vertical-align: top;">
                            <span style="font-size: 11px; color: #64748b; text-transform: uppercase; font-weight: 700; letter-spacing: 0.5px;">Gender</span>
                            <div style="font-size: 14px; font-weight: 600; color: #ffffff; margin-top: 4px;">{{ $application->gender }}</div>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding-bottom: 12px; vertical-align: top;">
                            <span style="font-size: 11px; color: #64748b; text-transform: uppercase; font-weight: 700; letter-spacing: 0.5px;">Date of Birth</span>
                            <div style="font-size: 14px; font-weight: 600; color: #ffffff; margin-top: 4px;">{{ $application->dob ? \Carbon\Carbon::parse($application->dob)->format('M d, Y') : 'N/A' }}</div>
                        </td>
                        <td style="padding-bottom: 12px; vertical-align: top;">
                            <span style="font-size: 11px; color: #64748b; text-transform: uppercase; font-weight: 700; letter-spacing: 0.5px;">Qualification</span>
                            <div style="font-size: 14px; font-weight: 600; color: #ffffff; margin-top: 4px;">{{ $application->qualification }}</div>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding-bottom: 12px; vertical-align: top;">
                            <span style="font-size: 11px; color: #64748b; text-transform: uppercase; font-weight: 700; letter-spacing: 0.5px;">Email Address</span>
                            <div style="font-size: 14px; font-weight: 600; color: #ffffff; margin-top: 4px;">{{ $application->email }}</div>
                        </td>
                        <td style="padding-bottom: 12px; vertical-align: top;">
                            <span style="font-size: 11px; color: #64748b; text-transform: uppercase; font-weight: 700; letter-spacing: 0.5px;">Phone Number</span>
                            <div style="font-size: 14px; font-weight: 600; color: #ffffff; margin-top: 4px;">{{ $application->phone }}</div>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding-bottom: 12px; vertical-align: top;">
                            <span style="font-size: 11px; color: #64748b; text-transform: uppercase; font-weight: 700; letter-spacing: 0.5px;">Location</span>
                            <div style="font-size: 14px; font-weight: 600; color: #ffffff; margin-top: 4px;">{{ $application->home_town }}, {{ $application->district }}, {{ $application->state }}</div>
                        </td>
                        <td style="padding-bottom: 12px; vertical-align: top;">
                            <span style="font-size: 11px; color: #64748b; text-transform: uppercase; font-weight: 700; letter-spacing: 0.5px;">Relevant Experience</span>
                            <div style="font-size: 14px; font-weight: 600; color: #ffffff; margin-top: 4px;">{{ $application->experience_years }}</div>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding-bottom: 12px; vertical-align: top;">
                            <span style="font-size: 11px; color: #64748b; text-transform: uppercase; font-weight: 700; letter-spacing: 0.5px;">Salary Expectations</span>
                            <div style="font-size: 14px; font-weight: 600; color: #ffffff; margin-top: 4px;">{{ $application->salary_expectation }}</div>
                        </td>
                        <td style="padding-bottom: 12px; vertical-align: top;">
                            <span style="font-size: 11px; color: #64748b; text-transform: uppercase; font-weight: 700; letter-spacing: 0.5px;">Ready to Relocate?</span>
                            <div style="font-size: 14px; font-weight: 600; color: #ffffff; margin-top: 4px;">{{ $application->ready_to_relocate }}</div>
                        </td>
                    </tr>
                    @if($application->linkedin_id)
                    <tr>
                        <td colspan="2" style="padding-bottom: 12px; vertical-align: top;">
                            <span style="font-size: 11px; color: #64748b; text-transform: uppercase; font-weight: 700; letter-spacing: 0.5px;">LinkedIn ID</span>
                            <div style="font-size: 14px; font-weight: 600; color: #38bdf8; margin-top: 4px;">{{ $application->linkedin_id }}</div>
                        </td>
                    </tr>
                    @endif
                    @if($application->cover_letter)
                    <tr>
                        <td colspan="2" style="vertical-align: top;">
                            <span style="font-size: 11px; color: #64748b; text-transform: uppercase; font-weight: 700; letter-spacing: 0.5px;">Cover Letter</span>
                            <div style="font-size: 13px; color: #cbd5e1; line-height: 1.5; margin-top: 6px; padding: 12px; background-color: #1e293b; border-radius: 6px; border: 1px solid rgba(255,255,255,0.05); white-space: pre-line;">{{ $application->cover_letter }}</div>
                        </td>
                    </tr>
                    @endif
                </table>
                
                <!-- Contact Info Box -->
                <table border="0" cellpadding="0" cellspacing="0" width="100%" style="background-color: #0f172a; border-radius: 12px; border: 1px solid rgba(255,255,255,0.08); margin-bottom: 24px; padding: 20px;">
                    <tr>
                        <td style="padding-bottom: 12px;">
                            <span style="font-size: 12px; color: #64748b; text-transform: uppercase; font-weight: 700; letter-spacing: 0.5px;">Applied Position</span>
                            <div style="font-size: 15px; font-weight: 600; color: #ffffff; margin-top: 4px;">{{ $application->jobVacancy?->title ?? 'Job Position' }}</div>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding-bottom: 12px;">
                            <span style="font-size: 12px; color: #64748b; text-transform: uppercase; font-weight: 700; letter-spacing: 0.5px;">Contact Support Phone</span>
                            <div style="font-size: 15px; font-weight: 600; color: #38bdf8; margin-top: 4px;">+91 88487 87656</div>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <span style="font-size: 12px; color: #64748b; text-transform: uppercase; font-weight: 700; letter-spacing: 0.5px;">Contact Support Email</span>
                            <div style="font-size: 15px; font-weight: 600; color: #38bdf8; margin-top: 4px;"><a href="mailto:hr@teamtechsoul.com" style="color: #38bdf8; text-decoration: none;">hr@teamtechsoul.com</a></div>
                        </td>
                    </tr>
                </table>

                <p style="font-size: 15px; line-height: 1.6; color: #cbd5e1;">We appreciate the time you took to share your credentials with us and wish you the best of luck with your application.</p>
                
                <p style="font-size: 15px; line-height: 1.6; color: #cbd5e1; margin-bottom: 0;">Warm regards,<br><strong>The Recruitment Team</strong></p>
            </td>
        </tr>

        <!-- Footer -->
        <tr>
            <td style="padding: 20px 40px 40px 40px; text-align: center; font-size: 12px; color: #64748b;">
                <p style="margin: 0 0 8px 0;">This is an automated email. Please do not reply directly to this message.</p>
                <p style="margin: 0;">&copy; {{ date('Y') }} Techsoul. All rights reserved.</p>
            </td>
        </tr>
    </table>
</body>
</html>
