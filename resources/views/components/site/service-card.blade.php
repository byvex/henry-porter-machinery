@props(['item'])
<div {{ $attributes->class(['relative overflow-hidden rounded-card border border-line bg-white px-8 py-10 transition duration-300 hover:-translate-y-1.5 hover:shadow-lg']) }}>
          <div class="mb-5 font-display text-xs font-extrabold tracking-widest text-brand">{{ $item['number'] }}</div>
          <h3 class="mb-2 font-display text-lg font-bold leading-snug">{{ $item['title'] }}</h3>
          <p class="mb-4 text-sm leading-relaxed text-muted">{{ $item['description'] }}</p>
        </div>
