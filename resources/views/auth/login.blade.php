<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login — {{ config('app.name') }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background: #0f172a;
            min-height: 100vh;
            display: flex;
            overflow: hidden;
        }

        /* Left panel */
        .auth-left {
            flex: 1;
            height: 100vh;
            background: linear-gradient(135deg, #0f172a 0%, #1e1b4b 50%, #0f172a 100%);
            display: flex;
            flex-direction: column;
            justify-content: flex-start;
            align-items: center;
            padding: 40px 60px;
            position: relative;
            overflow-y: auto;
        }

        .auth-left::-webkit-scrollbar {
            width: 6px;
        }
        .auth-left::-webkit-scrollbar-track {
            background: rgba(255, 255, 255, 0.02);
        }
        .auth-left::-webkit-scrollbar-thumb {
            background: rgba(255, 255, 255, 0.1);
            border-radius: 3px;
        }
        .auth-left::-webkit-scrollbar-thumb:hover {
            background: rgba(255, 255, 255, 0.2);
        }

        .auth-left::before {
            content: '';
            position: absolute;
            width: 600px; height: 600px;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(99,102,241,0.15) 0%, transparent 70%);
            top: -100px; left: -100px;
        }

        .auth-left::after {
            content: '';
            position: absolute;
            width: 400px; height: 400px;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(139,92,246,0.1) 0%, transparent 70%);
            bottom: -100px; right: -100px;
        }

        .auth-brand {
            display: flex;
            align-items: center;
            gap: 14px;
            margin-bottom: 30px;
            position: relative;
            z-index: 1;
        }

        .auth-brand-icon {
            width: 50px; height: 50px;
            background: linear-gradient(135deg, #6366f1, #8b5cf6);
            border-radius: 14px;
            display: flex; align-items: center; justify-content: center;
            font-size: 24px; color: white;
        }

        .auth-brand-name {
            font-size: 24px; font-weight: 800; color: white;
        }
        .auth-brand-name span { color: #818cf8; }

        .auth-hero { position: relative; z-index: 1; text-align: center; max-width: 440px; }
        .auth-hero h1 { font-size: 40px; font-weight: 800; color: white; line-height: 1.2; margin-bottom: 16px; }
        .auth-hero h1 span { background: linear-gradient(135deg, #6366f1, #a78bfa); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text; }
        .auth-hero p { font-size: 16px; color: #94a3b8; line-height: 1.7; }

        .auth-features { display: flex; flex-direction: column; gap: 12px; margin-top: 30px; position: relative; z-index: 1; width: 100%; max-width: 360px; }
        .auth-feature-item { display: flex; align-items: center; gap: 14px; padding: 10px 16px; background: rgba(255,255,255,0.04); border-radius: 12px; border: 1px solid rgba(255,255,255,0.06); }
        .auth-feature-icon { width: 32px; height: 32px; border-radius: 8px; display: flex; align-items: center; justify-content: center; font-size: 16px; flex-shrink: 0; }
        .auth-feature-text { font-size: 13px; color: #cbd5e1; font-weight: 500; }

        /* Left Quick Login Card Styles */
        .left-quick-login {
            background: rgba(255, 255, 255, 0.03);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-radius: 16px;
            padding: 20px;
            width: 100%;
            max-width: 440px;
            margin-top: 30px;
            position: relative;
            z-index: 1;
        }
        .quick-title {
            font-size: 13px;
            font-weight: 700;
            color: #818cf8;
            margin-bottom: 14px;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .quick-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 10px;
        }
        .btn-glass {
            background: rgba(255, 255, 255, 0.04);
            border: 1px solid rgba(255, 255, 255, 0.08);
            color: #e2e8f0;
            font-size: 12.5px;
            font-weight: 600;
            padding: 10px 12px;
            border-radius: 10px;
            transition: all 0.2s;
            display: flex;
            align-items: center;
            gap: 8px;
            text-decoration: none;
            justify-content: flex-start;
        }
        .btn-glass:hover {
            background: rgba(255, 255, 255, 0.1);
            border-color: rgba(255, 255, 255, 0.2);
            color: #ffffff;
            transform: translateY(-1px);
        }
        .btn-glass i {
            font-size: 14px;
        }
        .btn-glass.admin-btn:hover { border-color: rgba(239, 68, 68, 0.4); color: #f87171; }
        .btn-glass.pm-btn:hover { border-color: rgba(139, 92, 246, 0.4); color: #c084fc; }
        .btn-glass.leader-btn:hover { border-color: rgba(59, 130, 246, 0.4); color: #60a5fa; }
        .btn-glass.dev-btn:hover { border-color: rgba(16, 185, 129, 0.4); color: #34d399; }
        .btn-glass.tele-btn:hover { border-color: rgba(6, 182, 212, 0.4); color: #22d3ee; }
        .btn-glass.reseller-btn:hover { border-color: rgba(245, 158, 11, 0.4); color: #fbbf24; }

        /* Right panel */
        .auth-right {
            width: 480px;
            height: 100vh;
            background: white;
            display: flex;
            flex-direction: column;
            justify-content: center;
            padding: 60px 50px;
            overflow-y: auto;
        }

        .login-header { margin-bottom: 36px; }
        .login-header h2 { font-size: 28px; font-weight: 700; color: #0f172a; margin-bottom: 8px; }
        .login-header p { color: #64748b; font-size: 15px; }

        .form-label { font-size: 14px; font-weight: 500; color: #374151; margin-bottom: 6px; }

        .input-group-custom { position: relative; }
        .input-group-custom .input-icon {
            position: absolute; left: 14px; top: 50%; transform: translateY(-50%);
            color: #94a3b8; font-size: 18px; pointer-events: none; z-index: 2;
        }
        .input-group-custom .form-control {
            padding-left: 44px; border: 1.5px solid #e2e8f0;
            border-radius: 10px; height: 48px; font-size: 14px;
            transition: all 0.2s;
        }
        .input-group-custom .form-control:focus { border-color: #6366f1; box-shadow: 0 0 0 3px rgba(99,102,241,0.12); }

        .input-group-custom .toggle-pw {
            position: absolute; right: 14px; top: 50%; transform: translateY(-50%);
            color: #94a3b8; font-size: 18px; cursor: pointer; z-index: 2;
        }
        .input-group-custom .toggle-pw:hover { color: #6366f1; }

        .btn-login {
            width: 100%; height: 50px; border-radius: 10px;
            background: linear-gradient(135deg, #6366f1, #4f46e5);
            border: none; color: white; font-size: 15px; font-weight: 600;
            cursor: pointer; transition: all 0.2s;
            display: flex; align-items: center; justify-content: center; gap: 8px;
        }
        .btn-login:hover { transform: translateY(-1px); box-shadow: 0 8px 20px rgba(99,102,241,0.4); }

        @media (max-width: 900px) {
            .auth-left { display: none; }
            .auth-right { width: 100%; padding: 40px 30px; }
        }
    </style>
</head>
<body>

<!-- Left Panel -->
<div class="auth-left">
    <div class="auth-brand">
        <div class="auth-brand-icon"><i class="bi bi-lightning-charge-fill"></i></div>
        <div class="auth-brand-name d-flex flex-column" style="line-height: 1.1;">
            <div>Work<span>eX</span></div>
            <span style="font-size: 11px; font-weight: 500; color: #94a3b8; letter-spacing: 0.05em; margin-top: 4px;">By Techsoul</span>
        </div>
    </div>

    <div class="auth-hero">
        <h1>Manage Your Team<br>Like a <span>Pro</span></h1>
        <p>Track projects, tasks, attendance, and team productivity all in one powerful platform.</p>
    </div>

    <div class="auth-features">
        <div class="auth-feature-item">
            <div class="auth-feature-icon" style="background:rgba(99,102,241,0.15);color:#818cf8;"><i class="bi bi-stopwatch-fill"></i></div>
            <div class="auth-feature-text">Real-time Work Timer & Tracking</div>
        </div>
        <div class="auth-feature-item">
            <div class="auth-feature-icon" style="background:rgba(16,185,129,0.15);color:#10b981;"><i class="bi bi-kanban-fill"></i></div>
            <div class="auth-feature-text">Project & Task Management</div>
        </div>
        <div class="auth-feature-item">
            <div class="auth-feature-icon" style="background:rgba(245,158,11,0.15);color:#f59e0b;"><i class="bi bi-bar-chart-fill"></i></div>
            <div class="auth-feature-text">Advanced Reports & Analytics</div>
        </div>
        <div class="auth-feature-item">
            <div class="auth-feature-icon" style="background:rgba(239,68,68,0.15);color:#ef4444;"><i class="bi bi-receipt"></i></div>
            <div class="auth-feature-text">Invoice & Payment Tracking</div>
        </div>
    </div>

    <!-- Glassmorphic Quick Login Card -->
    <div class="left-quick-login">
        <div class="quick-title">
            <i class="bi bi-lightning-charge-fill text-warning" style="color: #fbbf24 !important;"></i> Demo Quick Access
        </div>
        <div class="quick-grid">
            <button type="button" class="btn btn-glass admin-btn" title="admin@workmonitor.com / Admin@123" onclick="quickLogin('admin@workmonitor.com', 'Admin@123')">
                <i class="bi bi-shield-lock text-danger"></i> Admin
            </button>
            <button type="button" class="btn btn-glass pm-btn" title="manager@workmonitor.com / Admin@123" onclick="quickLogin('manager@workmonitor.com', 'Admin@123')">
                <i class="bi bi-briefcase text-purple" style="color:#c084fc;"></i> PM
            </button>
            <button type="button" class="btn btn-glass leader-btn" title="vijil.techsoul@gmail.com / Admin@123" onclick="quickLogin('vijil.techsoul@gmail.com', 'Admin@123')">
                <i class="bi bi-people text-primary" style="color:#60a5fa;"></i> TL (Vijil)
            </button>
            <button type="button" class="btn btn-glass leader-btn" title="souban.techsoul@gmail.com / Admin@123" onclick="quickLogin('souban.techsoul@gmail.com', 'Admin@123')">
                <i class="bi bi-people text-primary" style="color:#60a5fa;"></i> TL (Souban)
            </button>
            <button type="button" class="btn btn-glass dev-btn" title="hisham.techsoul@gmail.com / Admin@123" onclick="quickLogin('hisham.techsoul@gmail.com', 'Admin@123')">
                <i class="bi bi-terminal text-success" style="color:#34d399;"></i> Dev (Hisham)
            </button>
            <button type="button" class="btn btn-glass tele-btn" title="telecaller@workmonitor.com / Admin@123" onclick="quickLogin('telecaller@workmonitor.com', 'Admin@123')">
                <i class="bi bi-telephone text-info" style="color:#22d3ee;"></i> Telecaller
            </button>
            <button type="button" class="btn btn-glass reseller-btn" style="grid-column: span 2;" title="reseller@workmonitor.com / Reseller@123" onclick="quickLogin('reseller@workmonitor.com', 'Reseller@123')">
                <i class="bi bi-buildings text-warning" style="color:#fbbf24;"></i> Reseller Company
            </button>
        </div>
    </div>
</div>

<!-- Right Panel (Login Form) -->
<div class="auth-right">
    <div class="login-header">
        <h2>Welcome back 👋</h2>
        <p>Sign in to your WorkeX account</p>
    </div>

    @if(session('status'))
        <div class="alert alert-success mb-3">{{ session('status') }}</div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger mb-3">{{ session('error') }}</div>
    @endif

    @if(session('warning'))
        <div class="alert alert-warning mb-3">{{ session('warning') }}</div>
    @endif

    <form method="POST" action="{{ route('login') }}">
        @csrf

        @if(isset($otp_required) && $otp_required)
            <input type="hidden" name="email" value="{{ $otp_email }}">
            
            <div class="mb-4">
                <label class="form-label">Email Address</label>
                <div class="input-group-custom">
                    <i class="bi bi-envelope input-icon"></i>
                    <input type="email" class="form-control" value="{{ $otp_email }}" readonly disabled>
                </div>
            </div>

            @if(config('app.env') === 'local' || config('app.debug'))
                <div class="alert alert-info border-info-subtle mb-3 p-2 text-center" style="font-size: 13.5px; background: rgba(13, 110, 253, 0.05); color: #0d6efd; border-radius: 8px;">
                    <i class="bi bi-info-circle-fill me-1"></i> <strong>[Local Development]</strong> Verification Code: <strong style="font-size: 15px; color: #0a58ca; letter-spacing: 1px;">{{ \Illuminate\Support\Facades\Cache::get('login_otp_' . $otp_email) }}</strong>
                </div>
            @endif

            <div class="mb-4">
                <label class="form-label">One-Time Password (OTP)</label>
                <div class="input-group-custom">
                    <i class="bi bi-shield-lock input-icon"></i>
                    <input type="text" name="otp" id="otp"
                        class="form-control @error('otp') is-invalid @enderror"
                        required autofocus placeholder="Enter 6-digit OTP code">
                    @error('otp')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <button type="submit" class="btn-login">
                <i class="bi bi-shield-check"></i> Verify & Sign In
            </button>

            <div class="mt-4 text-center d-flex justify-content-between" style="font-size: 13.5px;">
                <a href="javascript:void(0)" onclick="resendOtp()" style="color:#6366f1;text-decoration:none;font-weight:600;"><i class="bi bi-arrow-clockwise"></i> Resend Code</a>
                <a href="{{ route('login') }}" style="color:#64748b;text-decoration:none;"><i class="bi bi-arrow-left"></i> Use different email</a>
            </div>

        @else

            <div class="mb-4">
                <label class="form-label">Email Address</label>
                <div class="input-group-custom">
                    <i class="bi bi-envelope input-icon"></i>
                    <input type="email" name="email" id="email"
                        class="form-control @error('email') is-invalid @enderror"
                        value="{{ old('email') }}" required autofocus autocomplete="username"
                        placeholder="admin@workmonitor.com">
                    @error('email')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="mb-4">
                <div class="d-flex justify-content-between align-items-center mb-1">
                    <label class="form-label mb-0">Password</label>
                    @if(Route::has('password.request'))
                        <a href="{{ route('password.request') }}" style="font-size:13px;color:#6366f1;text-decoration:none;">Forgot password?</a>
                    @endif
                </div>
                <div class="input-group-custom">
                    <i class="bi bi-lock input-icon"></i>
                    <input type="password" name="password" id="password"
                        class="form-control @error('password') is-invalid @enderror"
                        required autocomplete="current-password" placeholder="••••••••">
                    <i class="bi bi-eye toggle-pw" onclick="togglePassword()" id="toggleIcon"></i>
                    @error('password')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            @if(isset($captcha_question) && $captcha_question)
                <div class="mb-4">
                    <label class="form-label text-warning"><i class="bi bi-shield-exclamation"></i> Security Verification (CAPTCHA)</label>
                    <div class="alert alert-dark p-2 mb-2 d-flex align-items-center justify-content-between" style="background: #1e293b; border-color: rgba(255,255,255,0.06);">
                        <span style="font-weight: 700; color: #ffffff; letter-spacing: 0.5px;">{{ $captcha_question }}</span>
                        <span style="font-size: 11px; background: rgba(245, 158, 11, 0.15); color: #fbbf24; padding: 2px 8px; border-radius: 4px;">Required</span>
                    </div>
                    <div class="input-group-custom">
                        <i class="bi bi-calculator input-icon"></i>
                        <input type="number" name="captcha" id="captcha"
                            class="form-control @error('captcha') is-invalid @enderror"
                            required placeholder="Enter the math result">
                        @error('captcha')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            @endif

            <div class="mb-4 d-flex align-items-center gap-2">
                <input type="checkbox" name="remember" id="remember" class="form-check-input mt-0" {{ old('remember') ? 'checked' : '' }}>
                <label for="remember" style="font-size:14px;color:#64748b;cursor:pointer;">Remember me for 30 days</label>
            </div>

            <button type="submit" class="btn-login">
                <i class="bi bi-box-arrow-in-right"></i> Sign In
            </button>

        @endif
    </form>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    function resendOtp() {
        const form = document.querySelector('form');
        const resendInput = document.createElement('input');
        resendInput.type = 'hidden';
        resendInput.name = 'resend_otp';
        resendInput.value = '1';
        form.appendChild(resendInput);
        form.submit();
    }

    function togglePassword() {
        const pw = document.getElementById('password');
        const icon = document.getElementById('toggleIcon');
        if (pw.type === 'password') {
            pw.type = 'text';
            icon.className = 'bi bi-eye-slash toggle-pw';
        } else {
            pw.type = 'password';
            icon.className = 'bi bi-eye toggle-pw';
        }
    }

    function quickLogin(email, password) {
        document.getElementById('email').value = email;
        document.getElementById('password').value = password;
        document.querySelector('form').submit();
    }
</script>
</body>
</html>
