@extends('layout')

@section('content')
<style>
    .packaging-image-container {
        position: relative;
        display: inline-block;
        max-width: 100%;
    }
    .fresh-delivery-badge {
        position: absolute;
        top: 14px;
        right: 14px;
        background: rgba(255, 255, 255, 0.92);
        border: 1px solid rgba(0, 0, 0, 0.08);
        border-radius: 12px;
        padding: 10px 12px;
        line-height: 1.1;
        box-shadow: 0 10px 20px rgba(0,0,0,0.10);
        backdrop-filter: blur(6px);
    }
    .fresh-delivery-badge .badge-top {
        font-weight: 800;
        color: #0d6efd; /* bootstrap primary */
        font-size: 1.05rem;
    }
    .fresh-delivery-badge .badge-sub {
        display: block;
        font-size: 0.8rem;
        color: #6c757d;
        margin-top: 2px;
        font-weight: 600;
    }
    .recycle-block {
        margin-top: 1.25rem;
        text-align: center;
    }
    .recyclebin-image-large {
        width: 140px;
        height: 140px;
        object-fit: contain;
        border-radius: 14px;
        border: 1px solid rgba(0, 0, 0, 0.08);
        background: #fff;
        box-shadow: 0 8px 20px rgba(0,0,0,0.1);
    }
    @media (min-width: 768px) {
        .recyclebin-image-large {
            width: 180px;
            height: 180px;
        }
    }
    /* One card for hero + areas + stats */
    .hero-unified-card {
        background: #e6f8ec;
        border-radius: 20px;
        padding: 2rem 1.5rem;
        box-shadow: 0 8px 32px rgba(0,0,0,0.08);
        transition: background-color 0.35s ease, box-shadow 0.35s ease;
    }
    .hero-tagline {
        font-size: 1.05rem;
        color: #212529;
        max-width: 520px;
        margin-left: auto;
        margin-right: auto;
        transition: color 0.35s ease;
    }
    .hero-badge-inline {
        display: inline-flex;
        flex-direction: column;
        align-items: center;
        background: rgba(255, 255, 255, 0.92);
        border: 1px solid rgba(0, 0, 0, 0.08);
        border-radius: 12px;
        padding: 10px 16px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.08);
        transition: background-color 0.35s ease, border-color 0.35s ease, color 0.35s ease;
    }
    .hero-badge-inline .hero-badge-top {
        font-weight: 800;
        color: #0d6efd;
        font-size: 1.1rem;
    }
    .hero-badge-inline .hero-badge-sub {
        display: block;
        font-size: 0.8rem;
        color: #6c757d;
        margin-top: 2px;
        font-weight: 600;
    }
    @media (min-width: 768px) {
        .hero-unified-card {
            padding: 2.5rem 2rem;
        }
    }
    /* Hero side images */
    .hero-side-image {
        max-width: 100%;
        display: block;
    }
    .hero-food-wrap,
    .hero-chef-wrap {
        border-radius: 16px;
        overflow: hidden;
        box-shadow: 0 8px 24px rgba(0,0,0,0.12);
        max-width: 280px;
        margin: 0 auto;
    }
    .hero-food-img,
    .hero-chef-img {
        width: 100%;
        height: auto;
        display: block;
        object-fit: cover;
        max-height: 320px;
    }
    @media (max-width: 991.98px) {
        .hero-food-wrap,
        .hero-chef-wrap {
            max-width: 200px;
        }
        .hero-food-img,
        .hero-chef-img {
            max-height: 240px;
        }
    }
    @media (max-width: 575.98px) {
        .hero-food-wrap,
        .hero-chef-wrap {
            max-width: 160px;
        }
        .hero-food-img,
        .hero-chef-img {
            max-height: 200px;
        }
    }
    .hero-search-input-group { max-width: 520px; width: 100%; margin: 0 auto; }
    .hero-search-input-group .form-control,
    .hero-search-input-group .hero-search-icon-btn { transition: background-color 0.35s ease, border-color 0.35s ease, color 0.35s ease; }
    .hero-search-input-group .form-control { border-radius: 50px 0 0 50px; padding-left: 1.25rem; }
    .hero-search-input-group .form-control:focus { box-shadow: none; border-color: #ced4da; }
    .hero-search-input-group .hero-search-icon-btn { border-radius: 0 50px 50px 0; padding: 0.375rem 1rem; cursor: pointer; }
    .hero-search-input-group .hero-search-icon-btn:hover { background-color: #f8f9fa !important; }
    .hero-stat-item { background: transparent; display: flex; flex-direction: row; align-items: center; justify-content: center; text-align: center; padding: 0.35rem 0.5rem; gap: 0.5rem; transition: color 0.35s ease; }
    .hero-stat-item .material-icons {
        font-family: 'Material Icons';
        font-size: 1.75rem;
        color: #212529;
        flex-shrink: 0;
        transition: color 0.35s ease;
        line-height: 1;
    }
    .hero-stat-item .material-icons.text-warning {
        color: #f59e0b !important;
    }
    .hero-stat-item .hero-stat-content { display: flex; flex-direction: column; align-items: center; text-align: center; }
    .hero-stat-value, .hero-stat-label { transition: color 0.35s ease; }
    .hero-stat-value { font-weight: 700; font-size: 1rem; color: #212529; display: block; }
    .hero-stat-label { font-size: 0.75rem; color: #6c757d; margin-top: 2px; }
    .hero-stats-row { margin-top: 0.5rem; }

    /* Mobile responsive – home page */
    @media (max-width: 767.98px) {
        .hero-unified-card {
            padding: 1.25rem 1rem !important;
        }
        .hero-unified-card .hero-title {
            font-size: 1.75rem !important;
            line-height: 1.25;
        }
        .hero-unified-card .hero-subtitle {
            font-size: 0.9375rem !important;
        }
        .hero-unified-card h3.mb-2.mt-5 {
            font-size: 1rem !important;
            margin-top: 1.5rem !important;
        }
        .hero-unified-card .row.g-4.justify-content-center.text-center.mt-3 {
            gap: 0.75rem !important;
        }
        .hero-unified-card .row.g-4 .col-auto {
            flex: 0 0 auto;
        }
        section.container.mb-5 {
            padding-left: 12px;
            padding-right: 12px;
        }
        section .mb-4.text-center h3,
        section .text-center h3 {
            font-size: 1.25rem !important;
        }
        section .mb-4.text-center p,
        section .text-center p.text-muted {
            font-size: 0.875rem;
        }
        .card .card-img-top[style*="height: 180px"] {
            height: 140px !important;
            object-fit: cover;
        }
        .card-footer.bg-transparent .btn {
            min-height: 44px;
        }
        .card-footer.bg-transparent .d-flex {
            flex-wrap: wrap;
            gap: 0.5rem;
            justify-content: center;
        }
        .packaging-image-container img,
        .packaging-image {
            max-width: 100%;
            height: auto;
        }
    }
    @media (max-width: 575.98px) {
        .hero-unified-card {
            padding: 1rem 0.75rem !important;
        }
        .hero-unified-card .hero-title {
            font-size: 1.5rem !important;
        }
        .hero-unified-card .row.g-3.justify-content-center {
            gap: 0.5rem !important;
        }
        section.container.mb-5 {
            padding-left: 10px;
            padding-right: 10px;
        }
        .meal-card .card-body,
        .card.meal-card .card-body {
            padding: 0.75rem !important;
        }
        .popular-meal-image-wrap { position: relative; overflow: hidden; }
        .popular-badge-overlay { position: absolute; top: 0.5rem; right: 0.5rem; z-index: 2; }
        .meal-card .meal-image,
        .meal-image {
            height: 160px !important;
        }
    }

    /* Meet Our Expert Chefs: 4 cards, larger to fit row width */
    .chefs-section .chef-card,
    .chefs-section .chef-card-featured {
        transition: background-color 0.35s ease, color 0.35s ease, border-color 0.35s ease;
    }
    .chefs-section .chef-card-featured {
        min-height: 280px;
    }
    .chefs-section .chef-card-featured .card-body {
        padding: 1.5rem 1.25rem;
    }
    .chefs-section .chef-card-featured .chef-profile-image {
        width: 110px;
        height: 110px;
        object-fit: cover;
    }
    .chefs-section .chef-card-featured .card-title {
        font-size: 1.1rem;
    }
</style>
<!-- Hero + Delivery Areas + Stats: one card with one background -->
<section class="container mb-5">
    <div class="hero-unified-card">
        <div class="row justify-content-center">
            <div class="col-12 col-lg-8 text-center">
                <h1 class="hero-title">
                    Discover Amazing Dishes<br>
                    <span class="text-success">From Expert Chefs</span>
                </h1>
                <p class="hero-subtitle">
                    Experience culinary excellence with our curated selection of professional chefs. Each dish tells a story, crafted with passion and delivered fresh to your door.
                </p>
                <p class="hero-tagline mt-3 mb-2"> We provide the platform, chefs provide the passion </p>
                <div class="hero-address-search mt-4 mb-4">
                    <form action="{{ route('meals.index') }}" method="GET" class="d-flex flex-column flex-sm-row gap-1 justify-content-center align-items-stretch align-items-sm-center">
                        <div class="input-group hero-search-input-group">
                            <input type="text" name="address" class="form-control border-end-0" placeholder="Enter your address for delivery" value="{{ request('address') }}">
                            <button type="submit" class="input-group-text hero-search-icon-btn border-start-0 bg-white" title="Search" aria-label="Search">
                                <i class="bi bi-search text-muted"></i>
                            </button>
                        </div>
                        <button type="submit" class="btn btn-success px-4 fw-semibold">Find Food</button>
                    </form>
                </div>
                <div class="row g-3 justify-content-center hero-stats-row">
                    <div class="col-6 col-md-4">
                        <div class="hero-stat-item">
                            <i class="material-icons" aria-hidden="true">schedule</i>
                            <div class="hero-stat-content">
                                <span class="hero-stat-value">30 min</span>
                                <span class="hero-stat-label">Average delivery time</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-6 col-md-4">
                        <div class="hero-stat-item">
                            <i class="material-icons text-warning" aria-hidden="true">star</i>
                            <div class="hero-stat-content">
                                <span class="hero-stat-value">4.8</span>
                                <span class="hero-stat-label">From 2000+ reviews</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-6 col-md-4">
                        <div class="hero-stat-item">
                            <i class="material-icons" aria-hidden="true">local_shipping</i>
                            <div class="hero-stat-content">
                                <span class="hero-stat-value">Free delivery</span>
                                <span class="hero-stat-label">On orders over $30</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Professional Packaging Section -->
<section class="container mb-5">
    <div class="row align-items-center">
        <div class="col-md-6 mb-4 mb-md-0">
            <h2 class="mb-3 headline-font">Professional Packaging, <span class="text-success">Restaurant Quality</span></h2>
            <p class="lead">Every order from {{ $siteName ?? config('app.name', 'One Stop') }} comes beautifully packaged with our signature branding. We believe that great food deserves great presentation, from kitchen to your doorstep.</p>
            <div class="row g-3 mt-4">
                <div class="col-12">
                    <div class="d-flex align-items-start mb-3">
                        <i class="bi bi-recycle text-success fs-4 me-3 mt-1"></i>
                        <div>
                            <strong>Eco-Friendly Materials</strong>
                            <p class="small text-muted mb-0">Sustainable packaging that keeps your food fresh and the environment clean.</p>
                        </div>
                    </div>
                </div>
                <div class="col-12">
                    <div class="d-flex align-items-start mb-3">
                        <i class="bi bi-thermometer-half text-primary fs-4 me-3 mt-1"></i>
                        <div>
                            <strong>Temperature Control</strong>
                            <p class="small text-muted mb-0">Insulated containers that maintain optimal temperature during delivery.</p>
                        </div>
                    </div>
                </div>
                <div class="col-12">
                    <div class="d-flex align-items-start">
                        <i class="bi bi-award text-warning fs-4 me-3 mt-1"></i>
                        <div>
                            <strong>Brand Recognition</strong>
                            <p class="small text-muted mb-0">Professional branding that reflects the quality of our partner chefs.</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="mt-4">
                <p class="mb-0"><strong>"Own your own restaurant"</strong> - We provide the platform, you provide the passion.</p>
            </div>
        </div>
        <div class="col-md-6 text-center">
            <div class="packaging-image-container">
                <div class="fresh-delivery-badge">
                    <span class="badge-top">24/7</span>
                    <span class="badge-sub">Fresh Delivery</span>
                </div>
                @if(file_exists(public_path('images/one-stop-food-container.jpeg')))
                    <img src="{{ asset('images/one-stop-food-container.jpeg') }}" 
                         alt="One Stop Professional Food Container" 
                         class="img-fluid rounded shadow-sm packaging-image">
                @elseif(file_exists(public_path('images/one stop food 01.jpeg')))
                    <img src="{{ asset('images/one stop food 01.jpeg') }}" 
                         alt="One Stop Professional Packaging" 
                         class="img-fluid rounded shadow-sm packaging-image">
                @else
                    <div class="bg-light p-5 rounded shadow-sm d-flex align-items-center justify-content-center" style="min-height: 400px;">
                        <div>
                            <i class="bi bi-box-seam text-muted" style="font-size: 4rem;"></i>
                            <p class="text-muted mt-3 mb-0">One Stop Professional Packaging</p>
                        </div>
                    </div>
                @endif
                <div class="recycle-block">
                    @if(file_exists(public_path('images/one stop food recyclebin 01.jpeg')))
                        <img src="{{ asset('images/one stop food recyclebin 01.jpeg') }}" class="recyclebin-image-large" alt="Recyclable">
                    @endif
                    <div class="mt-2">
                        <span class="h4 mb-0 text-success price">100%</span><br>
                        <small class="text-muted">Recyclable</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Popular Dishes -->
<section class="container mb-5">
    <div class="mb-4 d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3">
        <div>
            <h3 class="mb-1">Popular Dishes</h3>
            <p class="text-muted mb-0">Explore our most loved dishes, each with its own unique story and heritage. Crafted by expert chefs with premium ingredients and delivered fresh to your door.</p>
        </div>
        <div class="flex-shrink-0">
            <a href="{{ route('meals.index', ['sort' => 'popular']) }}" class="btn btn-view-outline">View Full Menu</a>
        </div>
    </div>
    <div class="row g-4">
        @php
            $popularMeals = \App\Models\Meal::where('is_available', true)
                ->where('is_popular', true)
                ->with(['chef.chefProfile'])
                ->get()
                ->map(function ($meal) {
                    $meal->average_rating = $meal->average_rating;
                    $meal->total_reviews = $meal->total_reviews;
                    return $meal;
                })
                ->sortByDesc('average_rating')
                ->take(8);
        @endphp
        @forelse($popularMeals as $meal)
            <div class="col-12 col-sm-6 col-md-6 col-lg-3">
                <div class="card meal-card h-100">
                    <div class="position-relative popular-meal-image-wrap">
                        @include('meals.partials.thumbnail', ['meal' => $meal, 'class' => 'meal-image'])
                        <div class="popular-badge-overlay">
                            <span class="badge badge-popular">Popular</span>
                        </div>
                    </div>
                    <div class="card-body">
                        <h6 class="card-title mb-2">{{ $meal->name }}</h6>
                        @if($meal->average_rating > 0)
                            <div class="mb-2">
                                <span class="badge bg-warning text-dark">
                                    <i class="bi bi-star-fill"></i> {{ number_format($meal->average_rating, 1) }}
                                </span>
                                <small class="text-muted">({{ $meal->total_reviews }})</small>
                            </div>
                        @endif
                        <p class="card-text small">{{ \Illuminate\Support\Str::limit($meal->description ?? '', 80) }}</p>
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <div class="fw-bold text-primary">${{ number_format((float)$meal->price, 2) }}</div>
                                <small class="text-muted">Chef: {{ $meal->chef->name }}</small>
                            </div>
                            <form method="POST" action="{{ route('cart.add', $meal) }}" class="d-inline">
                                @csrf
                                <input type="hidden" name="quantity" value="1">
                                <button class="btn btn-sm btn-success" type="submit">
                                    <i class="bi bi-cart-plus"></i> Add
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12">
                <p class="text-muted text-center">No popular dishes available yet.</p>
            </div>
        @endforelse
    </div>
</section>

<!-- Meet Our Expert Chefs -->
<section class="container mb-5 chefs-section">
    <div class="mb-4 d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3">
        <div>
            <h3 class="mb-1">Meet Our Expert Chefs</h3>
            <p class="text-muted mb-0">Discover the culinary artists behind your favorite dishes. Each chef brings unique expertise and passion to create exceptional dining experiences.</p>
        </div>
        <div class="flex-shrink-0">
            <a href="{{ route('chefs.index') }}" class="btn btn-view">View All Chefs</a>
        </div>
    </div>
    <div class="row g-4">
        @php
            $featuredChefs = \App\Models\User::where('role', 'chef')
                ->where('status', 'approved')
                ->with('chefProfile')
                ->get()
                ->map(function ($chef) {
                    $chef->average_rating = $chef->average_rating;
                    $chef->total_reviews = $chef->total_reviews;
                    $chef->total_orders = \App\Models\Order::where('chef_id', $chef->id)->where('status', 'delivered')->count();
                    return $chef;
                })
                ->sortByDesc('average_rating')
                ->take(4);
        @endphp
        @forelse($featuredChefs as $chef)
            @php
                $cuisineType = strtolower($chef->chefProfile?->cuisine_type ?? '');
                $chefImage = null;
                if (str_contains($cuisineType, 'african')) {
                    $chefImage = 'african chef 0' . (($loop->index % 8) + 1) . '.jpg';
                } elseif (str_contains($cuisineType, 'asian') || str_contains($cuisineType, 'fusion')) {
                    $chefImage = 'asian chef 0' . (($loop->index % 4) + 1) . '.jpg';
                } elseif (str_contains($cuisineType, 'american') || str_contains($cuisineType, 'bbq')) {
                    $chefImage = 'american chef 0' . (($loop->index % 2) + 1) . '.jpg';
                } elseif (str_contains($cuisineType, 'european') || str_contains($cuisineType, 'french') || str_contains($cuisineType, 'italian') || str_contains($cuisineType, 'mediterranean')) {
                    $chefImage = 'european chef 0' . (($loop->index % 2) + 1) . '.jpg';
                } else {
                    $chefImage = 'african chef 01.jpg';
                }
            @endphp
            <div class="col-12 col-sm-6 col-lg-3">
                <div class="card chef-card chef-card-featured h-100 text-center d-flex flex-column">
                    <div class="card-body flex-grow-1">
                        <div class="mb-3">
                            @if($chef->avatar_url)
                                <img src="{{ $chef->avatar_url }}"
                                     alt="{{ $chef->name }}"
                                     class="chef-profile-image rounded-circle object-fit-cover">
                            @else
                                <div class="bg-primary rounded-circle d-inline-flex align-items-center justify-content-center chef-profile-image">
                                    <span class="text-white fs-3 fw-bold">{{ strtoupper(substr($chef->name, 0, 1)) }}</span>
                                </div>
                            @endif
                        </div>
                        <h6 class="card-title mb-1">{{ $chef->name }}</h6>
                        <p class="text-muted small mb-1">{{ $chef->chefProfile?->cuisine_type ?? 'Cuisine' }}</p>
                        @if($chef->chefProfile?->years_experience)
                            <p class="text-muted small mb-1">{{ $chef->chefProfile->years_experience }} years</p>
                        @endif
                        @if($chef->average_rating > 0)
                            <p class="text-muted small mb-2">
                                <i class="bi bi-star-fill text-warning"></i>
                                {{ number_format($chef->average_rating, 1) }}
                                @if($chef->total_reviews > 0)
                                    <span>({{ $chef->total_reviews }} reviews)</span>
                                @endif
                            </p>
                        @endif
                    </div>
                    <div class="card-footer bg-transparent border-0 text-center pb-3">
                        <a href="{{ route('chefs.show', $chef) }}" class="btn btn-sm btn-view">View Kitchen</a>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12">
                <p class="text-muted text-center">No chefs available yet.</p>
            </div>
        @endforelse
    </div>
</section>
@endsection
