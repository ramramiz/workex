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
            background: linear-gradient(135deg, #0f172a 0%, #1e1b4b 50%, #0f172a 100%);
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            padding: 60px;
            position: relative;
            overflow: hidden;
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
            margin-bottom: 60px;
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

        .auth-features { display: flex; flex-direction: column; gap: 16px; margin-top: 48px; position: relative; z-index: 1; width: 100%; max-width: 360px; }
        .auth-feature-item { display: flex; align-items: center; gap: 14px; padding: 14px 18px; background: rgba(255,255,255,0.04); border-radius: 12px; border: 1px solid rgba(255,255,255,0.06); }
        .auth-feature-icon { width: 36px; height: 36px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 18px; flex-shrink: 0; }
        .auth-feature-text { font-size: 14px; color: #cbd5e1; font-weight: 500; }

        /* Right panel */
        .auth-right {
            width: 480px;
            background: white;
            display: flex;
            flex-direction: column;
            justify-content: center;
            padding: 60px 50px;
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

        .divider { display: flex; align-items: center; gap: 12px; margin: 24px 0; }
        .divider::before, .divider::after { content: ''; flex: 1; height: 1px; background: #e2e8f0; }
        .divider span { font-size: 12px; color: #94a3b8; font-weight: 500; }

        .demo-creds { background: #f8fafc; border-radius: 10px; padding: 16px; }
        .demo-creds .demo-title { font-size: 12px; font-weight: 600; color: #6366f1; margin-bottom: 8px; text-transform: uppercase; letter-spacing: 0.05em; }
        .demo-creds .cred-item { font-size: 13px; color: #64748b; margin-bottom: 4px; }
        .demo-creds .cred-item span { font-weight: 600; color: #374151; }

        .btn-outline-purple {
            color: #7c3aed;
            border-color: #7c3aed;
            background-color: transparent;
        }
        .btn-outline-purple:hover {
            color: #fff;
            background-color: #7c3aed;
            border-color: #7c3aed;
        }

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

        <div class="mb-4 d-flex align-items-center gap-2">
            <input type="checkbox" name="remember" id="remember" class="form-check-input mt-0" {{ old('remember') ? 'checked' : '' }}>
            <label for="remember" style="font-size:14px;color:#64748b;cursor:pointer;">Remember me for 30 days</label>
        </div>

        <button type="submit" class="btn-login">
            <i class="bi bi-box-arrow-in-right"></i> Sign In
        </button>
    </form>

    <div class="divider"><span>Quick Login</span></div>
    <div class="d-flex gap-2 mb-3 flex-wrap">
        <button type="button" class="btn btn-outline-danger btn-sm flex-grow-1 py-2" title="admin@workmonitor.com / Admin@123" onclick="quickLogin('admin@workmonitor.com', 'Admin@123')">
            <i class="bi bi-shield-lock me-1"></i> Admin
        </button>
        <button type="button" class="btn btn-outline-purple btn-sm flex-grow-1 py-2" title="manager@workmonitor.com / Admin@123" onclick="quickLogin('manager@workmonitor.com', 'Admin@123')">
            <i class="bi bi-briefcase me-1"></i> PM
        </button>
        <button type="button" class="btn btn-outline-primary btn-sm flex-grow-1 py-2" title="vijil.techsoul@gmail.com / Admin@123" onclick="quickLogin('vijil.techsoul@gmail.com', 'Admin@123')">
            <i class="bi bi-people me-1"></i> Leader (Vijil)
        </button>
        <button type="button" class="btn btn-outline-primary btn-sm flex-grow-1 py-2" title="souban.techsoul@gmail.com / Admin@123" onclick="quickLogin('souban.techsoul@gmail.com', 'Admin@123')">
            <i class="bi bi-people me-1"></i> Leader (Souban)
        </button>
        <button type="button" class="btn btn-outline-success btn-sm flex-grow-1 py-2" title="hisham.techsoul@gmail.com / Admin@123" onclick="quickLogin('hisham.techsoul@gmail.com', 'Admin@123')">
            <i class="bi bi-terminal me-1"></i> Developer (Hisham)
        </button>
        <button type="button" class="btn btn-outline-info btn-sm flex-grow-1 py-2" title="telecaller@workmonitor.com / Admin@123" onclick="quickLogin('telecaller@workmonitor.com', 'Admin@123')">
            <i class="bi bi-telephone me-1"></i> Telecaller
        </button>
        <button type="button" class="btn btn-outline-warning btn-sm flex-grow-1 py-2" title="reseller@workmonitor.com / Reseller@123" onclick="quickLogin('reseller@workmonitor.com', 'Reseller@123')">
            <i class="bi bi-buildings me-1"></i> Reseller
        </button>
    </div>

    <div class="demo-creds">
        <div class="demo-title"><i class="bi bi-info-circle me-1"></i>Test Accounts</div>
        <div class="cred-item">Super Admin: <span>admin@workmonitor.com</span> / <span>Admin@123</span></div>
        <div class="cred-item">Project Manager: <span>manager@workmonitor.com</span> / <span>Admin@123</span></div>
        <div class="cred-item">Team Leader (Vijil): <span>vijil.techsoul@gmail.com</span> / <span>Admin@123</span></div>
        <div class="cred-item">Team Leader (Souban): <span>souban.techsoul@gmail.com</span> / <span>Admin@123</span></div>
        <div class="cred-item">Developer (Hisham): <span>hisham.techsoul@gmail.com</span> / <span>Admin@123</span></div>
        <div class="cred-item">Telecaller: <span>telecaller@workmonitor.com</span> / <span>Admin@123</span></div>
        <div class="cred-item">Reseller: <span>reseller@workmonitor.com</span> / <span>Reseller@123</span></div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
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
