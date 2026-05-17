@extends('layout')

@section('content')
<div class="login-portal-wrapper">
    <div class="login-portal-container">
        <!-- Single card: left panel (brand) + right panel (form) -->
        <div class="login-portal-card">
            <div class="login-portal-row">
                <!-- Left: Expanded One Stop + User Manual + Guidelines -->
                <div class="login-portal-left">
                    <div class="login-portal-logo">
                        @if(file_exists(public_path('images/logo 01.webp')))
                            <img src="{{ asset('images/logo 01.webp') }}" alt="{{ $siteName ?? config('app.name') }}" class="login-portal-logo-img">
                        @elseif(file_exists(public_path('images/logo 02.avif')))
                            <img src="{{ asset('images/logo 02.avif') }}" alt="{{ $siteName ?? config('app.name') }}" class="login-portal-logo-img">
                        @elseif(file_exists(public_path('images/one stop food logo 01.jpeg')))
                            <img src="{{ asset('images/one stop food logo 01.jpeg') }}" alt="{{ $siteName ?? config('app.name') }}" class="login-portal-logo-img">
                        @else
                            <span class="login-portal-logo-text">{{ $siteName ?? config('app.name', 'One Stop') }}</span>
                        @endif
                    </div>
                    <div class="login-portal-links">
                        <!-- <a href="#" class="login-portal-link">User Manual</a>
                        <a href="#" class="login-portal-link login-portal-link-highlight">Guidelines!</a> -->
                        <a href="{{ route('home') }}" class="login-portal-link login-portal-back-home">
                            <span class="login-portal-icon material-icon" aria-hidden="true">
                                <svg xmlns="http://www.w3.org/2000/svg" height="24" viewBox="0 -960 960 960" width="24" fill="currentColor"><path d="M240-200h120v-240h240v240h120v-360L480-740 240-560v360Zm-80 80v-480l320-240 320 240v480H520v-240h-80v240H160Zm320-350Z"/></svg>
                            </span>
                            Back home
                        </a>
                    </div>
                </div>

                <!-- Right: Sign in form -->
                <div class="login-portal-right">
                    <div class="login-portal-title">
                        <h1 class="login-portal-org">WELCOME ONE STOP</h1>
                        <p class="login-portal-sub">Food Order Delivery Portal</p>
                    </div>

                    <p class="login-portal-signin-with text-muted small mb-2">Sign in with your account</p>
                    <div class="login-portal-social d-flex gap-2 justify-content-center mb-4">
                        <a href="#" class="btn login-portal-social-btn" title="Sign in with Google" aria-label="Sign in with Google">
                            <svg class="login-portal-social-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/><path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/><path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/><path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/></svg>
                            <span>Google</span>
                        </a>
                        <a href="#" class="btn login-portal-social-btn" title="Sign in with Facebook" aria-label="Sign in with Facebook">
                            <svg class="login-portal-social-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path fill="#1877F2" d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>
                            <span>Facebook</span>
                        </a>
                    </div>

                    <div class="login-portal-divider mb-3"><span>or</span></div>

                    @if(request()->boolean('session_expired'))
                        <div class="alert alert-warning small mb-3">
                            Your session has expired. Please sign in again.
                        </div>
                    @endif

                    <form method="POST" action="{{ route('login.store') }}" class="login-portal-form">
                        @csrf

                        <div class="mb-3">
                            <label for="email" class="form-label">Email Address</label>
                            <input type="email"
                                   name="email"
                                   id="email"
                                   class="form-control form-control-lg @error('email') is-invalid @enderror"
                                   value="{{ old('email') }}"
                                   placeholder="Enter your email"
                                   required
                                   autofocus>
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="password" class="form-label">Password</label>
                            <div class="input-group">
                                <input type="password"
                                       name="password"
                                       id="password"
                                       class="form-control form-control-lg @error('password') is-invalid @enderror"
                                       placeholder="Enter your password"
                                       required>
                                <button class="btn btn-outline-secondary" type="button" id="togglePassword" aria-label="Show password">
                                    <i class="bi bi-eye" id="eyeIcon"></i>
                                </button>
                            </div>
                            @error('password')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <input type="hidden" name="device_fingerprint" id="device_fingerprint">

                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="remember" id="remember" value="1" {{ old('remember') ? 'checked' : '' }}>
                                <label class="form-check-label small" for="remember">Remember me</label>
                            </div>
                            <a href="{{ route('password.request') }}" class="small text-decoration-none">Forgot Password</a>
                        </div>

                        <button type="submit" class="btn btn-success btn-lg w-100 login-portal-btn">
                            Sign in
                        </button>
                    </form>

                    <p class="login-portal-register text-center mt-4 mb-0">
                        Don't have an Account? <a href="javascript:void(0)" onclick="openRegisterModal()" class="fw-semibold">Register here!</a>
                    </p>
                </div>
            </div>
        </div>

        <footer class="login-portal-footer">
            ©{{ date('Y') }} {{ $siteName ?? config('app.name', 'One Stop') }}. All Rights Reserved.
        </footer>
    </div>
</div>

<style>
.login-portal-wrapper {
    min-height: 80vh;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 2rem 1rem;
    background: linear-gradient(180deg, #f0f7f4 0%, #e8f5e9 100%);
}

.login-portal-container {
    width: 100%;
    max-width: 820px;
}

.login-portal-card {
    background: #fff;
    border-radius: 14px;
    box-shadow: 0 4px 24px rgba(0,0,0,0.08);
    border: 1px solid #e5e7eb;
    overflow: hidden;
}

.login-portal-row {
    display: flex;
    align-items: stretch;
    min-height: 380px;
    flex-wrap: wrap;
}

.login-portal-left {
    flex: 1 1 42%;
    min-width: 240px;
    text-align: center;
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
    gap: 1.5rem;
    padding: 2.5rem;
    background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%);
    border-right: 1px solid #e5e7eb;
}

