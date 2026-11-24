<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />

    <title>@yield('title')</title>

    <script>
        (() => {
            const theme = localStorage.getItem('dark-theme');

            if (
                theme === 'true' ||
                theme === 'dark' ||
                (theme === 'system' &&
                    matchMedia('(prefers-color-scheme: dark)').matches)
            ) {
                document.documentElement.classList.add('dark');
            }
        })();
    </script>

    <style>
        :root {
            color-scheme: light;
            --error-background: #f8fafc;
            --error-text: #475569;
            --error-divider: #cbd5e1;
        }

        :root.dark {
            color-scheme: dark;
            --error-background: #1e293b;
            --error-text: #e2e8f0;
            --error-divider: #475569;
        }

        body {
            margin: 0;
            display: flex;
            min-height: 100vh;
            align-items: center;
            justify-content: center;
            background: var(--error-background);
            color: var(--error-text);
            font-family:
                ui-sans-serif,
                system-ui,
                -apple-system,
                'Segoe UI',
                Roboto,
                sans-serif;
            font-size: 1.125rem;
            -webkit-font-smoothing: antialiased;
        }

        main {
            display: flex;
            align-items: center;
        }

        h1 {
            margin: 0;
            padding: 0 1rem;
            border-right: 1px solid var(--error-divider);
            font-size: inherit;
            font-weight: 600;
        }

        p {
            margin: 0 0 0 1rem;
        }
    </style>
</head>
<body>
    <main>
        <h1>@yield('code')</h1>
        <p>@yield('message')</p>
    </main>
</body>
</html>
