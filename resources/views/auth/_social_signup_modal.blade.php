<!-- Complete Social Sign Up Modal -->
@php
    $otpSent = ! empty($socialSignupState['otpSent']);
    $verifyUser = $socialSignupState['user'];
    $hasEmail = ! str_ends_with((string) $verifyUser->email, '@social.local');
    $resendCooldown = (int) ($socialSignupState['resendCooldown'] ?? 0);
@endphp
<div class="modal fade" id="socialSignupModal" tabindex="-1" aria-labelledby="socialSignupModalLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-dialog-centered modal-md">
        <div class="modal-content border-0 shadow-lg social-signup-modal">
            <div class="modal-header bg-dark text-white">
                <div>
                    <h5 class="modal-title mb-0" id="socialSignupModalLabel">
                        <i class="bi bi-shield-check"></i>
                        {{ $otpSent ? 'Verify Your Account' : 'Complete Your Sign Up' }}
                    </h5>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body p-4">
                @if ($errors->social_signup->any())
                    <div class="alert alert-danger py-2 mb-3">
                        <ul class="mb-0 small">
                            @foreach ($errors->social_signup->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                @if (config('app.show_developer_hints') && !empty($socialSignupState['otpHint']))
                    <div class="alert alert-info py-2 small mb-3">
                        <strong>Development only:</strong> Your code is <code>{{ $socialSignupState['otpHint'] }}</code>
                    </div>
                @endif

                @if (! $otpSent)
                    @php
                        $selectedRole = $socialSignupState['selectedRole'] ?? 'customer';
                    @endphp

                    <form method="POST" action="{{ route('social.signup.send-otp') }}" novalidate>
                        @csrf

                        <div class="mb-3">
                            <label for="social_role" class="form-label fw-semibold small">
                                Account type <span class="text-danger">*</span>
                            </label>
                            <select
                                name="role"
                                id="social_role"
                                class="form-select form-select-sm @if($errors->social_signup->has('role')) is-invalid @endif"
                                required
                            >
                                <option value="customer" @selected($selectedRole === 'customer')>Customer</option>
                                <option value="chef" @selected($selectedRole === 'chef')>Chef</option>
                                <option value="traveler" @selected($selectedRole === 'traveler')>Traveler</option>
                            </select>
                            @if($errors->social_signup->has('role'))
                                <div class="invalid-feedback">{{ $errors->social_signup->first('role') }}</div>
                            @endif
                            <div class="form-text mt-2" id="social_role_note"></div>
                        </div>

                        <div class="mb-4">
                            @include('partials.phone-input', [
                                'label' => __('auth.phone_label'),
                                'errorBag' => 'social_signup',
                                'value' => old('phone', $verifyUser->phone ?? ''),
                                'inputId' => 'social_phone_number',
                                'selectId' => 'social_phone_country_code',
                            ])
                        </div>

                        <button type="submit" class="btn btn-success btn-sm w-100">
                            Continue
                        </button>
                    </form>
                @else
                    <p class="text-muted small mb-3">
                        To protect your account, we have sent a 6-digit verification code to:
                    </p>

                    <div class="social-verify-targets mb-4">
                        @if($hasEmail)
                            <div class="social-verify-target">
                                <span class="social-verify-label">Email</span>
                                <a href="mailto:{{ $verifyUser->email }}" class="social-verify-value">{{ $verifyUser->email }}</a>
                            </div>
                        @endif
                        <div class="social-verify-target">
                            <span class="social-verify-label">SMS</span>
                            <span class="social-verify-value">{{ $verifyUser->phone }}</span>
                        </div>
                    </div>

                    <form method="POST" action="{{ route('social.signup.verify-otp') }}" id="socialVerifyForm" novalidate>
                        @csrf

                        <p class="small fw-semibold mb-1">Enter the verification code below to continue.</p>
                        <p class="small text-muted mb-2">
                            The code is valid for {{ \App\Services\SocialSignupService::OTP_EXPIRY_MINUTES }} minutes.
                        </p>

                        <div class="social-otp-inputs mb-2" id="socialOtpInputs">
                            @for ($i = 0; $i < 6; $i++)
                                <input
                                    type="text"
                                    class="social-otp-digit form-control @if($errors->social_signup->has('code')) is-invalid @endif"
                                    inputmode="numeric"
                                    pattern="[0-9]*"
                                    maxlength="1"
                                    autocomplete="one-time-code"
                                    aria-label="Digit {{ $i + 1 }}"
                                    @if($i === 0) autofocus @endif
                                >
                            @endfor
                        </div>
                        <input type="hidden" name="code" id="social_code" value="{{ old('code') }}">
                        @if($errors->social_signup->has('code'))
                            <div class="text-danger small mb-2">{{ $errors->social_signup->first('code') }}</div>
                        @endif

                        <button type="submit" class="btn btn-success btn-sm w-100 mt-3">
                            Verify &amp; continue
                        </button>
                    </form>

                    <div class="text-center mt-4">
                        <p class="small text-muted mb-2">Didn't receive the code?</p>
                        <form method="POST" action="{{ route('social.signup.resend-otp') }}" class="d-inline" id="socialResendForm">
                            @csrf
                            <button
                                type="submit"
                                class="btn btn-link btn-sm p-0 social-resend-btn"
                                id="socialResendBtn"
                                @if($resendCooldown > 0) disabled @endif
                            >
                                @if($resendCooldown > 0)
                                    Resend Code (available in <span id="socialResendTimer">{{ sprintf('%02d:%02d', intdiv($resendCooldown, 60), $resendCooldown % 60) }}</span>)
                                @else
                                    Resend Code
                                @endif
                            </button>
                        </form>
                    </div>

                    <div class="text-center mt-3">
                        <a href="{{ route('login', ['restart_social_signup' => 1]) }}" class="small text-decoration-none">
                            Change Email or Phone Number
                        </a>
                    </div>
                @endif
            </div>

            @if (! $otpSent)
                <div class="modal-footer border-0 px-4 pb-4 pt-0">
                    <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">
                        Back to {{ __('auth.sign_in') }}
                    </button>
                </div>
            @endif
        </div>
    </div>
</div>

<style>
.social-signup-modal { border-radius: 16px; overflow: hidden; }
.social-signup-modal .modal-header { border-top-left-radius: 16px; border-top-right-radius: 16px; }
.social-verify-targets { border: 1px solid #e9ecef; border-radius: 10px; overflow: hidden; background: #f8f9fa; }
.social-verify-target { display: flex; align-items: center; gap: 1rem; padding: .85rem 1rem; }
.social-verify-target + .social-verify-target { border-top: 1px solid #e9ecef; }
.social-verify-label { min-width: 3.5rem; font-size: .75rem; font-weight: 700; text-transform: uppercase; letter-spacing: .04em; color: #6c757d; }
.social-verify-value { font-size: .95rem; font-weight: 600; color: #212529; word-break: break-all; text-decoration: none; }
.social-verify-value:hover { color: #198754; }
.social-otp-inputs { display: flex; gap: .5rem; justify-content: center; }
.social-otp-digit {
    width: 2.75rem; height: 3rem; text-align: center; font-size: 1.35rem; font-weight: 700;
    border-radius: 8px; border: 1px solid #ced4da; padding: 0;
}
.social-otp-digit:focus { border-color: #198754; box-shadow: 0 0 0 .2rem rgba(25, 135, 84, .15); }
.social-resend-btn:disabled { color: #6c757d; text-decoration: none; cursor: not-allowed; }
</style>

<script>
document.addEventListener('DOMContentLoaded', function () {
    window.openSocialSignupModal = function () {
        var el = document.getElementById('socialSignupModal');
        if (!el) return;
        bootstrap.Modal.getOrCreateInstance(el).show();
    };

    var roleSelect = document.getElementById('social_role');
    var roleNote = document.getElementById('social_role_note');
    function updateSocialRoleNote() {
        if (!roleSelect || !roleNote) return;
        if (roleSelect.value === 'chef' || roleSelect.value === 'traveler') {
            roleNote.innerHTML = '<span class="text-warning"><i class="bi bi-info-circle"></i> Requires admin approval after verification.</span>';
        } else {
            roleNote.innerHTML = '';
        }
    }
    roleSelect?.addEventListener('change', updateSocialRoleNote);
    updateSocialRoleNote();

    var otpInputs = document.querySelectorAll('.social-otp-digit');
    var hiddenCode = document.getElementById('social_code');
    var verifyForm = document.getElementById('socialVerifyForm');

    function syncOtpCode() {
        if (!hiddenCode) return;
        hiddenCode.value = Array.from(otpInputs).map(function (i) { return i.value; }).join('');
    }

    otpInputs.forEach(function (input, index) {
        input.addEventListener('input', function () {
            input.value = input.value.replace(/\D/g, '').slice(0, 1);
            syncOtpCode();
            if (input.value && index < otpInputs.length - 1) {
                otpInputs[index + 1].focus();
            }
        });

        input.addEventListener('keydown', function (e) {
            if (e.key === 'Backspace' && !input.value && index > 0) {
                otpInputs[index - 1].focus();
            }
        });

        input.addEventListener('paste', function (e) {
            e.preventDefault();
            var pasted = (e.clipboardData || window.clipboardData).getData('text').replace(/\D/g, '').slice(0, 6);
            pasted.split('').forEach(function (char, i) {
                if (otpInputs[i]) otpInputs[i].value = char;
            });
            syncOtpCode();
            if (pasted.length === 6 && verifyForm) verifyForm.requestSubmit();
        });
    });

    if (hiddenCode && hiddenCode.value) {
        hiddenCode.value.split('').forEach(function (char, i) {
            if (otpInputs[i]) otpInputs[i].value = char;
        });
    }

    var resendBtn = document.getElementById('socialResendBtn');
    var timerEl = document.getElementById('socialResendTimer');
    var cooldown = {{ (int) $resendCooldown }};

    if (resendBtn && timerEl && cooldown > 0) {
        var interval = setInterval(function () {
            cooldown -= 1;
            if (cooldown <= 0) {
                clearInterval(interval);
                resendBtn.disabled = false;
                resendBtn.textContent = 'Resend Code';
                return;
            }
            var mins = String(Math.floor(cooldown / 60)).padStart(2, '0');
            var secs = String(cooldown % 60).padStart(2, '0');
            timerEl.textContent = mins + ':' + secs;
        }, 1000);
    }

    @if (!empty($socialSignupState['showModal']))
        window.openSocialSignupModal();
    @endif
});
</script>
