<!DOCTYPE html>
<!-- version: 1.0.4 -->
<html>
<head>
    <meta charset="utf-8">
    <title>Interview Invitation</title>
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
                <h1 style="font-size: 24px; font-weight: 800; margin: 0; color: #ffffff; letter-spacing: -0.5px;">Interview Invitation</h1>
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
                
                <p style="font-size: 15px; line-height: 1.6; color: #cbd5e1;">Thank you for your application for the <strong>{{ $application->vacancy?->title ?? 'Position' }}</strong> position at <strong>Techsoul</strong>.</p>
                
                <p style="font-size: 15px; line-height: 1.6; color: #cbd5e1;">We were highly impressed by your qualifications and experience, and we would like to invite you for an interview to discuss this opportunity further.</p>
                
                <!-- Interview Schedule Box -->
                <h3 style="font-size: 15px; font-weight: 700; color: #ffffff; margin: 30px 0 12px 0; letter-spacing: -0.2px;">Interview Details</h3>
                <table border="0" cellpadding="0" cellspacing="0" width="100%" style="background-color: #0f172a; border-radius: 12px; border: 1px solid rgba(255,255,255,0.08); margin-bottom: 24px; padding: 20px;">
                    <tr>
                        <td style="padding-bottom: 12px; vertical-align: top;">
                            <span style="font-size: 11px; color: #64748b; text-transform: uppercase; font-weight: 700; letter-spacing: 0.5px;">Interview Date</span>
                            <div style="font-size: 14px; font-weight: 600; color: #ffffff; margin-top: 4px;">{{ \Carbon\Carbon::parse($date)->format('F d, Y (l)') }}</div>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding-bottom: 12px; vertical-align: top;">
                            <span style="font-size: 11px; color: #64748b; text-transform: uppercase; font-weight: 700; letter-spacing: 0.5px;">Time</span>
                            <div style="font-size: 14px; font-weight: 600; color: #ffffff; margin-top: 4px;">{{ \Carbon\Carbon::parse($time)->format('h:i A') }}</div>
                        </td>
                    </tr>
                    <tr>
                        <td style="vertical-align: top;">
                            <span style="font-size: 11px; color: #64748b; text-transform: uppercase; font-weight: 700; letter-spacing: 0.5px;">Venue / Location</span>
                            <div style="font-size: 14px; font-weight: 600; color: #38bdf8; margin-top: 4px; line-height: 1.5; white-space: pre-line;">{{ $venue }}</div>
                        </td>
                    </tr>
                </table>

                <p style="font-size: 15px; line-height: 1.6; color: #cbd5e1;">Please review these details and let us know if you will be able to attend at the scheduled time. If you need to reschedule or have any questions, feel free to contact us.</p>
                
                <p style="font-size: 15px; line-height: 1.6; color: #cbd5e1; margin-bottom: 0;">We look forward to meeting with you!</p>
                
                <!-- Contact Support Box -->
                <table border="0" cellpadding="0" cellspacing="0" width="100%" style="background-color: #0f172a; border-radius: 12px; border: 1px solid rgba(255,255,255,0.08); margin: 24px 0 0 0; padding: 20px;">
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
