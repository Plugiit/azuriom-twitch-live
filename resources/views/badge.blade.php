<script>
    (function () {
        var url = @json($url);
        var label = @json(trans('plugiit-twitch-live::messages.badge', ['count' => $count]));
        var links = document.querySelectorAll('a[href="' + url + '"]');

        links.forEach(function (link) {
            if (link.querySelector('.plugiit-twitch-live-badge') || !link.closest('nav, header, .navbar')) {
                return;
            }

            var badge = document.createElement('span');
            badge.className = 'badge rounded-pill ms-1 plugiit-twitch-live-badge';
            badge.style.background = '#eb0400';
            badge.style.color = '#fff';
            badge.title = label;
            badge.setAttribute('aria-label', label);
            badge.textContent = @json((string) $count);
            link.appendChild(badge);

            // When the link is inside a dropdown, also flag the dropdown toggle.
            var dropdown = link.closest('.dropdown');
            var toggle = dropdown ? dropdown.querySelector('.dropdown-toggle') : null;

            if (toggle && !toggle.querySelector('.plugiit-twitch-live-badge')) {
                var dot = badge.cloneNode(true);
                toggle.appendChild(dot);
            }
        });
    })();
</script>
