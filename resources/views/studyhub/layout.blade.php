<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>@yield('title', 'StudyHub')</title>
        <script>
            (function () {
                try {
                    if (localStorage.getItem('studyhub-login-theme') === 'dark') {
                        document.documentElement.dataset.studyhubFirstPaint = 'dark';
                    }
                } catch (error) {
                    // Keep the default first paint when browser storage is unavailable.
                }
            })();
        </script>
        <style>
            html,
            body {
                min-height: 100%;
                margin: 0;
                background: #edf2ec;
            }

            html[data-studyhub-first-paint="dark"],
            html[data-studyhub-first-paint="dark"] body {
                background: #0b1110;
                color-scheme: dark;
            }

            html.studyhub-is-navigating::before {
                content: "";
                position: fixed;
                inset: 0;
                z-index: 2147483647;
                pointer-events: none;
                background:
                    radial-gradient(circle at top left, rgba(145, 212, 164, 0.22), transparent 30%),
                    linear-gradient(180deg, #f4f6f1 0%, #edf2ec 100%);
            }

            html[data-studyhub-first-paint="dark"].studyhub-is-navigating::before {
                background:
                    radial-gradient(circle at top left, rgba(73, 182, 112, 0.18), transparent 30%),
                    radial-gradient(circle at top right, rgba(71, 115, 255, 0.12), transparent 28%),
                    linear-gradient(180deg, #101813 0%, #0b1110 100%);
            }
        </style>
        @vite(['resources/css/app.css'])
        @livewireStyles
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700,800" rel="stylesheet" />
        @stack('styles')
    </head>
    <body>
        @yield('content')
        <script>
            (function () {
                const root = document.documentElement;

                function showNavigationCover() {
                    root.classList.add('studyhub-is-navigating');
                }

                function isPlainLeftClick(event) {
                    return event.button === 0 && ! event.metaKey && ! event.ctrlKey && ! event.shiftKey && ! event.altKey;
                }

                function shouldCoverLink(anchor) {
                    if (! anchor || anchor.target || anchor.hasAttribute('download') || anchor.dataset.noNavigationCover === 'true') {
                        return false;
                    }

                    const href = anchor.getAttribute('href');

                    if (! href || href.startsWith('#') || href.startsWith('mailto:') || href.startsWith('tel:') || href.startsWith('javascript:')) {
                        return false;
                    }

                    try {
                        const url = new URL(anchor.href, window.location.href);

                        return url.origin === window.location.origin
                            && (url.pathname !== window.location.pathname || url.search !== window.location.search || url.hash === '');
                    } catch (error) {
                        return false;
                    }
                }

                document.addEventListener('click', function (event) {
                    if (! isPlainLeftClick(event) || event.defaultPrevented) {
                        return;
                    }

                    const anchor = event.target.closest('a[href]');

                    if (shouldCoverLink(anchor)) {
                        showNavigationCover();
                    }
                });

                document.addEventListener('submit', function (event) {
                    if (event.defaultPrevented) {
                        return;
                    }

                    const form = event.target;

                    if (form?.dataset.noNavigationCover === 'true' || form?.target) {
                        return;
                    }

                    showNavigationCover();
                });

                window.addEventListener('pageshow', function () {
                    root.classList.remove('studyhub-is-navigating');
                });
            })();
        </script>
        @livewireScripts
    </body>
</html>
