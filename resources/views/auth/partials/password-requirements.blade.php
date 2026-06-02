@php
    $hintId = $hintId ?? 'passwordRequirementsHint';
    $showHint = $showHint ?? false;
@endphp
@once('password-feedback-styles')
<style>
    .password-field-feedback {
        display: flex;
        flex-direction: column;
        align-items: stretch;
        justify-content: flex-start;
        width: 100%;
        gap: 0.25rem;
    }
    .password-field-feedback .password-requirements-hint,
    .password-field-feedback .password-field-error {
        width: 100%;
        margin: 0;
        text-align: justify;
        text-justify: inter-word;
    }
</style>
@endonce
<p class="form-text small text-muted mb-0 password-requirements-hint {{ $showHint ? '' : 'd-none' }}"
   id="{{ $hintId }}"
   role="status"
   aria-live="polite">
    <i class="bi bi-shield-lock me-1"></i>{{ __('auth.password_hint') }}
</p>
@once('password-requirements-script')
<script>
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('input[data-password-hint]').forEach(function (input) {
        var hintId = input.getAttribute('aria-describedby');
        var hint = hintId ? document.getElementById(hintId) : null;
        if (!hint) {
            return;
        }
        function revealHint() {
            hint.classList.remove('d-none');
        }
        input.addEventListener('focus', revealHint);
        input.addEventListener('input', revealHint);
    });
});
</script>
@endonce
