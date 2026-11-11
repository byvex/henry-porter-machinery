<footer class="site-footer bg-footer pt-18 pb-9 text-sm text-stone-300/70">
    <x-site.container>
      <div class="footer-grid mb-14 grid gap-9 lg:gap-14">
        <div>
          <a href="{{ route('home') }}" class="mb-4 inline-block rounded-xl bg-white p-2.5 shadow-sm transition hover:opacity-95" aria-label="Henry Porter Machinery">
            <img src="{{ asset('images/logo.jpeg') }}" alt="Henry Porter Machinery" width="460" height="108" class="h-8 w-auto object-contain">
          </a>
          <p>
            Premium agricultural machinery dealer based in Lisburn,
            Northern Ireland. Serving farmers and contractors across the province.
          </p>
        </div>
        <div>
          <h4>Machinery</h4>
          <ul>
            <li><a href="{{ route('machinery.new') }}">New Machinery</a></li>
            <li><a href="{{ route('machinery.used') }}">Used Machinery</a></li>
            <li><a href="{{ route('brands.index') }}">Brands</a></li>
            <li><a href="{{ route('parts') }}">Parts</a></li>
            <li><a href="{{ route('service') }}">Service</a></li>
          </ul>
        </div>
        <div>
          <h4>Company</h4>
          <ul>
            <li><a href="{{ route('about') }}">About Us</a></li>
            <li><a href="{{ route('news.index') }}">News & Events</a></li>
            <li><a href="{{ route('contact') }}">Contact</a></li>
            <li><span>Careers</span></li>
          </ul>
        </div>
        <div>
          <h4>Contact</h4>
          <ul class="space-y-2">
            <li><a class="inline-flex items-start gap-2 transition hover:text-white" href="tel:+442892622611"><x-icon.call class="mt-0.5 size-4 text-brand-light" />Office: (028) 9262 2611</a></li>
            <li><a class="inline-flex items-start gap-2 transition hover:text-white" href="tel:+447821065578"><x-icon.call class="mt-0.5 size-4 text-brand-light" />James: 07821 065578</a></li>
            <li><a class="inline-flex items-start gap-2 transition hover:text-white" href="tel:+447703011785"><x-icon.call class="mt-0.5 size-4 text-brand-light" />Henry: 07703 011785</a></li>
            <li><a class="inline-flex min-w-0 items-start gap-2 transition hover:text-white" href="mailto:info@henryportermachinery.co.uk"><x-icon.mail class="mt-0.5 size-4 text-brand-light" /><span class="min-w-0 break-words">info@henryportermachinery.co.uk</span></a></li>
            <li class="flex items-start gap-2"><x-icon.location-on class="mt-0.5 size-4 text-brand-light" />75 Cockhill Road, Lisburn, BT27 5RS</li>
          </ul>
        </div>
      </div>
      <div class="flex flex-wrap items-center justify-between gap-3 border-t border-white/10 pt-7 text-xs">
        <span>© {{ date('Y') }} Henry Porter Machinery. All rights reserved.</span>
        <div class="footer-social flex gap-2.5">
          <span aria-label="Facebook">f</span>
          <span aria-label="Instagram">◐</span>
          <span aria-label="YouTube">▶</span>
        </div>
        <div class="flex flex-wrap items-center gap-x-4 gap-y-1">
          <a href="{{ route('cookie-policy') }}">Cookie Policy</a>
          <button type="button" class="cursor-pointer text-stone-300/70 transition hover:text-white" data-cookie-settings-trigger>Cookie Settings</button>
          <a href="{{ route('sitemap') }}">Sitemap</a>
        </div>
      </div>
    </x-site.container>
  </footer>
