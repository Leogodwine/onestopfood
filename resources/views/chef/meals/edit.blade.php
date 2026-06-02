@extends('layouts.dashboard')

@section('content')
<div class="page-header">
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h2>Edit Meal</h2>
            <p class="text-muted mb-0">{{ $meal->name }}</p>
        </div>
        <div class="d-flex gap-2">
            <a class="btn btn-outline-primary" href="{{ route('chef.meals.show', $meal) }}">
                <i class="bi bi-eye"></i> View
            </a>
            <a class="btn btn-outline-primary" href="{{ route('chef.meals.index') }}">
                <i class="bi bi-arrow-left"></i> Back
            </a>
        </div>
    </div>
</div>

<div class="dashboard-card">
    <form method="POST" action="{{ route('chef.meals.update', $meal) }}" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        @include('chef.meals._form', ['meal' => $meal])

        @if ($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="d-flex gap-2">
            <button class="btn btn-primary" type="submit">
                <i class="bi bi-check-circle"></i> Save changes
            </button>
            <a href="{{ route('chef.meals.show', $meal) }}" class="btn btn-outline-secondary">Cancel</a>
        </div>
    </form>
</div>
@endsection
