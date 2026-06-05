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

            if (table.classList.contains('order-line-table')) {
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

    function init() {
        enhanceScrollTables(document);
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }

    document.addEventListener('shown.bs.modal', function (event) {
        if (event.target) {
            enhanceScrollTables(event.target);
        }
    });

    window.enhanceScrollTables = enhanceScrollTables;
    window.enhanceMobileTables = enhanceScrollTables;
})();
