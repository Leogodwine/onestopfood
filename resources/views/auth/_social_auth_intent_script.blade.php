<script>
document.addEventListener('DOMContentLoaded', function () {
    window.setOAuthIntent = function (role) {
        document.querySelectorAll('[data-oauth-link]').forEach(function (link) {
            var base = link.getAttribute('data-oauth-base');
            if (!base) {
                return;
            }

            if (role && (role === 'chef' || role === 'traveler')) {
                link.href = base + (base.indexOf('?') >= 0 ? '&' : '?') + 'intent=' + encodeURIComponent(role);
            } else {
                link.href = base;
            }
        });
    };
});
</script>
