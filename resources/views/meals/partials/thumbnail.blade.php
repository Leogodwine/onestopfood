{{-- Chef-uploaded meal photo via meals.image route; icon placeholder when none. --}}
@if($meal->image_url)
    <div class="meal-image-zoom-wrap">
        <img src="{{ $meal->image_url }}" class="{{ $class ?? 'meal-image' }}" alt="{{ $meal->name }}" loading="lazy" decoding="async">
    </div>
@else
    <div class="{{ $class ?? 'meal-image' }} bg-light d-flex align-items-center justify-content-center">
        <i class="bi bi-image text-muted fs-1"></i>
    </div>
@endif
