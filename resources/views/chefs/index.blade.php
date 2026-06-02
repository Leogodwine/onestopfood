@extends(auth()->check() ? 'layouts.dashboard' : 'layout')

@section('content')
<!-- Page Header -->
<div class="container-fluid bg-body-tertiary py-4 mb-4">
    <div class="container">
        <div class="text-center px-4">
            <h2 class="mb-1 headline-font">Meet Our Expert Chefs</h2>
            <p class="text-muted mb-0">Discover the culinary artists behind your favorite dishes</p>
        </div>
    </div>
</div>

<!-- Popular Chefs – prev/next navigation, clear profile images -->
@if(isset($popularChefs) && $popularChefs->isNotEmpty())
<section class="popular-chefs-section py-3 mb-4 bg-white border-bottom border-top">
    <div class="container">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h3 class="h5 mb-0 headline-font">Popular Chefs</h3>
            <div class="d-flex gap-1">
                <button type="button" class="btn btn-outline-secondary btn-sm rounded-circle p-2 popular-chefs-prev" aria-label="Previous chefs">
                    <i class="bi bi-chevron-left"></i>
                </button>
                <button type="button" class="btn btn-outline-secondary btn-sm rounded-circle p-2 popular-chefs-next" aria-label="Next chefs">
                    <i class="bi bi-chevron-right"></i>
                </button>
            </div>
        </div>
        <div class="popular-chefs-wrap">
            <div class="popular-chefs-track d-flex flex-nowrap justify-content-start gap-3 pb-2">
                @foreach($popularChefs as $chef)
                    @php
                        $ct = strtolower($chef->chefProfile?->cuisine_type ?? '');
                        if (str_contains($ct, 'african')) {
                            $chefImg = 'african chef 0' . (($loop->index % 8) + 1) . '.jpg';
                        } elseif (str_contains($ct, 'asian') || str_contains($ct, 'fusion')) {
                            $chefImg = 'asian chef 0' . (($loop->index % 4) + 1) . '.jpg';
                        } elseif (str_contains($ct, 'american') || str_contains($ct, 'bbq')) {
                            $chefImg = 'american chef 0' . (($loop->index % 2) + 1) . '.jpg';
                        } elseif (str_contains($ct, 'european') || str_contains($ct, 'french') || str_contains($ct, 'italian') || str_contains($ct, 'mediterranean')) {
                            $chefImg = 'european chef 0' . (($loop->index % 2) + 1) . '.jpg';
                        } else {
                            $chefImg = 'african chef 01.jpg';
                        }
                    @endphp
                    <a href="{{ route('chefs.show', $chef) }}" class="popular-chef-item text-decoration-none text-dark d-flex flex-column align-items-center">
                        <div class="popular-chef-avatar rounded-circle overflow-hidden flex-shrink-0">
                            @if($chef->avatar)
                                <img src="{{ $chef->avatar_url }}" alt="{{ $chef->name }}" class="w-100 h-100 object-fit-cover" onerror="this.onerror=null; this.src='{{ asset('images/' . $chefImg) }}';">
                            @elseif(file_exists(public_path('images/' . $chefImg)))
                                <img src="{{ asset('images/' . $chefImg) }}" alt="{{ $chef->name }}" class="w-100 h-100 object-fit-cover">
                            @else
                                <img src="{{ asset('images/african chef 01.jpg') }}" alt="{{ $chef->name }}" class="w-100 h-100 object-fit-cover" onerror="this.src='{{ asset('images/food 01.jpeg') }}'">
                            @endif
                        </div>
                        <span class="popular-chef-name mt-2 small fw-semibold text-center">{{ $chef->name }}</span>
                        @if($chef->average_rating > 0)
                            <span class="popular-chef-rating small text-warning d-inline-flex align-items-center justify-content-center gap-1 mt-1">
                                <i class="bi bi-star-fill"></i> {{ number_format($chef->average_rating, 1) }}
                                @if($chef->total_reviews > 0)
                                    <span class="text-muted fw-normal">({{ $chef->total_reviews }})</span>
                                @endif
                            </span>
                        @endif
                        @if($chef->chefProfile?->cuisine_type)
                            <span class="popular-chef-cuisine small text-muted text-center">{{ \Illuminate\Support\Str::limit($chef->chefProfile->cuisine_type, 12) }}</span>
                        @endif
                    </a>
                @endforeach
            </div>
        </div>
    </div>
</section>
<style>
    .popular-chefs-wrap { width: 100%; }
    .popular-chefs-track {
        width: 100%;
        display: flex;
        flex-wrap: nowrap;
        gap: 1rem;
    }
    .popular-chef-item {
        flex: 1 1 0;
        min-width: 90px;
        max-width: 220px;
        display: flex;
        flex-direction: column;
        align-items: center;
    }
    .popular-chef-avatar {
        width: 80px;
        height: 80px;
        background: #f0f0f0;
        flex-shrink: 0;
    }
    .popular-chef-avatar img { width: 100%; height: 100%; object-fit: cover; }
    .popular-chef-name { white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 100%; }
    .popular-chef-rating { white-space: nowrap; }
    .popular-chef-cuisine { white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 100%; display: block; }
    @media (min-width: 768px) {
        .popular-chef-item { max-width: none; }
        .popular-chef-avatar { width: 88px; height: 88px; }
    }
