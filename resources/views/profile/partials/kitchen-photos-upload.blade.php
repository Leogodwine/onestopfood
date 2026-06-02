@php
    $existingPhotos = $profile->kitchen_photos ?? [];
    if (! is_array($existingPhotos)) {
        $existingPhotos = [];
    }
    $photoOne = $existingPhotos[0] ?? null;
    $photoTwo = $existingPhotos[1] ?? null;
@endphp

<div class="kitchen-photos-upload">
    <label class="form-label">Kitchen Photos <span class="text-muted fw-normal">(Required for home kitchens)</span></label>

    @if($photoOne || $photoTwo)
        <div class="d-flex flex-wrap gap-3 mb-3">
            @if($photoOne)
                <div class="text-center">
                    <img src="{{ asset('storage/' . ltrim($photoOne, '/')) }}" alt="Kitchen photo 1" class="rounded border" style="width: 120px; height: 120px; object-fit: cover;">
                    <small class="d-block text-muted mt-1">Photo 1 saved</small>
                </div>
            @endif
            @if($photoTwo)
                <div class="text-center">
                    <img src="{{ asset('storage/' . ltrim($photoTwo, '/')) }}" alt="Kitchen photo 2" class="rounded border" style="width: 120px; height: 120px; object-fit: cover;">
                    <small class="d-block text-muted mt-1">Photo 2 saved</small>
                </div>
            @endif
        </div>
    @endif

    <div class="row g-3">
        <div class="col-md-6">
            <label class="form-label small text-muted">Kitchen Photo 1</label>
            <input type="file"
                   name="kitchen_photos[]"
                   class="form-control @error('kitchen_photos') is-invalid @enderror @error('kitchen_photos.0') is-invalid @enderror"
                   accept="image/*">
            @error('kitchen_photos.0')
                <div class="invalid-feedback d-block">{{ $message }}</div>
            @enderror
        </div>
        <div class="col-md-6">
            <label class="form-label small text-muted">Kitchen Photo 2</label>
            <input type="file"
                   name="kitchen_photos[]"
                   class="form-control @error('kitchen_photos') is-invalid @enderror @error('kitchen_photos.1') is-invalid @enderror"
                   accept="image/*">
            @error('kitchen_photos.1')
                <div class="invalid-feedback d-block">{{ $message }}</div>
            @enderror
        </div>
    </div>

    @error('kitchen_photos')
        <div class="text-danger small mt-1">{{ $message }}</div>
    @enderror

    <small class="text-muted d-block mt-2">Choose one photo in each field. Upload at least 2 clear photos of your cooking area.</small>
</div>
