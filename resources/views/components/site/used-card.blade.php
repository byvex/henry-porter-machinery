@props(['item'])
<div {{ $attributes->class(['group overflow-hidden rounded-card border border-line bg-white transition duration-300 hover:-translate-y-1.5 hover:shadow-lg group']) }}>
          <x-site.image-placeholder :src="$item['imageUrl'] ?? null" :alt="$item['title']" label="Used Machine Photo" />
          <div class="p-5">
            <h3 class="mb-2 font-display text-lg font-bold leading-snug">{{ $item['title'] }}</h3>
            <p class="mb-4 text-sm leading-relaxed text-muted">{{ $item['description'] }}</p>
            <a href="#contact" class="mt-3.5 inline-flex items-center gap-2 font-display text-sm font-extrabold text-brand">Enquire for Price →</a>
          </div>
        </div>
