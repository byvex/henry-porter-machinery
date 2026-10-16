@props(['item'])
<article {{ $attributes->class(['history-item reveal relative grid gap-4 border-b border-white/10 py-9 last:border-0 md:grid-cols-3 md:gap-10']) }}>
    <div class="history-year font-display text-4xl font-extrabold text-brand-light">{{ $item['year'] }}</div>
    <div class="md:col-span-2">
        <h3 class="mb-3 font-display text-xl font-bold text-white">{{ $item['title'] }}</h3>
        <p class="max-w-xl text-sm leading-relaxed text-white/60">{{ $item['description'] }}</p>
    </div>
</article>
