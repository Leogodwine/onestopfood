@extends(auth()->check() ? 'layouts.dashboard' : 'layout')

@section('content')
@php
    $cuisineType = strtolower($chef->chefProfile?->cuisine_type ?? '');
    $chefImage = null;
    if (str_contains($cuisineType, 'african')) {
        $chefImage = 'african chef 01.jpg';
    } elseif (str_contains($cuisineType, 'asian') || str_contains($cuisineType, 'fusion')) {
        $chefImage = 'asian chef 01.jpg';
    } elseif (str_contains($cuisineType, 'american') || str_contains($cuisineType, 'bbq')) {
        $chefImage = 'american chef 01.jpg';
    } elseif (str_contains($cuisineType, 'european') || str_contains($cuisineType, 'french') || str_contains($cuisineType, 'italian') || str_contains($cuisineType, 'mediterranean')) {
        $chefImage = 'european chef 01.jpg';
    } else {
        $chefImage = 'african chef 01.jpg';
    }
@endphp

<!-- Page Header: breadcrumb Home / Chefs / Chef name -->
<div class="chef-page-wrap">
    <div class="container chef-page-container">
        <nav aria-label="breadcrumb" class="chef-breadcrumb mb-2">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
                <li class="breadcrumb-item"><a href="{{ route('chefs.index') }}">Chefs</a></li>
                <li class="breadcrumb-item active" aria-current="page">{{ $chef->name }}</li>
            </ol>
        </nav>
        <h1 class="chef-page-title">{{ $chef->name }}</h1>

    <div class="row align-items-start g-4">
        <!-- Left: Chef profile (Heritage Story, About) -->
        <div class="col-12 col-lg-4 col-xl-3 order-2 order-lg-1">
            <div class="chef-profile-card sticky-top">
                <div class="chef-profile-card-body">
                    <div class="text-center mb-3">
                        @if(file_exists(public_path('images/' . $chefImage)))
                            <img src="{{ asset('images/' . $chefImage) }}"
                                 alt="{{ $chef->name }}"
                                 class="chef-profile-compact-img"
                                 onerror="this.onerror=null; this.src='{{ asset('images/african chef 01.jpg') }}';">
                        @else
                            <div class="chef-profile-avatar-placeholder">
                                <span>{{ substr($chef->name, 0, 1) }}</span>
                            </div>
                        @endif
                    </div>
                    <p class="chef-cuisine">{{ $chef->chefProfile?->cuisine_type ?? 'Cuisine' }}</p>
                    @if($chef->chefProfile?->years_experience)
                        <p class="chef-meta">{{ $chef->chefProfile->years_experience }} years experience</p>
                    @endif
                    <div class="d-flex justify-content-center gap-2 flex-wrap mb-3">
                        @if($chef->average_rating > 0)
                            <span class="chef-badge chef-badge-rating">
                                <i class="bi bi-star-fill"></i> {{ number_format($chef->average_rating, 1) }}
                            </span>
                        @endif
                        <span class="chef-badge chef-badge-orders">{{ $totalOrders }}+ orders</span>
                    </div>
                    @if($chef->chefProfile?->heritage_story)
                        <div class="chef-block">
                            <h6 class="chef-block-title"><i class="bi bi-book"></i> Heritage Story</h6>
                            <p class="chef-block-text">{{ $chef->chefProfile->heritage_story }}</p>
                        </div>
                    @endif
                    @if($chef->chefProfile?->bio)
                        <div class="chef-block">
                            <h6 class="chef-block-title">About</h6>
                            <p class="chef-block-text">{{ $chef->chefProfile->bio }}</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Right: Signature dishes -->
        <div class="col-12 col-lg-8 col-xl-9 order-1 order-lg-2">
            <h4 class="chef-page-heading mb-4">Signature Dishes</h4>
            <div class="row g-4">
            @forelse($meals as $meal)
                <div class="col-12 col-sm-6 col-lg-4">
                    <div class="meal-card">
                        <div class="meal-card-image-wrap">
                            @if($meal->image_path)
                                <img src="{{ asset('storage/' . $meal->image_path) }}" class="meal-card-img" alt="{{ $meal->name }}" onerror="this.src='{{ asset('images/food 01.jpeg') }}'">
                            @else
                                <div class="meal-card-img-placeholder">
                                    <i class="bi bi-image"></i>
                                </div>
                            @endif
                            <div class="meal-card-badges">
                                @if($meal->is_heritage)
                                    <span class="meal-badge meal-badge-heritage"><i class="bi bi-star-fill"></i> Heritage</span>
                                @endif
                                @if($meal->is_popular)
                                    <span class="meal-badge meal-badge-popular"><i class="bi bi-fire"></i> Popular</span>
                                @endif
                            </div>
                            @if($meal->average_rating > 0)
                                <span class="meal-badge meal-badge-rating"><i class="bi bi-star-fill"></i> {{ number_format($meal->average_rating, 1) }}</span>
                            @endif
                        </div>
                        <div class="meal-card-body">
                            @if($meal->category)
                                <span class="meal-card-category">{{ $meal->category }}</span>
                            @endif
                            <h6 class="meal-card-title">{{ $meal->name }}</h6>
                            @if($meal->origin)
                                <p class="meal-card-origin"><i class="bi bi-geo-alt"></i> {{ $meal->origin }}</p>
                            @endif
                            <p class="meal-card-desc">{{ \Illuminate\Support\Str::limit($meal->description ?? '', 80) }}</p>
                            <div class="meal-card-footer">
                                <div class="meal-card-meta">
                                    <span class="meal-card-price">TZS {{ number_format((float)$meal->price, 2) }}</span>
                                    @if($meal->prep_time_minutes)
                                        <span class="meal-card-time"><i class="bi bi-clock"></i> {{ $meal->prep_time_minutes }}m</span>
                                    @endif
                                </div>
                                <form method="POST" action="{{ route('cart.add', $meal) }}" class="meal-card-form">
                                    @csrf
                                    <input class="form-control form-control-sm meal-card-qty" type="number" min="1" name="quantity" value="1">
                                    <button class="btn btn-sm meal-card-add-btn" type="submit"><i class="bi bi-cart-plus"></i> Add</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
                @empty
                    <div class="col-12">
                        <div class="meal-empty-state">
                            <i class="bi bi-egg-fried"></i>
                            <h5>No dishes available yet</h5>
                            <p>This chef hasn’t added signature dishes.</p>
                        </div>
                    </div>
            @endforelse
            </div>
        </div>
    </div>
    </div>
