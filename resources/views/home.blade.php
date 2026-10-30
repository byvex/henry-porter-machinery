<x-layouts.site>

  <section class="hero">
    
    <div class="hero-bg" aria-hidden="true"></div>
    
    <div class="hero-overlay" aria-hidden="true"></div>

    <x-site.container>
      <div class="relative z-10 max-w-3xl">
        <span class="section-eyebrow">Henry Porter Machinery</span>
        <h1 class="hero-title">
          Premium Farm<br>
          Machinery for<br>
          <em>Northern Ireland</em>
        </h1>
        <p class="hero-description mb-9 max-w-xl text-base leading-relaxed text-white/70 sm:text-lg">
          Trusted dealer of Pottinger, Hustler, Strautmann, MultiOne and more.
          Quality machinery, expert service, and genuine parts — all from our Lisburn base.
        </p>
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center">
          <x-site.button :href="route('machinery.index')" variant="primary" iconTrailing="arrow_forward">Browse Machinery</x-site.button>
          <x-site.button :href="route('brands.show','pottinger')" variant="ghost" icon="workspace_premium" iconFilled>Official Pottinger Dealer</x-site.button>
        </div>
        <div class="mt-12 flex items-center gap-3 font-display text-xs font-semibold uppercase tracking-widest text-white/50">
          <span class="h-px w-10 bg-current"></span>
          <span>Scroll to explore</span>
          <x-icon.arrow-downward class="arrow size-4" />
        </div>
      </div>

      <div class="hero-stat-badge rounded-2xl border border-white/20 bg-white/10 px-7 py-6 font-display backdrop-blur-xl">
        <div class="num">2006</div>
        <div class="label">Established</div>
      </div>
    </x-site.container>
  </section>

  <div class="overflow-hidden border-y border-white/10 bg-brand py-5 text-white">
    <div class="marquee-track">
      <div class="flex items-center gap-15">
        @foreach ($brands as $brand)<a href="{{ $brand['url'] }}">{{ $brand['name'] }}</a><span class="size-1.5 shrink-0 rounded-full bg-white/60" aria-hidden="true"></span>@endforeach
      </div>
      <div class="flex items-center gap-15" aria-hidden="true" inert>
        @foreach ($brands as $brand)<a href="{{ $brand['url'] }}">{{ $brand['name'] }}</a><span class="size-1.5 shrink-0 rounded-full bg-white/60" aria-hidden="true"></span>@endforeach
      </div>
    </div>
  </div>

  <section class="relative z-10 border-b border-line bg-white py-9">
    <x-site.container class="flex flex-col flex-wrap gap-6 sm:flex-row sm:items-center sm:justify-between">
      @foreach ($trustItems as $item)
<x-site.trust-item :item="$item" />
@endforeach

    </x-site.container>
  </section>

  <section class="section-space relative overflow-hidden bg-white reveal" id="categories">
    <x-site.container>
      <x-site.heading eyebrow="Product Categories" title="Browse By Category" description="Browse our full range of premium machinery by category — from grassland equipment to compact loaders." :centered="true" />
      <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
        @foreach ($categories as $item)
<x-site.category-card :item="$item" />
@endforeach

      </div>
      
    </x-site.container>
  </section>

  <section class="section-space bg-canvas reveal" id="products" data-home-featured>
    <x-site.container>
      <x-site.heading eyebrow="Featured Machinery" title="Featured Machinery" description="Browse our newest and most popular machinery — filter by category to find exactly what you need." :centered="true" />
      <div class="mb-10 flex flex-wrap gap-2.5" role="group" aria-label="Filter featured machinery">
        <button type="button" data-filter="all" aria-pressed="true" class="filter-button rounded-full border border-line px-5 py-2.5 font-display text-xs font-semibold text-muted transition hover:border-brand hover:text-brand">All Products</button>
        @foreach($productFilters as $filter)<button type="button" data-filter="{{ $filter['slug'] }}" aria-pressed="false" class="filter-button rounded-full border border-line px-5 py-2.5 font-display text-xs font-semibold text-muted transition hover:border-brand hover:text-brand">{{ $filter['name'] }}</button>@endforeach
        <button type="button" data-filter="used" aria-pressed="false" class="filter-button rounded-full border border-line px-5 py-2.5 font-display text-xs font-semibold text-muted">Used</button>
      </div>
      <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
        @foreach ($products as $item)
