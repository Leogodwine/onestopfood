<!-- Compact Create Account Modal -->
<div class="modal fade" id="registerModal" tabindex="-1" aria-labelledby="registerModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-md">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 16px;">
            <div class="modal-header bg-dark text-white" style="border-top-left-radius: 16px; border-top-right-radius: 16px;">
                <div>
                    <h5 class="modal-title mb-0" id="registerModalLabel">
                        <i class="bi bi-person-plus"></i> Create Account
                    </h5>
                    <small class="text-white-50">Chefs and Travelers require admin approval</small>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <form method="POST" action="{{ route('register.store') }}" novalidate>
                @csrf

                <div class="modal-body p-4">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label fw-semibold mb-1">
                                <i class="bi bi-person"></i> Name
                            </label>
                            <input
                                class="form-control form-control-sm @if($errors->register->has('name')) is-invalid @endif"
                                name="name"
                                value="{{ old('name') }}"
                                required
                                autocomplete="name"
                                placeholder="Your full name"
                            >
                            @if($errors->register->has('name'))
                                <div class="invalid-feedback">{{ $errors->register->first('name') }}</div>
                            @endif
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-semibold mb-1">
                                <i class="bi bi-envelope"></i> Email
                            </label>
                            <input
                                class="form-control form-control-sm @if($errors->register->has('email')) is-invalid @endif"
                                type="email"
                                name="email"
                                value="{{ old('email') }}"
                                required
                                autocomplete="email"
                                placeholder="name@example.com"
                            >
                            @if($errors->register->has('email'))
                                <div class="invalid-feedback">{{ $errors->register->first('email') }}</div>
                            @endif
                        </div>

                        <div class="col-12">
                            @include('partials.phone-input', [
                                'label' => __('auth.phone_label'),
                                'labelIcon' => 'bi-telephone',
                                'errorBag' => 'register',
                                'value' => old('phone'),
                                'inputId' => 'register_phone_number',
                                'selectId' => 'register_phone_country_code',
                            ])
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-semibold mb-1">
                                <i class="bi bi-shield-lock"></i> Role
                            </label>
                            <select
                                class="form-select form-select-sm @if($errors->register->has('role')) is-invalid @endif"
                                name="role"
                                id="register_role"
                                required
                            >
                                <option value="customer" @selected(old('role', 'customer')==='customer')>Customer</option>
                                <option value="chef" @selected(old('role')==='chef')>Chef</option>
                                <option value="traveler" @selected(old('role')==='traveler')>Traveler (Delivery)</option>
                            </select>
                            @if($errors->register->has('role'))
                                <div class="invalid-feedback">{{ $errors->register->first('role') }}</div>
                            @endif

                            <div class="form-text mt-2" id="register_role_note">
                                Choose your account type to continue.
                            </div>
                        </div>

                        <div class="col-md-6">
                            @php
                                $registerPasswordErrors = $errors->register->get('password', []);
                                $registerPasswordError = collect($registerPasswordErrors)->first(
                                    fn ($message) => $message !== __('auth.password_confirmed')
                                );
                                $registerConfirmError = collect($registerPasswordErrors)->contains(__('auth.password_confirmed'))
                                    ? __('auth.password_confirmed')
                                    : $errors->register->first('password_confirmation');
                            @endphp
                            @include('auth.partials.password-input', [
                                'inputId' => 'register_password',
                                'name' => 'password',
                                'label' => __('auth.password_label'),
                                'labelIcon' => 'bi-lock',
                                'size' => 'sm',
                                'withHint' => true,
                                'withChoice' => true,
                                'weakErrorFullWidth' => true,
                                'hintId' => 'registerPasswordWeakError',
                                'confirmSelector' => '#register_password_confirmation',
                                'invalid' => (bool) $registerPasswordError,
                                'errorMessage' => $registerPasswordError,
                            ])
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold mb-1" for="register_password_confirmation">
                                <i class="bi bi-lock-fill"></i> {{ __('auth.password_confirm_label') }}
                            </label>
                            <div class="input-group password-input-group input-group-sm">
                                <input
                                    class="form-control form-control-sm @if($registerConfirmError) is-invalid @endif"
                                    type="password"
                                    name="password_confirmation"
                                    id="register_password_confirmation"
                                    required
                                    minlength="8"
                                    autocomplete="new-password"
                                    placeholder="{{ __('auth.password_confirm_placeholder') }}"
                                >
                                <button
                                    type="button"
                                    class="btn btn-outline-secondary btn-toggle-password js-toggle-password"
                                    data-target="register_password_confirmation"
                                    data-show-label="{{ __('auth.show_password') }}"
                                    data-hide-label="{{ __('auth.hide_password') }}"
                                    aria-label="{{ __('auth.show_password') }}"
                                >
                                    <i class="bi bi-eye"></i>
                                </button>
                            </div>
                            @if($registerConfirmError)
                                <p class="password-field-error text-danger small mb-0 mt-1">{{ $registerConfirmError }}</p>
                            @endif
                        </div>

                        <div class="col-12 password-weak-error-row">
                            @include('auth.partials.password-weak-error', [
                                'hintId' => 'registerPasswordWeakError',
                                'visible' => (bool) $registerPasswordError,
                                'message' => __('auth.password_hint'),
                            ])
                        </div>
                    </div>

                    @php
                        $registerSocialNames = array_values(array_filter([
                            ($googleSignInEnabled ?? false) ? 'Google' : null,
                            ($facebookSignInEnabled ?? false) ? 'Facebook' : null,
                        ]));
                        $hasRegisterSocial = count($registerSocialNames) > 0;
                    @endphp
                    @if ($hasRegisterSocial)
                        <div class="register-social-footer" id="register_social_section">
                            <div class="register-social-row">
                                <span class="register-social-label">Or continue with</span>
                                @include('auth._social_auth_buttons', ['class' => 'register-social-buttons mb-0'])
                            </div>
                        </div>
                    @endif
                </div>

                <div class="modal-footer border-0 px-4 pb-4 pt-0">
                    <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">
                        Cancel
                    </button>
                    <button type="submit" class="btn btn-success btn-sm">
                        <i class="bi bi-check-circle"></i> Create Account
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
#registerModal .register-social-footer {
    margin-top: 1rem;
    padding-top: 1rem;
    border-top: 1px solid #e9ecef;
}
#registerModal .register-social-row {
    display: flex;
    align-items: center;
    justify-content: center;
    flex-wrap: wrap;
    gap: 0.65rem;
}
#registerModal .register-social-label {
    color: #6c757d;
    font-size: 0.8rem;
    white-space: nowrap;
}
#registerModal .register-social-buttons.auth-social-buttons {
    flex-wrap: nowrap;
    justify-content: center;
    gap: 0.5rem;
}
#registerModal .login-portal-social-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    padding: 0.45rem 1rem;
    border-radius: 8px;
    border: 1px solid #d1d5db;
    background: #fff;
    color: #374151;
    font-weight: 600;
    font-size: 0.875rem;
    text-decoration: none;
    white-space: nowrap;
    line-height: 1.2;
    transition: border-color 0.2s, box-shadow 0.2s;
}
#registerModal .login-portal-social-btn:hover {
    border-color: #22c55e;
    box-shadow: 0 0 0 2px rgba(34, 197, 94, 0.15);
    color: #1f2937;
}
#registerModal .login-portal-social-icon {
    width: 1.125rem;
    height: 1.125rem;
    flex-shrink: 0;
    display: block;
}
#registerModal .login-portal-social-btn span {
    white-space: nowrap;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function () {
    // Expose opener for links/buttons
    window.openRegisterModal = function (role) {
        const el = document.getElementById('registerModal');
        if (!el) return;
        const roleSelect = document.getElementById('register_role');
        if (roleSelect && role && ['customer', 'chef', 'traveler'].includes(role)) {
            roleSelect.value = role;
            roleSelect.dispatchEvent(new Event('change'));
        }
        if (typeof window.setOAuthIntent === 'function') {
            window.setOAuthIntent(roleSelect?.value || role || null);
        }
        const modal = bootstrap.Modal.getOrCreateInstance(el);
        modal.show();
    };

    // Role helper note (keeps UI short but clear)
    const roleSelect = document.getElementById('register_role');
    const note = document.getElementById('register_role_note');
    function updateNote() {
        if (!roleSelect || !note) return;
        if (roleSelect.value === 'chef' || roleSelect.value === 'traveler') {
            note.innerHTML = '<span class="text-warning"><i class="bi bi-info-circle"></i> This role requires admin approval.</span>';
        } else {
            note.innerHTML = '<span class="text-muted">You can start ordering immediately.</span>';
        }
        if (typeof window.setOAuthIntent === 'function') {
            window.setOAuthIntent(roleSelect.value);
        }
    }
    roleSelect?.addEventListener('change', updateNote);
    updateNote();

    // Auto-open when registration has validation errors
    @if ($errors->register->any())
        window.openRegisterModal();
    @endif
});
</script>
