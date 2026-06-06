@extends('layouts.dashboard')

@section('content')
@php
    $profile = $user->role === 'chef' ? $user->chefProfile : ($user->role === 'traveler' ? $user->travelerProfile : null);
@endphp
<div class="page-header page-header-split">
    <h2 class="mb-0">{{ __('account.settings_title') }}</h2>
    <p class="text-muted mb-0 page-header-subtitle">{{ __('account.settings_subtitle') }}</p>
</div>

@if($errors->any())
    <div class="alert alert-danger">
        <ul class="mb-0 ps-3">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="row g-3 g-lg-4">
    <div class="col-lg-6">
        <div class="dashboard-card h-100">
            <div class="card-header">
                <h5 class="card-title mb-0"><i class="bi bi-person-gear"></i> {{ __('account.section_profile') }}</h5>
            </div>
            <div class="d-grid gap-2">
                <a href="{{ route('profile.edit') }}" class="btn btn-outline-primary">
                    <i class="bi bi-pencil"></i> {{ __('account.edit_profile') }}
                </a>
                <a href="{{ route('profile.edit') }}#change-password" class="btn btn-outline-secondary">
                    <i class="bi bi-shield-lock"></i> {{ __('account.change_password') }}
                </a>
            </div>
        </div>
    </div>

    <div class="col-lg-6">
        <div class="dashboard-card h-100">
            <div class="card-header">
                <h5 class="card-title mb-0"><i class="bi bi-geo-alt"></i> {{ __('account.section_location') }}</h5>
            </div>
            @if($user->role === 'customer')
                <p class="text-muted small">{{ __('account.manage_addresses') }}</p>
                <a href="{{ route('locations.index') }}" class="btn btn-outline-primary">
                    <i class="bi bi-geo-alt"></i> {{ __('account.manage_addresses') }}
                </a>
            @else
                <form method="POST" action="{{ route('account.location.update') }}" class="row g-2">
                    @csrf
                    <div class="col-12">
                        <label class="form-label">Street address</label>
                        <input type="text" name="street_address" class="form-control" value="{{ old('street_address', $profile->street_address ?? '') }}" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">City</label>
                        <input type="text" name="city" class="form-control" value="{{ old('city', $profile->city ?? '') }}" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">District</label>
                        <input type="text" name="district" class="form-control" value="{{ old('district', $profile->district ?? '') }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Ward / neighborhood</label>
                        <input type="text" name="ward_neighborhood" class="form-control" value="{{ old('ward_neighborhood', $profile->ward_neighborhood ?? '') }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Landmark / directions</label>
                        <input type="text" name="landmark_directions" class="form-control" value="{{ old('landmark_directions', $profile->landmark_directions ?? '') }}">
                    </div>
                    @if($user->role === 'chef')
                        <div class="col-12">
                            <label class="form-label">Kitchen address (if different)</label>
                            <input type="text" name="kitchen_address" class="form-control" value="{{ old('kitchen_address', $profile->kitchen_address ?? '') }}">
                        </div>
                    @endif
                    <div class="col-12">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check2"></i> Save location
                        </button>
                    </div>
                </form>
            @endif
        </div>
    </div>

    <div class="col-12">
        <div class="dashboard-card">
            <div class="card-header">
                <h5 class="card-title mb-0"><i class="bi bi-life-preserver"></i> {{ __('account.section_help') }}</h5>
            </div>
            <p class="text-muted">{{ __('account.help_text') }}</p>
            <div class="d-flex flex-wrap gap-2">
                <a href="{{ route('docs.user-manual') }}" class="btn btn-outline-primary btn-sm" target="_blank">
                    <i class="bi bi-book"></i> {{ __('account.user_manual') }}
                </a>
                <a href="{{ route('docs.guidelines') }}" class="btn btn-outline-secondary btn-sm" target="_blank">
                    <i class="bi bi-shield-check"></i> {{ __('account.guidelines') }}
                </a>
                <a href="mailto:{{ config('contacts.support_email') }}" class="btn btn-outline-success btn-sm">
                    <i class="bi bi-envelope"></i> {{ __('account.contact_support') }}
                </a>
            </div>
        </div>
    </div>

    <div class="col-12">
        <div class="dashboard-card border-warning">
            <div class="card-header">
                <h5 class="card-title mb-0"><i class="bi bi-exclamation-triangle"></i> {{ __('account.section_account') }}</h5>
            </div>

            @if($user->isSelfDeactivated())
                <div class="alert alert-warning">
                    <strong>{{ __('account.reactivate_title') }}</strong>
                    <p class="mb-0 small">{{ __('account.reactivate_desc') }}</p>
                </div>
                <form method="POST" action="{{ route('account.reactivate') }}">
                    @csrf
                    <button type="submit" class="btn btn-success">
                        <i class="bi bi-play-circle"></i> {{ __('account.reactivate_button') }}
                    </button>
                </form>
            @elseif($user->status === 'suspended' && $user->suspended_by === 'admin')
                <div class="alert alert-danger mb-0">
                    <strong>{{ __('account.admin_suspended_title') }}</strong>
                    <p class="mb-0 small">{{ __('account.admin_suspended_desc') }}</p>
                </div>
            @else
                <div class="row g-4">
                    <div class="col-lg-6">
                        <div class="alert alert-warning mb-3">
                            <strong>{{ __('account.deactivate_title') }}</strong>
                            <ol class="mb-0 mt-2">
                                <li>{{ __('account.deactivate_desc') }}</li>
                                <li>{{ __('account.' . $deactivateEffectsKey) }}</li>
                                <li>{{ __('account.deactivate_when_reactivate') }}</li>
                            </ol>
                        </div>
                        <form method="POST" action="{{ route('account.deactivate') }}" onsubmit="return confirm('Deactivate your account? You can reactivate by signing in again.');">
                            @csrf
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="checkbox" name="confirm_deactivate" value="1" id="confirm_deactivate" required>
                                <label class="form-check-label small" for="confirm_deactivate">{{ __('account.deactivate_confirm') }}</label>
                            </div>
                            <button type="submit" class="btn btn-warning">
                                <i class="bi bi-pause-circle"></i> {{ __('account.deactivate_button') }}
                            </button>
                        </form>
                    </div>

                    <div class="col-lg-6">
                        <div class="alert alert-danger mb-3">
                            <strong>{{ __('account.delete_title') }}</strong>
                            <ol class="mb-0 mt-2">
                                <li>{{ __('account.delete_desc') }}</li>
                                <li>{{ __('account.' . $effectsKey) }}</li>
                                @unless($canHardDelete)
                                    <li>{{ __('account.delete_blocked', ['details' => $dependencyMessage]) }}</li>
                                @endunless
                            </ol>
                        </div>

                        @if($pendingDeletion)
                            <div class="alert alert-info mb-3">
                                {{ __('account.delete_pending', ['date' => $pendingDeletion->created_at->format('M d, Y H:i')]) }}
                                <div class="mt-2"><strong>Your reason:</strong> {{ $pendingDeletion->reason }}</div>
                            </div>
                            <form method="POST" action="{{ route('account.deletion.cancel') }}">
                                @csrf
                                <button type="submit" class="btn btn-outline-secondary btn-sm">
                                    {{ __('account.delete_cancel') }}
                                </button>
                            </form>
                        @else
                            <form method="POST" action="{{ route('account.deletion.request') }}" onsubmit="return confirm('Submit permanent deletion request? An admin must approve this.');">
                                @csrf
                                <div class="mb-2">
                                    <label class="form-label small" for="delete_reason">{{ __('account.delete_reason_label') }}</label>
                                    <textarea name="reason" id="delete_reason" class="form-control @error('reason') is-invalid @enderror" rows="4" minlength="20" maxlength="2000" required placeholder="{{ __('account.delete_reason_placeholder') }}">{{ old('reason') }}</textarea>
                                    @error('reason')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="form-check mb-2">
                                    <input class="form-check-input" type="checkbox" name="confirm_delete" value="1" id="confirm_delete" required>
                                    <label class="form-check-label small" for="confirm_delete">{{ __('account.delete_confirm') }}</label>
                                </div>
                                <button type="submit" class="btn btn-danger">
                                    <i class="bi bi-trash"></i> {{ __('account.delete_button') }}
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
