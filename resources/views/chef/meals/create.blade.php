@extends('layouts.dashboard')

@section('content')
<div class="page-header">
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h2>Add New Meal</h2>
            <p class="text-muted mb-0">Create a new meal offering</p>
        </div>
        <a class="btn btn-outline-primary" href="{{ route('chef.meals.index') }}">
            <i class="bi bi-arrow-left"></i> Back
        </a>
    </div>
</div>

<div class="dashboard-card">
    <form method="POST" action="{{ route('chef.meals.store') }}" enctype="multipart/form-data">
        @csrf
        @include('chef.meals._form')

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
                <i class="bi bi-check-circle"></i> Create Meal
            </button>
            <a href="{{ route('chef.meals.index') }}" class="btn btn-outline-secondary">Cancel</a>
        </div>
    </form>
</div>
@endsection
