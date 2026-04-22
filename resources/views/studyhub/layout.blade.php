<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>@yield('title', 'StudyHub')</title>
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700,800" rel="stylesheet" />
        <style>
            :root {
                --green-main: #4a955f;
                --green-soft: #63bb7a;
                --green-pale: #dff6e3;
                --border-soft: #cfdbe7;
                --bg-soft: #f7f7f5;
                --text-main: #1a1f24;
                --text-muted: #6f767d;
                --card-shadow: 0 16px 32px rgba(47, 77, 95, 0.14);
            }

            * {
                box-sizing: border-box;
            }

            body {
                margin: 0;
                font-family: 'Instrument Sans', sans-serif;
                background: #f2f3ef;
                color: var(--text-main);
            }

            a {
                color: inherit;
                text-decoration: none;
            }

            .studyhub-shell {
                min-height: 100vh;
            }

            .icon-box {
                display: inline-flex;
                align-items: center;
                justify-content: center;
            }

            .icon-box svg {
                width: 20px;
                height: 20px;
                stroke: currentColor;
                fill: none;
                stroke-width: 2;
                stroke-linecap: round;
                stroke-linejoin: round;
            }

            @media (max-width: 900px) {
                .stack-mobile {
                    display: block !important;
                }

                .hide-mobile {
                    display: none !important;
                }
            }
        </style>
        @stack('styles')
    </head>
    <body>
        @yield('content')
    </body>
</html>
