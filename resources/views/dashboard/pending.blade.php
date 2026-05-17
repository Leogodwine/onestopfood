@extends('layouts.dashboard')

@section('content')
<div class="page-header">
    <h2>Account Pending Approval</h2>
    <p>Your account verification status</p>
</div>

<div class="dashboard-card">
    <div class="alert alert-warning" style="background-color: #fff3cd; border-left: 4px solid #ffc107; color: #856404;">
        <div class="d-flex align-items-center mb-3">
            <i class="bi bi-clock-history" style="font-size: 2rem; margin-right: 15px;"></i>
            <div>
                <h4 class="alert-heading mb-2" style="color: #856404;">Account Pending Approval</h4>
                <p class="mb-0">
                    Your account is pending admin verification. Once approved, you'll be able to access your full dashboard and all features.
                </p>
            </div>
        </div>
        <hr style="border-color: #ffc107;">
        <div class="d-flex gap-2">
            <a href="{{ route('home') }}" class="btn btn-outline-primary">
                <i class="bi bi-house"></i> Home
            </a>
            <a href="{{ route('verification.show') }}" class="btn btn-primary shadow-sm px-4">
                <i class="bi bi-pencil-square"></i> Update Verification Profile
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
