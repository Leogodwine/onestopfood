@extends('layouts.dashboard')

@section('content')
<div class="page-header page-header-split">
    <div class="d-flex justify-content-between align-items-center page-header-top">
        <h2 class="mb-0">Edit Profile</h2>
        <div class="page-header-actions">
            <a href="{{ route('profile.show') }}" class="btn btn-sm btn-outline-secondary page-header-action-btn">
                <i class="bi bi-arrow-left"></i> Back
            </a>
        </div>
    </div>
    <p class="text-muted mb-0 page-header-subtitle">{{ __('auth.edit_profile_subtitle') }}</p>
</div>

<div class="row g-3 g-lg-4 profile-edit-row">
    <div class="col-lg-5 col-xl-4">
        <div class="dashboard-card h-100 profile-edit-card">
            <div class="card-header">
                <h5 class="card-title mb-0"><i class="bi bi-person-circle"></i> {{ __('dashboard.profile') }}</h5>
            </div>
            <form method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data">
                @csrf

                <div class="mb-4 text-center profile-avatar-block">
                    <label class="form-label d-block">Profile Picture</label>
                    <div class="position-relative d-inline-block">
                        @if($user->avatar_url)
                            <img src="{{ $user->avatar_url }}" alt="Avatar" class="rounded-circle border" id="avatarPreview" style="width: 120px; height: 120px; object-fit: cover;">
                        @else
                            <div class="rounded-circle border d-flex align-items-center justify-content-center bg-light" id="avatarPreview" style="width: 120px; height: 120px; font-size: 3rem; color: var(--text-secondary);">
                                {{ strtoupper(substr($user->name, 0, 1)) }}
                            </div>
                        @endif
                    </div>
                    <div class="mt-2">
                        <input type="file" name="avatar" id="avatar" class="form-control form-control-sm w-auto mx-auto" accept="image/jpeg,image/jpg,image/png,image/gif,image/webp">
                        <small class="text-muted d-block mt-1">JPEG, PNG, GIF or WebP. Max 2 MB.</small>
                    </div>
                    @if($user->avatar_url)
                        <div class="form-check form-check-inline mt-2">
                            <input class="form-check-input" type="checkbox" name="remove_avatar" value="1" id="remove_avatar">
                            <label class="form-check-label text-danger" for="remove_avatar">Remove current photo</label>
                        </div>
                    @endif
                    @error('avatar')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-4">
                    <label for="name" class="form-label">Name</label>
                    <input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $user->name) }}" required>
                    @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
                    <a href="{{ route('profile.show') }}" class="btn btn-outline-secondary">Cancel</a>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check2-circle"></i> Save profile
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div class="col-lg-7 col-xl-8">
        <div class="dashboard-card h-100 profile-edit-card" id="change-password">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="bi bi-shield-lock"></i>
                    {{ ($isSocialOnlyUser ?? false) ? __('auth.profile_set_password') : __('auth.profile_change_password') }}
                </h5>
            </div>
            <form method="POST" action="{{ route('profile.password.update') }}">
                @csrf

                @if($isSocialOnlyUser ?? false)
                    <p class="text-muted small mb-3">{{ __('auth.profile_set_password_social') }}</p>
                @elseif($user->isSelfRegisteredRole())
                    <p class="text-muted small mb-3">{{ __('auth.profile_password_self_service_hint') }}</p>
                @else
                    <p class="text-muted small mb-3">{{ __('auth.profile_password_admin_hint') }}</p>
                @endif

                @unless($isSocialOnlyUser ?? false)
                    <div class="mb-3">
                        <label class="form-label" for="current_password">{{ __('auth.current_password_label') }}</label>
                        <div class="input-group password-input-group">
                            <input
                                type="password"
                                name="current_password"
                                id="current_password"
                                class="form-control @error('current_password') is-invalid @enderror"
                                placeholder="{{ __('auth.current_password_placeholder') }}"
                                autocomplete="current-password"
                                value="{{ old('current_password') }}"
                            >
                            <button
                                type="button"
                                class="btn btn-outline-secondary btn-toggle-password js-toggle-password"
                                data-target="current_password"
                                data-show-label="{{ __('auth.show_password') }}"
                                data-hide-label="{{ __('auth.hide_password') }}"
                                aria-label="{{ __('auth.show_password') }}"
                            >
                                <i class="bi bi-eye"></i>
                            </button>
                        </div>
                        @error('current_password')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>
                @endunless

                @php
                    $profilePasswordError = $errors->has('password') && $errors->first('password') !== __('auth.password_confirmed')
                        ? $errors->first('password')
                        : null;
                @endphp

                <div class="mb-3">
                    @include('auth.partials.password-input', [
                        'inputId' => 'profile_password',
                        'name' => 'password',
                        'label' => __('auth.password_label'),
                        'size' => 'sm',
                        'required' => true,
                        'withHint' => true,
                        'withChoice' => true,
                        'weakErrorFullWidth' => true,
                        'hintId' => 'profilePasswordWeakError',
                        'confirmSelector' => '#profile_password_confirmation',
                        'invalid' => (bool) $profilePasswordError,
                        'errorMessage' => $profilePasswordError,
                    ])
                </div>

                <div class="mb-3">
                    <label class="form-label" for="profile_password_confirmation">{{ __('auth.password_confirm_label') }}</label>
                    <div class="input-group password-input-group">
                        <input
                            type="password"
                            name="password_confirmation"
                            id="profile_password_confirmation"
                            class="form-control @error('password_confirmation') is-invalid @enderror"
                            placeholder="{{ __('auth.password_confirm_placeholder') }}"
                            minlength="8"
                            autocomplete="new-password"
                            required
                        >
                        <button
                            type="button"
                            class="btn btn-outline-secondary btn-toggle-password js-toggle-password"
                            data-target="profile_password_confirmation"
                            data-show-label="{{ __('auth.show_password') }}"
                            data-hide-label="{{ __('auth.hide_password') }}"
                            aria-label="{{ __('auth.show_password') }}"
                        >
                            <i class="bi bi-eye"></i>
                        </button>
                    </div>
                    @error('password_confirmation')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>

                <div class="password-weak-error-row mb-4">
                    @include('auth.partials.password-weak-error', [
                        'hintId' => 'profilePasswordWeakError',
                        'visible' => (bool) $profilePasswordError,
                        'message' => __('auth.password_hint'),
                    ])
                </div>

                <div class="d-flex flex-wrap justify-content-end">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-shield-check"></i> Update password
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.getElementById('avatar')?.addEventListener('change', function(e) {
    var file = e.target.files?.[0];
    var preview = document.getElementById('avatarPreview');
    if (!file || !preview) return;
    var reader = new FileReader();
    reader.onload = function() {
        if (preview.tagName === 'IMG') {
            preview.src = reader.result;
        } else {
            var img = document.createElement('img');
            img.id = 'avatarPreview';
            img.className = 'rounded-circle border';
            img.style.cssText = 'width: 120px; height: 120px; object-fit: cover;';
            img.alt = 'Avatar';
            img.src = reader.result;
            preview.parentNode.replaceChild(img, preview);
        }
    };
    reader.readAsDataURL(file);
});
</script>
@endpush
@endsection
