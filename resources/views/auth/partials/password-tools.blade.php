@once('password-tools-styles')
<style>
    .password-input-group .form-control.is-invalid {
        border-top-right-radius: 0;
        border-bottom-right-radius: 0;
    }
    .password-input-group .btn-toggle-password {
        border-color: #ced4da;
    }
    .password-field-wrap--choice {
        position: relative;
    }
    .password-choice-popover {
        position: absolute;
        top: 100%;
        left: 0;
        right: 0;
        z-index: 1060;
        margin-top: 0.5rem;
        padding: 0.625rem 0.875rem;
        background: #fff;
        border: 1px solid #dee2e6;
        border-radius: 10px;
        box-shadow: 0 10px 28px rgba(15, 23, 42, 0.12), 0 2px 8px rgba(15, 23, 42, 0.06);
    }
    .password-choice-popover::before {
        content: '';
        position: absolute;
        top: -6px;
        left: 1.5rem;
        width: 11px;
        height: 11px;
        background: #fff;
        border-left: 1px solid #dee2e6;
        border-top: 1px solid #dee2e6;
        transform: rotate(45deg);
    }
    .password-choice-popover__actions {
        display: flex;
        flex-direction: column;
        gap: 0.625rem;
    }
    .password-choice-paragraph {
        width: 100%;
        margin: 0;
        padding: 0;
        color: #212529;
        font-size: 0.875rem;
        line-height: 1.5;
        text-align: left;
        cursor: pointer;
        transition: color 0.15s ease;
    }
    .password-choice-paragraph:hover,
    .password-choice-paragraph:focus-visible {
        color: #157347;
        outline: none;
    }
    .password-choice-paragraph.js-password-choice-generate {
        color: #495057;
    }
    .password-choice-paragraph.js-password-choice-generate:hover,
    .password-choice-paragraph.js-password-choice-generate:focus-visible {
        color: #157347;
    }
    .password-choice-popover__status {
        display: block;
        margin-top: 0.625rem;
        padding: 0.375rem 0.5rem;
        text-align: center;
        background: #f0fdf4;
        border-radius: 6px;
    }
    .modal .password-choice-popover {
        z-index: 1065;
    }
    .modal-body:has(.password-field-wrap--choice) {
        overflow: visible;
    }
</style>
@endonce

