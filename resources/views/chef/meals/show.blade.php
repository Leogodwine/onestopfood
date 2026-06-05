@extends('layouts.dashboard')

@section('content')
<div class="page-header page-header-split">
    <div class="d-flex justify-content-between align-items-center page-header-top">
        <h2 class="mb-0">{{ $meal->name }}</h2>
        <div class="page-header-actions">
            <a class="btn btn-sm btn-outline-primary page-header-action-btn" href="{{ route('chef.meals.index') }}">
                <i class="bi bi-arrow-left"></i> Back
            </a>
            <a class="btn btn-sm btn-primary page-header-action-btn" href="{{ route('chef.meals.edit', $meal) }}">
                <i class="bi bi-pencil"></i> Edit
            </a>
        </div>
    </div>
    <p class="text-muted mb-0 page-header-subtitle">Meal details</p>
</div>

<div class="row g-4">
    <div class="col-lg-5">
        <div class="dashboard-card">
            <div class="card-body text-center">
                @if($meal->image_url)
                    <img src="{{ $meal->image_url }}" alt="{{ $meal->name }}" class="img-fluid rounded mb-3" style="max-height: 280px; object-fit: cover;">
                @else
                    <div class="bg-body-secondary rounded d-flex align-items-center justify-content-center mb-3" style="height: 200px;">
                        <i class="bi bi-image text-muted" style="font-size: 2.5rem;"></i>
                    </div>
                @endif
                <div class="h4 mb-1">TZS {{ number_format((float) $meal->price, 2) }}</div>
                <div class="text-muted small">{{ $meal->category ?? 'Uncategorized' }}</div>
            </div>
        </div>
    </div>

    <div class="col-lg-7">
        <div class="dashboard-card mb-4">
            <div class="card-header">
                <h5 class="card-title mb-0"><i class="bi bi-info-circle"></i> Overview</h5>
            </div>
            <div class="card-body">
                <dl class="row mb-0">
                    <dt class="col-sm-4 text-muted">Customer visible</dt>
                    <dd class="col-sm-8 d-flex align-items-center gap-2 flex-wrap">
                        @if($meal->is_available)
                            <span class="badge bg-success">Live on menu</span>
                        @else
                            <span class="badge bg-secondary">Hidden (suspended)</span>
                        @endif
                        <form method="POST" action="{{ route('chef.meals.toggle-availability', $meal) }}" class="d-inline">
                            @csrf
                            <button type="submit" class="btn btn-sm btn-outline-{{ $meal->is_available ? 'warning' : 'success' }}">
                                <i class="bi bi-{{ $meal->is_available ? 'eye-slash' : 'eye' }}"></i>
                                {{ $meal->is_available ? 'Hide from customers' : 'Show on menu' }}
                            </button>
                        </form>
                    </dd>
                    <dt class="col-sm-4 text-muted">Heritage</dt>
                    <dd class="col-sm-8">{{ $meal->is_heritage ? 'Yes' : 'No' }}</dd>
                    <dt class="col-sm-4 text-muted">Popular</dt>
                    <dd class="col-sm-8">{{ $meal->is_popular ? 'Yes' : 'No' }}</dd>
                    @if($meal->prep_time_minutes)
                        <dt class="col-sm-4 text-muted">Prep time</dt>
                        <dd class="col-sm-8">{{ $meal->prep_time_minutes }} minutes</dd>
                    @endif
                    @if($meal->origin)
                        <dt class="col-sm-4 text-muted">Origin</dt>
                        <dd class="col-sm-8">{{ $meal->origin }}</dd>
                    @endif
                    @if($meal->dietary_tags)
                        <dt class="col-sm-4 text-muted">Dietary tags</dt>
                        <dd class="col-sm-8">{{ $meal->dietary_tags }}</dd>
                    @endif
                    <dt class="col-sm-4 text-muted">Created</dt>
                    <dd class="col-sm-8">{{ $meal->created_at->format('M d, Y') }}</dd>
                    <dt class="col-sm-4 text-muted">Updated</dt>
                    <dd class="col-sm-8">{{ $meal->updated_at->format('M d, Y H:i') }}</dd>
                </dl>
            </div>
        </div>

        @if($meal->description)
            <div class="dashboard-card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">Description</h5>
                </div>
                <div class="card-body">
                    <p class="mb-0 page-header-subtitle">{{ $meal->description }}</p>
                </div>
            </div>
        @endif

        @if($meal->heritage_story)
            <div class="dashboard-card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">Heritage Story</h5>
                </div>
                <div class="card-body">
                    <p class="mb-0 page-header-subtitle">{{ $meal->heritage_story }}</p>
                </div>
            </div>
        @endif

        <div class="dashboard-card">
            <div class="card-header">
                <h5 class="card-title mb-0 text-danger"><i class="bi bi-trash"></i> Remove meal</h5>
            </div>
            <div class="card-body">
                @if($meal->order_items_count > 0)
                    <p class="text-muted mb-3">This meal has order history and cannot be deleted. You can mark it as unavailable from the edit page instead.</p>
                    <a href="{{ route('chef.meals.edit', $meal) }}" class="btn btn-outline-secondary btn-sm">Edit availability</a>
                @else
                    <p class="text-muted mb-3">Permanently remove this meal from your menu.</p>
                    <form method="POST" action="{{ route('chef.meals.destroy', $meal) }}" onsubmit="return confirm('Remove this meal permanently?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger btn-sm">
                            <i class="bi bi-trash"></i> Delete meal
                        </button>
                    </form>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