</style>
<script>
(function() {
    var wrap = document.querySelector('.popular-chefs-wrap');
    var track = document.querySelector('.popular-chefs-track');
    var prevBtn = document.querySelector('.popular-chefs-prev');
    var nextBtn = document.querySelector('.popular-chefs-next');
    if (!wrap || !track || !prevBtn || !nextBtn) return;
    var step = Math.min(280, wrap.clientWidth * 0.8);
    prevBtn.addEventListener('click', function() {
        wrap.scrollBy({ left: -step, behavior: 'smooth' });
    });
    nextBtn.addEventListener('click', function() {
        wrap.scrollBy({ left: step, behavior: 'smooth' });
    });
})();
</script>
@endif

<div class="container">
    <!-- Search -->
    <div class="card shadow-sm mb-4 border-0">
        <div class="card-body p-4">
            <form method="GET" action="{{ route('chefs.index') }}" class="row g-2 align-items-center">
                <div class="col">
                    <input type="text" class="form-control form-control-lg rounded-3" name="search" placeholder="Search by name, cuisine, bio, heritage…" value="{{ request('search') }}" aria-label="Search chefs">
                </div>
                <div class="col-auto">
                    <button class="btn btn-success btn-lg rounded-3 px-4" type="submit">
                        <i class="bi bi-search me-2"></i>Search
                    </button>
                </div>
            </form>
        </div>
    </div>

    @if($chefs->total() > 0)
        <div class="d-flex justify-content-between align-items-center mb-3">
            <p class="text-muted mb-0">
                Showing <strong>{{ $chefs->firstItem() }}</strong> to <strong>{{ $chefs->lastItem() }}</strong> of <strong>{{ $chefs->total() }}</strong> chefs
            </p>
            @if(request('search'))
                <a href="{{ route('chefs.index') }}" class="btn btn-sm btn-outline-secondary">
                    <i class="bi bi-x-circle"></i> Clear
                </a>
            @endif
        </div>
    @endif

    <div class="row g-4 mb-4">
        @forelse($chefs as $chef)
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
            <div class="col-md-6 col-lg-4 col-xl-3">
                <div class="card chef-card h-100 d-flex flex-column border shadow-sm overflow-hidden">
                    <div class="card-body text-center p-4 flex-grow-1 d-flex flex-column">
                        <div class="mb-3">
                            @if($chef->avatar)
                                <img src="{{ $chef->avatar_url }}" alt="{{ $chef->name }}" class="chef-profile-image rounded-circle object-fit-cover" style="width: 100px; height: 100px;" onerror="this.onerror=null; this.src='{{ asset('images/' . ($chefImage ?? 'african chef 01.jpg')) }}';">
                            @elseif(!empty($chefImage) && file_exists(public_path('images/' . $chefImage)))
                                <img src="{{ asset('images/' . $chefImage) }}" alt="{{ $chef->name }}" class="chef-profile-image rounded-circle object-fit-cover" style="width: 100px; height: 100px;" onerror="this.onerror=null; this.src='{{ asset('images/african chef 01.jpg') }}';">
                            @else
                                <div class="bg-primary rounded-circle d-inline-flex align-items-center justify-content-center" style="width: 100px; height: 100px;">
                                    <span class="text-white fs-3 fw-bold">{{ substr($chef->name, 0, 1) }}</span>
                                </div>
                            @endif
                        </div>
                        <h5 class="card-title mb-2 fw-bold">{{ $chef->name }}</h5>
                        <p class="text-success fw-semibold mb-3" style="font-size: 0.9rem;">{{ $chef->chefProfile?->cuisine_type ?: 'Expert Chef' }}</p>
                        
                        <div class="mt-auto">
                            @if($chef->average_rating > 0)
                                <div class="mb-3">
                                    <span class="badge bg-warning text-dark px-3 py-2">
                                        <i class="bi bi-star-fill"></i> {{ number_format($chef->average_rating, 1) }}
                                        @if($chef->total_reviews > 0)
                                            <span class="ms-1">({{ $chef->total_reviews }})</span>
                                        @endif
                                    </span>
                                </div>
                            @endif
                            @if($chef->chefProfile?->years_experience)
                                <p class="text-muted small mb-2">
                                    <i class="bi bi-clock-history"></i> {{ $chef->chefProfile->years_experience }} years
                                </p>
                            @endif
                            @if($chef->chefProfile?->specialties)
                                <p class="text-muted small mb-0" style="font-size: 0.8rem; line-height: 1.4;">
                                    {{ \Illuminate\Support\Str::limit($chef->chefProfile->specialties, 50) }}
                                </p>
                            @endif
                        </div>
                    </div>
                    <div class="card-footer bg-body-tertiary border-top pt-3 pb-3">
                        <a href="{{ route('chefs.show', $chef) }}" class="btn btn-success w-100 rounded-3">
                            <i class="bi bi-eye me-2"></i>View Kitchen
                        </a>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12">
                <div class="card shadow-sm border-0">
                    <div class="card-body text-center py-5">
                        <i class="bi bi-person-badge" style="font-size: 4rem; color: #ccc;"></i>
                        <h4 class="mt-3 text-muted">No chefs found</h4>
                        <p class="text-muted">Try a different search or browse from the home page</p>
                        @if(request('search'))
                            <a href="{{ route('chefs.index') }}" class="btn btn-success mt-2">
                                <i class="bi bi-arrow-left"></i> Clear Search
                            </a>
                        @endif
                    </div>
                </div>
            </div>
        @endforelse
    </div>

    @if($chefs->hasPages())
        <div class="d-flex justify-content-center">
            {{ $chefs->links() }}
        </div>
    @endif
</div>

<style>
    .chef-card {
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }
    .chef-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 8px 20px rgba(0,0,0,0.12) !important;
    }
    .chef-profile-image {
        width: 100px;
        height: 100px;
        object-fit: cover;
        border: 3px solid #f0f0f0;
    }
    .chef-card .card-body {
        min-height: 200px;
    }
</style>
@endsection
