@props(['item'])
<div {{ $attributes->class(['relative rounded-card border border-line bg-white px-7 py-9 transition duration-300 hover:-translate-y-1.5 hover:shadow-lg']) }}>
          <div class="mb-4 font-display text-6xl font-extrabold leading-none text-brand/25">"</div>
          <div class="mb-4 tracking-widest text-brand">★★★★★</div>
          <p class="mb-6 text-sm italic leading-relaxed text-ink">{{ $item['quote'] }}</p>
          <div class="flex items-center gap-3 border-t border-line pt-5">
            <div class="flex size-11 shrink-0 items-center justify-center rounded-full bg-brand/10 font-display font-extrabold text-brand">{{ $item['initials'] }}</div>
            <div>
              <div class="font-display text-sm font-bold">{{ $item['name'] }}</div>
              <div class="mt-1 text-xs text-muted">{{ $item['role'] }}</div>
            </div>
          </div>
        </div>
