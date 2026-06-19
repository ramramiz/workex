<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Your One-Time Password</title>
</head>
<body style="font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; background-color: #0f172a; margin: 0; padding: 40px 0; color: #e2e8f0; -webkit-font-smoothing: antialiased;">
    <table align="center" border="0" cellpadding="0" cellspacing="0" width="100%" style="max-width: 500px; background-color: #1e293b; border-radius: 16px; border: 1px solid rgba(255,255,255,0.06); box-shadow: 0 10px 25px -5px rgba(0,0,0,0.3); overflow: hidden;">
        <!-- Header -->
        <tr>
            <td style="padding: 40px 40px 20px 40px; text-align: center;">
                <div style="display: inline-block; background: linear-gradient(135deg, #ef4444, #f59e0b); padding: 12px; border-radius: 14px; margin-bottom: 20px;">
                    <span style="font-size: 24px; font-weight: 800; color: #ffffff; letter-spacing: 0.5px;">🔑</span>
                </div>
                <h1 style="font-size: 22px; font-weight: 800; margin: 0; color: #ffffff; letter-spacing: -0.5px;">Verification Code</h1>
                <p style="font-size: 13px; color: #94a3b8; margin: 6px 0 0 0;">WorkeX Secure Verification</p>
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
                <p style="font-size: 15px; line-height: 1.6; color: #cbd5e1; margin-top: 0;">We detected multiple failed login attempts on your account. To verify your identity, please use the following One-Time Password (OTP):</p>
                
                <!-- OTP Display Block -->
                <div style="text-align: center; margin: 30px 0; background-color: #0f172a; padding: 20px; border-radius: 12px; border: 1px solid rgba(255,255,255,0.08);">
                    <span style="font-size: 32px; font-weight: 800; color: #ffffff; font-family: 'Courier New', Courier, monospace; letter-spacing: 6px; text-shadow: 0 2px 4px rgba(0,0,0,0.5);">{{ $otp }}</span>
                </div>

                <p style="font-size: 14px; line-height: 1.5; color: #94a3b8;">This code is valid for **10 minutes**. Please do not share this code with anyone.</p>
                <p style="font-size: 13px; line-height: 1.5; color: #64748b; margin-top: 20px;">If you did not request this verification, please contact your administrator immediately.</p>
            </td>
        </tr>

        <!-- Footer -->
        <tr>
            <td style="padding: 20px 40px 40px 40px; text-align: center; font-size: 12px; color: #64748b;">
                <p style="margin: 0 0 8px 0;">This is a secure automated notification from WorkeX.</p>
                <p style="margin: 0;">&copy; {{ date('Y') }} Techsoul. All rights reserved.</p>
            </td>
        </tr>
    </table>
</body>
</html>
