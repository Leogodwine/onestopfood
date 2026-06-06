(function () {
    'use strict';

    function getStack() {
        var stack = document.getElementById('appToastStack');

        if (!stack) {
            stack = document.createElement('div');
            stack.id = 'appToastStack';
            stack.className = 'app-toast-stack';

            var profileAnchor = document.querySelector('.top-navbar .user-menu');

            if (profileAnchor) {
                stack.classList.add('app-toast-stack--profile');
                profileAnchor.appendChild(stack);
            } else {
                document.body.appendChild(stack);
            }

            stack.setAttribute('aria-live', 'polite');
            stack.setAttribute('aria-atomic', 'true');
        }

        return stack;
    }

    function hideToast(toast) {
        if (!toast || toast.dataset.hiding === '1') {
            return;
        }

        toast.dataset.hiding = '1';
        toast.classList.remove('show');

        window.setTimeout(function () {
            toast.remove();
        }, 350);
    }

    function showAppToast(message, options) {
        options = options || {};

        if (!message) {
            return null;
        }

        var type = options.type || 'success';
        var icon = options.icon || (type === 'error' ? 'bi-exclamation-circle-fill' : 'bi-check-circle-fill');
        var duration = typeof options.duration === 'number' ? options.duration : 4000;
        var stack = getStack();
        var toast = document.createElement('div');

        toast.className = 'app-toast app-toast--' + type;
        toast.setAttribute('role', 'alert');

        var iconEl = document.createElement('i');
        iconEl.className = 'bi ' + icon;
        iconEl.setAttribute('aria-hidden', 'true');

        var textEl = document.createElement('span');
        textEl.textContent = message;

        toast.appendChild(iconEl);
        toast.appendChild(textEl);
        stack.appendChild(toast);

        requestAnimationFrame(function () {
            toast.classList.add('show');
        });

        var timer = window.setTimeout(function () {
            hideToast(toast);
        }, duration);

        toast.addEventListener('click', function () {
            window.clearTimeout(timer);
            hideToast(toast);
        });

        return toast;
    }

    function bootFromJson() {
        var dataEl = document.getElementById('appToastData');

        if (!dataEl) {
            return;
        }

        try {
            var payloads = JSON.parse(dataEl.textContent || '[]');

            if (Array.isArray(payloads)) {
                payloads.forEach(function (payload) {
                    if (payload && payload.message) {
                        showAppToast(payload.message, payload);
                    }
                });
            }
        } catch (error) {
            // Ignore malformed toast payloads.
        }

        dataEl.remove();
    }

    window.showAppToast = showAppToast;

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', bootFromJson);
    } else {
        bootFromJson();
    }
})();
