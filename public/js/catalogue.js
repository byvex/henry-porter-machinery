document.addEventListener('DOMContentLoaded', () => {
 const filterPanel = document.querySelector('[data-catalogue-filters]');
 if (filterPanel) { const desktop = matchMedia('(min-width: 1024px)'); filterPanel.open = desktop.matches; desktop.addEventListener('change', event => { filterPanel.open = event.matches; }); }
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
});
