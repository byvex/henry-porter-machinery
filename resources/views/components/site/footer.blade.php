<footer class="site-footer bg-footer pt-18 pb-9 text-sm text-stone-300/70">
    <x-site.container>
      <div class="footer-grid mb-14 grid gap-9 lg:gap-14">
        <div>
          <div class="site-logo inline-flex items-center font-display text-lg font-extrabold tracking-tight">
            Henry<span>Porter</span>
          </div>
          <p>
            Premium agricultural machinery dealer based in Hillsborough,
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
          <ul>
            <li><a href="tel:+442892622611">📞 (028) 9262 2611</a></li>
            <li><a class="break-words" href="mailto:info@henryportermachinery.co.uk">📧 info@henryportermachinery.co.uk</a></li>
            <li>📍 46 St James Road, Hillsborough</li>
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
        <div class="flex gap-4"><a href="{{ route('cookie-policy') }}">Cookie Policy</a><a href="{{ route('sitemap') }}">Sitemap</a></div>
      </div>
    </x-site.container>
  </footer>