<x-site.machinery-card :item="$item" />
@endforeach

      </div>
      
    </x-site.container>
  @if(!$products)<p class="site-container pb-8 text-center text-muted">Our machinery listings are being updated. <a href="{{ route('contact') }}" class="text-brand underline">Contact us about your requirements.</a></p>@endif
  <p hidden data-filter-empty role="status" class="site-container pb-12 text-center text-muted">No featured machinery matches this category. <a class="text-brand underline" href="#contact">Contact us about availability.</a></p><x-site.container class="mt-8 text-center"><a href="{{ route('machinery.index') }}" class="font-semibold text-brand">View all machinery &rarr;</a></x-site.container>
  </section>


  <section class="stats-section relative overflow-hidden border-t-4 border-brand bg-ink py-22 text-stone-200 reveal">
    <x-site.container>
      <div class="relative z-10 grid grid-cols-1 gap-10 sm:grid-cols-2 lg:grid-cols-4">
        @foreach ($statistics as $statistic)
        <div class="p-5 text-center">
          <div class="stat-big font-display font-extrabold text-white" data-count="{{ $statistic['value'] }}" data-suffix="{{ $statistic['suffix'] }}">{{ $statistic['value'] }}{{ $statistic['suffix'] }}</div>
          <div class="mt-3 font-display text-xs uppercase tracking-widest text-white/60">{{ $statistic['label'] }}</div>
        </div>
        @endforeach

      </div>
    </x-site.container>
  </section>

  <section class="overflow-hidden border-b border-line bg-white py-18 reveal" id="brands">
    <x-site.container>
      <h2>Brands We Supply</h2>
      <div class="flex flex-wrap items-center justify-center gap-5">
        @foreach ($brands as $brand)
        <a href="{{ $brand['url'] }}" class="rounded-xl border border-line bg-white px-6 py-3.5 font-display font-extrabold text-muted transition hover:border-brand hover:text-brand">@if($brand['logo'])<img class="h-12 max-w-40 object-contain" src="{{ $brand['logo'] }}" alt="{{ $brand['name'] }}">@else{{ $brand['name'] }}@endif</a>
        @endforeach

      </div>
    </x-site.container>
  </section>

  <section class="section-space relative overflow-hidden reveal" id="about">
    <div class="blob pointer-events-none absolute rounded-full blob-warm blob-2"></div>
    <x-site.container class="relative z-10 grid items-center gap-12 lg:grid-cols-2 lg:gap-18">
      <div class="about-visual relative rounded-card group">
        <x-site.image-placeholder label="Team / Workshop Photo" />
        <div class="about-badge absolute -bottom-5 right-0 rounded-card bg-brand px-7 py-5 font-display text-white shadow-xl lg:-right-5">
          <div class="num">2006</div>
          <div class="lbl">Established</div>
        </div>
      </div>
      <div class="relative">
        <span class="section-eyebrow">About Henry Porter</span>
        <h2>Rooted in Farming,<br>Driven by Service</h2>
        <p>
          Based in Lisburn, County Antrim, Henry Porter Machinery has grown from a
          used tractor dealership into one of Northern Ireland's most trusted suppliers
          of premium agricultural machinery.
        </p>
        <p>
          We work directly with farmers and contractors across the province, providing
          not just machinery but the expert advice, parts, and service that keeps your
          operation running.
        </p>
        <div class="mt-6 grid gap-3 sm:grid-cols-2">
          @foreach ($features as $feature)
          <div class="about-feature flex items-center gap-2.5 font-display text-xs font-semibold">{{ $feature }}</div>
          @endforeach

        </div>
      </div>
    </x-site.container>
  </section>

  <section class="section-bottom relative overflow-hidden reveal">
    <x-site.container>
      <x-site.heading eyebrow="How We Work" title="From Enquiry to Delivery" description="A straightforward process built around your farm's needs." :centered="true" />
      <div class="process-grid relative grid gap-10 sm:grid-cols-2 lg:grid-cols-4 lg:gap-5">
        @foreach ($steps as $item)
<x-site.process-step :item="$item" />
@endforeach

      </div>
    </x-site.container>
  </section>

  <section class="section-bottom reveal" id="services">
    <x-site.container>
      <x-site.heading eyebrow="What We Offer" title="More Than Just Machinery" description="We support you before, during, and after the sale."  />
      <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
        @foreach ($services as $item)
