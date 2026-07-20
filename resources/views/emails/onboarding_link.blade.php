<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Internship Onboarding Form - Techsoul Cyber Solutions</title>
</head>
<body style="font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; background-color: #0f172a; margin: 0; padding: 40px 0; color: #e2e8f0; -webkit-font-smoothing: antialiased;">
    <table align="center" border="0" cellpadding="0" cellspacing="0" width="100%" style="max-width: 600px; background-color: #1e293b; border-radius: 16px; border: 1px solid rgba(255,255,255,0.06); box-shadow: 0 10px 25px -5px rgba(0,0,0,0.3); overflow: hidden;">
        <!-- Header -->
        <tr>
            <td style="padding: 40px 40px 20px 40px; text-align: center;">
                <div style="display: inline-block; background: linear-gradient(135deg, #10b981, #059669); padding: 12px; border-radius: 14px; margin-bottom: 20px;">
                    <span style="font-size: 24px; font-weight: 800; color: #ffffff; letter-spacing: 0.5px;">TSL</span>
                </div>
                <h1 style="font-size: 24px; font-weight: 800; margin: 0; color: #ffffff; letter-spacing: -0.5px;">Intern Onboarding & Information Form</h1>
                <p style="font-size: 14px; color: #10b981; margin: 8px 0 0 0; font-weight: 600; text-transform: uppercase; letter-spacing: 1px;">Techsoul Cyber Solutions</p>
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
                <p style="font-size: 16px; line-height: 1.6; margin-top: 0; color: #cbd5e1;">Dear <strong>{{ $onboarding->intern->name }}</strong>,</p>
                <p style="font-size: 15px; line-height: 1.6; color: #cbd5e1;">Congratulations on your selection! We are excited to welcome you as an intern at Techsoul Cyber Solutions.</p>
                <p style="font-size: 15px; line-height: 1.6; color: #cbd5e1;">To begin your onboarding process, please click the link below to access the **Intern Onboarding & Information Form**. You will need to fill in your personal, educational, family, emergency details, and upload the required documents (such as Aadhaar, Resume, Photo, and College details).</p>
                
                <!-- Info Table -->
                <table border="0" cellpadding="0" cellspacing="0" width="100%" style="background-color: #0f172a; border-radius: 12px; border: 1px solid rgba(255,255,255,0.08); margin: 24px 0; padding: 20px;">
                    <tr>
                        <td style="padding-bottom: 12px; width: 50%;">
                            <span style="font-size: 11px; color: #64748b; text-transform: uppercase; font-weight: 700; letter-spacing: 0.5px;">Position</span>
                            <div style="font-size: 14px; font-weight: 600; color: #ffffff; margin-top: 4px;">{{ $onboarding->intern->designation->name ?? 'Intern' }}</div>
                        </td>
                        <td style="padding-bottom: 12px; width: 50%;">
                            <span style="font-size: 11px; color: #64748b; text-transform: uppercase; font-weight: 700; letter-spacing: 0.5px;">Department</span>
                            <div style="font-size: 14px; font-weight: 600; color: #ffffff; margin-top: 4px;">{{ $onboarding->intern->department->name ?? 'N/A' }}</div>
                        </td>
                    </tr>
                    <tr>
                        <td style="width: 50%;">
                            <span style="font-size: 11px; color: #64748b; text-transform: uppercase; font-weight: 700; letter-spacing: 0.5px;">Joining Date</span>
                            <div style="font-size: 14px; font-weight: 600; color: #ffffff; margin-top: 4px;">{{ $onboarding->intern->joining_date ? $onboarding->intern->joining_date->format('d M Y') : 'N/A' }}</div>
                        </td>
                        <td style="width: 50%;">
                            <span style="font-size: 11px; color: #64748b; text-transform: uppercase; font-weight: 700; letter-spacing: 0.5px;">Duration</span>
                            <div style="font-size: 14px; font-weight: 600; color: #ffffff; margin-top: 4px;">
                                @if($onboarding->intern->joining_date && $onboarding->intern->end_date)
                                    {{ $onboarding->intern->joining_date->diffInMonths($onboarding->intern->end_date) }} Month(s)
                                @else
                                    N/A
                                @endif
                            </div>
                        </td>
                    </tr>
                </table>

                <p style="font-size: 14px; line-height: 1.5; color: #94a3b8;"><em>Please complete this form at your earliest convenience to avoid any delays in your onboarding and system setups.</em></p>

                <!-- CTA Button -->
                <div style="text-align: center; margin: 35px 0 10px 0;">
                    <a href="{{ $link }}" target="_blank" style="display: inline-block; background: linear-gradient(135deg, #10b981, #059669); color: #ffffff; text-decoration: none; padding: 14px 30px; font-size: 15px; font-weight: 600; border-radius: 10px; box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3); transition: all 0.2s;">Start Onboarding Form</a>
                </div>
            </td>
        </tr>

        <!-- Footer -->
        <tr>
            <td style="padding: 20px 40px 40px 40px; text-align: center; font-size: 12px; color: #64748b;">
                <p style="margin: 0 0 8px 0;">This is a secure onboarding request from Techsoul Cyber Solutions. Please do not share this link.</p>
                <p style="margin: 0;">&copy; {{ date('Y') }} Techsoul Cyber Solutions. All rights reserved.</p>
            </td>
        </tr>
    </table>
</body>
</html>
