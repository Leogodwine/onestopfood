(function () {
    'use strict';

    function isScrollableX(el) {
        return el.scrollWidth > el.clientWidth + 1;
    }

    function scrollPosition(el) {
        var max = el.scrollWidth - el.clientWidth;

        if (max <= 1) {
            return { atStart: true, atEnd: true, max: 0 };
        }

        var raw = el.scrollLeft;
        var isRtl = getComputedStyle(el).direction === 'rtl';
        var normalized;

        if (isRtl) {
            if (raw < 0) {
                normalized = Math.abs(raw);
            } else {
                normalized = max - raw;
            }
        } else {
            normalized = raw;
        }

        normalized = Math.max(0, Math.min(max, normalized));

        return {
            atStart: normalized <= 2,
            atEnd: normalized >= max - 2,
            max: max,
            current: normalized,
        };
    }

    function updateScrollState(outer, scroller) {
        var pos = scrollPosition(scroller);
        var scrollable = isScrollableX(scroller);

        outer.classList.toggle('is-scrollable-x', scrollable);
        outer.classList.toggle('can-scroll-left', scrollable && !pos.atStart);
        outer.classList.toggle('can-scroll-right', scrollable && !pos.atEnd);

        var hint = outer.querySelector('.table-scroll-hint');
        if (hint) {
            hint.hidden = !scrollable;
        }
    }

    function ensureScrollControls(outer, scroller) {
        if (outer.querySelector('.table-scroll-controls')) {
            return;
        }

        var controls = document.createElement('div');
        controls.className = 'table-scroll-controls';
        controls.innerHTML =
            '<button type="button" class="table-scroll-btn table-scroll-btn--left" aria-label="Scroll table left">' +
            '<i class="bi bi-chevron-left"></i></button>' +
            '<button type="button" class="table-scroll-btn table-scroll-btn--right" aria-label="Scroll table right">' +
            '<i class="bi bi-chevron-right"></i></button>';

        outer.appendChild(controls);

        var leftBtn = controls.querySelector('.table-scroll-btn--left');
        var rightBtn = controls.querySelector('.table-scroll-btn--right');
        var step = function () {
            return Math.max(160, Math.floor(scroller.clientWidth * 0.65));
        };

        leftBtn.addEventListener('click', function () {
            scroller.scrollBy({ left: -step(), behavior: 'smooth' });
        });

        rightBtn.addEventListener('click', function () {
            scroller.scrollBy({ left: step(), behavior: 'smooth' });
        });
    }

    function ensureHint(outer) {
        if (outer.querySelector('.table-scroll-hint')) {
            return;
        }

        var hint = document.createElement('div');
        hint.className = 'table-scroll-hint';
        hint.hidden = true;
        hint.innerHTML =
            '<i class="bi bi-arrows-angle-expand" aria-hidden="true"></i>' +
            '<span>Swipe or scroll horizontally to view all columns</span>';
        outer.appendChild(hint);
    }

    function enhanceScrollTables(root) {
        var scope = root || document;

        scope.querySelectorAll('.table-responsive').forEach(function (wrapper) {
            if (wrapper.dataset.scrollEnhanced === '1') {
                updateScrollState(wrapper.closest('.table-scroll-pro') || wrapper, wrapper);
                return;
            }

            wrapper.classList.remove('table-mobile-stack');

            var table = wrapper.querySelector('table');
            if (!table) {
                return;
            }

            if (
                table.classList.contains('order-line-table')
                || table.classList.contains('order-detail-meta-table')
                || table.classList.contains('order-detail-items-table')
                || wrapper.classList.contains('table-responsive-fit')
            ) {
                wrapper.dataset.scrollEnhanced = '1';
                return;
            }

            var outer = wrapper.closest('.table-scroll-pro');
            if (!outer) {
                outer = document.createElement('div');
                outer.className = 'table-scroll-pro';
                wrapper.parentNode.insertBefore(outer, wrapper);
                outer.appendChild(wrapper);
            }

            table.classList.add('table-pro');

            var headerCount = table.querySelectorAll('thead th').length;
            var bodyCols = table.querySelectorAll('tbody tr:first-child td').length;
            var cols = Math.max(headerCount, bodyCols);

            if (cols > 0 && !table.style.minWidth) {
                table.style.minWidth = Math.max(720, cols * 128) + 'px';
            }

            wrapper.setAttribute('tabindex', '0');
            wrapper.setAttribute('role', 'region');
            wrapper.setAttribute('aria-label', 'Scrollable data table');

            ensureHint(outer);
            ensureScrollControls(outer, wrapper);

            var onScroll = function () {
                updateScrollState(outer, wrapper);
            };

            wrapper.addEventListener('scroll', onScroll, { passive: true });
            window.addEventListener('resize', onScroll, { passive: true });

            wrapper.dataset.scrollEnhanced = '1';
            onScroll();
        });
    }

    function closePhoneCountryMenus(exceptPicker) {
        document.querySelectorAll('[data-phone-country-picker]').forEach(function (picker) {
            if (exceptPicker && picker === exceptPicker) {
                return;
            }

            var menu = picker.querySelector('.phone-country-menu');
            var trigger = picker.querySelector('.phone-country-trigger');

            if (menu) {
                menu.hidden = true;
            }

            if (trigger) {
                trigger.setAttribute('aria-expanded', 'false');
            }
        });
    }

    function parsePhoneRules(group) {
        if (!group || !group.dataset.phoneRules) {
            return {};
        }

        try {
            return JSON.parse(group.dataset.phoneRules);
        } catch (error) {
            return {};
        }
    }

    function applyPhoneNationalRules(group, countryCode) {
        if (!group) {
            return;
        }

        var nationalInput = group.querySelector('[data-phone-national-input]');
        var rules = parsePhoneRules(group);
        var rule = rules[countryCode] || rules[String(countryCode)];

        if (!nationalInput || !rule) {
            return;
        }

        nationalInput.maxLength = rule.length;
        nationalInput.pattern = rule.pattern;
        nationalInput.placeholder = rule.placeholder || nationalInput.placeholder;
        nationalInput.title = rule.title || nationalInput.title;
    }

    function setPhoneCountrySelection(picker, value, flag, dial) {
        var input = picker.querySelector('[data-phone-country-input]');
        var flagEl = picker.querySelector('[data-phone-country-flag]');
        var dialEl = picker.querySelector('[data-phone-country-dial]');
        var group = picker.closest('.phone-input-group');

        if (input) {
            input.value = value;
            input.dispatchEvent(new Event('change', { bubbles: true }));
        }

        if (flagEl) {
            flagEl.textContent = flag;
        }

        if (dialEl) {
            dialEl.textContent = dial;
        }

        applyPhoneNationalRules(group, value);

        picker.querySelectorAll('.phone-country-option').forEach(function (option) {
            var isActive = option.dataset.value === value;
            option.classList.toggle('is-active', isActive);
            option.setAttribute('aria-selected', isActive ? 'true' : 'false');
        });
    }

    function enhancePhoneCountryPickers(root) {
        (root || document).querySelectorAll('[data-phone-country-picker]').forEach(function (picker) {
            if (picker.dataset.phoneCountryEnhanced === '1') {
                return;
            }

            var trigger = picker.querySelector('.phone-country-trigger');
            var menu = picker.querySelector('.phone-country-menu');

            if (!trigger || !menu) {
                return;
            }

            trigger.addEventListener('click', function (event) {
                event.preventDefault();
                event.stopPropagation();

                var willOpen = menu.hidden;
                closePhoneCountryMenus(picker);
                menu.hidden = !willOpen;
                trigger.setAttribute('aria-expanded', willOpen ? 'true' : 'false');
            });

            menu.querySelectorAll('.phone-country-option').forEach(function (option) {
                option.addEventListener('click', function (event) {
                    event.preventDefault();
                    setPhoneCountrySelection(
                        picker,
                        option.dataset.value || '',
                        option.dataset.flag || '',
                        option.dataset.dial || ''
                    );
                    menu.hidden = true;
                    trigger.setAttribute('aria-expanded', 'false');
                });
            });

            var group = picker.closest('.phone-input-group');
            var countryInput = picker.querySelector('[data-phone-country-input]');

            if (group && countryInput) {
                applyPhoneNationalRules(group, countryInput.value);
            }

            picker.dataset.phoneCountryEnhanced = '1';
        });
    }

    function init() {
        enhanceScrollTables(document);
        enhancePhoneCountryPickers(document);
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }

    document.addEventListener('click', function () {
        closePhoneCountryMenus();
    });

    document.addEventListener('shown.bs.modal', function (event) {
        if (event.target) {
            enhanceScrollTables(event.target);
            enhancePhoneCountryPickers(event.target);
        }
    });

    window.enhanceScrollTables = enhanceScrollTables;
    window.enhanceMobileTables = enhanceScrollTables;
    window.enhancePhoneCountryPickers = enhancePhoneCountryPickers;
})();
