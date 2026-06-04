{{--
    Heritage Stories grid (home + meals). Expects $heritageMeals collection with chef.chefProfile, average_rating.
    Optional: $heritageCurrency (default '$') — use 'TZS' on meals page.
--}}
@php $heritageCurrency = $heritageCurrency ?? '$'; @endphp
@if(isset($heritageMeals) && $heritageMeals->isNotEmpty())
<section class="container mb-5" id="heritage-stories">
    <div class="mb-4 d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3">
        <div>
            <h3 class="mb-1">Heritage Stories</h3>
            <p class="text-muted mb-0">Discover the origins and family traditions behind our most special dishes. Each dish carries a story worth savoring.</p>
        </div>
        <div class="d-flex flex-wrap gap-2 align-items-center">
            <button type="button" class="btn btn-sm btn-outline-secondary" id="heritage-clear-filters" aria-label="Clear filters">
                Clear Filters
            </button>
            <a href="{{ route('stories.index') }}" class="btn btn-view-outline">View All Stories</a>
        </div>
    </div>

    @php
        $heritageCategories = $heritageMeals->pluck('category')->filter()->unique()->sort()->values();
    @endphp
    <div class="d-flex flex-column flex-md-row flex-wrap gap-2 align-items-stretch align-items-md-center justify-content-center mb-4">
        <form method="GET" action="{{ route('meals.index') }}" class="heritage-search-form d-flex justify-content-center">
            <input type="hidden" name="filter" value="heritage">
            <div class="input-group input-group-sm heritage-search-input-group">
                <input type="text" name="search" class="form-control heritage-search-input border-end-0" placeholder="Search dishes, chefs, origins, or stories..." value="{{ request('search') }}" aria-label="Search heritage dishes">
                <button type="submit" class="input-group-text heritage-search-icon-btn bg-white border-start-0" title="Search" aria-label="Search">
                    <i class="bi bi-search text-muted"></i>
                </button>
            </div>
        </form>
        <div class="d-flex flex-wrap gap-2 align-items-center justify-content-center" id="heritage-filter-pills">
        <button type="button" class="btn btn-sm rounded-pill heritage-filter-pill active" data-category="">
            All Dishes ({{ $heritageMeals->count() }})
        </button>
        @foreach($heritageCategories as $cat)
            @php $count = $heritageMeals->where('category', $cat)->count(); @endphp
            @if($count > 0)
                <button type="button" class="btn btn-sm rounded-pill heritage-filter-pill" data-category="{{ $cat }}">
                    {{ $cat }} ({{ $count }})
                </button>
            @endif
        @endforeach
        </div>
    </div>

    <div class="row g-4" id="heritage-dishes-grid">
        @foreach($heritageMeals as $meal)
            @php
                $category = strtolower($meal->category ?? '');
                $origin = strtolower($meal->origin ?? '');
                $name = strtolower($meal->name ?? '');
                $storyImage = 'food 01.jpeg';
                if ($name === 'korean bbq tacos' || str_contains($category, 'korean') || str_contains($category, 'fusion')) {
                    $storyImage = 'american food 02.jpg';
                } elseif (str_contains($category, 'italian') || str_contains($category, 'dessert') || str_contains($category, 'chocolate') || str_contains($origin, 'umbrian') || str_contains($origin, 'turin')) {
                    $storyImage = 'european food 01.jpg';
                } elseif (str_contains($category, 'bbq') || str_contains($category, 'american') || str_contains($origin, 'hyogo') || str_contains($origin, 'texas')) {
                    $storyImage = 'american food 01.jpeg';
                } elseif (str_contains($category, 'sushi') || str_contains($category, 'japanese')) {
                    $storyImage = 'asian food 01.jpg';
                } elseif (str_contains($category, 'african') || str_contains($origin, 'africa')) {
                    $storyImage = 'african food 0' . (($loop->index % 6) + 1) . '.jpg';
                } else {
                    $fallbacks = ['food 01.jpeg', 'food 03.png', 'juice and food 01.jpg', 'one stop food 01.jpeg'];
                    $storyImage = $fallbacks[$loop->index % count($fallbacks)];
                }
                $cuisineType = strtolower($meal->chef?->chefProfile?->cuisine_type ?? '');
                $chefImage = 'african chef 01.jpg';
                if (str_contains($cuisineType, 'asian') || str_contains($cuisineType, 'fusion')) {
                    $chefImage = 'asian chef 0' . (($loop->index % 4) + 1) . '.jpg';
                } elseif (str_contains($cuisineType, 'american') || str_contains($cuisineType, 'bbq')) {
                    $chefImage = 'american chef 0' . (($loop->index % 2) + 1) . '.jpg';
                } elseif (str_contains($cuisineType, 'european') || str_contains($cuisineType, 'french') || str_contains($cuisineType, 'italian') || str_contains($cuisineType, 'mediterranean')) {
                    $chefImage = 'european chef 0' . (($loop->index % 2) + 1) . '.jpg';
                }
            @endphp
            <div class="col-12 col-sm-6 col-lg-4 heritage-dish-card" data-category="{{ $meal->category ?? '' }}">
                <div class="card heritage-card meal-card h-100">
                    <div class="position-relative heritage-image-wrap">
                        @if($meal->image_path)
                            <img src="{{ asset('storage/' . $meal->image_path) }}" class="meal-image heritage-image" alt="{{ $meal->name }}" onerror="this.src='{{ asset('images/' . $storyImage) }}'">
                        @elseif(file_exists(public_path('images/' . $storyImage)))
                            <img src="{{ asset('images/' . $storyImage) }}" class="meal-image heritage-image" alt="{{ $meal->name }}">
                        @else
                            <div class="meal-image heritage-image bg-light d-flex align-items-center justify-content-center">
                                <i class="bi bi-image text-muted fs-1"></i>
                            </div>
                        @endif
                        <div class="heritage-badges-overlay">
                            @if($meal->is_popular)
                                <span class="badge badge-popular">Popular</span>
                            @endif
                            <span class="badge badge-heritage">Heritage</span>
                            @if($meal->heritage_story)
                                <span class="badge bg-secondary">Story</span>
                            @endif
                        </div>
                        <div class="heritage-like-dislike-overlay">
                            <button type="button" class="heritage-like-btn" data-meal-id="{{ $meal->id }}" title="Like" aria-label="Like">
                                <i class="bi bi-heart"></i>
                            </button>
                            <button type="button" class="heritage-dislike-btn" data-meal-id="{{ $meal->id }}" title="Dislike" aria-label="Dislike">
                                <i class="bi bi-hand-thumbs-down"></i>
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <h6 class="card-title mb-1">{{ $meal->name }}</h6>
                        <div class="fw-bold text-primary mb-1">{{ $heritageCurrency }} {{ number_format((float)$meal->price, 2) }}</div>
                        <p class="card-text small text-muted mb-2">{{ \Illuminate\Support\Str::limit($meal->description ?? '', 90) }}</p>
                        @if($meal->origin)
                            <p class="small text-primary fw-semibold mb-1">{{ $meal->origin }}</p>
                        @endif
                        <div class="heritage-chef-meta mb-2">
                            <div class="d-flex flex-wrap gap-1 align-items-center small text-muted">
                                <span class="heritage-chef-photo-wrap me-1">
                                    @if($meal->chef?->avatar_url)
                                        <img src="{{ $meal->chef->avatar_url }}" alt="{{ $meal->chef->name }}" class="heritage-chef-photo rounded-circle">
                                    @else
                                        <span class="heritage-chef-photo heritage-chef-initial rounded-circle">{{ $meal->chef ? strtoupper(substr($meal->chef->name, 0, 1)) : '?' }}</span>
                                    @endif
                                </span>
                                <span>Chef {{ $meal->chef?->name ?? '—' }}</span>
                                @if(($meal->average_rating ?? 0) > 0)
                                    <span><i class="bi bi-star-fill text-warning"></i> {{ number_format($meal->average_rating, 1) }}</span>
                                @endif
                                @if($meal->category)
                                    <span class="badge bg-light text-dark">{{ $meal->category }}</span>
                                @endif
                                @if($meal->prep_time_minutes)
                                    <span class="ms-auto">{{ $meal->prep_time_minutes }} min</span>
                                @endif
                            </div>
                            @if($meal->dietary_tags)
                                <div class="d-flex flex-wrap gap-1 mt-1">
                                    @foreach(array_map('trim', explode(',', $meal->dietary_tags)) as $tag)
                                        @if($tag)
                                            <span class="badge bg-light text-dark border">{{ $tag }}</span>
                                        @endif
                                    @endforeach
                                </div>
                            @endif
                        </div>
                        <details class="heritage-chef-details mb-2">
                            <summary class="heritage-chef-summary">
                                <span>Chef's Heritage</span>
                                <i class="bi bi-chevron-down heritage-chef-arrow"></i>
                            </summary>
                            <div class="heritage-chef-block p-2 rounded bg-light mt-1">
                                <div class="d-flex align-items-center gap-2 mb-1">
                                    @if($meal->chef?->avatar_url)
                                        <img src="{{ $meal->chef->avatar_url }}" alt="{{ $meal->chef->name }}" class="heritage-chef-photo-sm rounded-circle">
                                    @else
                                        <span class="heritage-chef-photo-sm heritage-chef-initial rounded-circle">{{ $meal->chef ? strtoupper(substr($meal->chef->name, 0, 1)) : '?' }}</span>
                                    @endif
                                    <span class="fw-semibold small">{{ $meal->chef?->name ?? '—' }}</span>
                                </div>
                                <h6 class="heritage-signature-title small text-muted mb-1">Signature Creation</h6>
                                <p class="heritage-chef-text small mb-1">
                                    @if($meal->chef?->chefProfile?->heritage_story)
                                        {{ \Illuminate\Support\Str::limit($meal->chef->chefProfile->heritage_story, 140) }}
                                    @else
                                        This dish represents Chef {{ $meal->chef?->name ?? 'our' }}'s culinary heritage and has been perfected through years of dedication.
                                    @endif
                                </p>
                                @if($meal->chef)
                                    <a href="{{ route('chefs.show', $meal->chef) }}" class="heritage-view-chef-link small">View Chef Profile →</a>
                                @endif
                            </div>
                        </details>
                        @if($meal->heritage_story)
                            <details class="heritage-dish-story-details mb-2">
                                <summary class="heritage-dish-story-summary">
                                    <span>Dish Story</span>
                                    <i class="bi bi-chevron-down heritage-dish-story-arrow"></i>
                                </summary>
                                <div class="heritage-dish-story-block p-2 rounded bg-light mt-1">
                                    <p class="small mb-0">{{ $meal->heritage_story }}</p>
                                </div>
                            </details>
                        @endif
                        <div class="d-flex gap-2 mt-2">
                            <form method="POST" action="{{ route('cart.add', $meal) }}" class="d-inline">
                                @csrf
                                <input type="hidden" name="quantity" value="1">
                                <button class="btn btn-sm btn-success" type="submit">
                                    <i class="bi bi-cart-plus"></i> Add Heritage Dish
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</section>

