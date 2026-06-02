@once('password-tools-styles')
<style>
    .password-input-group .form-control.is-invalid {
        border-top-right-radius: 0;
        border-bottom-right-radius: 0;
    }
    .password-input-group .btn-toggle-password {
        border-color: #ced4da;
    }
    .password-generate-row {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        justify-content: space-between;
        gap: 0.5rem;
        width: 100%;
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

        var genBtn = e.target.closest('.js-generate-password');
        if (genBtn) {
            var pwdSel = genBtn.getAttribute('data-password');
            var confirmSel = genBtn.getAttribute('data-confirm');
            var pwdInput = pwdSel ? document.querySelector(pwdSel) : null;
            var confirmInput = confirmSel ? document.querySelector(confirmSel) : null;
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
            var hint = document.getElementById(pwdInput.getAttribute('aria-describedby') || '');
            if (hint) hint.classList.remove('d-none');

            var container = genBtn.closest('.col-12') || genBtn.parentElement;
            var status = container ? container.querySelector('.js-generate-status') : null;
            if (status) {
                status.classList.remove('d-none');
                clearTimeout(status._hideTimer);
                status._hideTimer = setTimeout(function () {
                    status.classList.add('d-none');
                }, 4000);
            }
        }
    });
})();
</script>
@endonce
