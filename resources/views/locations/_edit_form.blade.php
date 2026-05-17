<form method="POST" action="{{ route('locations.update', $location) }}" id="editAddressForm" class="edit-address-form">
    @csrf
    @method('PUT')

    <div id="editFormErrorAlert" class="alert alert-danger d-none" role="alert">
        <strong><i class="bi bi-exclamation-triangle me-1"></i> Please fix the errors below.</strong>
        <ul id="editFormErrorList" class="mb-0 mt-2"></ul>
    </div>

    <div class="mb-3">
        <label class="form-label fw-semibold">
            <i class="bi bi-bookmark text-muted"></i> Label (e.g., Home, Office)
        </label>
        <input class="form-control @error('label') is-invalid @enderror"
               type="text"
               name="label"
               value="{{ old('label', $location->label) }}"
               placeholder="Home">
        @error('label')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="mb-3">
        <label class="form-label fw-semibold">
            <i class="bi bi-geo-alt text-muted"></i> Address Line <span class="text-danger">*</span>
        </label>
        <textarea class="form-control @error('address_line') is-invalid @enderror"
                  name="address_line"
                  rows="2"
                  required
                  placeholder="Enter your full address">{{ old('address_line', $location->address_line) }}</textarea>
        @error('address_line')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="row g-2">
        <div class="col-md-6 mb-3">
            <label class="form-label fw-semibold">
                <i class="bi bi-building text-muted"></i> City
            </label>
            <input class="form-control @error('city') is-invalid @enderror"
                   type="text"
                   name="city"
                   value="{{ old('city', $location->city) }}"
                   placeholder="Dar es Salaam">
            @error('city')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
        <div class="col-md-6 mb-3">
            <label class="form-label fw-semibold">
                <i class="bi bi-geo text-muted"></i> Region
            </label>
            <input class="form-control @error('region') is-invalid @enderror"
                   type="text"
                   name="region"
                   value="{{ old('region', $location->region) }}"
                   placeholder="Dar es Salaam">
            @error('region')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>

    <div class="mb-3">
        <label class="form-label fw-semibold">
            <i class="bi bi-globe text-muted"></i> Country
        </label>
        <input class="form-control @error('country') is-invalid @enderror"
               type="text"
               name="country"
               value="{{ old('country', $location->country ?? 'Tanzania') }}">
        @error('country')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="row g-2">
        <div class="col-md-6 mb-3">
            <label class="form-label fw-semibold">
                <i class="bi bi-geo-alt-fill text-muted"></i> Latitude (optional)
            </label>
            <input class="form-control @error('latitude') is-invalid @enderror"
                   type="number"
                   step="any"
                   name="latitude"
                   value="{{ old('latitude', $location->latitude) }}"
                   placeholder="e.g., -6.7924">
            <small class="form-text text-muted">For precise location tracking</small>
            @error('latitude')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
        <div class="col-md-6 mb-3">
            <label class="form-label fw-semibold">
                <i class="bi bi-geo-alt-fill text-muted"></i> Longitude (optional)
            </label>
            <input class="form-control @error('longitude') is-invalid @enderror"
                   type="number"
                   step="any"
                   name="longitude"
                   value="{{ old('longitude', $location->longitude) }}"
                   placeholder="e.g., 39.2083">
            <small class="form-text text-muted">For precise location tracking</small>
            @error('longitude')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>

    @if(!$location->is_primary)
        <div class="form-check mb-3">
            <input class="form-check-input"
                   type="checkbox"
                   name="is_primary"
                   id="edit_is_primary"
                   value="1"
                   {{ old('is_primary', $location->is_primary) ? 'checked' : '' }}>
            <label class="form-check-label" for="edit_is_primary">
                <strong>Set as primary address</strong>
                <small class="d-block text-muted">This will be your default delivery location</small>
            </label>
        </div>
    @else
        <div class="alert alert-light border mb-3 py-2 small">
            <i class="bi bi-info-circle text-primary"></i> This is your primary address. To change it, set another address as primary.
        </div>
    @endif

    <div class="d-flex gap-2 justify-content-end pt-2 border-top">
        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
            <i class="bi bi-x-circle"></i> Cancel
        </button>
        <button type="submit" class="btn btn-success">
            <i class="bi bi-check-circle"></i> Update Address
        </button>
    </div>
</form>
