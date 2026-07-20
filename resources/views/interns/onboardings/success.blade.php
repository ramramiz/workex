<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Submission Successful - Techsoul Cyber Solutions</title>
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
            --accent-primary: #10b981;
            --accent-glow: rgba(16, 185, 129, 0.1);
            --text-main: #0f172a;
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

        .glass-card {
            background: var(--card-bg);
            border: 1px solid var(--border-color);
            border-radius: 24px;
            box-shadow: 0 10px 30px rgba(15, 23, 42, 0.05);
            max-width: 550px;
            width: 100%;
            padding: 40px;
            text-align: center;
        }

        .success-icon {
            width: 80px;
            height: 80px;
            background: rgba(16, 185, 129, 0.08);
            border: 2px solid var(--accent-primary);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 24px auto;
            color: var(--accent-primary);
            font-size: 40px;
            box-shadow: 0 0 20px var(--accent-glow);
            animation: scaleIn 0.5s ease-out;
        }

        @keyframes scaleIn {
            0% { transform: scale(0.6); opacity: 0; }
            100% { transform: scale(1); opacity: 1; }
        }

        h3 {
            font-weight: 800;
            letter-spacing: -0.5px;
            color: #0f172a;
        }
    </style>
</head>
<body>
    <div class="glass-card">
        <div class="success-icon">
            <i class="bi bi-patch-check-fill"></i>
        </div>
        
        <h3>Submission Successful!</h3>
        <p class="text-success fw-semibold text-uppercase tracking-wider fs-8 mb-4" style="letter-spacing: 1px;">Techsoul Cyber Solutions</p>
        
        <p class="text-secondary mb-4 fs-7" style="line-height: 1.7; color: #475569;">
            {{ $message ?? 'Thank you for submitting your Intern Onboarding & Information Form. Your details and document attachments have been securely received. Our HR and management team will review your application and finalize systems setup.' }}
        </p>

        <div class="p-3 bg-light rounded-3 mb-4 border border-secondary border-opacity-10 text-start" style="font-size: 13px;">
            <div class="d-flex justify-content-between mb-2">
                <span class="text-muted">Intern Name:</span>
                <strong class="text-dark">{{ $onboarding->intern->name }}</strong>
            </div>
            <div class="d-flex justify-content-between mb-2">
                <span class="text-muted">Onboarding Status:</span>
                <span class="badge bg-warning text-dark"><i class="bi bi-hourglass-split me-1"></i> Under Review</span>
            </div>
            <div class="d-flex justify-content-between">
                <span class="text-muted">Position / Dept:</span>
                <strong class="text-dark">{{ $onboarding->intern->designation->name ?? 'Intern' }} ({{ $onboarding->intern->department->name ?? 'N/A' }})</strong>
            </div>
        </div>

        <p class="text-muted mb-0 fs-8"><i class="bi bi-info-circle me-1"></i> You can close this window now. An official email will be sent once approved.</p>
    </div>
</body>
</html>
