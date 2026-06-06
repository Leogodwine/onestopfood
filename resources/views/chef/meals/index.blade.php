@extends('layouts.dashboard')

@section('content')
<div class="page-header page-header-split">
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h2>My Meals</h2>
            <p class="text-muted mb-0 page-header-subtitle">Manage your meal offerings</p>
        </div>
        <a class="btn btn-primary" href="{{ route('chef.meals.create') }}">
            <i class="bi bi-plus-circle"></i> Add Meal
        </a>
    </div>
</div>

@if($errors->any())
    <div class="alert alert-danger alert-dismissible fade show">
        {{ $errors->first() }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<div class="dashboard-card">
    <div class="card-header d-flex flex-wrap align-items-center justify-content-between gap-2">
        <h5 class="card-title mb-0"><i class="bi bi-utensils"></i>
            @if(isset($availableFilter) && (string) $availableFilter === '0')
                Hidden from customers
            @elseif(isset($availableFilter) && $availableFilter == 1)
                Live on menu
            @else
                All Meals
            @endif
        </h5>
        <div class="nav nav-pills nav-pills-sm gap-1">
            <a class="nav-link {{ !isset($availableFilter) || $availableFilter === '' ? 'active' : '' }}" href="{{ route('chef.meals.index') }}">
                All
            </a>
            <a class="nav-link {{ isset($availableFilter) && $availableFilter == 1 ? 'active' : '' }}" href="{{ route('chef.meals.index', ['available' => 1]) }}">
                Live on menu
            </a>
            <a class="nav-link {{ isset($availableFilter) && (string) $availableFilter === '0' ? 'active' : '' }}" href="{{ route('chef.meals.index', ['available' => 0]) }}">
                Hidden
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
                    <th class="text-center">Customer visible</th>
                    <th class="text-center">Heritage</th>
                    <th class="text-center">Popular</th>
                    <th>Created</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($meals as $meal)
                    <tr>
                        <td>
                            <div class="fw-semibold">
                                <a href="{{ route('chef.meals.show', $meal) }}" class="text-decoration-none">{{ $meal->name }}</a>
                            </div>
                            @if($meal->description)
                                <small class="text-muted">{{ \Illuminate\Support\Str::limit($meal->description, 50) }}</small>
                            @endif
                        </td>
                        <td>{{ $meal->category ?? 'Uncategorized' }}</td>
                        <td class="text-end">TZS {{ number_format((float)$meal->price, 2) }}</td>
                        <td class="text-center">
                            @if($meal->is_available)
                                <span class="badge bg-success">Live</span>
                            @else
                                <span class="badge bg-secondary">Hidden</span>
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
                        <td class="text-end text-nowrap">
                            <form method="POST" action="{{ route('chef.meals.toggle-availability', $meal) }}" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-sm btn-outline-{{ $meal->is_available ? 'warning' : 'success' }}" title="{{ $meal->is_available ? 'Hide from customers' : 'Show on menu' }}">
                                    <i class="bi bi-{{ $meal->is_available ? 'eye-slash' : 'eye' }}"></i>
                                </button>
                            </form>
                            <a class="btn btn-sm btn-outline-primary" href="{{ route('chef.meals.show', $meal) }}" title="View">
                                <i class="bi bi-eye"></i>
                            </a>
                            <a class="btn btn-sm btn-outline-secondary" href="{{ route('chef.meals.edit', $meal) }}" title="Edit">
                                <i class="bi bi-pencil"></i>
                            </a>
                            @if($meal->order_items_count === 0)
                                <form method="POST" action="{{ route('chef.meals.destroy', $meal) }}" class="d-inline" onsubmit="return confirm('Remove {{ $meal->name }}?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center text-muted">
                            @if(isset($availableFilter) && (string) $availableFilter === '0')
                                No hidden meals. Use the <i class="bi bi-eye-slash"></i> button on a live meal to hide it from customers.
                            @elseif(isset($availableFilter) && $availableFilter == 1)
                                No meals are live on the menu. <a href="{{ route('chef.meals.create') }}">Create a meal</a> or show a hidden one.
                            @else
                                No meals yet. <a href="{{ route('chef.meals.create') }}">Create your first meal!</a>
                            @endif
                        </td>
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

