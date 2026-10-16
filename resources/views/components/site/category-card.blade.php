@props(['item'])
<a href="{{ $item['url'] ?? route('categories.index') }}" data-category="{{ $item['filter'] }}" {{ $attributes->class(['category-card group block relative overflow-hidden rounded-card transition duration-500 hover:-translate-y-1.5 hover:shadow-xl', $item['size']]) }}>
          <x-site.image-placeholder :src="$item['imageUrl'] ?? null" :alt="$item['title']" :label="$item['image']" />
          <div class="category-overlay absolute inset-0 flex flex-col items-start justify-end p-8">
            <div class="mb-2.5 font-display text-xs font-bold uppercase tracking-widest text-white/70">{{ $item['eyebrow'] }}</div>
            <div @class(['mb-2 font-display font-extrabold leading-tight text-white', 'text-xl' => $item['size'] === 'small', 'text-3xl' => $item['size'] !== 'small'])>{{ $item['title'] }}</div>
            @if($item['description'])<div class="mb-4 max-w-sm text-sm leading-relaxed text-white/80">{{ $item['description'] }}</div>@endif
            <div class="inline-flex rounded-full border border-white/40 px-5 py-2.5 font-display text-xs font-bold text-white transition group-hover:bg-white group-hover:text-brand">View Category</div>
          </div>
        </a>
