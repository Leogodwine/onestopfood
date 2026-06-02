@extends('layout')

@section('content')
<div class="container py-5" style="max-width: 480px;">
    <div class="card shadow-sm">
        <div class="card-body p-4">
            <h2 class="h4 mb-2 text-center">Two-Factor Verification</h2>
            <p class="text-muted small text-center mb-4">
                For security, please enter the 6-digit verification code sent to your admin email.
            </p>

            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @if (config('app.show_developer_hints') && session('two_factor_hint'))
                <div class="alert alert-info small">
                    <strong>Development only:</strong> Your 2FA code is
                    <code>{{ session('two_factor_hint') }}</code>.
                </div>
            @endif

            <form method="POST" action="{{ route('login.2fa.verify') }}">
                @csrf

                <div class="mb-3">
                    <label for="code" class="form-label">Verification Code</label>
                    <input
                        id="code"
                        type="text"
                        name="code"
                        inputmode="numeric"
                        pattern="[0-9]*"
                        maxlength="6"
                        class="form-control @error('code') is-invalid @enderror"
                        placeholder="Enter 6-digit code"
                        required
                        autofocus
                    >
                </div>

                <button type="submit" class="btn btn-primary w-100">
                    Verify & Continue
                </button>
            </form>

            <p class="text-center mt-3 mb-0">
                <a href="{{ route('login') }}" class="small">{{ __('auth.back_to_sign_in') }}</a>
            </p>
        </div>
    </div>
</div>
@endsection

