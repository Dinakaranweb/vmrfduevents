<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $globalLoginTitle ?? 'Institutional Login - VMRFDU Centralized Portal' }}</title>
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@500;600;700;800&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        :root {
            --portal-primary: {{ !empty($globalPrimaryColor) ? $globalPrimaryColor : '#1e293b' }} !important;
            --portal-accent: {{ !empty($globalAccentColor) ? $globalAccentColor : '#2563eb' }} !important;
            --radius-md: {{ !empty($globalCardRadius) ? $globalCardRadius : '16px' }} !important;
        }
        .login-banner { background: var(--portal-primary) !important; }
        .btn-portal-primary { background: var(--portal-accent) !important; border-color: var(--portal-accent) !important; }
        .login-card { border-radius: var(--radius-md) !important; }
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background: #070a12;
            background-image: 
                radial-gradient(circle at 15% 50%, rgba(37, 99, 235, 0.18) 0%, transparent 45%),
                radial-gradient(circle at 85% 30%, rgba(79, 70, 229, 0.15) 0%, transparent 50%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2.5rem 1rem;
            -webkit-font-smoothing: antialiased;
            position: relative;
            overflow-x: hidden;
        }
        h1, h2, h3, h4, h5, h6 { font-family: 'Outfit', sans-serif; letter-spacing: -0.025em; }
        .login-card {
            background: rgba(255, 255, 255, 0.96);
            backdrop-filter: blur(24px) saturate(180%);
            -webkit-backdrop-filter: blur(24px) saturate(180%);
            border-radius: 28px;
            box-shadow: 0 35px 70px -15px rgba(0, 0, 0, 0.65), 0 0 0 1px rgba(255, 255, 255, 0.2);
            overflow: hidden;
            width: 100%;
            max-width: 1060px;
            animation: fadeInUp 0.5s cubic-bezier(0.16, 1, 0.3, 1) forwards;
        }
        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .login-banner {
            background: linear-gradient(145deg, #0a0a0a 0%, #111827 50%, #1e3a8a 100%);
            color: #ffffff;
            padding: 4rem 3.2rem;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            position: relative;
            overflow: hidden;
        }
        .login-banner::before {
            content: '';
            position: absolute;
            top: -50px;
            right: -50px;
            width: 250px;
            height: 250px;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(37, 99, 235, 0.35) 0%, transparent 70%);
            pointer-events: none;
        }
        .login-form-area {
            padding: 3.8rem 3.2rem;
        }
        .form-control {
            border-radius: 14px;
            padding: 0.85rem 1.15rem;
            border: 1.5px solid #e2e8f0;
            font-size: 0.95rem;
            font-family: 'Plus Jakarta Sans', sans-serif;
            transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .form-control:focus {
            box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.15);
            border-color: #2563eb;
            outline: none;
        }
        .btn-portal-primary {
            background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
            color: #fff;
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 14px;
            padding: 1rem;
            font-weight: 600;
            font-size: 0.96rem;
            letter-spacing: 0.02em;
            box-shadow: 0 8px 20px rgba(37, 99, 235, 0.35);
            transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .btn-portal-primary:hover {
            background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
            transform: translateY(-2px);
            box-shadow: 0 12px 25px rgba(37, 99, 235, 0.45);
            color: #fff;
        }
        .btn-portal-primary:active {
            transform: scale(0.98);
        }
        .credential-item {
            font-size: 0.82rem;
            padding: 0.8rem 1rem;
            background: #f8fafc;
            border: 1.5px solid #e2e8f0;
            border-radius: 12px;
            cursor: pointer;
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .credential-item:hover {
            background: #eff6ff;
            border-color: #3b82f6;
            transform: translateY(-2px);
            box-shadow: 0 6px 15px rgba(37, 99, 235, 0.12);
        }
    </style>
</head>
<body>

<div class="login-card row g-0">
    <!-- Left Banner Area -->
    <div class="col-12 col-lg-5 login-banner">
        <div>
            <div class="d-flex align-items-center gap-3 mb-4">
                <div class="bg-white bg-opacity-25 rounded-circle p-1 d-flex align-items-center justify-content-center overflow-hidden shadow-sm" style="width: 85px; height: 85px;">
                    @if(!empty($globalPortalLogo))
                        <img src="{{ asset($globalPortalLogo) }}" alt="Logo" class="w-100 h-100" style="object-fit: contain;">
                    @else
                        <i class="bi bi-bank2 fs-1 text-white"></i>
                    @endif
                </div>
                <div>
                    <h4 class="fw-bold mb-1 lh-sm">{{ $globalLoginBannerHeader ?? 'VMRFDU Official Portal' }}</h4>
                    <span class="small opacity-75">{{ $globalLoginBannerSubheader ?? 'Central Administration' }}</span>
                </div>
            </div>

            <h3 class="fw-bold mb-3">{{ $globalLoginBannerMainText ?? 'Centralized Activity & Media Publication' }}</h3>
            <p class="small opacity-85 lh-lg mb-4">
                {{ $globalLoginBannerParagraph ?? 'A unified institutional platform designed for constituent colleges across Salem, Chennai, and Puducherry to submit, verify, approve, and broadcast university events and research milestones.' }}
            </p>
        </div>

        <div class="border-top border-light border-opacity-25 pt-3">
            <div class="small opacity-75 d-flex flex-column gap-1">
                <div>{{ !empty($globalLoginFooterText) ? $globalLoginFooterText : (!empty($globalPortalName) ? $globalPortalName : 'VMRFDU Activity Portal' . ' - © ' . date('Y') . ' All Rights Reserved.') }}</div>
                <div class="fw-semibold">{{ !empty($globalLoginFooterSystemText) ? $globalLoginFooterSystemText : 'Institutional Activity Verification Engine' }}</div>
            </div>
        </div>
    </div>

    <!-- Right Form Area -->
    <div class="col-12 col-lg-7 login-form-area bg-white">
        <div class="mb-4">
            <h4 class="fw-bold text-dark mb-1">{{ $globalLoginFormTitle ?? 'Sign in to your account' }}</h4>
            <p class="text-muted small">{{ $globalLoginFormSubtitle ?? 'Enter your authorized institutional email credentials to proceed' }}</p>
        </div>

        @if(session('info'))
            <div class="alert alert-info py-2 px-3 small rounded-3 mb-3"><i class="bi bi-info-circle me-1"></i> {{ session('info') }}</div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger py-2 px-3 small rounded-3 mb-3"><i class="bi bi-exclamation-triangle me-1"></i> {{ session('error') }}</div>
        @endif
        @if($errors->any())
            <div class="alert alert-danger py-2 px-3 small rounded-3 mb-3">
                <ul class="mb-0 ps-3">
                    @foreach($errors->all() as $err)
                        <li>{{ $err }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @if(!empty($globalLoginAnnouncement))
            <div class="alert alert-{{ $globalLoginAnnouncementType ?? 'info' }} py-2 px-3 small rounded-3 mb-3 d-flex align-items-center">
                <i class="bi bi-info-circle-fill me-2"></i>
                <div>{{ $globalLoginAnnouncement }}</div>
            </div>
        @endif

        <form method="POST" action="{{ route('login.post') }}" class="mb-4">
            @csrf
            <div class="mb-3">
                <label class="form-label small fw-semibold text-dark">Institutional Email Address</label>
                <div class="input-group">
                    <span class="input-group-text bg-light border-end-0"><i class="bi bi-envelope text-muted"></i></span>
                    <input type="email" name="email" id="loginEmail" class="form-control border-start-0" placeholder="name@vmrfdu.edu.in" required value="{{ old('email') }}">
                </div>
            </div>

            <div class="mb-3">
                <div class="d-flex justify-content-between align-items-center mb-1">
                    <label class="form-label small fw-semibold text-dark mb-0">Password</label>
                </div>
                <div class="input-group">
                    <span class="input-group-text bg-light border-end-0"><i class="bi bi-key text-muted"></i></span>
                    <input type="password" name="password" id="loginPassword" class="form-control border-start-0 border-end-0" placeholder="Enter Password" required>
                    <button class="btn border border-start-0 bg-transparent" type="button" id="togglePassword" style="border-color: #dee2e6;">
                        <i class="bi bi-eye text-muted" id="togglePasswordIcon"></i>
                    </button>
                </div>
            </div>

            <div class="d-flex justify-content-between align-items-center mb-4">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="remember" id="rememberMe" checked>
                    <label class="form-check-label small text-muted" for="rememberMe">Remember this device</label>
                </div>
            </div>

            <button type="submit" class="btn btn-portal-primary w-100 shadow-sm">
                Sign In to Portal <i class="bi bi-arrow-right ms-1"></i>
            </button>
            
            @if(!empty($globalLoginSupportEmail) || !empty($globalLoginHelpdeskUrl))
            <div class="d-flex justify-content-center gap-3 mt-4 pt-3 border-top border-light border-opacity-50">
                @if(!empty($globalLoginHelpdeskUrl))
                    <a href="{{ $globalLoginHelpdeskUrl }}" target="_blank" class="small text-decoration-none text-muted hover-primary transition-all">
                        <i class="bi bi-life-preserver me-1"></i>IT Helpdesk
                    </a>
                @endif
                @if(!empty($globalLoginSupportEmail))
                    <a href="mailto:{{ $globalLoginSupportEmail }}" class="small text-decoration-none text-muted hover-primary transition-all">
                        <i class="bi bi-envelope me-1"></i>{{ $globalLoginSupportEmail }}
                    </a>
                @endif
            </div>
            @endif
        </form>

        <!-- Live Deployment Institutional Access Directory -->
        @if(!isset($globalLoginShowDirectory) || $globalLoginShowDirectory)
        <div class="border-top pt-3">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <span class="text-xs fw-bold text-uppercase text-secondary">Official Access Directory (Click to Autofill)</span>
                <span class="badge bg-light text-dark border small">Live Portal Access</span>
            </div>
            
            <div class="row g-2" style="max-height: 180px; overflow-y: auto;">
                @foreach($officialAccounts as $acc)
                <div class="col-12 col-md-6">
                    <div class="credential-item d-flex align-items-center justify-content-between" onclick="autofill('{{ $acc->email }}')">
                        <div class="text-truncate me-2">
                            <div class="fw-bold text-dark text-truncate">{{ $acc->name }}</div>
                            <div class="text-xs text-muted text-truncate">{{ $acc->email }}</div>
                        </div>
                        <span class="badge bg-primary bg-opacity-10 text-primary text-xs">{{ $acc->role_title }}</span>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endif
    </div>
</div>

<script>
    function autofill(email) {
        document.getElementById('loginEmail').value = email;
        document.getElementById('loginPassword').focus();
    }

    document.getElementById('togglePassword').addEventListener('click', function (e) {
        const passwordInput = document.getElementById('loginPassword');
        const icon = document.getElementById('togglePasswordIcon');
        if (passwordInput.type === 'password') {
            passwordInput.type = 'text';
            icon.classList.remove('bi-eye');
            icon.classList.add('bi-eye-slash');
        } else {
            passwordInput.type = 'password';
            icon.classList.remove('bi-eye-slash');
            icon.classList.add('bi-eye');
        }
    });
</script>

</body>
</html>
