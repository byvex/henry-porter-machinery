@props(['links' => []])
<!-- Modern Off-Canvas Drawer (Outside Header to avoid sticky/transform/clipping contexts) -->
<div id="mobile-drawer" class="mobile-drawer fixed inset-0 z-50 lg:hidden pointer-events-none" aria-hidden="true" role="dialog" aria-modal="true" aria-label="Mobile Navigation Menu">
    <!-- Backdrop -->
    <div class="mobile-drawer-backdrop fixed inset-0 bg-charcoal/60 backdrop-blur-sm" data-menu-close tabindex="-1"></div>

    <!-- Off-canvas panel -->
    <div class="mobile-drawer-panel fixed top-0 right-0 bottom-0 z-10 flex h-dvh w-full max-w-sm flex-col bg-white shadow-2xl">
        <!-- Drawer Header -->
        <div class="flex h-19 shrink-0 items-center justify-between border-b border-line px-6">
            <a href="{{ route('home') }}" class="inline-flex items-center" data-menu-item aria-label="Henry Porter Machinery home">
                <img src="{{ asset('images/logo.jpeg') }}" alt="Henry Porter Machinery" width="460" height="108" class="h-9 w-auto object-contain">
            </a>
            <button type="button" data-menu-close aria-label="Close navigation menu" class="inline-flex size-10 items-center justify-center rounded-xl border border-line text-charcoal transition hover:border-brand hover:text-brand focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand">
                <x-icon.close class="size-5" />
            </button>
        </div>

        <!-- Drawer Content (Scrollable) -->
        <div class="flex-1 overflow-y-auto px-6 py-5">
            <!-- Navigation Links -->
            <div class="mb-6">
                <p class="mb-2 font-display text-[10px] font-bold tracking-wider text-muted uppercase">Navigation</p>
                <ul class="space-y-1">
                    @foreach ($links as $link)
                        @if (!empty($link['children']))
                            <li class="rounded-xl border border-line/60 bg-canvas/40 overflow-hidden" data-mobile-dropdown>
                                <div class="flex items-center justify-between">
                                    <a href="{{ $link['href'] }}" data-menu-item @if($link['active']) aria-current="page" @endif @class([
                                        'flex-1 px-3.5 py-2.5 font-display text-sm font-semibold transition',
                                        'text-brand' => $link['active'],
                                        'text-charcoal hover:text-brand' => ! $link['active'],
                                    ])>
                                        {{ $link['label'] }}
                                    </a>
                                    <button type="button" data-mobile-dropdown-toggle aria-expanded="{{ $link['active'] ? 'true' : 'false' }}" aria-label="Toggle {{ $link['label'] }} submenu" class="p-2.5 text-muted transition hover:text-brand">
                                        <x-icon.keyboard-arrow-down class="size-4 transition-transform duration-200" />
                                    </button>
                                </div>
                                <div class="mobile-submenu {{ $link['active'] ? '' : 'hidden' }} border-t border-line/60 bg-white px-2 py-1.5">
                                    <ul class="space-y-1">
                                        <li>
                                            <a href="{{ $link['href'] }}" data-menu-item class="block rounded-lg px-3 py-2 text-xs font-bold text-brand hover:bg-canvas">
                                                All New Machinery &rarr;
                                            </a>
                                        </li>
                                        @foreach ($link['children'] as $child)
                                            <li>
                                                <a href="{{ $child['href'] }}" data-menu-item @class([
                                                    'block rounded-lg px-3 py-2 text-xs transition',
                                                    'bg-brand/10 font-bold text-brand' => $child['active'],
                                                    'font-medium text-charcoal hover:bg-canvas hover:text-brand' => ! $child['active'],
                                                ])>
                                                    <span class="block">{{ $child['label'] }}</span>
                                                    @if(!empty($child['sublabel']))
                                                        <span class="block text-[10px] font-normal text-muted truncate">{{ $child['sublabel'] }}</span>
                                                    @endif
                                                </a>
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>
                            </li>
                        @else
                            <li>
                                <a href="{{ $link['href'] }}" data-menu-item @if($link['active']) aria-current="page" @endif @class([
                                    'flex items-center justify-between rounded-xl px-3.5 py-2.5 font-display text-sm font-semibold transition',
                                    'bg-brand/10 text-brand' => $link['active'],
                                    'text-charcoal hover:bg-canvas hover:text-brand' => ! $link['active'],
                                ])>
                                    <span>{{ $link['label'] }}</span>
                                    <x-icon.arrow-forward class="size-4 opacity-50 transition group-hover:translate-x-0.5" />
                                </a>
                            </li>
                        @endif
                    @endforeach
                </ul>
            </div>

            <!-- Primary Action -->
            <div class="mb-6">
                <a href="{{ route('contact') }}" data-menu-item class="flex w-full items-center justify-center gap-2 rounded-xl bg-brand px-4 py-3 text-center font-display text-sm font-bold text-white shadow-sm transition hover:bg-brand-dark">
                    <span>Contact & Enquire</span>
                    <x-icon.arrow-forward class="size-4" />
                </a>
            </div>

            <!-- Direct Contact Actions -->
            <div class="mb-6 rounded-2xl border border-line bg-canvas/60 p-4">
                <p class="mb-3 font-display text-[10px] font-bold tracking-wider text-muted uppercase">Direct Contact</p>
                <div class="space-y-2.5 text-xs">
                    <a href="tel:+442892622611" class="flex items-center gap-3 text-charcoal transition hover:text-brand">
                        <span class="flex size-7 items-center justify-center rounded-lg bg-brand/10 text-brand">
                            <x-icon.call class="size-3.5" />
                        </span>
                        <div>
                            <span class="block text-[10px] text-muted">Office</span>
                            <span class="font-medium">(028) 9262 2611</span>
                        </div>
                    </a>
                    <a href="tel:+447821065578" class="flex items-center gap-3 text-charcoal transition hover:text-brand">
                        <span class="flex size-7 items-center justify-center rounded-lg bg-brand/10 text-brand">
                            <x-icon.call class="size-3.5" />
                        </span>
                        <div>
                            <span class="block text-[10px] text-muted">James</span>
                            <span class="font-medium">07821 065578</span>
                        </div>
                    </a>
                    <a href="tel:+447703011785" class="flex items-center gap-3 text-charcoal transition hover:text-brand">
                        <span class="flex size-7 items-center justify-center rounded-lg bg-brand/10 text-brand">
                            <x-icon.call class="size-3.5" />
                        </span>
                        <div>
                            <span class="block text-[10px] text-muted">Henry</span>
                            <span class="font-medium">07703 011785</span>
                        </div>
                    </a>
                    <a href="mailto:info@henryportermachinery.co.uk" class="flex items-center gap-3 text-charcoal transition hover:text-brand">
                        <span class="flex size-7 items-center justify-center rounded-lg bg-brand/10 text-brand">
                            <x-icon.mail class="size-3.5" />
                        </span>
                        <div class="min-w-0">
                            <span class="block text-[10px] text-muted">Email</span>
                            <span class="truncate font-medium block">info@henryportermachinery.co.uk</span>
                        </div>
                    </a>
                </div>
            </div>

            <!-- Location & Opening Hours -->
            <div class="rounded-2xl border border-line/70 bg-stone-50 p-4 text-xs text-muted">
                <div class="mb-2 flex items-center gap-2 font-semibold text-charcoal">
                    <x-icon.location-on class="size-4 text-brand" />
                    <span>Lisburn, Co. Antrim</span>
                </div>
                <p class="text-[11px] leading-relaxed">75 Cockhill Road, Lisburn, BT27 5RS</p>
                <div class="mt-2.5 flex items-center gap-2 border-t border-line/60 pt-2 text-[11px]">
                    <x-icon.schedule class="size-3.5 text-brand" />
                    <span>Mon – Fri: 8:00 AM – 5:30 PM</span>
                </div>
            </div>
        </div>
    </div>
</div>
