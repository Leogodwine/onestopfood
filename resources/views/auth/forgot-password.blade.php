@extends('layout')

@section('content')
<div class="login-portal-wrapper">
    <div class="login-portal-container">
        <div class="login-portal-card">
            <div class="login-portal-row">
                <!-- Left: Brand -->
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
                        <a href="{{ route('login') }}" class="login-portal-link login-portal-back-home">
                            <i class="bi bi-arrow-left"></i> {{ __('auth.back_to_sign_in') }}
                        </a>
                    </div>
                </div>

                <!-- Right: Form -->
                <div class="login-portal-right">
                    <div class="login-portal-title">
                        <h1 class="login-portal-org">FORGOT PASSWORD</h1>
                        <p class="login-portal-sub">Enter your email to receive a reset link</p>
                    </div>

                    <form method="POST" action="{{ route('password.email') }}" class="login-portal-form">
                        @csrf

                        <div class="mb-4">
                            <label for="email" class="form-label">Email Address</label>
                            <input type="email" 
                                   name="email" 
                                   id="email" 
                                   class="form-control form-control-lg @error('email') is-invalid @enderror" 
                                   value="{{ old('email') }}" 
                                   placeholder="Enter your registered email" 
                                   required 
                                   autofocus>
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <button type="submit" class="btn btn-success btn-lg w-100 login-portal-btn">
                            Send Reset Link
                        </button>
                    </form>

                    <p class="login-portal-register text-center mt-4 mb-0">
                        Remembered your password? <a href="{{ route('login') }}" class="fw-semibold">{{ __('auth.sign_in_here') }}</a>
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
/* Reusing login portal styles for consistency */
.login-portal-wrapper {
    min-height: 80vh;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 2rem 1rem;
    background: linear-gradient(180deg, #f0f7f4 0%, #e8f5e9 100%);
}
.login-portal-container { width: 100%; max-width: 820px; }
.login-portal-card { background: #fff; border-radius: 14px; box-shadow: 0 4px 24px rgba(0,0,0,0.08); border: 1px solid #e5e7eb; overflow: hidden; }
.login-portal-row { display: flex; align-items: stretch; min-height: 380px; flex-wrap: wrap; }
.login-portal-left { flex: 1 1 42%; min-width: 240px; text-align: center; display: flex; flex-direction: column; justify-content: center; align-items: center; gap: 1.5rem; padding: 2.5rem; background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%); border-right: 1px solid #e5e7eb; }
.login-portal-logo-img { height: 140px; width: auto; max-width: 100%; object-fit: contain; }
.login-portal-logo-text { font-family: sans-serif; font-size: 3rem; font-weight: 700; color: #22c55e; }
.login-portal-right { flex: 1 1 340px; min-width: 0; padding: 2rem 2.5rem; display: flex; flex-direction: column; justify-content: center; }
.login-portal-org { font-size: 1.35rem; font-weight: 700; letter-spacing: 0.08em; color: #1f2937; margin-bottom: 0.25rem; }
.login-portal-sub { font-size: 0.8rem; font-weight: 600; color: #6b7280; margin-bottom: 1.5rem; }
.login-portal-form .form-label { font-weight: 600; font-size: 0.9rem; color: #374151; }
.login-portal-form .form-control-lg { border-radius: 8px; border: 1px solid #d1d5db; }
.login-portal-form .form-control-lg:focus { border-color: #22c55e; box-shadow: 0 0 0 3px rgba(34, 197, 94, 0.2); }
.login-portal-btn { border-radius: 8px; font-weight: 600; padding: 0.75rem 1rem; }
.login-portal-register { font-size: 0.9rem; color: #6b7280; }
.login-portal-register a { color: #22c55e; text-decoration: none; }

@media (max-width: 575.98px) {
    .login-portal-row { flex-direction: column; }
    .login-portal-left { border-right: none; border-bottom: 1px solid #e5e7eb; padding: 1.5rem 2rem; }
    .login-portal-logo-img { height: 100px; }
    .login-portal-right { padding: 1.5rem 2rem; }
}
</style>
@endsection
