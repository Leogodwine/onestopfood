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
                            <label class="form-label fw-semibold mb-1">
                                <i class="bi bi-telephone"></i> Phone <span class="text-danger">*</span>
                            </label>
                            <input
                                class="form-control form-control-sm @if($errors->register->has('phone')) is-invalid @endif"
                                name="phone"
                                value="{{ old('phone') }}"
                                autocomplete="tel"
                                required
                                placeholder="e.g., +255 7xx xxx xxx"
                            >
                            @if($errors->register->has('phone'))
                                <div class="invalid-feedback">{{ $errors->register->first('phone') }}</div>
                            @endif
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

                        <div class="col-12 d-none" id="register_social_section" data-social-providers="{{ ($googleSignInEnabled ?? false ? 'Google' : '') . (($googleSignInEnabled ?? false) && ($facebookSignInEnabled ?? false) ? '/' : '') . ($facebookSignInEnabled ?? false ? 'Facebook' : '') }}">
                            <div class="border-top pt-3 mt-1">
                                @php
                                    $registerSocialNames = array_values(array_filter([
                                        ($googleSignInEnabled ?? false) ? 'Google' : null,
                                        ($facebookSignInEnabled ?? false) ? 'Facebook' : null,
                                    ]));
                                    $registerSocialLabel = count($registerSocialNames) === 1
                                        ? $registerSocialNames[0]
                                        : implode(' / ', $registerSocialNames);
                                @endphp
                                @if ($registerSocialLabel)
                                    <p class="small text-muted mb-2 text-center" id="register_social_heading">Or continue with {{ $registerSocialLabel }}</p>
                                    @include('auth._social_auth_buttons', ['class' => 'mb-0'])
                                @endif
                            </div>
                        </div>

                        <div class="col-12">
                            <p class="small text-muted mb-2 password-generate-row">
                                <span>{{ __('auth.password_own_or_generate') }}</span>
                                <button type="button"
                                        class="btn btn-outline-success btn-sm js-generate-password"
                                        data-password="#register_password"
                                        data-confirm="#register_password_confirmation">
                                    <i class="bi bi-stars"></i> {{ __('auth.generate_password') }}
                                </button>
                            </p>
                            <span class="small text-success d-none js-generate-status" id="register_password_generated">{{ __('auth.password_generated') }}</span>
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
                    </div>
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
    const socialSection = document.getElementById('register_social_section');
    const socialProviders = socialSection?.dataset.socialProviders || '';
    function updateNote() {
        if (!roleSelect || !note) return;
        if (roleSelect.value === 'chef' || roleSelect.value === 'traveler') {
            const socialHint = socialProviders
                ? ' You can register with email or use ' + socialProviders + ' below.'
                : ' You can register with email below.';
            note.innerHTML = '<span class="text-warning"><i class="bi bi-info-circle"></i> This role requires admin approval.' + socialHint + '</span>';
            if (socialProviders) {
                socialSection?.classList.remove('d-none');
            } else {
                socialSection?.classList.add('d-none');
            }
            if (typeof window.setOAuthIntent === 'function') {
                window.setOAuthIntent(roleSelect.value);
            }
        } else {
            note.innerHTML = '<span class="text-muted">You can start ordering immediately.</span>';
            socialSection?.classList.add('d-none');
            if (typeof window.setOAuthIntent === 'function') {
                window.setOAuthIntent(null);
            }
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
