@php
    $meal = $meal ?? null;
    $standardCategories = \App\Models\Meal::getStandardCategories();
    $standardNames = collect($standardCategories)->pluck('name');
    $selectedCategory = old('category', $meal->category ?? '');
    $isCustomCategory = $selectedCategory !== '' && ! $standardNames->contains($selectedCategory);
@endphp

<div class="mb-3">
    <label class="form-label">Name <span class="text-danger">*</span></label>
    <input class="form-control @error('name') is-invalid @enderror" name="name" value="{{ old('name', $meal->name ?? '') }}" required>
    @error('name')
        <div class="invalid-feedback d-block">{{ $message }}</div>
    @enderror
</div>
<div class="mb-3">
    <label class="form-label">Description</label>
    <textarea class="form-control" name="description" rows="3">{{ old('description', $meal->description ?? '') }}</textarea>
</div>
<div class="mb-3">
    <label class="form-label">Heritage Story (optional)</label>
    <textarea class="form-control" name="heritage_story" rows="3" placeholder="Tell the story behind this dish...">{{ old('heritage_story', $meal->heritage_story ?? '') }}</textarea>
    <div class="form-text">Share the heritage, tradition, or personal story behind this dish</div>
</div>
<div class="mb-3">
    <label class="form-label">Origin (optional)</label>
    <input class="form-control" name="origin" value="{{ old('origin', $meal->origin ?? '') }}" placeholder="e.g., Umbrian Family Tradition">
    <div class="form-text">Geographic or cultural origin of this dish</div>
</div>
<div class="row">
    <div class="col-md-4 mb-3">
        <label class="form-label">Prep time (minutes)</label>
        <input class="form-control" type="number" name="prep_time_minutes" min="1" value="{{ old('prep_time_minutes', $meal->prep_time_minutes ?? '') }}">
    </div>
    <div class="col-md-4 mb-3">
        <label class="form-label">Price <span class="text-danger">*</span></label>
        <input class="form-control @error('price') is-invalid @enderror" type="number" step="0.01" name="price" min="0" value="{{ old('price', $meal->price ?? '') }}" required>
        @error('price')
            <div class="invalid-feedback d-block">{{ $message }}</div>
        @enderror
    </div>
    <div class="col-md-4 mb-3">
        <label class="form-label">Category</label>
        <select class="form-select" id="categorySelect">
            <option value="">Select category (optional)</option>
            @foreach($standardCategories as $cat)
                <option value="{{ $cat['name'] }}" data-description="{{ $cat['description'] }}" data-examples="{{ $cat['examples'] }}" @selected(! $isCustomCategory && $selectedCategory === $cat['name'])>
                    {{ $cat['name'] }}
                </option>
            @endforeach
            <option value="__custom__" @selected($isCustomCategory)>Other (custom)</option>
        </select>
        <input type="text" class="form-control mt-2 {{ $isCustomCategory ? '' : 'd-none' }}" name="category" id="categoryCustom" placeholder="Enter custom category" value="{{ $isCustomCategory ? $selectedCategory : '' }}">
        <input type="hidden" name="category" id="categoryStandard" value="{{ ! $isCustomCategory ? $selectedCategory : '' }}" @if($isCustomCategory) disabled @endif>
        <div class="form-text" id="categoryHelp">
            <span id="categoryDesc"></span>
            <span id="categoryExamples" class="d-block text-muted small mt-1"></span>
        </div>
    </div>
</div>
<div class="mb-3">
    <label class="form-label">Dietary tags (comma separated)</label>
    <input class="form-control" name="dietary_tags" value="{{ old('dietary_tags', $meal->dietary_tags ?? '') }}" placeholder="vegan, halal">
</div>
<div class="mb-3">
    <label class="form-label">Meal Image</label>
    @if($meal?->image_url)
        <div class="mb-2">
            <img src="{{ $meal->image_url }}" alt="{{ $meal->name }}" class="rounded border" style="max-height: 120px;">
            <div class="form-text">Upload a new image to replace the current one.</div>
        </div>
    @endif
    <input class="form-control @error('image') is-invalid @enderror" type="file" name="image" accept="image/jpeg,image/jpg,image/png,image/gif">
    <div class="form-text">Max 2MB. JPG, PNG, GIF</div>
    @error('image')
        <div class="invalid-feedback d-block">{{ $message }}</div>
    @enderror
</div>
<div class="row">
    <div class="col-md-4">
        <div class="form-check mb-3">
            <input type="hidden" name="is_available" value="0">
            <input class="form-check-input" type="checkbox" name="is_available" id="is_available" value="1" @checked(old('is_available', $meal->is_available ?? true))>
            <label class="form-check-label" for="is_available">Visible to customers</label>
            <div class="form-text">When unchecked, the meal is hidden from the public menu (suspended) but kept in your account.</div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="form-check mb-3">
            <input class="form-check-input" type="checkbox" name="is_heritage" id="is_heritage" value="1" @checked(old('is_heritage', $meal->is_heritage ?? false))>
            <label class="form-check-label" for="is_heritage">Heritage Dish</label>
        </div>
    </div>
    <div class="col-md-4">
        <div class="form-check mb-3">
            <input class="form-check-input" type="checkbox" name="is_popular" id="is_popular" value="1" @checked(old('is_popular', $meal->is_popular ?? false))>
            <label class="form-check-label" for="is_popular">Popular</label>
        </div>
    </div>
</div>

@push('scripts')
<script>
(function () {
    var select = document.getElementById('categorySelect');
    var custom = document.getElementById('categoryCustom');
    var standard = document.getElementById('categoryStandard');
    var desc = document.getElementById('categoryDesc');
    var examples = document.getElementById('categoryExamples');
    if (!select || !custom || !standard) return;

    function syncCategory() {
        var isCustom = select.value === '__custom__';
        custom.classList.toggle('d-none', !isCustom);
        custom.disabled = !isCustom;
        standard.disabled = isCustom;
        if (!isCustom) {
            standard.value = select.value === '' ? '' : select.value;
        }
        var opt = select.options[select.selectedIndex];
        if (desc) desc.textContent = opt.dataset.description || '';
        if (examples) examples.textContent = opt.dataset.examples ? 'Examples: ' + opt.dataset.examples : '';
    }

    select.addEventListener('change', syncCategory);
    syncCategory();
})();
</script>
@endpush
