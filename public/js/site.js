function initializeSite() {
 const site = document.querySelector('.public-site');
 if (!site || site.dataset.initialized) return;
 site.dataset.initialized = 'true';

 const toggle = site.querySelector('[data-menu-toggle]');
 const drawer = site.querySelector('#mobile-drawer');
 const closeButtons = site.querySelectorAll('[data-menu-close]');
 const drawerItems = site.querySelectorAll('[data-menu-item]');

 const openMenu = () => {
  if (!drawer || !toggle) return;
  drawer.classList.add('is-active');
  drawer.setAttribute('aria-hidden', 'false');
  toggle.setAttribute('aria-expanded', 'true');
  toggle.classList.add('menu-open');
  document.body.classList.add('mobile-menu-locked');
  const firstFocusable = drawer.querySelector('button, [href], input, select, textarea, [tabindex]:not([tabindex="-1"])');
  firstFocusable?.focus();
 };

 const closeMenu = () => {
  if (!drawer || !toggle) return;
  drawer.classList.remove('is-active');
  drawer.setAttribute('aria-hidden', 'true');
  toggle.setAttribute('aria-expanded', 'false');
  toggle.classList.remove('menu-open');
  document.body.classList.remove('mobile-menu-locked');
 };

 toggle?.addEventListener('click', () => {
  const isOpen = drawer?.classList.contains('is-active');
  if (isOpen) {
   closeMenu();
  } else {
   openMenu();
  }
 });

 closeButtons.forEach(btn => btn.addEventListener('click', () => {
  closeMenu();
  toggle?.focus();
 }));

 drawerItems.forEach(item => item.addEventListener('click', () => {
  closeMenu();
 }));

 // Mobile submenu accordion
 site.querySelectorAll('[data-mobile-dropdown]').forEach(container => {
  const btn = container.querySelector('[data-mobile-dropdown-toggle]');
  const menu = container.querySelector('.mobile-submenu');
  const icon = btn?.querySelector('svg');
  btn?.addEventListener('click', (e) => {
   e.stopPropagation();
   const isExpanded = btn.getAttribute('aria-expanded') === 'true';
   btn.setAttribute('aria-expanded', String(!isExpanded));
   menu?.classList.toggle('hidden', isExpanded);
   icon?.classList.toggle('rotate-180', !isExpanded);
  });
 });

 // Desktop dropdown toggle via keyboard / click
 site.querySelectorAll('[data-nav-dropdown]').forEach(container => {
  const btn = container.querySelector('.nav-dropdown-toggle');
  const menu = container.querySelector('.nav-dropdown-menu');
  btn?.addEventListener('click', (e) => {
   e.stopPropagation();
   const isExpanded = btn.getAttribute('aria-expanded') === 'true';
   btn.setAttribute('aria-expanded', String(!isExpanded));
   menu?.classList.toggle('is-open', !isExpanded);
  });
 });

 document.addEventListener('click', (e) => {
  site.querySelectorAll('[data-nav-dropdown]').forEach(container => {
   const btn = container.querySelector('.nav-dropdown-toggle');
   const menu = container.querySelector('.nav-dropdown-menu');
   if (!container.contains(e.target)) {
    btn?.setAttribute('aria-expanded', 'false');
    menu?.classList.remove('is-open');
   }
  });
 });

 site.addEventListener('keydown', event => {
  if (!drawer?.classList.contains('is-active')) return;
  if (event.key === 'Escape') {
   closeMenu();
   toggle?.focus();
   return;
  }
  if (event.key === 'Tab') {
   const focusables = Array.from(drawer.querySelectorAll('button:not([disabled]), [href], input:not([disabled]), select:not([disabled]), textarea:not([disabled]), [tabindex]:not([tabindex="-1"])'));
   if (focusables.length === 0) return;
   const first = focusables[0];
   const last = focusables[focusables.length - 1];
   if (event.shiftKey && document.activeElement === first) {
    last.focus();
    event.preventDefault();
   } else if (!event.shiftKey && document.activeElement === last) {
    first.focus();
    event.preventDefault();
   }
  }
 });

 window.matchMedia('(min-width: 1024px)').addEventListener('change', event => {
  if (event.matches) {
   closeMenu();
  }
 });

 // Cookie consent banner
 const cookieBanner = site.querySelector('[data-cookie-banner]');
 const cookieTriggers = site.querySelectorAll('[data-cookie-settings-trigger]');

 if (cookieBanner) {
  const getConsentCookie = () => {
   const match = document.cookie.match(/(?:^|;\s*)hpm_cookie_consent=([^;]*)/);
   return match ? decodeURIComponent(match[1]) : null;
  };

  const setConsentCookie = (val) => {
   const maxAge = 365 * 24 * 60 * 60;
   const secure = window.location.protocol === 'https:' ? '; Secure' : '';
   document.cookie = `hpm_cookie_consent=${encodeURIComponent(val)}; max-age=${maxAge}; path=/; SameSite=Lax${secure}`;
  };

  const hideBanner = () => {
   cookieBanner.classList.add('is-hidden');
   setTimeout(() => {
    if (cookieBanner.classList.contains('is-hidden')) {
     cookieBanner.style.display = 'none';
    }
   }, 300);
  };

  const showBanner = () => {
   cookieBanner.style.display = '';
   void cookieBanner.offsetWidth;
   cookieBanner.classList.remove('is-hidden');
   const primaryBtn = cookieBanner.querySelector('[data-cookie-consent="accept"]') || cookieBanner.querySelector('button');
   primaryBtn?.focus();
  };

  const currentConsent = getConsentCookie();
  if (currentConsent) {
   cookieBanner.classList.add('is-hidden');
   cookieBanner.style.display = 'none';
  } else {
   cookieBanner.style.display = '';
   requestAnimationFrame(() => {
    cookieBanner.classList.remove('is-hidden');
   });
  }

  cookieBanner.querySelector('[data-cookie-consent="accept"]')?.addEventListener('click', () => {
   setConsentCookie('accepted');
   hideBanner();
  });

  cookieBanner.querySelector('[data-cookie-consent="reject"]')?.addEventListener('click', () => {
   setConsentCookie('rejected');
   hideBanner();
  });

  cookieTriggers.forEach(btn => {
   btn.addEventListener('click', (e) => {
    e.preventDefault();
    showBanner();
   });
  });
 }

 const featured = site.querySelector('[data-home-featured]');
 const filters = [...(featured?.querySelectorAll('[data-filter]') || [])];
 const products = [...(featured?.querySelectorAll('[data-product]') || [])];
 const empty = featured?.querySelector('[data-filter-empty]');
 const filterProducts = filter => {
  let count = 0;
  filters.forEach(button => button.setAttribute('aria-pressed', String(button.dataset.filter === filter)));
  products.forEach(card => {
   const matches = filter === 'all' || (card.dataset.category || '').split(' ').includes(filter) || (filter === 'used' && card.dataset.condition === 'used');
   card.hidden = !matches; if (matches) count++;
  });
  if (empty) empty.hidden = count !== 0;
 };
 filters.forEach(button => button.addEventListener('click', () => filterProducts(button.dataset.filter)));

 const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)');

 // Homepage Hero Ambient Background Slideshow
 const heroSlideshow = site.querySelector('[data-hero-slideshow]');
 if (heroSlideshow && !heroSlideshow.dataset.slideshowInitialized) {
  heroSlideshow.dataset.slideshowInitialized = 'true';
  const slides = Array.from(heroSlideshow.querySelectorAll('[data-hero-slide]'));
  if (slides.length > 1 && !reducedMotion.matches) {
   let currentIndex = slides.findIndex(s => s.classList.contains('is-active'));
   if (currentIndex < 0) currentIndex = 0;

   const preloadImage = index => {
    const nextImg = slides[index]?.querySelector('img');
    if (nextImg && nextImg.loading === 'lazy') {
     nextImg.loading = 'eager';
    }
   };
   preloadImage((currentIndex + 1) % slides.length);

   let timer = null;
   const transitionDuration = 1200; // 1.2s cross-fade
   const slideInterval = 6000; // 6s display interval

   const nextSlide = () => {
    const prevSlide = slides[currentIndex];
    currentIndex = (currentIndex + 1) % slides.length;
    const activeSlide = slides[currentIndex];

    preloadImage((currentIndex + 1) % slides.length);

    prevSlide.classList.remove('is-active');
    prevSlide.classList.add('is-previous');

    activeSlide.classList.add('is-active');

    setTimeout(() => {
     prevSlide.classList.remove('is-previous');
    }, transitionDuration);
   };

   const startTimer = () => {
    if (!timer) timer = setInterval(nextSlide, slideInterval);
   };

   const stopTimer = () => {
    if (timer) {
     clearInterval(timer);
     timer = null;
    }
   };

   startTimer();

   document.addEventListener('visibilitychange', () => {
    if (document.hidden) {
     stopTimer();
    } else {
     startTimer();
    }
   });

   reducedMotion.addEventListener('change', e => {
    if (e.matches) {
     stopTimer();
     slides.forEach((s, i) => {
      s.classList.toggle('is-active', i === 0);
      s.classList.remove('is-previous');
     });
    } else {
     startTimer();
    }
   });
  }
 }

 // Homepage Bottom Timed Tractor Animation (Window Pass)
 const tracker = site.querySelector('[data-scroll-tracker]');
 if (tracker && !tracker.dataset.started) {
  tracker.dataset.started = 'true';
  if (reducedMotion.matches) {
   tracker.classList.add('is-finished');
  } else {
   const vehicle = tracker.querySelector('.scroll-tracker-vehicle');

   setTimeout(() => {
    const vehicleWidth = vehicle?.offsetWidth || 52;
    const beamLength = 80;
    const startX = -vehicleWidth - beamLength - 15; // ~ -147px
    const endX = window.innerWidth + 20;
    const totalDistance = endX - startX;
    const cruiseDuration = 15000; // 15s steady realistic tractor cruise
    let startTime = null;

    tracker.style.setProperty('--scroll-x', `${startX}px`);

    // Stage 1: Reveal container and slide the road up from below the viewport
    tracker.classList.add('is-active', 'road-up');

    // Stage 2: Once the road has slid up (600ms), start the tractor cruise from off-screen left
    setTimeout(() => {
     const animate = timestamp => {
      if (!startTime) startTime = timestamp;
      const elapsed = timestamp - startTime;
      const progress = Math.min(elapsed / cruiseDuration, 1);

      const x = startX + progress * totalDistance;
      const distanceTraveled = progress * totalDistance;

      // Wheel rotation strictly bound to ground travel distance
      const rearDeg = (distanceTraveled / 69.1) * 360;
      const frontDeg = (distanceTraveled / 47.1) * 360;

      tracker.style.setProperty('--scroll-x', `${x.toFixed(2)}px`);
      tracker.style.setProperty('--rear-deg', `${rearDeg.toFixed(1)}deg`);
      tracker.style.setProperty('--front-deg', `${frontDeg.toFixed(1)}deg`);

      if (progress < 1) {
       requestAnimationFrame(animate);
      } else {
       // Stage 3: Tractor has exited off-screen right; slide road down
       tracker.classList.remove('road-up');
       tracker.classList.add('road-down');

       // Stage 4: Unmount once road slide-down finishes
       setTimeout(() => {
        tracker.classList.add('is-finished');
        tracker.classList.remove('is-active', 'road-down');
       }, 650);
      }
     };

     requestAnimationFrame(animate);
    }, 650);
   }, 5000);
  }
 }

 if (!('IntersectionObserver' in window) || reducedMotion.matches) return;
 const reveal = new IntersectionObserver(entries => entries.forEach(entry => {
  if (entry.isIntersecting) { entry.target.classList.add('is-visible'); reveal.unobserve(entry.target); }
 }), { threshold: 0.1 });
 site.querySelectorAll('.reveal').forEach(section => reveal.observe(section));
 const counters = new IntersectionObserver(entries => entries.forEach(entry => {
  if (!entry.isIntersecting) return;
  const el = entry.target, target = Number(el.dataset.count), suffix = el.dataset.suffix || '', start = performance.now();
  const update = now => {
   const progress = Math.min((now - start) / 1800, 1);
   el.textContent = Math.floor((1 - Math.pow(1 - progress, 4)) * target) + suffix;
   if (progress < 1 && !reducedMotion.matches) requestAnimationFrame(update); else el.textContent = target + suffix;
  };
  requestAnimationFrame(update); counters.unobserve(el);
 }), { threshold: 0.5 });
 site.querySelectorAll('[data-count]').forEach(counter => counters.observe(counter));
}
initializeSite();
document.addEventListener('livewire:navigated', initializeSite);
