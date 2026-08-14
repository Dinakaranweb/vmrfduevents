<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title') - VMRFDU Enterprise Portal</title>
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;600;700;800&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        :root {
            --font-outfit: 'Outfit', sans-serif;
            --font-jakarta: 'Plus Jakarta Sans', sans-serif;
        }
        body {
            font-family: var(--font-jakarta);
            background: radial-gradient(circle at 10% 20%, rgb(15, 23, 42) 0%, rgb(30, 41, 59) 50%, rgb(15, 23, 42) 100%);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            margin: 0;
            color: #1e293b;
            position: relative;
            overflow-x: hidden;
        }
        /* Glowing Orbs for Premium Aesthetic */
        .orb-1 {
            position: absolute;
            top: -100px;
            left: -100px;
            width: 400px;
            height: 400px;
            background: radial-gradient(circle, rgba(59, 130, 246, 0.25) 0%, rgba(0,0,0,0) 70%);
            border-radius: 50%;
            z-index: 0;
            pointer-events: none;
        }
        .orb-2 {
            position: absolute;
            bottom: -150px;
            right: -150px;
            width: 500px;
            height: 500px;
            background: radial-gradient(circle, rgba(99, 102, 241, 0.22) 0%, rgba(0,0,0,0) 70%);
            border-radius: 50%;
            z-index: 0;
            pointer-events: none;
        }
        .error-navbar {
            z-index: 10;
            padding: 1.25rem 0;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            background: rgba(15, 23, 42, 0.6);
            backdrop-filter: blur(12px);
        }
        .error-main {
            z-index: 10;
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem 1rem;
        }
        .portal-error-card {
            background: rgba(255, 255, 255, 0.98);
            backdrop-filter: blur(25px);
            border: 1px solid rgba(255, 255, 255, 0.4);
            border-radius: 28px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5), 0 0 0 1px rgba(255,255,255,0.1);
            max-width: 620px;
            width: 100%;
            padding: 3.5rem 2.5rem;
            text-align: center;
            animation: cardAppear 0.6s cubic-bezier(0.16, 1, 0.3, 1) forwards;
        }
        @keyframes cardAppear {
            0% {
                opacity: 0;
                transform: translateY(20px) scale(0.98);
            }
            100% {
                opacity: 1;
                transform: translateY(0) scale(1);
            }
        }
        .error-code {
            font-family: var(--font-outfit);
            font-weight: 800;
            font-size: 6rem;
            line-height: 1;
            letter-spacing: -3px;
            background: linear-gradient(135deg, @yield('accent-color', '#3b82f6') 0%, #1e1b4b 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            margin-bottom: 1rem;
            display: inline-block;
        }
        .error-icon-badge {
            width: 86px;
            height: 86px;
            border-radius: 22px;
            background: @yield('accent-bg', 'rgba(59, 130, 246, 0.1)');
            color: @yield('accent-color', '#3b82f6');
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 2.75rem;
            margin-bottom: 1.5rem;
            box-shadow: 0 10px 15px -3px @yield('accent-bg', 'rgba(59, 130, 246, 0.1)');
        }
        .error-title {
            font-family: var(--font-outfit);
            font-weight: 700;
            font-size: 1.85rem;
            color: #0f172a;
            margin-bottom: 0.75rem;
        }
        .error-message {
            color: #64748b;
            font-size: 1.05rem;
            line-height: 1.6;
            margin-bottom: 2.25rem;
        }
        .btn-portal-primary {
            background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
            color: #ffffff;
            font-weight: 600;
            border: none;
            border-radius: 14px;
            padding: 0.85rem 1.75rem;
            transition: all 0.25s ease;
            box-shadow: 0 10px 15px -3px rgba(59, 130, 246, 0.3);
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
        }
        .btn-portal-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 15px 20px -3px rgba(59, 130, 246, 0.45);
            color: #ffffff;
        }
        .btn-portal-outline {
            background: #f8fafc;
            color: #334155;
            font-weight: 600;
            border: 1px solid #cbd5e1;
            border-radius: 14px;
            padding: 0.85rem 1.75rem;
            transition: all 0.25s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
        }
        .btn-portal-outline:hover {
            background: #f1f5f9;
            color: #0f172a;
            border-color: #94a3b8;
        }
        .error-footer {
            z-index: 10;
            padding: 1.5rem 0;
            background: rgba(15, 23, 42, 0.85);
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            color: rgba(255, 255, 255, 0.8);
        }
    </style>
</head>
<body>
    <!-- Background Orbs -->
    <div class="orb-1"></div>
    <div class="orb-2"></div>

    <!-- Header / Navbar -->
    <header class="error-navbar">
        <div class="container d-flex align-items-center justify-content-between">
            <a href="{{ url('/') }}" class="d-flex align-items-center gap-3 text-decoration-none text-white">
                <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center shadow" style="width: 42px; height: 42px; background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%) !important;">
                    <i class="bi bi-bank2 fs-5"></i>
                </div>
                <div>
                    <span class="fw-bold fs-5 d-block lh-1 text-white" style="font-family: var(--font-outfit);">VMRFDU Activity Portal</span>
                    <span class="text-xs text-white opacity-75 d-block mt-1">Institutional Governance &amp; Media Engine</span>
                </div>
            </a>
            <div>
                <span class="badge bg-light bg-opacity-10 text-white border border-light border-opacity-25 px-3 py-2 rounded-pill font-monospace small">
                    <i class="bi bi-shield-check me-1"></i> System Diagnostics
                </span>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="error-main">
        <div class="portal-error-card">
            <div class="error-icon-badge">
                <i class="@yield('icon', 'bi bi-exclamation-triangle-fill')"></i>
            </div>
            
            <div class="error-code">@yield('code')</div>
            <h1 class="error-title">@yield('title')</h1>
            <p class="error-message">@yield('message')</p>
            
            <div class="d-flex flex-column flex-sm-row justify-content-center gap-3">
                @if(auth()->check())
                    <a href="{{ route('dashboard') }}" class="btn-portal-primary">
                        <i class="bi bi-grid-fill"></i> Return to Dashboard
                    </a>
                @else
                    <a href="{{ url('/') }}" class="btn-portal-primary">
                        <i class="bi bi-house-door-fill"></i> Return to Home
                    </a>
                @endif
                <a href="{{ url()->previous() }}" class="btn-portal-outline">
                    <i class="bi bi-arrow-left"></i> Go Back Previous Page
                </a>
            </div>

            @if(app()->bound('session') && session()->has('error_details'))
            <div class="mt-4 pt-4 border-top text-start">
                <small class="text-muted d-block mb-1 fw-bold text-uppercase tracking-wider text-xxs">Technical Diagnostics:</small>
                <div class="p-3 bg-light rounded-3 font-monospace text-xs text-danger border overflow-auto" style="max-height: 120px;">
                    {{ session('error_details') }}
                </div>
            </div>
            @endif
        </div>
    </main>

    <!-- Dynamic Error Page Footer -->
    <footer class="error-footer text-center">
        <div class="container">
            <p class="mb-1 fw-bold tracking-wider text-xs text-white opacity-90" style="font-family: var(--font-outfit);">{{ strtoupper(!empty($globalPortalName) ? $globalPortalName : 'VMRFDU ACTIVITY PORTAL') }}</p>
            <p class="mb-0 text-xxs opacity-75">{{ !empty($globalFooterSystemText) ? $globalFooterSystemText : 'Institutional Activity Verification & Tracking Engine' }}</p>
        </div>
    </footer>
</body>
</html>
