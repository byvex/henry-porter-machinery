@props(['links' => []])
<nav class="sticky top-0 z-40 border-b border-line bg-white/95 backdrop-blur-xl" aria-label="Main navigation">
    <x-site.container class="flex h-19 items-center justify-between gap-4">
        <a href="{{ route('home') }}" class="inline-flex shrink-0 items-center" aria-label="Henry Porter Machinery home">
            <img src="{{ asset('images/logo.jpeg') }}" alt="Henry Porter Machinery" width="460" height="108" class="h-10 w-auto object-contain sm:h-11">
        </a>
        <ul class="hidden items-center gap-5 text-sm font-medium text-muted lg:flex xl:gap-8">
            @foreach ($links as $link)
                @if (!empty($link['children']))
                    <li class="nav-dropdown-wrapper relative group" data-nav-dropdown>
                        <div class="inline-flex items-center gap-1">
                            <a href="{{ $link['href'] }}" @if($link['active']) aria-current="page" @endif @class(['transition hover:text-brand font-medium inline-flex items-center gap-1 py-2', 'text-brand' => $link['active']])>
                                <span>{{ $link['label'] }}</span>
                            </a>
                            <button type="button" aria-haspopup="true" aria-expanded="false" aria-label="Toggle {{ $link['label'] }} submenu" class="nav-dropdown-toggle p-1 text-muted transition hover:text-brand focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand rounded">
                                <x-icon.keyboard-arrow-down class="size-4 transition-transform duration-200 group-hover:rotate-180" />
                            </button>
                        </div>
                        <div class="nav-dropdown-menu pointer-events-none opacity-0 invisible group-hover:pointer-events-auto group-hover:opacity-100 group-hover:visible group-focus-within:pointer-events-auto group-focus-within:opacity-100 group-focus-within:visible absolute top-full left-0 mt-1 w-80 rounded-2xl border border-line bg-white/95 p-2 shadow-xl backdrop-blur-xl transition-all duration-200 z-50">
                            <div class="px-3 pt-2 pb-1.5 border-b border-line/60 flex items-center justify-between">
                                <span class="font-display text-[11px] font-bold uppercase tracking-wider text-muted">New Equipment</span>
                                <a href="{{ $link['href'] }}" class="text-xs font-semibold text-brand hover:underline">View All &rarr;</a>
                            </div>
                            <ul class="py-1">
                                @foreach ($link['children'] as $child)
                                    <li>
                                        <a href="{{ $child['href'] }}" @class([
                                            'block rounded-xl px-3 py-2.5 transition',
                                            'bg-brand/10 text-brand' => $child['active'],
                                            'text-charcoal hover:bg-canvas hover:text-brand' => ! $child['active'],
                                        ])>
                                            <div class="font-display text-xs font-bold">{{ $child['label'] }}</div>
                                            @if(!empty($child['sublabel']))
                                                <div class="text-[11px] font-normal text-muted truncate mt-0.5">{{ $child['sublabel'] }}</div>
                                            @endif
                                        </a>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    </li>
                @else
                    <li><a href="{{ $link['href'] }}" @if($link['active']) aria-current="page" @endif @class(['transition hover:text-brand py-2', 'text-brand' => $link['active']])>{{ $link['label'] }}</a></li>
                @endif
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