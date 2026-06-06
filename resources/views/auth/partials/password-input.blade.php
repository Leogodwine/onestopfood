@php
    $inputId = $inputId ?? 'password';
    $name = $name ?? 'password';
    $label = $label ?? __('auth.password_label');
    $size = $size ?? 'sm';
    $required = $required ?? true;
    $withHint = $withHint ?? false;
    $withChoice = $withChoice ?? false;
    $confirmSelector = $confirmSelector ?? null;
    $weakErrorFullWidth = $weakErrorFullWidth ?? false;
    $invalid = $invalid ?? false;
    $placeholder = $placeholder ?? __('auth.password_placeholder');
    $groupClass = $size === 'lg' ? 'input-group input-group-lg' : 'input-group input-group-sm';
    $controlClass = $size === 'lg' ? 'form-control form-control-lg' : 'form-control form-control-sm';
    $labelClass = ($size === 'lg' ? 'form-label' : 'form-label fw-semibold mb-1');
    $hintId = $hintId ?? 'passwordRequirementsHint';
    $weakPasswordError = ($invalid && !empty($errorMessage) && ($errorMessage ?? '') !== __('auth.password_confirmed'))
        ? $errorMessage
        : null;
    $showWeakError = $withHint && !empty($weakPasswordError);
@endphp
@include('auth.partials.password-tools')

<div class="password-field-wrap @if($withChoice) password-field-wrap--choice @endif">
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
            class="{{ $controlClass }} @if($showWeakError) is-invalid @endif @if($withChoice) js-password-choice-trigger @endif"
            @if($required) required @endif
            minlength="8"
            autocomplete="new-password"
            @if($withHint) data-password-weak-check @endif
            @if($withChoice && $confirmSelector) data-confirm-selector="{{ $confirmSelector }}" @endif
            placeholder="{{ $placeholder }}"
            @if($withHint) aria-describedby="{{ $hintId }}" @endif
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

    @if($withChoice)
        <div class="password-choice-popover d-none" role="dialog" aria-label="{{ __('auth.password_choice_aria') }}">
            <div class="password-choice-popover__actions">
                <p class="password-choice-paragraph mb-0 js-password-choice-own" role="button" tabindex="0">
                    {{ __('auth.password_create_own') }}
                </p>
                <p class="password-choice-paragraph mb-0 js-password-choice-generate" role="button" tabindex="0">
                    {{ __('auth.password_generate_short') }}
                </p>
            </div>
            <span class="password-choice-popover__status small text-success d-none js-generate-status">{{ __('auth.password_generated') }}</span>
        </div>
    @endif

    @if($withHint && !$weakErrorFullWidth)
        @include('auth.partials.password-weak-error', [
            'hintId' => $hintId,
            'visible' => $showWeakError,
            'message' => __('auth.password_hint'),
        ])
    @elseif(!empty($errorMessage) && !$withHint)
        <p class="password-field-error text-danger small mb-0 mt-1">{{ $errorMessage }}</p>
    @endif
</div>
