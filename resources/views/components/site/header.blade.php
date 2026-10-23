@props(['links' => []])
<nav class="sticky top-0 z-40 border-b border-line bg-white/95 backdrop-blur-xl" aria-label="Main navigation">
    <x-site.container class="flex h-19 items-center justify-between gap-4">
        <a href="{{ route('home') }}" class="inline-flex shrink-0 items-center" aria-label="Henry Porter Machinery home">
            <img src="{{ asset('images/logo.jpeg') }}" alt="Henry Porter Machinery" width="460" height="108" class="h-10 w-auto object-contain sm:h-11">
        </a>
        <ul class="hidden items-center gap-5 text-sm font-medium text-muted lg:flex xl:gap-9">
            @foreach ($links as $link)
                <li><a href="{{ $link['href'] }}" @if($link['active']) aria-current="page" @endif @class(['transition hover:text-brand', 'text-brand' => $link['active']])>{{ $link['label'] }}</a></li>
            @endforeach
        </ul>
        <div class="flex items-center gap-3">
            <a href="{{ route('contact') }}" class="rounded-full bg-brand px-3 py-3 font-display text-xs font-bold text-white transition hover:bg-brand-dark sm:px-5">Contact Us</a>
            <button type="button" data-menu-toggle aria-expanded="false" aria-controls="mobile-drawer" aria-label="Toggle navigation menu" class="relative inline-flex size-11 items-center justify-center rounded-xl border border-line text-charcoal transition hover:border-brand hover:text-brand focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand lg:hidden">
                <span class="nav-toggle-icon" aria-hidden="true">
                    <span class="nav-toggle-bar"></span>
                    <span class="nav-toggle-bar"></span>
                    <span class="nav-toggle-bar"></span>
                </span>
                <span class="sr-only">Toggle Menu</span>
            </button>
        </div>
    </x-site.container>
</nav>