@extends('layout')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-6">
            <div class="card shadow-lg border-0">
                <div class="card-body p-5 text-center">
                    <h2 class="fw-bold mb-2">Create Account</h2>
                    <p class="text-muted mb-4">Continue in a quick popup form.</p>
                    <button type="button" class="btn btn-success btn-lg" onclick="openRegisterModal()">
                        <i class="bi bi-person-plus"></i> Open Create Account
                    </button>
                    <div class="mt-4">
                        <a class="text-decoration-none" href="{{ route('login') }}">Already have an account? Sign in</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@include('auth._register_modal')

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    // If someone directly visits /register, open the modal immediately
    window.openRegisterModal?.();
});
</script>
@endpush
@endsection
