@extends('errors.layout')

@section('title', 'Session Expired')

@section('icon')
    <div class="error-icon error-icon-warning"><i class="bi bi-shield-exclamation"></i></div>
@endsection

@section('heading', 'Your session has expired')

@section('message')
    For your security, this form or link is no longer valid. Please go back to the home page and try again. If you were signing in or verifying your account, start that process again from the beginning.
@endsection

@section('secondary_action')
    <a href="{{ route('login') }}" class="btn btn-outline-secondary px-4">
        <i class="bi bi-box-arrow-in-right me-1"></i> Sign in
    </a>
@endsection
