<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Application Submitted successfully — Thank You</title>

    @php
        $companyLogo = \App\Models\Setting::get('company_logo');
    @endphp
    @if($companyLogo)
        <link rel="icon" type="image/x-icon" href="{{ asset('storage/' . $companyLogo) }}">
        <link rel="shortcut icon" href="{{ asset('storage/' . $companyLogo) }}">
    @else
        <link rel="icon" type="image/x-icon" href="/favicon.ico">
    @endif

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        :root {
            --primary: #4f46e5;
            --success: #10b981;
            --dark: #0f172a;
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: #f1f5f9;
            color: var(--dark);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .success-card {
            background-color: #ffffff;
            border-radius: 20px;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.05), 0 4px 6px -2px rgba(0, 0, 0, 0.03);
            border: 1px solid rgba(226, 232, 240, 0.8);
            max-width: 550px;
            width: 100%;
            padding: 3rem 2rem;
            text-align: center;
            animation: slideUp 0.5s ease-out;
        }

        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .check-circle {
            width: 80px;
            height: 80px;
            background-color: #d1fae5;
            color: var(--success);
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 2.5rem;
            margin-bottom: 2rem;
            animation: scaleIn 0.5s cubic-bezier(0.175, 0.885, 0.32, 1.275) 0.15s both;
        }

        @keyframes scaleIn {
            from {
                opacity: 0;
                transform: scale(0.6);
            }
            to {
                opacity: 1;
                transform: scale(1);
            }
        }

        .company-name {
            font-weight: 700;
            color: var(--primary);
            text-transform: uppercase;
            font-size: 0.85rem;
            letter-spacing: 0.05em;
            margin-bottom: 0.75rem;
        }
    </style>
</head>
<body>

    <div class="container px-4">
        <div class="success-card mx-auto">
            
            <div class="check-circle">
                <i class="bi bi-patch-check-fill"></i>
            </div>
            
            @php
                $companyLogo = \App\Models\Setting::get('company_logo');
                $companyName = \App\Models\Setting::get('company_name', 'Techsoul');
            @endphp
            @if($companyLogo)
                <div class="brand-logo-container d-flex align-items-center justify-content-center mx-auto mb-3" style="width: 52px; height: 52px; overflow: hidden; border-radius: 12px; background: white; border: 1px solid #e2e8f0; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05);">
                    <img src="{{ asset('storage/' . $companyLogo) }}" alt="Logo" style="max-width: 100%; max-height: 100%; object-fit: contain; padding: 3px;">
                </div>
            @endif
            <div class="company-name">{{ $companyName }}</div>
            <h2 class="fw-bold mb-3">Application Submitted!</h2>
            
            <p class="text-secondary mb-4" style="line-height: 1.6;">
                Thank you for your interest in joining our team. Your application for the position of 
                <strong class="text-dark">{{ $vacancy->title }}</strong> has been successfully received and our hiring team is reviewing it.
            </p>

            <div class="p-3 bg-light rounded-3 mb-4">
                <small class="text-muted d-block mb-1">Position Workplace & Type</small>
                <div class="fw-semibold text-dark">
                    {{ $vacancy->job_type }} &mdash; {{ $vacancy->location ?? 'Remote' }}
                </div>
            </div>

            <p class="text-muted small mb-0">
                If your profile matches the role requirements, we will get in touch with you shortly via email or phone.
            </p>
            
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
