@props(['item'])
<div {{ $attributes->class(['group relative z-10 text-center']) }}>
          <div class="mb-3 font-display text-xs font-extrabold tracking-widest text-brand">{{ $item['number'] }}</div>
          <div class="mx-auto mb-6 flex size-16 items-center justify-center rounded-full border-2 border-line bg-white text-2xl transition group-hover:border-brand group-hover:bg-brand">{{ $item['icon'] }}</div>
          <h3 class="mb-2 font-display text-lg font-bold leading-snug">{{ $item['title'] }}</h3>
          <p class="mb-4 text-sm leading-relaxed text-muted">{{ $item['description'] }}</p>
        </div>
