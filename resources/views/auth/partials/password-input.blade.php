@php
    $inputId = $inputId ?? 'password';
    $name = $name ?? 'password';
    $label = $label ?? __('auth.password_label');
    $size = $size ?? 'sm';
    $required = $required ?? true;
    $withHint = $withHint ?? false;
    $invalid = $invalid ?? false;
    $placeholder = $placeholder ?? __('auth.password_placeholder');
    $groupClass = $size === 'lg' ? 'input-group input-group-lg' : 'input-group input-group-sm';
    $controlClass = $size === 'lg' ? 'form-control form-control-lg' : 'form-control form-control-sm';
    $labelClass = ($size === 'lg' ? 'form-label' : 'form-label fw-semibold mb-1');
@endphp
@include('auth.partials.password-tools')

<label class="{{ $labelClass }}" for="{{ $inputId }}">
    @if(!empty($labelIcon))
        <i class="bi {{ $labelIcon }}"></i>
    @endif
    {{ $label }}
</label>
<div class="input-group password-input-group {{ $groupClass }}">
    <input
        type="password"
        name="{{ $name }}"
        id="{{ $inputId }}"
        class="{{ $controlClass }} @if($invalid) is-invalid @endif"
        @if($required) required @endif
        minlength="8"
        autocomplete="new-password"
        @if($withHint) data-password-hint @endif
        placeholder="{{ $placeholder }}"
        @if($withHint) aria-describedby="passwordRequirementsHint" @endif
    >
    <button
        type="button"
        class="btn btn-outline-secondary btn-toggle-password js-toggle-password"
        data-target="{{ $inputId }}"
        data-show-label="{{ __('auth.show_password') }}"
        data-hide-label="{{ __('auth.hide_password') }}"
        aria-label="{{ __('auth.show_password') }}"
    >
        <i class="bi bi-eye"></i>
    </button>
</div>
@if($withHint)
    <div class="password-field-feedback w-100">
        @include('auth.partials.password-requirements')
        @if(!empty($errorMessage))
            <p class="password-field-error text-danger small mb-0">{{ $errorMessage }}</p>
        @endif
    </div>
@elseif(!empty($errorMessage))
    <p class="password-field-error text-danger small mb-0 mt-1">{{ $errorMessage }}</p>
@endif
