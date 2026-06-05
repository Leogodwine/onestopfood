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
    <p class="text-muted mb-0 page-header-subtitle">Update your name and profile picture</p>
</div>

<div class="row justify-content-center">
    <div class="col-md-8 col-lg-6">
        <div class="dashboard-card">
            <div class="card-header">
                <h5 class="card-title mb-0"><i class="bi bi-person-circle"></i> Edit Profile</h5>
            </div>
            <form method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data">
                @csrf

                <div class="mb-4 text-center">
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

                <div class="d-flex justify-content-between align-items-center">
                    <a href="{{ route('profile.show') }}" class="btn btn-outline-secondary">Cancel</a>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check2-circle"></i> Save changes
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
