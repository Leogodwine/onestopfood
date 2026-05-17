@extends('layouts.dashboard')

@section('content')
<div class="page-header">
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h2>Add New Meal</h2>
            <p class="text-muted mb-0">Create a new meal offering</p>
        </div>
        <a class="btn btn-outline-primary" href="{{ route('chef.meals.index') }}">
            <i class="bi bi-arrow-left"></i> Back
        </a>
    </div>
</div>

<div class="dashboard-card">
        <form method="POST" action="{{ route('chef.meals.store') }}" enctype="multipart/form-data">
            @csrf
            <div class="mb-3">
                <label class="form-label">Name <span class="text-danger">*</span></label>
                <input class="form-control @error('name') is-invalid @enderror" name="name" value="{{ old('name') }}" required>
                @error('name')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
            </div>
            <div class="mb-3">
                <label class="form-label">Description</label>
                <textarea class="form-control" name="description" rows="3">{{ old('description') }}</textarea>
            </div>
            <div class="mb-3">
                <label class="form-label">Heritage Story (optional)</label>
                <textarea class="form-control" name="heritage_story" rows="3" placeholder="Tell the story behind this dish...">{{ old('heritage_story') }}</textarea>
                <div class="form-text">Share the heritage, tradition, or personal story behind this dish</div>
            </div>
            <div class="mb-3">
                <label class="form-label">Origin (optional)</label>
                <input class="form-control" name="origin" value="{{ old('origin') }}" placeholder="e.g., Umbrian Family Tradition">
                <div class="form-text">Geographic or cultural origin of this dish</div>
            </div>
            <div class="row">
                <div class="col-md-4 mb-3">
                    <label class="form-label">Prep time (minutes)</label>
                    <input class="form-control" type="number" name="prep_time_minutes" min="1" value="{{ old('prep_time_minutes') }}">
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Price <span class="text-danger">*</span></label>
                    <input class="form-control @error('price') is-invalid @enderror" type="number" step="0.01" name="price" min="0" value="{{ old('price') }}" required>
                    @error('price')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Category</label>
                    <select class="form-select" name="category" id="categorySelect">
                        <option value="">Select category (optional)</option>
                        @foreach(\App\Models\Meal::getStandardCategories() as $key => $cat)
                            <option value="{{ $cat['name'] }}" data-description="{{ $cat['description'] }}" data-examples="{{ $cat['examples'] }}" @selected(old('category') === $cat['name'])>
                                {{ $cat['name'] }}
                            </option>
                        @endforeach
                        <option value="__custom__">Other (custom)</option>
                    </select>
                    <input type="text" class="form-control mt-2 d-none" name="category" id="categoryCustom" placeholder="Enter custom category" value="{{ old('category') }}">
                    <div class="form-text" id="categoryHelp">
                        <span id="categoryDesc"></span>
                        <span id="categoryExamples" class="d-block text-muted small mt-1"></span>
                    </div>
                </div>
            </div>
            <div class="mb-3">
                <label class="form-label">Dietary tags (comma separated)</label>
                <input class="form-control" name="dietary_tags" value="{{ old('dietary_tags') }}" placeholder="vegan, halal">
            </div>
            <div class="mb-3">
                <label class="form-label">Meal Image</label>
                <input class="form-control @error('image') is-invalid @enderror" type="file" name="image" accept="image/jpeg,image/jpg,image/png,image/gif">
                <div class="form-text">Max 2MB. JPG, PNG, GIF</div>
                @error('image')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
            </div>
            <div class="row">
                <div class="col-md-4">
                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" name="is_available" id="is_available" value="1" checked>
                        <label class="form-check-label" for="is_available">Available</label>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" name="is_heritage" id="is_heritage" value="1">
                        <label class="form-check-label" for="is_heritage">Heritage Dish</label>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" name="is_popular" id="is_popular" value="1">
                        <label class="form-check-label" for="is_popular">Popular</label>
                    </div>
                </div>
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
                <button class="btn btn-primary" type="submit">
                    <i class="bi bi-check-circle"></i> Create Meal
                </button>
                <a href="{{ route('chef.meals.index') }}" class="btn btn-outline-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection

