<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Submission Successful - Techsoul Onboarding</title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    
    <style>
        :root {
            --bg-color: #f8fafc;
            --card-bg: #ffffff;
            --border-color: #e2e8f0;
            --accent-primary: #6366f1;
            --accent-glow: rgba(99, 102, 241, 0.15);
            --text-main: #0f172a;
            --text-muted: #64748b;
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--bg-color);
            color: var(--text-main);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .success-card {
            background: var(--card-bg);
            border: 1px solid var(--border-color);
            border-radius: 24px;
            box-shadow: 0 10px 40px rgba(15, 23, 42, 0.04);
            max-width: 550px;
            width: 100%;
            padding: 40px;
            text-align: center;
        }

        .success-icon-wrap {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            background: rgba(99, 102, 241, 0.08);
            border: 1px solid rgba(99, 102, 241, 0.2);
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 30px auto;
            color: var(--accent-primary);
            font-size: 38px;
            box-shadow: 0 0 20px var(--accent-glow);
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.05); }
            100% { transform: scale(1); }
        }

        h2 {
            font-weight: 800;
            letter-spacing: -0.5px;
            color: #0f172a;
            margin-bottom: 12px;
        }

        p.subtitle {
            color: var(--text-muted);
            font-size: 15px;
            line-height: 1.6;
            margin-bottom: 30px;
        }

        .info-strip {
            background: #f8fafc;
            border: 1px solid #cbd5e1;
            border-radius: 12px;
            padding: 18px;
            text-align: left;
            margin-bottom: 30px;
        }

        .info-item {
            display: flex;
            justify-content: space-between;
            font-size: 13.5px;
            margin-bottom: 8px;
        }

        .info-item:last-child {
            margin-bottom: 0;
        }

        .info-label {
            color: var(--text-muted);
            font-weight: 500;
        }

        .info-value {
            color: var(--text-main);
            font-weight: 600;
        }
    </style>
</head>
<body>
    <div class="success-card">
        <div class="success-icon-wrap">
            <i class="bi bi-shield-check"></i>
        </div>
        
        <h2>Submission Successful</h2>
        <p class="subtitle">{{ $message ?? 'Thank you! Your employee onboarding form and uploaded documents have been received successfully.' }}</p>
        
        <div class="info-strip">
            <div class="info-item">
                <span class="info-label">Candidate Name</span>
                <span class="info-value">{{ $onboarding->name }}</span>
            </div>
            <div class="info-item">
                <span class="info-label">Personal Email</span>
                <span class="info-value">{{ $onboarding->personal_email ?? $onboarding->email }}</span>
            </div>
            <div class="info-item">
                <span class="info-label">Department</span>
                <span class="info-value">{{ $onboarding->department->name ?? 'N/A' }}</span>
            </div>
            <div class="info-item">
                <span class="info-label">Joining Date</span>
                <span class="info-value">{{ $onboarding->joining_date ? $onboarding->joining_date->format('d M Y') : 'N/A' }}</span>
            </div>
            <div class="info-item">
                <span class="info-label">Submission Status</span>
                <span class="info-value text-warning"><i class="bi bi-clock-history me-1"></i> Under Review</span>
            </div>
        </div>

        <p class="text-muted fs-8 mb-0">Our HR department will review your details and documents. You will receive an official email confirmation containing your system access credentials and IT asset details once approved.</p>
    </div>

    <script>
        // Prevent navigating back to the form page
        history.pushState(null, null, location.href);
        window.onpopstate = function () {
            history.go(1);
        };
    </script>
</body>
</html>
