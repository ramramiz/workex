<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Mail\PasswordResetOtpMail;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class PasswordResetLinkController extends Controller
{
    /**
     * Display the password reset view.
     */
    public function create(Request $request): View
    {
        if ($request->has('change_email')) {
            session()->forget(['reset_email', 'reset_otp_sent']);
            // Redirect to clean URL to remove the parameter from browser address bar
            return view('auth.forgot-password', [
                'reset_email' => null,
                'reset_otp_sent' => false,
            ]);
        }

        $reset_email = session('reset_email') ?: old('email');
        $reset_otp_sent = (bool) (session('reset_otp_sent', false));

        return view('auth.forgot-password', compact('reset_email', 'reset_otp_sent'));
    }

    /**
     * Handle password reset OTP generation or verification.
     */
    public function store(Request $request): RedirectResponse
    {
        // Case A: Verify OTP & Reset Password
        if ($request->has('otp') || $request->filled('password')) {
            $request->validate([
                'email' => ['required', 'email', 'exists:users,email'],
                'otp' => ['required', 'string'],
                'password' => ['required', 'string', 'min:8', 'confirmed'],
            ]);

            $email = $request->input('email');
            $inputOtp = $request->input('otp');
            $cachedOtp = Cache::get('password_reset_otp_' . $email);

            if (!$cachedOtp || $inputOtp != $cachedOtp) {
                return redirect()->route('password.request')->withInput()->with([
                    'reset_email' => $email,
                    'reset_otp_sent' => true
                ])->withErrors([
                    'otp' => 'The provided One-Time Password (OTP) is incorrect or has expired.'
                ]);
            }

            // Reset password
            $user = User::where('email', $email)->first();
            $user->update([
                'password' => Hash::make($request->input('password'))
            ]);

            // Clear cache and session
            Cache::forget('password_reset_otp_' . $email);
            session()->forget(['reset_email', 'reset_otp_sent']);

            return redirect()->route('login')->with('status', 'Your password has been successfully reset. Please log in with your new password.');
        }

        // Case B: Request / Resend OTP
        $request->validate([
            'email' => ['required', 'email', 'exists:users,email'],
        ], [
            'email.exists' => 'We couldn\'t find a user with that email address.'
        ]);

        $email = $request->input('email');

        // Generate 6-digit OTP
        $otp = (string) rand(100000, 999999);
        Cache::put('password_reset_otp_' . $email, $otp, now()->addMinutes(10));

        // Log locally for debugging/retrieve if mail delivery has issues
        \Illuminate\Support\Facades\Log::info("Generated Password Reset OTP for {$email}: {$otp}");

        try {
            Mail::to($email)->send(new PasswordResetOtpMail($otp));
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning("Failed to send Password Reset OTP email to {$email}: " . $e->getMessage());
            return redirect()->route('password.request')->withInput()->withErrors([
                'email' => 'Failed to send OTP email. Please check your SMTP settings or try again later.'
            ]);
        }

        // Explicitly set in persistent session & flash it on redirect
        session([
            'reset_email' => $email,
            'reset_otp_sent' => true
        ]);

        return redirect()->route('password.request')->with([
            'reset_email' => $email,
            'reset_otp_sent' => true,
            'status' => 'A One-Time Password (OTP) has been sent to your email.'
        ]);
    }
}
