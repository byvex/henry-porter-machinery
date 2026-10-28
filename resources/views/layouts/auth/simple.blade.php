<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="light">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>{{ filled($title ?? null) ? $title.' | ' : '' }}Henry Porter Machinery</title>
        <meta name="robots" content="noindex,follow">
        <link rel="icon" href="/favicon.ico">
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Sora:wght@400;500;600;700;800&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        <link rel="stylesheet" href="{{ asset('css/auth.css') }}?v={{ filemtime(public_path('css/auth.css')) }}">
    </head>
    <body class="auth-site">
        <a href="#auth-content" class="auth-skip">Skip to account form</a>
        <div class="auth-page">
            <header class="auth-topbar">
                <a href="{{ route('home') }}" class="auth-logo" aria-label="Henry Porter Machinery home">Henry<span>Porter</span><small>Machinery</small></a>
                <a href="{{ route('home') }}" class="auth-back"><span aria-hidden="true">&larr;</span> Back to website</a>
            </header>

            <main id="auth-content" class="auth-shell">
                <aside class="auth-story" aria-label="Henry Porter Machinery">
                    <img src="{{ asset('images/about/henry-and-james.jpeg') }}" alt="Henry and James beside Pöttinger machinery" class="auth-story-image">
                    <div class="auth-story-content">
                        <span class="auth-location">Lisburn, Northern Ireland</span>
                        <div>
                            <p class="auth-eyebrow">Serving farmers since 2006</p>
                            <h2>Rooted in farming.<br>Here for you.</h2>
                            <p>Quality machinery, genuine parts, and expert support from people who know your farm.</p>
                            <a href="{{ route('about') }}" class="auth-story-link">Get to know us <span aria-hidden="true">&rarr;</span></a>
                        </div>
                    </div>
                </aside>

                <section class="auth-panel" aria-label="Your account">
                    <div class="auth-form-wrap">
                        <div class="auth-account-label"><span aria-hidden="true"></span> Your Henry Porter account</div>
                        {{ $slot }}
                    </div>
                </section>
            </main>

            <footer class="auth-footer">
                <p>&copy; {{ date('Y') }} Henry Porter Machinery</p>
                <p>Need a hand? <a href="{{ route('contact') }}">Contact our team <span aria-hidden="true">&rarr;</span></a></p>
            </footer>
        </div>

        @persist('toast')
            <flux:toast.group>
                <flux:toast />
            </flux:toast.group>
        @endpersist

        @fluxScripts
    </body>
</html>
