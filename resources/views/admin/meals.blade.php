@extends('layouts.dashboard')

@section('content')
<div class="page-header page-header-split">
    <h2 class="mb-0">Meals</h2>
    <p class="text-muted mb-0 page-header-subtitle">Browse and review all meals on the platform</p>
</div>

<div class="dashboard-card mb-3 mb-md-4">
    <div class="card-header">
        <h5 class="card-title mb-0"><i class="bi bi-filter"></i> Filters</h5>
    </div>
    <form method="GET" action="{{ route('admin.meals.index') }}" class="dashboard-filter-form row g-2 align-items-end">
        <div class="col-12 col-lg-6">
            <label class="form-label dashboard-filter-label" for="meal-search">Search</label>
            <input type="text" id="meal-search" name="search" value="{{ $search }}" class="form-control" placeholder="Meal name, category, origin">
        </div>
        <div class="col-6 col-lg-3">
            <label class="form-label dashboard-filter-label" for="meal-availability">Availability</label>
            <select id="meal-availability" name="availability" class="form-select">
                <option value="" @selected($availability === '')>All</option>
                <option value="available" @selected($availability === 'available')>Available</option>
                <option value="unavailable" @selected($availability === 'unavailable')>Unavailable</option>
            </select>
        </div>
        <div class="col-6 col-lg-3 dashboard-filter-actions">
            <button type="submit" class="btn btn-primary">
                <i class="bi bi-funnel"></i> Apply
            </button>
            <a href="{{ route('admin.meals.index') }}" class="btn btn-outline-secondary">
                Reset
            </a>
        </div>
        <div class="col-12 d-flex justify-content-end">
            <div class="dropdown">
                <button class="btn btn-sm btn-outline-primary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                    Per page: {{ (int)$perPage }}
                </button>
                <ul class="dropdown-menu dropdown-menu-end">
                    @foreach([10, 20, 50, 100] as $size)
                        <li>
                            <a class="dropdown-item @if((int)$perPage === $size) active @endif"
                               href="{{ route('admin.meals.index', array_filter(['search' => $search ?: null, 'availability' => $availability ?: null, 'per_page' => $size])) }}">
                                {{ $size }} per page
                            </a>
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>
    </form>
</div>

<div class="dashboard-card">
    <div class="card-header">
        <h5 class="card-title mb-0"><i class="bi bi-utensils"></i> Meals ({{ number_format($meals->total()) }})</h5>
    </div>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead>
                <tr>
                    <th style="width: 80px;">ID</th>
                    <th>Meal</th>
                    <th>Chef</th>
                    <th style="width: 140px;">Category</th>
                    <th style="width: 120px;">Price</th>
                    <th style="width: 130px;">Availability</th>
                    <th style="width: 150px;">Created</th>
                </tr>
            </thead>
            <tbody>
                @forelse($meals as $meal)
                    <tr>
                        <td class="text-muted">#{{ $meal->id }}</td>
                        <td>
                            <div class="fw-semibold">{{ $meal->name }}</div>
                            <div class="text-muted small">
                                @if($meal->origin)
                                    <span>{{ $meal->origin }}</span>
                                @endif
                                @if($meal->is_popular)
                                    <span class="badge bg-info text-dark ms-2">Popular</span>
                                @endif
                                @if($meal->is_heritage)
                                    <span class="badge badge-heritage ms-1">Heritage</span>
                                @endif
                            </div>
                        </td>
                        <td>
                            <div class="fw-semibold">{{ $meal->chef?->name ?? 'N/A' }}</div>
                            <div class="text-muted small">{{ $meal->chef?->chefProfile?->cuisine_type ?? '' }}</div>
                        </td>
                        <td>{{ $meal->category ?? '—' }}</td>
                        <td class="fw-bold text-success">{{ money($meal->price) }}</td>
                        <td>
                            @if($meal->is_available)
                                <span class="badge bg-success">Available</span>
                            @else
                                <span class="badge bg-secondary">Unavailable</span>
                            @endif
                        </td>
                        <td class="text-muted small">{{ optional($meal->created_at)->format('M d, Y') }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center text-muted py-5">
                            <i class="bi bi-inbox" style="font-size: 3rem; color: #ccc;"></i>
                            <div class="mt-3">No meals found</div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-footer bg-light">
        <div class="d-flex justify-content-center">
            {{ $meals->links() }}
        </div>
    </div>
</div>
@endsection

