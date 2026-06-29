<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title')</title>
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        body {
            font-family: 'Figtree', sans-serif;
            background-color: #0f172a;
            color: #f1f5f9;
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0;
        }
        .error-container {
            text-align: center;
            max-width: 500px;
            padding: 40px 20px;
        }
        .error-code {
            font-size: 72px;
            font-weight: 800;
            color: #38bdf8;
            line-height: 1;
            margin-bottom: 20px;
            letter-spacing: -0.05em;
        }
        .error-message {
            font-size: 16px;
            font-weight: 500;
            color: #94a3b8;
            margin-bottom: 30px;
            line-height: 1.6;
        }
        .btn-home {
            background-color: #38bdf8;
            color: #0f172a;
            font-weight: 600;
            font-size: 14.5px;
            padding: 12px 30px;
            border-radius: 12px;
            text-decoration: none;
            transition: all 0.2s ease-in-out;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            box-shadow: 0 4px 12px rgba(56, 189, 248, 0.2);
            border: none;
        }
        .btn-home:hover {
            background-color: #0ea5e9;
            color: #0f172a;
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(56, 189, 248, 0.3);
        }
        .btn-home:active {
            transform: translateY(0);
        }
    </style>
</head>
<body>
    <div class="error-container">
        <div class="error-code">@yield('code')</div>
        <div class="error-message">@yield('message')</div>
        <div>
            <a href="{{ auth()->check() ? route('dashboard') : route('login') }}" class="btn-home">
                <i class="bi bi-house-fill"></i> Go Back Home
            </a>
        </div>
    </div>
</body>
</html>