.login-portal-logo {
    display: flex;
    align-items: center;
    justify-content: center;
}

.login-portal-logo-img {
    height: 140px;
    width: auto;
    max-width: 100%;
    object-fit: contain;
}

.login-portal-logo-text {
    display: block;
    font-family: sans-serif;
    font-size: 3rem;
    font-weight: 700;
    color: #22c55e;
    letter-spacing: 0.02em;
}

.login-portal-links {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 0.75rem;
}

.login-portal-link {
    font-size: 1.05rem;
    color: #374151;
    text-decoration: none;
    font-weight: 600;
}
.login-portal-link:hover {
    color: #16a34a;
}
.login-portal-link-highlight {
    color: #16a34a;
    font-weight: 700;
}
.login-portal-link-highlight:hover {
    text-decoration: underline;
}

.login-portal-back-home {
    margin-top: 1rem;
    font-size: 0.95rem;
    display: inline-flex;
    align-items: center;
    gap: 0.35rem;
}
.login-portal-back-home:hover {
    text-decoration: underline;
}
.login-portal-icon.material-icon {
    display: inline-flex;
    align-items: center;
    justify-content: center;
}
.login-portal-icon.material-icon svg {
    width: 1.25rem;
    height: 1.25rem;
}

.login-portal-right {
    flex: 1 1 340px;
    min-width: 0;
    padding: 2rem 2.5rem;
    display: flex;
    flex-direction: column;
    justify-content: center;
}

.login-portal-title {
    text-align: center;
    margin-bottom: 1.25rem;
}

.login-portal-org {
    font-size: 1.35rem;
    font-weight: 700;
    letter-spacing: 0.08em;
    color: #1f2937;
    margin: 0 0 0.25rem 0;
}

.login-portal-sub {
    font-size: 0.8rem;
    font-weight: 600;
    letter-spacing: 0.12em;
    color: #6b7280;
    margin: 0;
}

.login-portal-signin-with {
    font-size: 0.9rem;
    font-weight: 600;
    text-align: center;
}

.login-portal-social-btn {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.5rem 1rem;
    border-radius: 8px;
    border: 1px solid #d1d5db;
    background: #fff;
    color: #374151;
    font-weight: 600;
    font-size: 0.9rem;
    text-decoration: none;
    transition: border-color 0.2s, box-shadow 0.2s;
}
.login-portal-social-btn:hover {
    border-color: #22c55e;
    box-shadow: 0 0 0 2px rgba(34, 197, 94, 0.15);
    color: #1f2937;
}
.login-portal-social-icon {
    width: 1.25rem;
    height: 1.25rem;
    flex-shrink: 0;
}

.login-portal-divider {
    display: flex;
    align-items: center;
    gap: 1rem;
    color: #9ca3af;
    font-size: 0.85rem;
}
.login-portal-divider::before,
.login-portal-divider::after {
    content: '';
    flex: 1;
    height: 1px;
    background: #e5e7eb;
}
.login-portal-divider span {
    font-weight: 600;
}

.login-portal-form .form-label {
    font-weight: 600;
    font-size: 0.9rem;
    color: #374151;
}

.login-portal-form .form-control-lg {
    border-radius: 8px;
    border: 1px solid #d1d5db;
}
.login-portal-form .form-control-lg:focus {
    border-color: #22c55e;
    box-shadow: 0 0 0 3px rgba(34, 197, 94, 0.2);
}

.login-portal-btn {
    border-radius: 8px;
    font-weight: 600;
    padding: 0.75rem 1rem;
}

.login-portal-register {
    font-size: 0.9rem;
    color: #6b7280;
}
.login-portal-register a {
    color: #22c55e;
}
.login-portal-register a:hover {
    text-decoration: underline !important;
}

.login-portal-footer {
    text-align: center;
    font-size: 0.75rem;
    color: #9ca3af;
    margin-top: 2rem;
}

@media (max-width: 575.98px) {
    .login-portal-row {
        flex-direction: column;
        min-height: 0;
    }
    .login-portal-left {
        border-right: none;
        border-bottom: 1px solid #e5e7eb;
        padding: 1.5rem 2rem;
    }
    .login-portal-logo-img {
        height: 100px;
    }
    .login-portal-logo-text {
        font-size: 2.25rem;
    }
    .login-portal-right {
        width: 100%;
        padding: 1.5rem 2rem;
    }
}
</style>

<script>
document.getElementById('togglePassword')?.addEventListener('click', function() {
    var el = document.getElementById('password');
    var icon = document.getElementById('eyeIcon');
    if (el.type === 'password') {
        el.type = 'text';
        icon.classList.remove('bi-eye');
        icon.classList.add('bi-eye-slash');
    } else {
        el.type = 'password';
        icon.classList.remove('bi-eye-slash');
        icon.classList.add('bi-eye');
    }
});

window.addEventListener('load', function () {
    try {
        var parts = [
            navigator.userAgent || '',
            navigator.language || '',
            screen.width + 'x' + screen.height,
            String(new Date().getTimezoneOffset())
        ];
        var raw = parts.join('|');
        var hash = 0;
        for (var i = 0; i < raw.length; i++) {
            hash = ((hash << 5) - hash) + raw.charCodeAt(i);
            hash |= 0;
        }
        var input = document.getElementById('device_fingerprint');
        if (input) input.value = 'dev-' + Math.abs(hash);
    } catch (e) {}
});
</script>

@include('auth._register_modal')
@endsection
