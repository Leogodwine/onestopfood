<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title') — {{ $siteName ?? config('app.name', 'One Stop') }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="{{ asset('css/mobile-responsive.css') }}">
    <style>
        :root { --primary-color: #ff6b35; --secondary-color: #2c3e50; }
        body {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            font-family: system-ui, -apple-system, sans-serif;
        }
        .error-card {
            max-width: 520px;
            width: 100%;
            margin: 1rem;
            border: 0;
            border-radius: 16px;
            box-shadow: 0 12px 40px rgba(0, 0, 0, .08);
        }
        .error-icon {
            width: 64px;
            height: 64px;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 1.75rem;
            margin-bottom: 1rem;
        }
        .error-icon-warning { background: #fff3cd; color: #856404; }
        .error-icon-danger { background: #f8d7da; color: #842029; }
        .error-icon-muted { background: #e9ecef; color: #495057; }
        .btn-home {
            background: var(--primary-color);
            border-color: var(--primary-color);
            color: #fff;
        }
        .btn-home:hover {
            background: #e55a28;
            border-color: #e55a28;
            color: #fff;
        }
    </style>
</head>
<body>
    <div class="card error-card mx-3">
        <div class="card-body p-4 p-md-5 text-center">
            @yield('icon')
            <h1 class="h4 mb-2">@yield('heading')</h1>
            <p class="text-muted mb-4">@yield('message')</p>
            <div class="d-grid gap-2 d-sm-flex justify-content-sm-center">
                <a href="{{ home_url() }}" class="btn btn-home px-4">
                    <i class="bi bi-house-door me-1"></i> Back to Home
                </a>
                @hasSection('secondary_action')
                    @yield('secondary_action')
                @endif
            </div>
        </div>
    </div>
</body>
</html>
