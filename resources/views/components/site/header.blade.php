@php
    $homeSection = fn ($section) => request()->routeIs('home') ? '#'.$section : route('home').'#'.$section;
    $links = [
        ['label' => 'New Machinery', 'href' => $homeSection('products'), 'active' => false],
        ['label' => 'Used Machinery', 'href' => $homeSection('used'), 'active' => false],
        ['label' => 'Brands', 'href' => $homeSection('brands'), 'active' => false],
        ['label' => 'About', 'href' => route('about'), 'active' => request()->routeIs('about')],
        ['label' => 'News', 'href' => $homeSection('news'), 'active' => false],
    ];
@endphp
<nav class="sticky top-0 z-50 border-b border-line bg-white/95 backdrop-blur-xl" aria-label="Main navigation">
    <x-site.container class="flex h-19 items-center justify-between gap-4">
        <a href="{{ route('home') }}" class="site-logo inline-flex items-center font-display text-base font-extrabold tracking-tight sm:text-lg" aria-label="Henry Porter home">Henry<span>Porter</span></a>
        <ul class="hidden items-center gap-5 text-sm font-medium text-muted lg:flex xl:gap-9">
            @foreach ($links as $link)
                <li><a href="{{ $link['href'] }}" @if($link['active']) aria-current="page" @endif @class(['transition hover:text-brand', 'text-brand' => $link['active']])>{{ $link['label'] }}</a></li>
            @endforeach
        </ul>
        <div class="flex items-center gap-3">
            <a href="#contact" class="rounded-full bg-brand px-3 py-3 font-display text-xs font-bold text-white transition hover:bg-brand-dark sm:px-5">Contact Us</a>
            <button type="button" data-menu-toggle aria-expanded="false" aria-controls="mobile-menu" class="rounded-lg border border-line px-3 py-2 text-sm font-semibold lg:hidden">Menu</button>
        </div>
    </x-site.container>
    <div id="mobile-menu" hidden class="border-t border-line bg-white lg:hidden">
        <x-site.container><ul class="grid gap-1 py-4">
            @foreach ($links as $link)
                <li><a href="{{ $link['href'] }}" @if($link['active']) aria-current="page" @endif @class(['block rounded-lg px-3 py-2 text-sm transition hover:bg-canvas hover:text-brand', 'text-brand' => $link['active']])>{{ $link['label'] }}</a></li>
            @endforeach
        </ul></x-site.container>
    </div>
</nav>
