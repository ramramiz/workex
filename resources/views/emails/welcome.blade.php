<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Welcome to WorkeX</title>
</head>
<body style="font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; background-color: #0f172a; margin: 0; padding: 40px 0; color: #e2e8f0; -webkit-font-smoothing: antialiased;">
    <table align="center" border="0" cellpadding="0" cellspacing="0" width="100%" style="max-width: 600px; background-color: #1e293b; border-radius: 16px; border: 1px solid rgba(255,255,255,0.06); box-shadow: 0 10px 25px -5px rgba(0,0,0,0.3); overflow: hidden;">
        <!-- Header -->
        <tr>
            <td style="padding: 40px 40px 20px 40px; text-align: center;">
                <div style="display: inline-block; background: linear-gradient(135deg, #6366f1, #8b5cf6); padding: 12px; border-radius: 14px; margin-bottom: 20px;">
                    <span style="font-size: 24px; font-weight: 800; color: #ffffff; letter-spacing: 0.5px;">W</span>
                </div>
                <h1 style="font-size: 26px; font-weight: 800; margin: 0; color: #ffffff; letter-spacing: -0.5px;">Welcome to <span style="color: #818cf8;">WorkeX</span></h1>
                <p style="font-size: 14px; color: #94a3b8; margin: 8px 0 0 0;">By Techsoul</p>
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
                <p style="font-size: 16px; line-height: 1.6; margin-top: 0; color: #cbd5e1;">Hello <strong>{{ $user->name }}</strong>,</p>
                <p style="font-size: 15px; line-height: 1.6; color: #cbd5e1;">Welcome to the team! An account has been created for you on the <strong>WorkeX Team Management Portal</strong>. You can now log in using the credentials below to track your tasks, projects, and attendance.</p>
                
                <!-- Credentials Card -->
                <table border="0" cellpadding="0" cellspacing="0" width="100%" style="background-color: #0f172a; border-radius: 12px; border: 1px solid rgba(255,255,255,0.08); margin: 24px 0; padding: 20px;">
                    <tr>
                        <td style="padding-bottom: 12px;">
                            <span style="font-size: 12px; color: #64748b; text-transform: uppercase; font-weight: 700; letter-spacing: 0.5px;">Username / Email</span>
                            <div style="font-size: 15px; font-weight: 600; color: #ffffff; margin-top: 4px;">{{ $user->email }}</div>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <span style="font-size: 12px; color: #64748b; text-transform: uppercase; font-weight: 700; letter-spacing: 0.5px;">Temporary Password</span>
                            <div style="font-size: 15px; font-weight: 600; color: #38bdf8; font-family: monospace; margin-top: 4px; letter-spacing: 1px;">{{ $password }}</div>
                        </td>
                    </tr>
                </table>

                <p style="font-size: 14px; line-height: 1.5; color: #94a3b8;"><em>Note: For security reasons, please change your password immediately after logging in for the first time.</em></p>

                <!-- CTA Button -->
                <div style="text-align: center; margin: 35px 0 10px 0;">
                    <a href="{{ config('app.url') }}" target="_blank" style="display: inline-block; background: linear-gradient(135deg, #6366f1, #4f46e5); color: #ffffff; text-decoration: none; padding: 14px 30px; font-size: 15px; font-weight: 600; border-radius: 10px; box-shadow: 0 4px 12px rgba(99, 102, 241, 0.3); transition: all 0.2s;">Log In to WorkeX</a>
                </div>
            </td>
        </tr>

        <!-- Footer -->
        <tr>
            <td style="padding: 20px 40px 40px 40px; text-align: center; font-size: 12px; color: #64748b;">
                <p style="margin: 0 0 8px 0;">This is an automated email from WorkeX. Please do not reply directly.</p>
                <p style="margin: 0;">&copy; {{ date('Y') }} Techsoul. All rights reserved.</p>
            </td>
        </tr>
    </table>
</body>
</html>
