<x-layouts.site title="About Us | Henry Porter Machinery" description="Meet Henry and James Porter and discover the story of our family machinery business in Lisburn, established in 2006.">
    <section class="about-hero relative overflow-hidden">
        <x-site.container class="relative">
            <nav aria-label="Breadcrumb" class="mb-10 text-xs text-muted sm:mb-14">
                <ol class="flex items-center gap-3"><li><a class="transition hover:text-brand" href="{{ route('home') }}">Home</a></li><li aria-hidden="true">/</li><li aria-current="page" class="text-ink">About us</li></ol>
            </nav>
            <div class="grid items-center gap-12 lg:grid-cols-2 lg:gap-18">
                <div class="about-introduction">
                    <span class="section-eyebrow">Our story. Your next chapter.</span>
                    <h1 class="about-page-title mb-7 font-extrabold">Built on experience.<br>Grown through <span class="text-brand">trust.</span></h1>
                    <p class="mb-8 max-w-lg text-base leading-relaxed text-muted sm:text-lg">A family machinery business based in Lisburn, County Antrim. Henry and James Porter bring a personal approach to finding your next machine.</p>
                    <div class="flex flex-wrap gap-3">
                        <x-site.button href="#our-story" iconTrailing="arrow_downward">Discover our story</x-site.button>
                        <x-site.button href="#contact" variant="outline" iconTrailing="arrow_outward">Talk to our team</x-site.button>
                    </div>
                    <div class="mt-10 flex items-center gap-4 border-t border-line pt-7">
                        <span class="font-display text-4xl font-extrabold text-brand">2006</span>
                        <p class="text-xs leading-relaxed text-muted">Our first chapter.<br><span class="font-semibold text-ink">The same personal approach today.</span></p>
                    </div>
                </div>
                <figure class="about-portrait relative mx-auto w-full max-w-lg">
                    <div class="about-photo-frame overflow-hidden rounded-3xl bg-stone-200">
                        <img class="size-full object-cover" src="{{ asset('images/about/henry-and-james.jpeg') }}" alt="Henry and James Porter alongside Pöttinger machinery at their premises" width="600" height="883" fetchpriority="high">
                    </div>
                    <figcaption class="about-photo-caption absolute rounded-2xl border border-white/60 bg-white/95 px-6 py-5 shadow-xl backdrop-blur-md">
                        <span class="mb-1 block font-display text-sm font-bold">Two generations. One commitment.</span>
                        <span class="text-xs text-muted">Henry &amp; James Porter · Lisburn</span>
                    </figcaption>
                    <div class="about-photo-index absolute right-5 top-5 rounded-full bg-ink/80 px-4 py-2 font-display text-xs font-semibold text-white backdrop-blur-md">Family run. Farmer focused.</div>
                </figure>
            </div>
        </x-site.container>
    </section>

    <div class="border-y border-line bg-white">
        <x-site.container class="grid gap-5 py-7 text-center font-display text-xs font-semibold sm:grid-cols-3 sm:gap-8">
            <p><span class="mr-2 text-brand" aria-hidden="true">●</span> Established in 2006</p>
            <p><span class="mr-2 text-brand" aria-hidden="true">●</span> A family business</p>
            <p><span class="mr-2 text-brand" aria-hidden="true">●</span> Lisburn, County Antrim</p>
        </x-site.container>
    </div>

    <section id="our-story" class="section-space bg-white">
        <x-site.container class="grid gap-8 lg:grid-cols-2 lg:gap-18">
            <div class="reveal"><span class="section-eyebrow">More than machinery</span><h2 class="section-title max-w-lg">It starts with people.<br>It grows with you.</h2></div>
            <div class="reveal space-y-5 text-base leading-relaxed text-muted">
                <p>Our story started with used tractors. As the business grew, we expanded into new farm machinery, from feeding equipment to compact loaders.</p>
                <p>Today, our focus is still personal: understanding the work you do and helping you explore the machinery that suits it.</p>
                <p class="border-l-2 border-brand pl-5 font-display text-lg font-semibold leading-relaxed text-ink">To our customers, thank you for being part of the journey. To those we have yet to meet, we look forward to the conversation.</p>
            </div>
        </x-site.container>
    </section>

    <section class="about-history section-space relative overflow-hidden bg-ink text-white" aria-labelledby="history-heading">
        <x-site.container class="relative grid items-start gap-10 lg:grid-cols-2 lg:gap-18">
            <div class="reveal lg:sticky lg:top-28">
                <span class="section-eyebrow text-brand-light">The journey so far</span>
                <h2 id="history-heading" class="section-title mb-6">Small beginnings.<br>A growing story.</h2>
                <p class="max-w-sm text-sm leading-relaxed text-white/60">The names and the machinery have evolved. The people behind the business remain at the heart of it.</p>
                <a href="#meet-the-team" class="mt-8 inline-flex items-center gap-3 border-b border-white/30 pb-2 font-display text-sm font-semibold transition hover:border-brand-light hover:text-brand-light">Meet Henry &amp; James <x-icon.arrow-outward class="size-4 icon-nudge icon-nudge--outward" /></a>
                <div class="about-history-word mt-12 hidden font-display font-extrabold text-white/5 lg:block" aria-hidden="true">SINCE<br>2006.</div>
            </div>
            <div class="history-list relative pl-7 sm:pl-10">
                @foreach ($milestones as $item)
                    <x-site.timeline-item :item="$item" />
                @endforeach
            </div>
        </x-site.container>
    </section>

    <section class="section-space">
        <x-site.container>
            <x-site.heading eyebrow="The way we work" title="Good machinery. Straightforward advice." description="A practical approach, with your work at the centre of the conversation." :centered="true" class="reveal" />
            <div class="grid gap-5 lg:grid-cols-3">
                @foreach ($values as $item)
                    <x-site.value-card :item="$item" class="reveal" />
                @endforeach
            </div>
        </x-site.container>
    </section>

    <section id="meet-the-team" class="section-bottom">
        <x-site.container>
            <div class="about-team-panel grid overflow-hidden rounded-3xl border border-line bg-white lg:grid-cols-2">
                <div class="reveal p-8 sm:p-12 lg:p-14">
                    <span class="section-eyebrow">People you can talk to</span>
                    <h2 class="section-title mb-6">A familiar face.<br>A direct conversation.</h2>
                    <p class="max-w-sm text-sm leading-relaxed text-muted">Speak to Henry or James about your next machine. A phone call is a good place to start.</p>
                    <a class="mt-8 inline-flex items-center gap-3 font-display text-sm font-bold text-brand transition hover:gap-5" href="mailto:info@henryportermachinery.co.uk">Email our team <x-icon.arrow-outward class="size-4 icon-nudge icon-nudge--outward" /></a>
                </div>
                <div class="divide-y divide-line border-t border-line lg:border-l lg:border-t-0">
                    @foreach ($people as $person)
                        <article class="reveal p-8 sm:p-10">
                            <div class="mb-4 flex items-center gap-4">
                                <span class="flex size-13 shrink-0 items-center justify-center rounded-full bg-brand/8 font-display text-sm font-extrabold text-brand" aria-hidden="true">{{ $person['initials'] }}</span>
                                <div><h3 class="font-display text-xl font-bold">{{ $person['name'] }}</h3><p class="text-xs text-muted">{{ $person['role'] }}</p></div>
                            </div>
                            <p class="mb-5 text-sm leading-relaxed text-muted">{{ $person['description'] }}</p>
                            <a class="inline-flex items-center gap-3 font-display text-sm font-bold transition hover:text-brand" href="tel:{{ $person['telephone'] }}"><x-icon.arrow-outward class="size-4 text-brand" /> {{ $person['phone'] }}<span class="sr-only"> — call {{ $person['name'] }}</span></a>
                        </article>
                    @endforeach
                </div>
            </div>
        </x-site.container>
    </section>

    <section id="contact" class="about-visit section-space border-t border-line bg-white">
        <x-site.container class="grid gap-10 lg:grid-cols-2 lg:gap-18">
            <div class="reveal">
                <span class="section-eyebrow">Come and say hello</span>
                <h2 class="section-title mb-6">Find us in<br><span class="text-brand">Lisburn.</span></h2>
                <p class="mb-8 max-w-sm text-sm leading-relaxed text-muted">Have a machine in mind, or just exploring your options? Call ahead and let’s arrange a conversation.</p>
                <x-site.button href="tel:+442892622611" iconTrailing="arrow_outward">Call (028) 9262 2611</x-site.button>
            </div>
            <div class="reveal grid gap-8 rounded-3xl border border-line bg-canvas p-8 sm:grid-cols-2 sm:p-10">
                <div><p class="mb-4 font-display text-xs font-bold uppercase tracking-widest text-brand">Our address</p><address class="mb-5 text-sm not-italic leading-loose">75 Cockhill Road<br>Lisburn, Co. Antrim<br>BT27 5RS</address><a class="inline-flex gap-2 font-display text-xs font-bold text-brand underline-offset-4 hover:underline" href="https://www.google.com/maps/search/?api=1&amp;query=75+Cockhill+Road+Lisburn+BT27+5RS" target="_blank" rel="noopener noreferrer">Get directions <x-icon.arrow-outward class="size-4 icon-nudge icon-nudge--outward" /></a></div>
                <div><p class="mb-4 font-display text-xs font-bold uppercase tracking-widest text-brand">Opening hours</p><dl class="space-y-3 text-sm"><div><dt class="font-semibold">Monday – Friday</dt><dd class="text-muted">8:30am – 5:30pm</dd></div><div><dt class="font-semibold">Saturday</dt><dd class="text-muted">8:30am – 12:30pm</dd></div><div><dt class="font-semibold">Sunday</dt><dd class="text-muted">Closed</dd></div></dl></div>
            </div>
        </x-site.container>
    </section>

    <section class="dark-banner relative overflow-hidden bg-ink py-24 text-center text-stone-200">
        <x-site.container class="reveal relative">
            <span class="section-eyebrow">Your next chapter</span>
            <h2>Let’s find your<br>next machine.</h2>
            <p>Explore the range, then talk through the options with our team.</p>
            <div class="relative flex flex-col items-center justify-center gap-4 sm:flex-row">
                <x-site.button :href="route('home').'#products'" iconTrailing="arrow_forward">Explore machinery</x-site.button>
                <x-site.button href="mailto:info@henryportermachinery.co.uk" variant="ghost" iconTrailing="arrow_outward">Get in touch</x-site.button>
            </div>
        </x-site.container>
    </section>
</x-layouts.site>
