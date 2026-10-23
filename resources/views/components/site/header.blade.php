@php
    $homeSection = fn ($section) => request()->routeIs('home') ? '#'.$section : route('home').'#'.$section;
    $links = [
        ['label' => 'New Machinery', 'href' => route('machinery.new'), 'active' => request()->routeIs('machinery.new')],
        ['label' => 'Used Machinery', 'href' => route('machinery.used'), 'active' => request()->routeIs('machinery.used')],
        ['label' => 'Brands', 'href' => route('brands.index'), 'active' => request()->routeIs('brands.*')],
        ['label'=>'Parts','href'=>route('parts'),'active'=>request()->routeIs('parts')],
        ['label' => 'About', 'href' => route('about'), 'active' => request()->routeIs('about')],
        ['label' => 'News', 'href' => route('news.index'), 'active' => request()->routeIs('news.*')],
    ];
@endphp
<nav class="sticky top-0 z-50 border-b border-line bg-white/95 backdrop-blur-xl" aria-label="Main navigation">
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
