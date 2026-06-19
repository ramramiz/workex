<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;
use App\Models\User;
use App\Mail\OtpMail;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(Request $request): View
    {
        $email = $request->input('email') ?: old('email') ?: session('otp_email');
        $attempts = 0;
        
        if ($email) {
            $attempts = Cache::get('login_attempts_' . $email, 0);
        }

        $captcha_question = null;
        if ($attempts == 3) {
            if (!session()->has('captcha_answer')) {
                $num1 = rand(1, 9);
                $num2 = rand(1, 9);
                session([
                    'captcha_num1' => $num1,
                    'captcha_num2' => $num2,
                    'captcha_answer' => $num1 + $num2
                ]);
            }
            $captcha_question = "What is " . session('captcha_num1') . " + " . session('captcha_num2') . "?";
        }

        $otp_required = session('otp_required') || ($email && $attempts >= 4);
        $otp_email = session('otp_email') ?: $email;

        return view('auth.login', compact('captcha_question', 'otp_required', 'otp_email'));
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $email = $request->input('email');
        $attempts = Cache::get('login_attempts_' . $email, 0);

        // A. Resend OTP request
        if ($request->input('resend_otp') == '1') {
            $user = User::where('email', $email)->first();
            if ($user) {
                $otp = rand(100000, 999999);
                Cache::put('login_otp_' . $email, $otp, now()->addMinutes(10));
                
                try {
                    Mail::to($email)->send(new OtpMail($otp));
                } catch (\Throwable $e) {
                    \Illuminate\Support\Facades\Log::warning("Failed to send OTP email: " . $e->getMessage());
                }
            }
            return back()->withInput()->with([
                'otp_required' => true,
                'otp_email' => $email,
                'status' => 'A new One-Time Password (OTP) code has been sent to your email.',
            ]);
        }

        // B. OTP Verification or Locked Out -> OTP forced
        if ($attempts >= 4 || $request->has('otp') || session('otp_required')) {
            if ($request->filled('otp')) {
                $inputOtp = $request->input('otp');
                $cachedOtp = Cache::get('login_otp_' . $email);

                if ($cachedOtp && $inputOtp == $cachedOtp) {
                    $user = User::where('email', $email)->first();
                    if ($user) {
                        Auth::login($user, $request->boolean('remember'));

                        // Success - Clear attempts and OTP
                        Cache::forget('login_attempts_' . $email);
                        Cache::forget('login_otp_' . $email);
                        session()->forget(['captcha_num1', 'captcha_num2', 'captcha_answer', 'otp_required', 'otp_email']);
                        
                        $request->session()->regenerate();

                        return redirect()->intended(route('dashboard', absolute: false));
                    }
                }

                // OTP check failed
                return back()->withInput()->with([
                    'otp_required' => true,
                    'otp_email' => $email,
                    'warning' => 'The provided One-Time Password (OTP) is incorrect or has expired.',
                ])->withErrors([
                    'otp' => 'Incorrect OTP code.',
                ]);
            } else {
                // Email is locked or OTP mode active, but OTP code is not filled in request
                $user = User::where('email', $email)->first();
                if ($user) {
                    $otp = rand(100000, 999999);
                    Cache::put('login_otp_' . $email, $otp, now()->addMinutes(10));
                    
                    try {
                        Mail::to($email)->send(new OtpMail($otp));
                    } catch (\Throwable $e) {
                        \Illuminate\Support\Facades\Log::warning("Failed to send OTP email: " . $e->getMessage());
                    }
                }

                return back()->withInput()->with([
                    'otp_required' => true,
                    'otp_email' => $email,
                    'warning' => 'Too many failed login attempts. A One-Time Password (OTP) has been sent to your email.',
                ]);
            }
        }

        // C. CAPTCHA Verification Stage (on 3rd failure)
        if ($attempts == 3) {
            $request->validate([
                'captcha' => ['required', 'integer'],
            ]);

            $correctAnswer = session('captcha_answer');
            if ($request->input('captcha') != $correctAnswer) {
                // Incorrect CAPTCHA counts as the 4th failure!
                $attempts = 4;
                Cache::put('login_attempts_' . $email, $attempts, now()->addMinutes(30));

                // Send OTP immediately
                $user = User::where('email', $email)->first();
                if ($user) {
                    $otp = rand(100000, 999999);
                    Cache::put('login_otp_' . $email, $otp, now()->addMinutes(10));
                    
                    try {
                        Mail::to($email)->send(new OtpMail($otp));
                    } catch (\Throwable $e) {
                        \Illuminate\Support\Facades\Log::warning("Failed to send OTP email: " . $e->getMessage());
                    }
                }

                session()->forget(['captcha_num1', 'captcha_num2', 'captcha_answer']);

                return back()->withInput()->with([
                    'otp_required' => true,
                    'otp_email' => $email,
                    'error' => 'Incorrect CAPTCHA answer. Too many failed attempts. An OTP has been sent to your email.',
                ]);
            }
        }

        // D. Normal Login Authentication
        try {
            $request->authenticate();

            // Success - Clear attempts counter
            Cache::forget('login_attempts_' . $email);
            session()->forget(['captcha_num1', 'captcha_num2', 'captcha_answer', 'otp_required', 'otp_email']);

            $request->session()->regenerate();

            return redirect()->intended(route('dashboard', absolute: false));

        } catch (\Illuminate\Validation\ValidationException $e) {
            // Failure - Increment attempts counter
            $attempts = Cache::get('login_attempts_' . $email, 0) + 1;
            Cache::put('login_attempts_' . $email, $attempts, now()->addMinutes(30));

            if ($attempts == 3) {
                // Generate CAPTCHA parameters
                $num1 = rand(1, 9);
                $num2 = rand(1, 9);
                session([
                    'captcha_num1' => $num1,
                    'captcha_num2' => $num2,
                    'captcha_answer' => $num1 + $num2
                ]);
            } elseif ($attempts >= 4) {
                // Send OTP immediately
                $user = User::where('email', $email)->first();
                if ($user) {
                    $otp = rand(100000, 999999);
                    Cache::put('login_otp_' . $email, $otp, now()->addMinutes(10));
                    
                    try {
                        Mail::to($email)->send(new OtpMail($otp));
                    } catch (\Throwable $e) {
                        \Illuminate\Support\Facades\Log::warning("Failed to send OTP email: " . $e->getMessage());
                    }
                }

                session()->forget(['captcha_num1', 'captcha_num2', 'captcha_answer']);

                return back()->withInput()->with([
                    'otp_required' => true,
                    'otp_email' => $email,
                    'error' => 'Incorrect credentials. Too many failed attempts. An OTP has been sent to your email.',
                ]);
            }

            throw $e;
        }
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}