</div>

<style>
/* Modern, professional (ChatGPT-style) chef page */
.chef-page-wrap {
    background: #f7f7f8;
    min-height: 100%;
    padding: 1.5rem 0 3rem;
}
.chef-page-container {
    max-width: 1200px;
    margin: 0 auto;
}
.chef-breadcrumb {
    font-size: 0.8125rem;
    color: #6e6e80;
}
.chef-breadcrumb .breadcrumb-item a {
    color: #6e6e80;
    text-decoration: none;
}
.chef-breadcrumb .breadcrumb-item a:hover {
    color: #0d0d0d;
}
.chef-breadcrumb .breadcrumb-item.active {
    color: #0d0d0d;
    font-weight: 500;
}
.chef-page-title,
.chef-page-heading {
    font-family: var(--font-headline);
    font-weight: 600;
    color: #0d0d0d;
    letter-spacing: -0.02em;
}
.chef-page-title {
    font-size: 1.75rem;
    margin-bottom: 1.5rem;
}
.chef-page-heading {
    font-size: 1.25rem;
    font-weight: 600;
    color: #0d0d0d;
}
/* Chef profile card */
.chef-profile-card {
    background: #fff;
    border: 1px solid #e5e5e5;
    border-radius: 12px;
    overflow: hidden;
    top: 1rem;
}
.chef-profile-card-body {
    padding: 1.5rem;
}
.chef-profile-compact-img {
    width: 80px;
    height: 80px;
    object-fit: cover;
    border-radius: 50%;
}
.chef-profile-avatar-placeholder {
    width: 80px;
    height: 80px;
    border-radius: 50%;
    background: #e5e5e5;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-weight: 600;
    font-size: 1.5rem;
    color: #6e6e80;
}
.chef-cuisine {
    font-size: 0.9375rem;
    font-weight: 600;
    color: #0d0d0d;
    text-align: center;
    margin-bottom: 0.25rem;
}
.chef-meta {
    font-size: 0.8125rem;
    color: #6e6e80;
    text-align: center;
    margin-bottom: 0.75rem;
}
.chef-badge {
    font-size: 0.75rem;
    padding: 0.25rem 0.5rem;
    border-radius: 6px;
    font-weight: 500;
}
.chef-badge-rating {
    background: #fef3c7;
    color: #92400e;
}
.chef-badge-orders {
    background: #d1fae5;
    color: #065f46;
}
.chef-block {
    margin-bottom: 1rem;
}
.chef-block:last-child {
    margin-bottom: 0;
}
.chef-block-title {
    font-size: 0.8125rem;
    font-weight: 600;
    color: #0d0d0d;
    margin-bottom: 0.375rem;
}
.chef-block-title i {
    margin-right: 0.25rem;
    color: #6e6e80;
}
.chef-block-text {
    font-size: 0.8125rem;
    color: #6e6e80;
    line-height: 1.5;
    margin: 0;
}
/* Meal cards */
.meal-card {
    background: #fff;
    border: 1px solid #e5e5e5;
    border-radius: 12px;
    overflow: hidden;
    height: 100%;
    display: flex;
    flex-direction: column;
    transition: border-color 0.2s ease, box-shadow 0.2s ease;
}
.meal-card:hover {
    border-color: #d1d5db;
    box-shadow: 0 4px 12px rgba(0,0,0,0.06);
}
.meal-card-image-wrap {
    position: relative;
    flex-shrink: 0;
}
.meal-card-img {
    width: 100%;
    height: 200px;
    object-fit: cover;
    display: block;
}
.meal-card-img-placeholder {
    width: 100%;
    height: 200px;
    background: #f7f7f8;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #9ca3af;
    font-size: 1.5rem;
}
.meal-card-badges {
    position: absolute;
    top: 0.75rem;
    left: 0.75rem;
    display: flex;
    flex-wrap: wrap;
    gap: 0.375rem;
}
.meal-badge {
    font-size: 0.6875rem;
    font-weight: 500;
    padding: 0.2rem 0.4rem;
    border-radius: 6px;
}
.meal-badge-heritage {
    background: rgba(0,0,0,0.6);
    color: #fff;
}
.meal-badge-popular {
    background: rgba(59, 130, 246, 0.9);
    color: #fff;
}
.meal-badge-rating {
    position: absolute;
    top: 0.75rem;
    right: 0.75rem;
    background: #fef3c7;
    color: #92400e;
}
.meal-card-body {
    padding: 1rem 1.25rem;
    flex: 1;
    display: flex;
    flex-direction: column;
}
.meal-card-category {
    font-size: 0.6875rem;
    color: #6e6e80;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    margin-bottom: 0.375rem;
}
.meal-card-title {
    font-size: 1rem;
    font-weight: 600;
    color: #0d0d0d;
    margin-bottom: 0.25rem;
    line-height: 1.3;
}
.meal-card-origin {
    font-size: 0.8125rem;
    color: #6e6e80;
    margin-bottom: 0.5rem;
}
.meal-card-desc {
    font-size: 0.8125rem;
    color: #6e6e80;
    line-height: 1.45;
    flex-grow: 1;
    margin-bottom: 1rem;
}
.meal-card-footer {
    padding-top: 1rem;
    border-top: 1px solid #e5e5e5;
}
.meal-card-meta {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 0.75rem;
}
.meal-card-price {
    font-weight: 600;
    font-size: 1rem;
    color: #0d0d0d;
}
.meal-card-time {
    font-size: 0.8125rem;
    color: #6e6e80;
}
.meal-card-form {
    display: flex;
    gap: 0.5rem;
}
.meal-card-qty {
    max-width: 56px;
    font-size: 0.875rem;
    border: 1px solid #e5e5e5;
    border-radius: 8px;
}
.meal-card-add-btn {
    flex: 1;
    background: #28a745;
    color: #fff;
    border: none;
    border-radius: 8px;
    font-weight: 500;
    font-size: 0.875rem;
    padding: 0.4rem 0.75rem;
}
.meal-card-add-btn:hover {
    background: #1e7e34;
    color: #fff;
}
.meal-empty-state {
    background: #fff;
    border: 1px solid #e5e5e5;
    border-radius: 12px;
    padding: 3rem 2rem;
    text-align: center;
    color: #6e6e80;
}
.meal-empty-state i {
    font-size: 3rem;
    color: #d1d5db;
    display: block;
    margin-bottom: 1rem;
}
.meal-empty-state h5 {
    font-size: 1rem;
    font-weight: 600;
    color: #0d0d0d;
    margin-bottom: 0.25rem;
}
</style>
@endsection
