@extends('layouts.dashboard')

@section('content')
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-1">Add Delivery Address</h2>
            <p class="text-muted mb-0">Add a new delivery location for your orders</p>
        </div>
        <a class="btn btn-outline-secondary" href="{{ route('locations.index') }}">
            <i class="bi bi-arrow-left"></i> Back
        </a>
    </div>

    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-sm border-0">
                <div class="card-body p-4">
                    <form method="POST" action="{{ route('locations.store') }}">
                        @csrf

                        <div class="mb-3">
                            <label class="form-label fw-semibold">
                                <i class="bi bi-bookmark"></i> Label (e.g., Home, Office)
                            </label>
                            <input class="form-control form-control-lg @error('label') is-invalid @enderror" 
                                   type="text" 
                                   name="label" 
                                   value="{{ old('label') }}" 
                                   placeholder="Home">
                            @error('label')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">
                                <i class="bi bi-geo-alt"></i> Address Line <span class="text-danger">*</span>
                            </label>
                            <textarea class="form-control form-control-lg @error('address_line') is-invalid @enderror" 
                                      name="address_line" 
                                      rows="3" 
                                      required
                                      placeholder="Enter your full address">{{ old('address_line') }}</textarea>
                            @error('address_line')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-semibold">
                                    <i class="bi bi-building"></i> City
                                </label>
                                <input class="form-control form-control-lg @error('city') is-invalid @enderror" 
                                       type="text" 
                                       name="city" 
                                       value="{{ old('city') }}" 
                                       placeholder="Dar es Salaam">
                                @error('city')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-semibold">
                                    <i class="bi bi-geo"></i> Region
                                </label>
                                <input class="form-control form-control-lg @error('region') is-invalid @enderror" 
                                       type="text" 
                                       name="region" 
                                       value="{{ old('region') }}" 
                                       placeholder="Dar es Salaam">
                                @error('region')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">
                                <i class="bi bi-globe"></i> Country
                            </label>
                            <input class="form-control form-control-lg @error('country') is-invalid @enderror" 
                                   type="text" 
                                   name="country" 
                                   value="{{ old('country', 'Tanzania') }}">
                            @error('country')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-semibold">
                                    <i class="bi bi-geo-alt-fill"></i> Latitude (optional)
                                </label>
                                <input class="form-control form-control-lg @error('latitude') is-invalid @enderror" 
                                       type="number" 
                                       step="any" 
                                       name="latitude" 
                                       value="{{ old('latitude') }}"
                                       placeholder="e.g., -6.7924">
                                <small class="form-text text-muted">For precise location tracking</small>
                                @error('latitude')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-semibold">
                                    <i class="bi bi-geo-alt-fill"></i> Longitude (optional)
                                </label>
                                <input class="form-control form-control-lg @error('longitude') is-invalid @enderror" 
                                       type="number" 
                                       step="any" 
                                       name="longitude" 
                                       value="{{ old('longitude') }}"
                                       placeholder="e.g., 39.2083">
                                <small class="form-text text-muted">For precise location tracking</small>
                                @error('longitude')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="form-check mb-4">
                            <input class="form-check-input" 
                                   type="checkbox" 
                                   name="is_primary" 
                                   id="is_primary" 
                                   value="1" 
                                   {{ old('is_primary') ? 'checked' : '' }}>
                            <label class="form-check-label" for="is_primary">
                                <strong>Set as primary address</strong>
                                <small class="d-block text-muted">This will be your default delivery location</small>
                            </label>
                        </div>

                        @if ($errors->any())
                            <div class="alert alert-danger">
                                <ul class="mb-0">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <div class="d-flex gap-2">
                            <button class="btn btn-success btn-lg flex-grow-1" type="submit">
                                <i class="bi bi-check-circle"></i> Add Address
                            </button>
                            <a href="{{ route('locations.index') }}" class="btn btn-outline-secondary btn-lg">
                                Cancel
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
