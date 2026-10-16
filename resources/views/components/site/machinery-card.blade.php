@props(['item'])
<div data-product data-category="{{ $item['category'] }}" data-condition="{{ strtolower($item['condition']) }}" {{ $attributes->class(['group flex h-full flex-col overflow-hidden rounded-card border border-line bg-white transition duration-300 hover:-translate-y-2 hover:shadow-xl']) }}>
          <div class="relative h-60 overflow-hidden">
            <x-site.image-placeholder :src="$item['imageUrl'] ?? null" :alt="$item['title']" label="Product Image" />
            <span class="absolute left-4 top-4 rounded-full px-3.5 py-1.5 font-display text-xs font-bold uppercase tracking-widest text-white {{ $item['condition'] === 'Used' ? 'bg-ink' : 'bg-brand' }}">{{ $item['condition'] }}</span>
          </div>
          <div class="flex flex-1 flex-col p-6">
            <div class="mb-2 font-display text-xs font-bold uppercase tracking-widest text-brand">{{ $item['brand'] }}</div>
            <h3 class="mb-2 font-display text-lg font-bold leading-snug">{{ $item['title'] }}</h3>
            <p class="mb-4 text-sm leading-relaxed text-muted">{{ $item['description'] }}</p>
            <div class="mt-auto flex flex-wrap items-center justify-between gap-3 border-t border-line pt-4">
              <div class="font-display font-extrabold text-ink">Enquire<small class="mt-0.5 block text-xs font-normal text-muted">Price on request</small></div>
              <a href="#contact" class="inline-flex rounded-full border border-brand px-4 py-2 font-display text-xs font-bold text-brand transition hover:bg-brand hover:text-white">View Details →</a>
            </div>
          </div>
        </div>
