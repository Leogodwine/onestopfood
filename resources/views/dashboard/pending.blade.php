@extends('layouts.dashboard')

@section('content')
<div class="page-header page-header-split">
    <div class="d-flex justify-content-between align-items-center page-header-top">
        <h2 class="mb-0">Account Pending Approval</h2>
        <div class="page-header-actions">
            <a href="{{ route('verification.status') }}" class="btn btn-sm btn-primary page-header-action-btn">
                <i class="bi bi-list-check"></i> Track status
            </a>
            <a href="{{ route('notifications.index') }}" class="btn btn-sm btn-outline-secondary page-header-action-btn">
                <i class="bi bi-bell"></i> Notifications
            </a>
        </div>
    </div>
    <p class="text-muted mb-0 page-header-subtitle">Your account verification status on OneStopFood</p>
</div>

<div class="dashboard-card">
    <div class="alert alert-warning mb-0" style="background-color: #fff3cd; border-left: 4px solid #ffc107; color: #856404;">
        <div class="d-flex align-items-center mb-3">
            <i class="bi bi-clock-history" style="font-size: 2rem; margin-right: 15px;"></i>
            <div>
                <h4 class="alert-heading mb-2" style="color: #856404;">Account Pending Approval</h4>
                <p class="mb-0">
                    Your account is pending admin verification. You will receive email and SMS updates when your documents are reviewed and when your account is approved.
                </p>
            </div>
        </div>
        <hr style="border-color: #ffc107;">
        <div class="d-flex flex-wrap gap-2">
            <a href="{{ route('verification.status') }}" class="btn btn-primary shadow-sm">
                <i class="bi bi-list-check"></i> Review details &amp; approval steps
            </a>
            <a href="{{ route('verification.show') }}" class="btn btn-outline-primary">
                <i class="bi bi-pencil-square"></i> Update verification profile
            </a>
            <a href="{{ route('home') }}" class="btn btn-outline-secondary">
                <i class="bi bi-house"></i> Home
            </a>
            <form method="POST" action="{{ route('logout') }}" class="m-0">
                @csrf
                <button type="submit" class="btn btn-outline-secondary">
                    <i class="bi bi-box-arrow-right"></i> Logout
                </button>
            </form>
        </div>
    </div>
</div>
@endsection
