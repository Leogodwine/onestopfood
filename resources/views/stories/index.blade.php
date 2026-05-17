@extends(auth()->check() ? 'layouts.dashboard' : 'layout')

@section('content')
<style>
    .heritage-stories-hero {
        background: #fff9e6;
        border-radius: 18px;
        padding: 20px 22px;
        margin-bottom: 28px;
        display: flex;
        flex-wrap: wrap;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        gap: 12px;
        text-align: center;
    }
    .heritage-stories-hero h2 {
        margin-bottom: 6px;
    }
    .heritage-stories-hero p {
        margin-bottom: 0;
        font-size: 0.95rem;
    }
    .heritage-stories-search {
        max-width: 360px;
        width: 100%;
        margin: 0 auto;
    }
    .heritage-card {
        border-radius: 14px;
        overflow: hidden;
        border: 1px solid #e9ecef;
        box-shadow: 0 6px 20px rgba(0,0,0,0.05);
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }
    .heritage-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 10px 26px rgba(0,0,0,0.10);
    }
    .heritage-image {
        width: 100%;
        height: 190px;
        object-fit: cover;
    }
    @media (min-width: 992px) {
        .heritage-image {
            height: 210px;
        }
    }
    .heritage-meta {
        font-size: 0.8rem;
    }
</style>

<div class="container my-5">
    <div class="heritage-stories-hero">
        <div>
            <h2 class="mb-1">Heritage Stories</h2>
            <p class="text-muted">Discover the origins and family traditions behind our most special dishes.</p>
        </div>
        <form method="GET" action="{{ route('stories.index') }}" class="heritage-stories-search">
            <div class="input-group input-group-sm">
                <input type="text" class="form-control" name="search" placeholder="Search stories, origins, chefs..." value="{{ request('search') }}">
                <button class="btn btn-primary" type="submit">
                    <i class="bi bi-search"></i>
                </button>
            </div>
        </form>
    </div>

    <div class="row g-4">
        @forelse($stories as $meal)
            <div class="col-sm-6 col-lg-4 col-xl-3">
                @php
                    $category = strtolower($meal->category ?? '');
                    $origin = strtolower($meal->origin ?? '');
                    $name = strtolower($meal->name ?? '');
                    $storyImage = 'food 01.jpeg';

                    if ($name === 'korean bbq tacos') {
                        // Dedicated image for Korean BBQ Tacos heritage story
                        $storyImage = 'american food 02.jpg';
                    } elseif (str_contains($category, 'italian') || str_contains($category, 'dessert') || str_contains($origin, 'umbrian') || str_contains($origin, 'turin')) {
                        $storyImage = 'european food 01.jpg';
                    } elseif (str_contains($category, 'bbq') || str_contains($category, 'american') || str_contains($origin, 'hyogo')) {
                        $storyImage = 'american food 01.jpeg';
                    } elseif (str_contains($category, 'african') || str_contains($origin, 'africa')) {
                        $storyImage = 'african food 0' . (($loop->index % 6) + 1) . '.jpg';
                    } else {
                        $fallbacks = ['food 01.jpeg', 'food 03.png', 'juice and food 01.jpg', 'one stop food 01.jpeg'];
                        $storyImage = $fallbacks[$loop->index % count($fallbacks)];
                    }
                @endphp
                <div class="card heritage-card h-100">
                    @if(file_exists(public_path('images/' . $storyImage)))
                        <img src="{{ asset('images/' . $storyImage) }}" class="heritage-image" alt="{{ $meal->name }}">
                    @elseif($meal->image_path)
                        <img src="{{ asset('storage/' . $meal->image_path) }}" class="heritage-image" alt="{{ $meal->name }}">
                    @else
                        <div class="bg-light d-flex align-items-center justify-content-center heritage-image">
                            <i class="bi bi-image text-muted fs-1"></i>
                        </div>
                    @endif
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <h6 class="card-title mb-0">{{ $meal->name }}</h6>
                            <span class="badge badge-heritage">Heritage</span>
                        </div>
                        <div class="heritage-meta text-muted mb-2">
                            <div>Chef: {{ $meal->chef->name }}</div>
                            @if($meal->origin)
                                <div class="text-primary fw-semibold mt-1">{{ $meal->origin }}</div>
                            @endif
                        </div>
                        @if($meal->heritage_story)
                            <p class="card-text small mb-3">{{ \Illuminate\Support\Str::limit($meal->heritage_story, 140) }}</p>
                        @endif
                        <div class="d-flex justify-content-between align-items-center">
                            <div class="fw-bold text-primary">TZS {{ number_format((float)$meal->price, 2) }}</div>
                            <a href="{{ route('meals.index', ['search' => $meal->name]) }}" class="btn btn-sm btn-view">View Dish</a>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12">
                <div class="alert alert-info text-center">No heritage stories available yet.</div>
            </div>
        @endforelse
    </div>

    <div class="mt-4">
        {{ $stories->links() }}
    </div>
</div>
@endsection