<style>
    .heritage-image-wrap { position: relative; overflow: hidden; }
    .heritage-badges-overlay { position: absolute; top: 0.5rem; right: 0.5rem; display: flex; flex-wrap: wrap; gap: 0.25rem; justify-content: flex-end; z-index: 2; }
    .heritage-like-dislike-overlay { position: absolute; top: 0.5rem; left: 0.5rem; display: flex; gap: 0.35rem; z-index: 2; }
    .heritage-like-btn, .heritage-dislike-btn { width: 36px; height: 36px; border-radius: 50%; border: none; background: rgba(255,255,255,0.9); color: #6c757d; display: flex; align-items: center; justify-content: center; font-size: 1rem; cursor: pointer; transition: all 0.2s ease; box-shadow: 0 2px 8px rgba(0,0,0,0.1); }
    .heritage-like-btn:hover, .heritage-dislike-btn:hover { background: #fff; color: #212529; transform: scale(1.08); }
    .heritage-like-btn.liked { color: #dc3545; background: rgba(255,255,255,0.95); }
    .heritage-like-btn.liked i { font-weight: bold; }
    .heritage-dislike-btn.disliked { color: #6c757d; background: rgba(255,255,255,0.95); }
    .heritage-card { border-radius: 14px; overflow: hidden; border: 1px solid #e9ecef; box-shadow: 0 6px 20px rgba(0,0,0,0.05); transition: transform 0.2s ease, box-shadow 0.2s ease; }
    .heritage-card:hover { transform: translateY(-4px); box-shadow: 0 10px 26px rgba(0,0,0,0.10); }
    .heritage-image { height: 190px; object-fit: cover; }
    @media (min-width: 992px) { .heritage-image { height: 210px; } }
    .heritage-search-input-group { max-width: 280px; margin: 0 auto; }
    .heritage-search-input-group .form-control { border-radius: 50px 0 0 50px; border-end: 0; }
    .heritage-search-input-group .heritage-search-icon-btn { border-radius: 0 50px 50px 0; cursor: pointer; padding: 0.25rem 0.75rem; }
    .heritage-search-input-group .heritage-search-icon-btn:hover { background-color: #f8f9fa !important; }
    .heritage-filter-pill { border: 1px solid #dee2e6; background: #fff; color: #495057; }
    .heritage-filter-pill:hover { background: #f8f9fa; border-color: #e66220; color: #e66220; }
    .heritage-filter-pill.active { background: #e66220; border-color: #e66220; color: #fff; }
    .heritage-dish-card.hidden { display: none !important; }
    .heritage-chef-photo { width: 28px; height: 28px; object-fit: cover; vertical-align: middle; }
    .heritage-chef-photo-sm { width: 32px; height: 32px; object-fit: cover; flex-shrink: 0; }
    .heritage-chef-initial { display: inline-flex; align-items: center; justify-content: center; background: #e66220; color: #fff; font-size: 0.75rem; font-weight: 600; }
    span.heritage-chef-photo { min-width: 28px; min-height: 28px; }
    span.heritage-chef-photo-sm { min-width: 32px; min-height: 32px; font-size: 0.7rem; }
    .heritage-chef-photo-wrap { display: inline-flex; align-items: center; }
    .heritage-chef-details { border: 1px solid #e9ecef; border-radius: 8px; }
    .heritage-chef-summary { display: flex; align-items: center; justify-content: space-between; padding: 0.4rem 0.6rem; cursor: pointer; list-style: none; font-size: 0.875rem; font-weight: 600; color: #495057; }
    .heritage-chef-summary::-webkit-details-marker { display: none; }
    .heritage-chef-summary::marker { content: none; }
    .heritage-chef-arrow { font-size: 0.75rem; transition: transform 0.2s ease; flex-shrink: 0; margin-left: 0.5rem; }
    .heritage-chef-details[open] .heritage-chef-arrow { transform: rotate(180deg); }
    .heritage-chef-block { border: 1px solid #e9ecef; }
    .heritage-signature-title { font-size: 0.7rem; text-transform: uppercase; letter-spacing: 0.5px; }
    .heritage-view-chef-link { color: #e66220; text-decoration: none; font-weight: 600; }
    .heritage-view-chef-link:hover { color: #c95218; text-decoration: underline; }
    .heritage-dish-story-details { border: 1px solid #e9ecef; border-radius: 8px; }
    .heritage-dish-story-summary { display: flex; align-items: center; justify-content: space-between; padding: 0.4rem 0.6rem; cursor: pointer; list-style: none; font-size: 0.875rem; font-weight: 600; color: #495057; }
    .heritage-dish-story-summary::-webkit-details-marker { display: none; }
    .heritage-dish-story-summary::marker { content: none; }
    .heritage-dish-story-arrow { font-size: 0.75rem; transition: transform 0.2s ease; flex-shrink: 0; margin-left: 0.5rem; }
    .heritage-dish-story-details[open] .heritage-dish-story-arrow { transform: rotate(180deg); }
    .heritage-dish-story-block { border: 1px solid #e9ecef; }
    #heritage-stories .btn-view-outline { border-color: #e66220; color: #e66220; background: transparent; }
    #heritage-stories .btn-view-outline:hover { background: #e66220; border-color: #e66220; color: #fff; }
    #heritage-stories .badge-heritage {
        background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
        color: white;
    }
    #heritage-stories .badge-popular {
        background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
        color: white;
    }
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var section = document.getElementById('heritage-stories');
    if (!section) return;
    var pills = section.querySelectorAll('.heritage-filter-pill');
    var cards = section.querySelectorAll('.heritage-dish-card');
    var clearBtn = document.getElementById('heritage-clear-filters');
    if (!pills.length || !cards.length) return;

    function filterByCategory(cat) {
        cards.forEach(function(card) {
            var cardCat = (card.getAttribute('data-category') || '').trim();
            if (!cat || cardCat === cat) {
                card.classList.remove('hidden');
            } else {
                card.classList.add('hidden');
            }
        });
        pills.forEach(function(p) {
            p.classList.toggle('active', (p.getAttribute('data-category') || '') === (cat || ''));
        });
    }

    pills.forEach(function(pill) {
        pill.addEventListener('click', function() {
            filterByCategory(this.getAttribute('data-category') || '');
        });
    });
    if (clearBtn) {
        clearBtn.addEventListener('click', function() {
            filterByCategory('');
        });
    }

    section.querySelectorAll('.heritage-like-btn').forEach(function(btn) {
        btn.addEventListener('click', function() {
            var card = this.closest('.card');
            var dislikeBtn = card ? card.querySelector('.heritage-dislike-btn') : null;
            var icon = this.querySelector('i');
            this.classList.toggle('liked');
            if (dislikeBtn) dislikeBtn.classList.remove('disliked');
            icon.classList.toggle('bi-heart', !this.classList.contains('liked'));
            icon.classList.toggle('bi-heart-fill', this.classList.contains('liked'));
        });
    });
    section.querySelectorAll('.heritage-dislike-btn').forEach(function(btn) {
        btn.addEventListener('click', function() {
            var card = this.closest('.card');
            var likeBtn = card ? card.querySelector('.heritage-like-btn') : null;
            this.classList.toggle('disliked');
            if (likeBtn) {
                likeBtn.classList.remove('liked');
                var icon = likeBtn.querySelector('i');
                if (icon) { icon.classList.remove('bi-heart-fill'); icon.classList.add('bi-heart'); }
            }
        });
    });
});
</script>
@endif
