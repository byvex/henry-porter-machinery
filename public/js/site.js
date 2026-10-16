function initializeSite() {
 const site = document.querySelector('.public-site');
 if (!site || site.dataset.initialized) return;
 site.dataset.initialized = 'true';
 const toggle = site.querySelector('[data-menu-toggle]');
 const menu = site.querySelector('#mobile-menu');
 const closeMenu = () => { menu.hidden = true; toggle.setAttribute('aria-expanded', 'false'); };
 toggle?.addEventListener('click', () => { menu.hidden = !menu.hidden; toggle.setAttribute('aria-expanded', String(!menu.hidden)); });
 menu?.addEventListener('click', event => { if (event.target.closest('a')) closeMenu(); });
 site.addEventListener('keydown', event => { if (event.key === 'Escape' && menu && !menu.hidden) { closeMenu(); toggle.focus(); } });
 window.matchMedia('(min-width: 1024px)').addEventListener('change', closeMenu);
 const filters = [...site.querySelectorAll('[data-filter]')];
 const products = [...site.querySelectorAll('[data-product]')];
 const empty = site.querySelector('[data-filter-empty]');
 const filterProducts = filter => {
  let count = 0;
  filters.forEach(button => button.setAttribute('aria-pressed', String(button.dataset.filter === filter)));
  products.forEach(card => {
   const matches = filter === 'all' || card.dataset.category === filter || (filter === 'used' && card.dataset.condition === 'used');
   card.hidden = !matches; if (matches) count++;
  });
  if (empty) empty.hidden = count !== 0;
 };
 filters.forEach(button => button.addEventListener('click', () => filterProducts(button.dataset.filter)));
 site.querySelectorAll('[data-category]:not([data-product])').forEach(link => link.addEventListener('click', () => filterProducts(link.dataset.category)));
 const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)');
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
