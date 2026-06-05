@extends(auth()->check() ? 'layouts.dashboard' : 'layout')

@section('content')
<!-- Page Header -->
<div class="container-fluid bg-light py-4 mb-4">
    <div class="container">
        <div class="text-center px-4">
            <h2 class="mb-1 headline-font">Meals</h2>
            <p class="text-muted mb-0">Browse available meals from expert chefs</p>
        </div>
    </div>
</div>

<div class="container">
    <!-- Search & Filter Section -->
    <div class="meals-search-filter card border-0 shadow-sm mb-4 overflow-hidden">
        <div class="card-body p-0">
            <form method="GET" action="{{ route('meals.index') }}" class="meals-search-form">
                <div class="row g-0 align-items-stretch">
                    <div class="col-12 col-md-5 col-lg-4">
                        <div class="position-relative p-2 p-md-3">
                            <i class="bi bi-search position-absolute text-muted meals-search-icon"></i>
                            <input type="text" name="search" value="{{ request('search') }}" class="form-control form-control-sm rounded-2 ps-3 meals-search-input" placeholder="Dish name, chef, or category…" aria-label="Search meals">
                        </div>
                    </div>
                    <div class="col-12 col-md col-lg-3 border-top border-md-top-0 border-md-start">
                        <div class="p-2 p-md-3">
                            <select name="category" class="form-select form-select-sm rounded-2 meals-search-select" aria-label="Filter by category">
                                <option value="">All categories</option>
                                @php $standardCats = \App\Models\Meal::getStandardCategories(); @endphp
                                @foreach($categories ?? [] as $category)
                                    <option value="{{ $category }}" @selected(request('category') === $category) @if(isset($standardCats[$category])) title="{{ $standardCats[$category]['description'] }} — Examples: {{ $standardCats[$category]['examples'] }}" @endif>{{ $category }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-12 col-md col-lg-3 border-top border-md-top-0 border-md-start">
                        <div class="p-2 p-md-3">
                            <select name="sort" class="form-select form-select-sm rounded-2 meals-search-select" aria-label="Sort results">
                                <option value="latest" @selected(request('sort') === 'latest')>Latest first</option>
                                <option value="popular" @selected(request('sort') === 'popular')>Popular first</option>
                                <option value="name" @selected(request('sort') === 'name')>Name (A–Z)</option>
                                <option value="price_low" @selected(request('sort') === 'price_low')>Price: low to high</option>
                                <option value="price_high" @selected(request('sort') === 'price_high')>Price: high to low</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-12 col-md-auto border-top border-md-top-0 border-md-start d-flex align-items-center">
                        <div class="w-100 d-flex gap-2 p-2 p-md-3">
                            <button type="submit" class="btn btn-success btn-sm flex-grow-1 flex-md-grow-0 px-3 rounded-2" aria-label="Apply search and filters">
                                <i class="bi bi-search me-1"></i>Search
                            </button>
                            @if(request('search') || request('category') || request('sort'))
                                <a href="{{ route('meals.index') }}" class="btn btn-outline-secondary btn-sm rounded-2" aria-label="Clear all filters">Clear</a>
                            @endif
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
    <style>
        .meals-search-filter .form-control:focus, .meals-search-filter .form-select:focus { border-color: rgba(34, 197, 94, 0.5); box-shadow: 0 0 0 0.2rem rgba(34, 197, 94, 0.15); }
        .meals-search-icon { left: 0.65rem; top: 50%; transform: translateY(-50%); font-size: 0.85rem; pointer-events: none; }
        .meals-search-form .meals-search-input.ps-3 { padding-left: 2rem !important; }
        .meals-search-form .meals-search-input, .meals-search-form .meals-search-select { font-size: 0.875rem; }
        @media (min-width: 768px) { .meals-search-form .border-md-start { border-left: 1px solid var(--bs-border-color) !important; } }
    </style>

    <!-- Results Count -->
    @if($meals->total() > 0)
        <div class="d-flex justify-content-between align-items-center mb-3">
            <p class="text-muted mb-0">
                Showing <strong>{{ $meals->firstItem() }}</strong> to <strong>{{ $meals->lastItem() }}</strong> of <strong>{{ $meals->total() }}</strong> meals
            </p>
            <div class="d-flex gap-2">
                @if(request('category') || request('search') || request('sort'))
                    <a href="{{ route('meals.index') }}" class="btn btn-sm btn-outline-secondary">
                        <i class="bi bi-x-circle"></i> Clear Filters
                    </a>
                @endif
            </div>
        </div>
    @endif

    <!-- Meals Grid -->
    <div class="row g-4 mb-4">
        @forelse($meals as $meal)
            <div class="col-md-6 col-lg-4 col-xl-3">
                <div class="card h-100 shadow-sm border-0 meal-product-card">
                    <!-- Image Section -->
                    <div class="position-relative">
                        @include('meals.partials.thumbnail', ['meal' => $meal, 'class' => 'card-img-top meal-card-img'])
                        
                        <!-- Badges Overlay -->
                        <div class="position-absolute top-0 start-0 p-2 d-flex flex-column gap-1">
                            @if($meal->is_heritage)
                                <span class="badge bg-gradient" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">
                                    <i class="bi bi-star-fill"></i> Heritage
                                </span>
                            @endif
                            @if($meal->is_popular)
                                <span class="badge bg-gradient" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);">
                                    <i class="bi bi-fire"></i> Popular
                                </span>
                            @endif
                        </div>

                        <!-- Rating Badge -->
                        @php
                            $rating = $meal->average_rating;
                            $reviewCount = $meal->total_reviews;
                        @endphp
                        @if($rating > 0)
                            <div class="position-absolute top-0 end-0 p-2">
                                <span class="badge bg-warning text-dark">
                                    <i class="bi bi-star-fill"></i> {{ number_format($rating, 1) }}
                                    @if($reviewCount > 0)
                                        <small>({{ $reviewCount }})</small>
                                    @endif
                                </span>
                            </div>
                        @endif
                    </div>

                    <!-- Card Body -->
                    <div class="card-body meal-card-body d-flex flex-column">
                        @if($meal->category)
                            <div class="mb-1">
                                <span class="badge bg-light text-dark border small">{{ $meal->category }}</span>
                            </div>
                        @endif

                        <h6 class="card-title mb-1 fw-bold meal-card-title">{{ $meal->name }}</h6>
                        <small class="text-muted d-block mb-2">Chef: {{ $meal->chef?->name }}</small>

                        @if($meal->description)
                            <p class="card-text text-muted small mb-2 flex-grow-1 meal-card-desc">
                                {{ \Illuminate\Support\Str::limit($meal->description, 60) }}
                            </p>
                        @endif

                        <!-- Price, quantity and Add to Cart -->
                        <div class="mt-auto pt-2 border-top">
                            <div class="d-flex justify-content-between align-items-center gap-2 flex-wrap">
                                <div>
                                    <span class="text-success fw-bold">{{ money($meal->price) }}</span>
                                    @if($meal->prep_time_minutes)
                                        <small class="text-muted ms-1"><i class="bi bi-clock"></i> {{ $meal->prep_time_minutes }}m</small>
                                    @endif
                                </div>
                                <form method="POST" action="{{ route('cart.add', $meal) }}" class="d-flex align-items-center gap-1 meal-card-form">
                                    @csrf
                                    <input type="number" name="quantity" class="form-control form-control-sm meal-qty-input text-center" value="1" min="1" max="99" style="width: 60px;" aria-label="Quantity">
                                    <button class="btn btn-success btn-sm meal-add-btn" type="submit" title="Add to cart">
                                        <i class="bi bi-cart-plus"></i> Add
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12">
                <div class="card shadow-sm border-0">
                    <div class="card-body text-center py-5">
                        <i class="bi bi-inbox" style="font-size: 4rem; color: #ccc;"></i>
                        <h4 class="mt-3 text-muted">No meals found</h4>
                        <p class="text-muted">Try adjusting your filters or search terms</p>
                        @if(request('category') || request('search') || request('sort'))
                            <a href="{{ route('meals.index') }}" class="btn btn-success mt-2">
                                <i class="bi bi-arrow-left"></i> Clear Filters
                            </a>
                        @endif
                    </div>
                </div>
            </div>
        @endforelse
    </div>

    <!-- Pagination -->
    @if($meals->hasPages())
        <div class="d-flex justify-content-center">
            {{ $meals->links() }}
        </div>
    @endif

    <div class="mt-5 pt-4 border-top">
        @include('partials.heritage-stories-section')
    </div>
</div>

<style>
.meal-product-card {
    transition: all 0.25s ease;
    border: 1px solid #e9ecef !important;
}

.meal-product-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 6px 20px rgba(0,0,0,0.1) !important;
}

.meal-card-img,
.meal-card-img-placeholder {
    height: 140px;
    object-fit: cover;
}
.meal-card-img-placeholder {
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.75rem;
}

.meal-card-body {
    padding: 0.75rem 1rem;
}
.meal-card-title {
    font-size: 0.95rem;
    line-height: 1.3;
}
.meal-card-desc {
    font-size: 0.8rem;
    line-height: 1.35;
}

.meal-product-card .card-img-top {
    transition: transform 0.25s ease;
}
.meal-product-card:hover .card-img-top {
    transform: scale(1.03);
}

.bg-gradient {
    color: white;
    font-weight: 600;
}

.meal-add-btn {
    padding: 0.2rem 0.5rem;
    font-size: 0.75rem;
}
</style>
@endsection
