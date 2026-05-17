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
                    @if ($errors->register->any())
                        <div class="alert alert-danger py-2">
                            <ul class="mb-0 small">
                                @foreach ($errors->register->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

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
                                <i class="bi bi-telephone"></i> Phone <span class="text-muted fw-normal">(optional)</span>
                            </label>
                            <input
                                class="form-control form-control-sm @if($errors->register->has('phone')) is-invalid @endif"
                                name="phone"
                                value="{{ old('phone') }}"
                                autocomplete="tel"
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

                        <div class="col-md-6">
                            <label class="form-label fw-semibold mb-1">
                                <i class="bi bi-lock"></i> Password
                            </label>
                            <input
                                class="form-control form-control-sm @if($errors->register->has('password')) is-invalid @endif"
                                type="password"
                                name="password"
                                required
                                autocomplete="new-password"
                                placeholder="Min 8 characters"
                            >
                            @if($errors->register->has('password'))
                                <div class="invalid-feedback">{{ $errors->register->first('password') }}</div>
                            @endif
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold mb-1">
                                <i class="bi bi-lock-fill"></i> Confirm
                            </label>
                            <input
                                class="form-control form-control-sm"
                                type="password"
                                name="password_confirmation"
                                required
                                autocomplete="new-password"
                                placeholder="Repeat password"
                            >
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
    window.openRegisterModal = function () {
        const el = document.getElementById('registerModal');
        if (!el) return;
        const modal = bootstrap.Modal.getOrCreateInstance(el);
        modal.show();
    };

    // Role helper note (keeps UI short but clear)
    const roleSelect = document.getElementById('register_role');
    const note = document.getElementById('register_role_note');
    function updateNote() {
        if (!roleSelect || !note) return;
        if (roleSelect.value === 'chef' || roleSelect.value === 'traveler') {
            note.innerHTML = '<span class="text-warning"><i class="bi bi-info-circle"></i> This role requires admin approval before you can access the dashboard.</span>';
        } else {
            note.innerHTML = '<span class="text-muted">You can start ordering immediately.</span>';
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
