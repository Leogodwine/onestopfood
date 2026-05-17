@extends('layouts.dashboard')

@section('content')
<div class="page-header">
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h2>My Meals</h2>
            <p class="text-muted mb-0">Manage your meal offerings</p>
        </div>
        <a class="btn btn-primary" href="{{ route('chef.meals.create') }}">
            <i class="bi bi-plus-circle"></i> Add Meal
        </a>
    </div>
</div>

<div class="dashboard-card">
    <div class="card-header d-flex flex-wrap align-items-center justify-content-between gap-2">
        <h5 class="card-title mb-0"><i class="bi bi-utensils"></i>
            @if(isset($availableFilter) && $availableFilter != '')
                {{ $availableFilter ? 'Available Meals' : 'Unavailable Meals' }}
            @else
                All Meals
            @endif
        </h5>
        <div class="nav nav-pills nav-pills-sm gap-1">
            <a class="nav-link {{ !isset($availableFilter) || $availableFilter === '' ? 'active' : '' }}" href="{{ route('chef.meals.index') }}">
                All
            </a>
            <a class="nav-link {{ isset($availableFilter) && $availableFilter == 1 ? 'active' : '' }}" href="{{ route('chef.meals.index', ['available' => 1]) }}">
                Available
            </a>
        </div>
    </div>
    <div class="table-responsive">
        <table class="table table-hover">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Category</th>
                    <th class="text-end">Price</th>
                    <th class="text-center">Available</th>
                    <th class="text-center">Heritage</th>
                    <th class="text-center">Popular</th>
                    <th>Created</th>
                </tr>
            </thead>
            <tbody>
                @forelse($meals as $meal)
                    <tr>
                        <td>
                            <div class="fw-semibold">{{ $meal->name }}</div>
                            @if($meal->description)
                                <small class="text-muted">{{ \Illuminate\Support\Str::limit($meal->description, 50) }}</small>
                            @endif
                        </td>
                        <td>{{ $meal->category ?? 'Uncategorized' }}</td>
                        <td class="text-end">TZS {{ number_format((float)$meal->price, 2) }}</td>
                        <td class="text-center">
                            @if($meal->is_available)
                                <span class="badge badge-success">Yes</span>
                            @else
                                <span class="badge badge-primary">No</span>
                            @endif
                        </td>
                        <td class="text-center">
                            @if($meal->is_heritage)
                                <span class="badge badge-success"><i class="bi bi-star"></i></span>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td class="text-center">
                            @if($meal->is_popular)
                                <span class="badge badge-primary"><i class="bi bi-fire"></i></span>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td>{{ $meal->created_at->format('M d, Y') }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center text-muted">No meals yet. <a href="{{ route('chef.meals.create') }}">Create your first meal!</a></td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-footer">
        {{ $meals->links() }}
    </div>
</div>
@endsection

