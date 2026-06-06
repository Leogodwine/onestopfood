@extends('layouts.dashboard')

@section('content')
<div class="page-header page-header-split">
    <div class="d-flex justify-content-between align-items-center page-header-top">
        <h2 class="mb-0">{{ __('dashboard.profile') }}</h2>
        <div class="page-header-actions">
            <a href="{{ route('profile.edit') }}" class="btn btn-sm btn-primary page-header-action-btn">
                <i class="bi bi-pencil"></i> Edit
            </a>
        </div>
    </div>
    <p class="text-muted mb-0 page-header-subtitle">Your account information</p>
</div>

<div class="row g-3 g-lg-4 profile-edit-row">
    <div class="col-lg-5 col-xl-4">
        <div class="dashboard-card h-100 profile-edit-card">
            <div class="card-header">
                <h5 class="card-title mb-0"><i class="bi bi-person-circle"></i> {{ __('dashboard.profile') }}</h5>
            </div>
            <div class="text-center py-2">
                <div class="mb-3 profile-avatar-block">
                    @if($user->avatar_url)
                        <img src="{{ $user->avatar_url }}" alt="Profile picture" class="rounded-circle border" style="width: 120px; height: 120px; object-fit: cover;">
                    @else
                        <div class="rounded-circle border d-inline-flex align-items-center justify-content-center bg-light" style="width: 120px; height: 120px; font-size: 3rem; color: var(--text-secondary);">
                            {{ strtoupper(substr($user->name, 0, 1)) }}
                        </div>
                    @endif
                </div>
                <h4 class="mb-1">{{ $user->name }}</h4>
                <p class="text-muted mb-2">{{ $user->email }}</p>
                <p class="mb-0">
                    <span class="badge bg-secondary text-capitalize">{{ $user->role }}</span>
                </p>
                @if($user->phone)
                    <p class="mt-3 mb-0 text-muted small">
                        <i class="bi bi-telephone"></i> {{ $user->phone }}
                    </p>
                @endif
                <div class="mt-4 d-flex flex-wrap justify-content-center gap-2">
                    <a href="{{ route('profile.edit') }}" class="btn btn-outline-primary">
                        <i class="bi bi-pencil"></i> Edit Profile
                    </a>
                    @if(in_array($user->role, ['chef', 'traveler'], true))
                        <a href="{{ route('verification.status') }}" class="btn btn-outline-success">
                            <i class="bi bi-list-check"></i> Verification status
                        </a>
                    @endif
                    @if($user->isSelfRegisteredRole())
                        <a href="{{ route('account.settings') }}" class="btn btn-outline-secondary">
                            <i class="bi bi-gear"></i> {{ __('account.settings_title') }}
                        </a>
                    @endif
                    <a href="{{ route('notifications.index') }}" class="btn btn-outline-secondary">
                        <i class="bi bi-bell"></i> Notifications
                        @if(auth()->user()->unreadNotifications->count() > 0)
                            <span class="badge bg-danger">{{ auth()->user()->unreadNotifications->count() }}</span>
                        @endif
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-7 col-xl-8">
        <div class="dashboard-card h-100 profile-edit-card">
            <div class="card-header">
                <h5 class="card-title mb-0"><i class="bi bi-shield-lock"></i> {{ __('auth.profile_change_password') }}</h5>
            </div>
            @if($user->isSelfRegisteredRole())
                <p class="text-muted small mb-3">{{ __('auth.profile_password_self_service_hint') }}</p>
            @else
                <p class="text-muted small mb-3">{{ __('auth.profile_password_admin_hint') }}</p>
            @endif
            <a href="{{ route('profile.edit') }}#change-password" class="btn btn-primary">
                <i class="bi bi-shield-lock"></i> Change password
            </a>
        </div>
    </div>
</div>
@endsection
