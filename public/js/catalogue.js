document.addEventListener('DOMContentLoaded', () => {
 const drawer = document.querySelector('[data-catalogue-filter-drawer]');
 const backdrop = document.querySelector('[data-catalogue-drawer-backdrop]');
 const triggers = document.querySelectorAll('[data-catalogue-filter-trigger]');
 const closeBtn = document.querySelector('[data-catalogue-drawer-close]');

 if (drawer) {
  let lastFocused = null;
  const isDesktop = () => window.matchMedia('(min-width: 1024px)').matches;

  const openDrawer = (sourceTrigger) => {
   if (isDesktop()) return;
   lastFocused = sourceTrigger || document.activeElement;
   drawer.classList.add('is-open');
   if (backdrop) backdrop.classList.add('is-open');
   document.body.classList.add('catalogue-filters-locked');
   triggers.forEach(t => t.setAttribute('aria-expanded', 'true'));
   const focusTarget = drawer.querySelector('[data-catalogue-drawer-close]') || drawer.querySelector('input, select, button');
   if (focusTarget) {
    setTimeout(() => focusTarget.focus(), 50);
   }
  };

  const closeDrawer = () => {
   drawer.classList.remove('is-open');
   if (backdrop) backdrop.classList.remove('is-open');
   document.body.classList.remove('catalogue-filters-locked');
   triggers.forEach(t => t.setAttribute('aria-expanded', 'false'));
   if (lastFocused && typeof lastFocused.focus === 'function') {
    lastFocused.focus();
    lastFocused = null;
   }
  };

  triggers.forEach(trigger => {
   trigger.addEventListener('click', (e) => {
    e.preventDefault();
    openDrawer(trigger);
   });
  });

  if (closeBtn) {
   closeBtn.addEventListener('click', (e) => {
    e.preventDefault();
    closeDrawer();
   });
  }

  if (backdrop) {
   backdrop.addEventListener('click', closeDrawer);
  }

  document.addEventListener('keydown', (e) => {
   if (e.key === 'Escape' && drawer.classList.contains('is-open')) {
    e.preventDefault();
    closeDrawer();
   }
  });

  drawer.addEventListener('keydown', (e) => {
   if (e.key !== 'Tab' || isDesktop() || !drawer.classList.contains('is-open')) return;
   const focusables = Array.from(drawer.querySelectorAll('button:not([disabled]), input:not([disabled]), select:not([disabled]), a[href], [tabindex]:not([tabindex="-1"])')).filter(el => el.offsetParent !== null);
   if (!focusables.length) return;
   const first = focusables[0];
   const last = focusables[focusables.length - 1];
   if (e.shiftKey && document.activeElement === first) {
    e.preventDefault();
    last.focus();
   } else if (!e.shiftKey && document.activeElement === last) {
    e.preventDefault();
    first.focus();
   }
  });

  const desktopMql = window.matchMedia('(min-width: 1024px)');
  desktopMql.addEventListener('change', (e) => {
   if (e.matches && drawer.classList.contains('is-open')) {
    closeDrawer();
   }
  });
 }

 document.querySelectorAll('[data-gallery]').forEach(gallery => {
  const thumbs = [...gallery.querySelectorAll('[data-gallery-thumb]')];
  const main = gallery.querySelector('[data-gallery-main]');
  const open = gallery.querySelector('[data-gallery-open]');
  const dialog = gallery.querySelector('[data-gallery-dialog]');
  const large = gallery.querySelector('[data-gallery-large]');
  if (!main || !dialog || typeof dialog.showModal !== 'function') return;
  let index = 0;
  const show = next => { index = (next + thumbs.length) % thumbs.length; const thumb = thumbs[index]; main.src = large.src = open.href = thumb.href; main.alt = large.alt = thumb.dataset.alt; thumbs.forEach((t, i) => t.setAttribute('aria-current', String(i === index))); };
  thumbs.forEach((thumb, i) => thumb.addEventListener('click', e => { e.preventDefault(); show(i); }));
  open.addEventListener('click', e => { e.preventDefault(); dialog.showModal(); });
  dialog.querySelector('[data-gallery-close]').addEventListener('click', () => dialog.close());
  dialog.querySelector('[data-gallery-prev]').addEventListener('click', () => show(index - 1));
  dialog.querySelector('[data-gallery-next]').addEventListener('click', () => show(index + 1));
  dialog.addEventListener('keydown', e => { if (e.key === 'Escape') { e.preventDefault(); dialog.close(); return; } if (e.key === 'ArrowLeft') { e.preventDefault(); show(index - 1); } if (e.key === 'ArrowRight') { e.preventDefault(); show(index + 1); } });
  dialog.addEventListener('close', () => open.focus());
 });
 document.querySelectorAll('[data-video-play]').forEach(button => button.addEventListener('click', () => {
  const wrapper = button.closest('[data-youtube]');
  const id = wrapper.dataset.youtube;
  if (!/^[a-zA-Z0-9_-]{11}$/.test(id)) return;
  const frame = document.createElement('iframe'); frame.src = 'https://www.youtube-nocookie.com/embed/' + id + '?autoplay=1'; frame.title = 'Machinery demonstration on YouTube'; frame.allow = 'autoplay; encrypted-media; picture-in-picture'; frame.allowFullscreen = true; wrapper.replaceChildren(frame);
 }));
 document.querySelectorAll('[data-local-video-play]').forEach(button => button.addEventListener('click', () => {
  const wrapper = button.closest('[data-local-video]');
  if (!wrapper) return;
  const src = wrapper.dataset.localVideo;
  if (!src) return;
  const video = document.createElement('video');
  video.src = src;
  video.controls = true;
  video.autoplay = true;
  video.playsInline = true;
  video.className = 'w-full h-full rounded-card object-contain bg-black';
  wrapper.classList.remove('p-8', 'flex', 'flex-col', 'justify-between');
  wrapper.replaceChildren(video);
 }));
});
