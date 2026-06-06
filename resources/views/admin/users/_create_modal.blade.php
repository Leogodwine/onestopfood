@php
    $access = app(\App\Services\AdminAccessService::class);
    $canCreateAdmin = auth()->user()?->adminCan('users.create_admin') ?? false;
    $adminTitles = $access->titles();
    $createUserErrors = $errors->getBag('create_user');
    $showCreateUserModal = $showCreateUserModal ?? $createUserErrors->isNotEmpty() || session('open_create_user_modal');
@endphp
<div class="modal fade" id="createUserModal" tabindex="-1" aria-labelledby="createUserModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 16px;">
            <div class="modal-header bg-dark text-white" style="border-top-left-radius: 16px; border-top-right-radius: 16px;">
                <div>
                    <h5 class="modal-title mb-0 text-white" id="createUserModalLabel">
                        <i class="bi bi-person-plus"></i> Create User Account
                    </h5>
                    <small class="text-white-50">Add a customer, partner, or admin account</small>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <form method="POST" action="{{ route('admin.users.store') }}" novalidate>
                @csrf

                <div class="modal-body p-4">
                    @if ($createUserErrors->any())
                        <div class="alert alert-danger py-2 mb-3">
                            <ul class="mb-0 small">
                                @foreach ($createUserErrors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold mb-1" for="create_user_name">Full name</label>
                            <input type="text"
                                   name="name"
                                   id="create_user_name"
                                   class="form-control form-control-sm @if($createUserErrors->has('name')) is-invalid @endif"
                                   value="{{ old('name') }}"
                                   required
                                   autocomplete="name">
                            @if($createUserErrors->has('name'))
                                <div class="invalid-feedback">{{ $createUserErrors->first('name') }}</div>
                            @endif
                        </div>

                        <div class="col-md-6">
                            @include('partials.phone-input', [
                                'label' => __('auth.phone_label'),
                                'errorBag' => 'create_user',
                                'value' => old('phone'),
                                'inputId' => 'create_user_phone_number',
                                'selectId' => 'create_user_phone_country_code',
                            ])
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold mb-1" for="create_user_email">Email</label>
                            <input type="email"
                                   name="email"
                                   id="create_user_email"
                                   class="form-control form-control-sm @if($createUserErrors->has('email')) is-invalid @endif"
                                   value="{{ old('email') }}"
                                   required
                                   autocomplete="email">
                            @if($createUserErrors->has('email'))
                                <div class="invalid-feedback">{{ $createUserErrors->first('email') }}</div>
                            @endif
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold mb-1" for="create_user_role">Role</label>
                            <select name="role"
                                    id="create_user_role"
                                    class="form-select form-select-sm @if($createUserErrors->has('role')) is-invalid @endif"
                                    required>
                                <option value="">Select role</option>
                                <option value="customer" @selected(old('role') === 'customer')>Customer</option>
                                <option value="chef" @selected(old('role') === 'chef')>Chef</option>
                                <option value="traveler" @selected(old('role') === 'traveler')>Traveler</option>
                                @if($canCreateAdmin)
                                    <option value="admin" @selected(old('role') === 'admin')>Admin</option>
                                @endif
                            </select>
                            @if($createUserErrors->has('role'))
                                <div class="invalid-feedback">{{ $createUserErrors->first('role') }}</div>
                            @endif
                        </div>

                        <div class="col-md-6 d-none" id="create-user-partner-status-wrap">
                            <label class="form-label fw-semibold mb-1" for="create_user_status">Partner status</label>
                            <select name="status"
                                    id="create_user_status"
                                    class="form-select form-select-sm @if($createUserErrors->has('status')) is-invalid @endif">
                                <option value="approved" @selected(old('status', 'approved') === 'approved')>Approved — can use the platform</option>
                                <option value="pending" @selected(old('status') === 'pending')>Pending — must complete verification</option>
                            </select>
                            <small class="text-muted d-block mt-1">Applies to chef and traveler accounts only.</small>
                            @if($createUserErrors->has('status'))
                                <div class="invalid-feedback d-block">{{ $createUserErrors->first('status') }}</div>
                            @endif
                        </div>

                        @if($canCreateAdmin)
                            <div class="col-md-6 d-none" id="create-user-admin-title-wrap">
                                <label class="form-label fw-semibold mb-1" for="create_user_admin_title">Admin privileges</label>
                                <select name="admin_title"
                                        id="create_user_admin_title"
                                        class="form-select form-select-sm @if($createUserErrors->has('admin_title')) is-invalid @endif">
                                    @foreach($adminTitles as $key => $meta)
                                        <option value="{{ $key }}" @selected(old('admin_title', 'manager') === $key)>
                                            {{ $meta['label'] }}
                                        </option>
                                    @endforeach
                                </select>
                                <small class="text-muted d-block mt-1" id="create-user-admin-title-note">
                                    {{ $adminTitles['manager']['description'] ?? '' }}
                                </small>
                                @if($createUserErrors->has('admin_title'))
                                    <div class="invalid-feedback d-block">{{ $createUserErrors->first('admin_title') }}</div>
                                @endif
                            </div>
                        @endif

                        <div class="col-md-6">
                            @php
                                $passwordErrors = $createUserErrors->get('password', []);
                                $passwordError = collect($passwordErrors)->first(
                                    fn ($message) => $message !== __('auth.password_confirmed')
                                );
                                $confirmError = collect($passwordErrors)->contains(__('auth.password_confirmed'))
                                    ? __('auth.password_confirmed')
                                    : $createUserErrors->first('password_confirmation');
                            @endphp
                            @include('auth.partials.password-input', [
                                'inputId' => 'create_user_password',
                                'name' => 'password',
                                'label' => 'Password',
                                'size' => 'sm',
                                'withHint' => true,
                                'withChoice' => true,
                                'weakErrorFullWidth' => true,
                                'confirmSelector' => '#create_user_password_confirmation',
                                'hintId' => 'createUserPasswordWeakError',
                                'invalid' => (bool) $passwordError,
                                'errorMessage' => $passwordError,
                            ])
                        </div>

                        <div class="col-md-6">
                            @include('auth.partials.password-input', [
                                'inputId' => 'create_user_password_confirmation',
                                'name' => 'password_confirmation',
                                'label' => 'Confirm password',
                                'size' => 'sm',
                                'withHint' => false,
                                'invalid' => (bool) $confirmError,
                                'errorMessage' => $confirmError,
                            ])
                        </div>

                        <div class="col-12 password-weak-error-row">
                            @include('auth.partials.password-weak-error', [
                                'hintId' => 'createUserPasswordWeakError',
                                'visible' => (bool) $passwordError,
                                'message' => __('auth.password_hint'),
                            ])
                        </div>
                    </div>
                </div>

                <div class="modal-footer border-0 px-4 pb-4 pt-0">
                    <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success btn-sm">
                        <i class="bi bi-person-plus"></i> Create account
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    window.openCreateUserModal = function () {
        var el = document.getElementById('createUserModal');
        if (!el) return;
        bootstrap.Modal.getOrCreateInstance(el).show();
    };

    var roleSelect = document.getElementById('create_user_role');
    var statusWrap = document.getElementById('create-user-partner-status-wrap');
    var adminTitleWrap = document.getElementById('create-user-admin-title-wrap');
    var adminTitleSelect = document.getElementById('create_user_admin_title');
    var adminTitleNote = document.getElementById('create-user-admin-title-note');
    var adminTitleDescriptions = @json(collect($adminTitles)->map(fn ($m) => $m['description'] ?? ''));

    function syncCreateUserRoleFields() {
        var role = roleSelect?.value || '';
        var isPartner = role === 'chef' || role === 'traveler';
        var isAdmin = role === 'admin';

        if (statusWrap) {
            statusWrap.classList.toggle('d-none', !isPartner);
        }
        if (adminTitleWrap) {
            adminTitleWrap.classList.toggle('d-none', !isAdmin);
        }
    }

    function syncAdminTitleNote() {
        if (!adminTitleSelect || !adminTitleNote) return;
        var key = adminTitleSelect.value;
        adminTitleNote.textContent = adminTitleDescriptions[key] || '';
    }

    roleSelect?.addEventListener('change', syncCreateUserRoleFields);
    adminTitleSelect?.addEventListener('change', syncAdminTitleNote);
    syncCreateUserRoleFields();
    syncAdminTitleNote();

    @if($showCreateUserModal)
        window.openCreateUserModal();
    @endif
});
</script>
@endpush
