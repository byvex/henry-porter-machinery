@props(['item'])
<a href="{{ $item['url'] ?? route('news.index') }}" {{ $attributes->class(['group overflow-hidden rounded-card border border-line bg-white transition duration-300 hover:-translate-y-1.5 hover:shadow-lg group']) }}>
          <div class="h-60 overflow-hidden"><x-site.image-placeholder :src="$item['imageUrl'] ?? null" :alt="$item['title']" label="News Image" /></div>
          <div class="p-7">
            <div class="mb-3 font-display text-xs font-bold uppercase tracking-widest text-brand">{{ $item['date'] }}</div>
            <h3 class="mb-2 font-display text-lg font-bold leading-snug">{{ $item['title'] }}</h3>
            <p class="mb-4 text-sm leading-relaxed text-muted">{{ $item['description'] }}</p>
          </div>
        </a>
