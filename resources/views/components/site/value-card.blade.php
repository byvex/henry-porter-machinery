@props(['item'])
<article {{ $attributes->class(['about-value group rounded-card border border-line bg-white p-7 transition duration-300 hover:-translate-y-1.5 hover:border-brand/30 hover:shadow-lg sm:p-9']) }}>
    <div class="mb-10 flex items-center justify-between">
        <span class="flex size-13 items-center justify-center rounded-2xl bg-brand/8 text-brand transition duration-300 group-hover:bg-brand group-hover:text-white"><x-site.icon :name="$item['icon']" /></span>
        <span class="font-display text-xs font-bold tracking-widest text-muted">{{ $item['number'] }}</span>
    </div>
    <h3 class="mb-4 font-display text-xl font-bold leading-snug">{{ $item['title'] }}</h3>
    <p class="text-sm leading-relaxed text-muted">{{ $item['description'] }}</p>
</article>