<x-site.service-card :item="$item" />
@endforeach

      </div>
    </x-site.container>
  </section>

  @if($usedMachines)
  <section class="section-bottom reveal" id="used">
    <x-site.container>
      <x-site.heading eyebrow="New in Used" title="Quality Pre-Owned Machinery"  />
      <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
        @foreach ($usedMachines as $item)
<x-site.used-card :item="$item" />
@endforeach

      </div>
    </x-site.container>
  </section>
  @endif

  <section class="section-bottom relative overflow-hidden reveal">
    <div class="blob pointer-events-none absolute rounded-full blob-red blob-1"></div>
    <x-site.container>
      <x-site.heading eyebrow="What Farmers Say" title="Trusted by Farmers Across Ulster" description="Feedback shared with our machinery range." :centered="true" />
      <div class="relative grid gap-5 lg:grid-cols-3">
        @foreach ($testimonials as $item)
<x-site.testimonial-card :item="$item" />
@endforeach

      </div>
    </x-site.container>
  </section>

  <section class="section-bottom reveal" id="news">
    <x-site.container>
      <x-site.heading eyebrow="Latest News" title="From the Yard"  />
      <div class="grid gap-5 sm:grid-cols-2">
        @foreach ($news as $item)
<x-site.news-card :item="$item" />
@endforeach
        
      </div>
    </x-site.container>
  </section>

  <section class="section-bottom reveal">
    <x-site.container class="faq-grid grid items-start gap-10 lg:gap-18">
      <div class="faq-header">
        <span class="section-eyebrow">Common Questions</span>
        <h2>Frequently Asked</h2>
        <p>Can't find what you're looking for? Our team is always happy to help.</p>
        <a href="#contact" class="inline-flex items-center gap-2 rounded-full border border-brand px-6 py-3.5 font-display text-sm font-bold text-brand transition hover:bg-brand hover:text-white">Get in Touch <x-icon.arrow-forward class="size-4 icon-nudge" /></a>
      </div>
      <div class="flex flex-col gap-3">
        @foreach ($faqs as $item)
<x-site.faq-item :item="$item" :open="$loop->first" />
@endforeach

      </div>
    </x-site.container>
  </section>

  <section class="section-bottom reveal">
    <x-site.container>
      <div class="newsletter-inner relative grid items-center gap-10 overflow-hidden rounded-3xl px-8 py-12 text-white lg:grid-cols-2 lg:px-14 lg:py-16">
        <div class="relative z-10">
          <h2>Stay in the Loop</h2>
          <p>New stock alerts, seasonal offers, and machinery news — straight to your inbox. No spam, just the good stuff.</p>
        </div>
        <div class="relative z-10 min-w-0"><form class="newsletter-form relative z-10 flex flex-col gap-2.5 rounded-2xl border border-white/20 bg-white/15 p-2 backdrop-blur-md sm:flex-row sm:rounded-full" aria-describedby="newsletter-status"><label class="sr-only" for="newsletter-email">Your email address</label><input id="newsletter-email" type="email" placeholder="Your email address" disabled><button type="submit" disabled>Subscribe <x-icon.arrow-forward class="size-4" /></button></form><p id="newsletter-status" class="mt-3 text-sm text-white/80">Newsletter signup is currently unavailable. Please contact us for updates.</p></div>
      </div>
    </x-site.container>
  </section>

  <section class="dark-banner relative overflow-hidden bg-ink py-30 text-center text-stone-200 reveal" id="contact">
    <x-site.container>
      <span class="section-eyebrow">Get Started</span>
      <h2>Ready to Upgrade<br>Your Machinery?</h2>
      <p>
        Get in touch with our team for expert advice, a demonstration, or a quote.
        We're here to help you find the right machine for the job.
      </p>
      <div class="relative flex flex-col items-center justify-center gap-4 sm:flex-row">
        <x-site.button href="mailto:info@henryportermachinery.co.uk" variant="primary" iconTrailing="arrow_forward">Get a Quote</x-site.button>
        <x-site.button href="tel:+442892622611" variant="ghost">Call (028) 9262 2611</x-site.button>
      </div>
    </x-site.container>
  </section>

  <x-site.scroll-tracker />
</x-layouts.site>
