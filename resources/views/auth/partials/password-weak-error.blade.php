@php
    $hintId = $hintId ?? 'passwordRequirementsHint';
    $visible = $visible ?? false;
    $message = $message ?? __('auth.password_hint');
@endphp
@once('password-weak-error-styles')
<style>
    .password-weak-error {
        display: block;
        width: 100%;
        margin: 0.5rem 0 0;
        padding: 0.75rem 1rem;
        border: none;
        border-left: 4px solid #dc3545;
        border-radius: 8px;
        background-color: #f8d7da;
        color: #721c24;
        font-size: 0.875rem;
        line-height: 1.5;
        text-align: left;
    }
    .password-weak-error-row {
        width: 100%;
    }
    .password-weak-error-row .password-weak-error {
        margin-top: 0;
    }
</style>
@endonce
<p class="password-weak-error mb-0 {{ $visible ? '' : 'd-none' }}"
   id="{{ $hintId }}"
   role="alert"
   aria-live="polite"
   data-message="{{ $message }}">{{ $message }}</p>
@once('password-weak-error-script')
<script>
document.addEventListener('DOMContentLoaded', function () {
    window.isStrongPassword = function (value) {
        if (!value || value.length < 8) {
            return false;
        }
        if (!/[a-z]/.test(value)) {
            return false;
        }
        if (!/[A-Z]/.test(value)) {
            return false;
        }
        if (!/[0-9]/.test(value)) {
            return false;
        }
        if (!/[^a-zA-Z0-9]/.test(value)) {
            return false;
        }
        return true;
    };

    function updatePasswordWeakState(input) {
        if (!input || !input.hasAttribute('data-password-weak-check')) {
            return;
        }
        var errorId = input.getAttribute('aria-describedby');
        var errorEl = errorId ? document.getElementById(errorId) : null;
        if (!errorEl) {
            return;
        }
        var message = errorEl.getAttribute('data-message') || errorEl.textContent.trim();
        var value = input.value;

        if (!value) {
            errorEl.classList.add('d-none');
            input.classList.remove('is-invalid');
            return;
        }

        if (window.isStrongPassword(value)) {
            errorEl.classList.add('d-none');
            input.classList.remove('is-invalid');
            return;
        }

        errorEl.textContent = message;
        errorEl.classList.remove('d-none');
        input.classList.add('is-invalid');
    }

    window.updatePasswordWeakState = updatePasswordWeakState;

    document.querySelectorAll('input[data-password-weak-check]').forEach(function (input) {
        input.addEventListener('input', function () {
            updatePasswordWeakState(input);
        });
        input.addEventListener('blur', function () {
            updatePasswordWeakState(input);
        });
    });
});
</script>
@endonce
