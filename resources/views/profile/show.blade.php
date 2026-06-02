@extends('layouts.dashboard')

@section('content')
<div class="page-header d-flex justify-content-between align-items-center flex-wrap gap-2">
    <div>
        <h2>Profile</h2>
        <p class="text-muted mb-0">Your account information</p>
    </div>
    <a href="{{ route('profile.edit') }}" class="btn btn-primary">
        <i class="bi bi-pencil"></i> Edit Profile
    </a>
</div>

<div class="row justify-content-center">
    <div class="col-md-8 col-lg-6">
        <div class="dashboard-card">
            <div class="card-body text-center py-4">
                <div class="mb-4">
                    @if($user->avatar_url)
                        <img src="{{ $user->avatar_url }}" alt="Profile picture" class="rounded-circle border" style="width: 120px; height: 120px; object-fit: cover;">
                    @else
                        <div class="rounded-circle border d-inline-flex align-items-center justify-content-center bg-light" style="width: 120px; height: 120px; font-size: 3rem; color: var(--text-secondary);">
                            {{ strtoupper(substr($user->name, 0, 1)) }}
                        </div>
                    @endif
                </div>
                <h4 class="mb-1">{{ $user->name }}</h4>
                <p class="text-muted mb-0">{{ $user->email }}</p>
                <p class="mt-2 mb-0">
                    <span class="badge bg-secondary text-capitalize">{{ $user->role }}</span>
                </p>
                @if($user->phone)
                    <p class="mt-3 mb-0 text-muted small">
                        <i class="bi bi-telephone"></i> {{ $user->phone }}
                    </p>
                @endif
                <div class="mt-4 pt-3 border-top">
                    <a href="{{ route('profile.edit') }}" class="btn btn-outline-primary">
                        <i class="bi bi-pencil"></i> Edit Profile
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