@once('password-tools-script')
<script>
(function () {
    function shuffle(str) {
        var arr = str.split('');
        for (var i = arr.length - 1; i > 0; i--) {
            var j = Math.floor(Math.random() * (i + 1));
            var t = arr[i];
            arr[i] = arr[j];
            arr[j] = t;
        }
        return arr.join('');
    }

    function pick(chars) {
        return chars.charAt(Math.floor(Math.random() * chars.length));
    }

    window.generateStrongPassword = function (length) {
        length = Math.max(12, length || 12);
        var upper = 'ABCDEFGHJKLMNPQRSTUVWXYZ';
        var lower = 'abcdefghjkmnpqrstuvwxyz';
        var numbers = '23456789';
        var symbols = '!@#$%&*';
        var all = upper + lower + numbers + symbols;
        var pwd = pick(upper) + pick(lower) + pick(numbers) + pick(symbols);
        while (pwd.length < length) {
            pwd += pick(all);
        }
        return shuffle(pwd);
    };

    function setToggleIcon(btn, visible) {
        var icon = btn.querySelector('i');
        if (!icon) return;
        icon.classList.toggle('bi-eye', !visible);
        icon.classList.toggle('bi-eye-slash', visible);
    }

    function showGenerateStatus(wrap) {
        if (!wrap) return;
        var status = wrap.querySelector('.js-generate-status');
        if (!status) return;
        status.classList.remove('d-none');
        clearTimeout(status._hideTimer);
        status._hideTimer = setTimeout(function () {
            status.classList.add('d-none');
        }, 4000);
    }

    function applyGeneratedPassword(pwdInput, confirmInput) {
        if (!pwdInput) return;

        var generated = window.generateStrongPassword(12);
        pwdInput.value = generated;
        pwdInput.type = 'text';
        if (confirmInput) {
            confirmInput.value = generated;
            confirmInput.type = 'text';
        }

        document.querySelectorAll('.js-toggle-password[data-target="' + pwdInput.id + '"]').forEach(function (btn) {
            setToggleIcon(btn, true);
            btn.setAttribute('aria-label', btn.dataset.hideLabel || '');
        });
        if (confirmInput && confirmInput.id) {
            document.querySelectorAll('.js-toggle-password[data-target="' + confirmInput.id + '"]').forEach(function (btn) {
                setToggleIcon(btn, true);
                btn.setAttribute('aria-label', btn.dataset.hideLabel || '');
            });
        }

        pwdInput.dispatchEvent(new Event('input', { bubbles: true }));
        if (typeof window.updatePasswordWeakState === 'function') {
            window.updatePasswordWeakState(pwdInput);
        }

        var wrap = pwdInput.closest('.password-field-wrap');
        showGenerateStatus(wrap);
        if (!wrap) {
            var container = pwdInput.closest('.col-12') || pwdInput.parentElement;
            showGenerateStatus(container);
        }
    }

    function hideAllPasswordChoices() {
        document.querySelectorAll('.password-choice-popover').forEach(function (el) {
            el.classList.add('d-none');
        });
    }

    function showPasswordChoice(input) {
        if (!input || input.value.trim()) return;
        if (input._skipPasswordChoiceOnce) {
            input._skipPasswordChoiceOnce = false;
            return;
        }
        var wrap = input.closest('.password-field-wrap');
        var pop = wrap ? wrap.querySelector('.password-choice-popover') : null;
        if (!pop) return;
        hideAllPasswordChoices();
        pop.classList.remove('d-none');
    }

    document.addEventListener('focusin', function (e) {
        var input = e.target.closest('.js-password-choice-trigger');
        if (input) showPasswordChoice(input);
    });

    document.addEventListener('mousedown', function (e) {
        if (e.target.closest('.password-choice-popover') || e.target.closest('.js-toggle-password')) {
            return;
        }
        if (!e.target.closest('.js-password-choice-trigger')) {
            hideAllPasswordChoices();
        }
    });

    document.addEventListener('click', function (e) {
        var toggleBtn = e.target.closest('.js-toggle-password');
        if (toggleBtn) {
            var targetId = toggleBtn.getAttribute('data-target');
            var input = targetId ? document.getElementById(targetId) : null;
            if (!input) return;
            var show = input.type === 'password';
            input.type = show ? 'text' : 'password';
            toggleBtn.setAttribute('aria-label', show ? toggleBtn.dataset.hideLabel : toggleBtn.dataset.showLabel);
            setToggleIcon(toggleBtn, show);
            return;
        }

        var ownBtn = e.target.closest('.js-password-choice-own');
        if (ownBtn) {
            e.preventDefault();
            var wrap = ownBtn.closest('.password-field-wrap');
            var input = wrap ? wrap.querySelector('.js-password-choice-trigger') : null;
            hideAllPasswordChoices();
            if (input) {
                input._skipPasswordChoiceOnce = true;
                input.focus();
            }
            return;
        }

        var genChoiceBtn = e.target.closest('.js-password-choice-generate');
        if (genChoiceBtn) {
            e.preventDefault();
            var choiceWrap = genChoiceBtn.closest('.password-field-wrap');
            var pwdInput = choiceWrap ? choiceWrap.querySelector('.js-password-choice-trigger') : null;
            var confirmSel = pwdInput ? pwdInput.getAttribute('data-confirm-selector') : null;
            var confirmInput = confirmSel ? document.querySelector(confirmSel) : null;
            applyGeneratedPassword(pwdInput, confirmInput);
            hideAllPasswordChoices();
            if (pwdInput) pwdInput.focus();
            return;
        }

        var genBtn = e.target.closest('.js-generate-password');
        if (genBtn) {
            var pwdSel = genBtn.getAttribute('data-password');
            var confirmSelLegacy = genBtn.getAttribute('data-confirm');
            var pwdInputLegacy = pwdSel ? document.querySelector(pwdSel) : null;
            var confirmInputLegacy = confirmSelLegacy ? document.querySelector(confirmSelLegacy) : null;
            applyGeneratedPassword(pwdInputLegacy, confirmInputLegacy);
        }
    });

    document.addEventListener('keydown', function (e) {
        if (e.key !== 'Enter' && e.key !== ' ') {
            return;
        }
        var choice = e.target.closest('.js-password-choice-own, .js-password-choice-generate');
        if (!choice) {
            return;
        }
        e.preventDefault();
        choice.click();
    });
})();
</script>
@endonce
