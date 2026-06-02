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
                    <div class="login-portal-partner-links">
                        <a href="javascript:void(0)" class="login-portal-link login-portal-link-highlight" onclick="openRegisterModal('chef')">
                            <i class="bi bi-egg-fried"></i> Become a Chef
                        </a>
                        <a href="javascript:void(0)" class="login-portal-link login-portal-link-highlight" onclick="openRegisterModal('traveler')">
                            <i class="bi bi-truck"></i> Become a Traveler / Dropper
                        </a>
                    </div>
                    <div class="login-portal-links">
                        <a href="{{ route('docs.user-manual') }}" class="login-portal-link" target="_blank" rel="noopener">
                            <i class="bi bi-book"></i> User Manual
                        </a>
                        <a href="{{ route('docs.guidelines') }}" class="login-portal-link login-portal-link-highlight" target="_blank" rel="noopener">
                            <i class="bi bi-journal-text"></i> Guidelines!
                        </a>
                        <a href="{{ route('home') }}" class="login-portal-link login-portal-back-home">
                            <i class="bi bi-house"></i> Back home
                        </a>
                    </div>
                </div>

                <!-- Right: Sign in form -->
                <div class="login-portal-right">
                    <div class="login-portal-title">
                        <h1 class="login-portal-org">Welcome to OneStop</h1>
                        <p class="login-portal-sub">Food Order &amp; Delivery System</p>
                    </div>

                    @php
                        $socialProviderNames = array_values(array_filter([
                            ($googleSignInEnabled ?? false) ? 'Google' : null,
                            ($facebookSignInEnabled ?? false) ? 'Facebook' : null,
                        ]));
                        $socialSignInHint = count($socialProviderNames) === 0
                            ? null
                            : (count($socialProviderNames) === 1
                                ? 'Sign in with your '.$socialProviderNames[0].' account'
                                : 'Sign in with your '.implode(' or ', $socialProviderNames).' account');
                    @endphp

                    @if ($socialSignInHint)
                        <p class="login-portal-signin-with text-muted small mb-2">{{ $socialSignInHint }}</p>
                        @include('auth._social_auth_buttons', ['class' => 'login-portal-social mb-4'])

                        <div class="login-portal-divider mb-3"><span>or</span></div>
                    @endif

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
                                <button class="btn btn-outline-secondary js-toggle-password" type="button" id="togglePassword"
                                        data-target="password"
                                        data-show-label="{{ __('auth.show_password') }}"
                                        data-hide-label="{{ __('auth.hide_password') }}"
                                        aria-label="{{ __('auth.show_password') }}">
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
                            {{ __('auth.sign_in') }}
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

.login-portal-partner-links {
    display: flex;
    flex-direction: column;
    align-items: flex-start;
    gap: 0.5rem;
    margin-top: 0.5rem;
    width: 100%;
    align-self: stretch;
    padding-inline-start: 2.5rem;
}

.login-portal-partner-links .login-portal-link,
.login-portal-links .login-portal-link {
    display: inline-flex;
    align-items: center;
    gap: 0.4rem;
    font-size: 0.95rem;
}

.login-portal-links {
    display: flex;
    flex-direction: column;
    align-items: flex-start;
    gap: 0.5rem;
    width: 100%;
    align-self: stretch;
    padding-inline-start: 2.5rem;
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

@include('auth.partials.password-tools')

<script>
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

@include('auth._social_auth_intent_script')
@include('auth._register_modal')
@if(!empty($socialSignupState))
    @include('auth._social_signup_modal')
@endif
@endsection
