<x-layouts.site :title="$product->name.' | Henry Porter Machinery'" :description="Str::limit(strip_tags($product->summary ?: $product->description_html ?? ''),160)" :image="$product->coverImage()?->url()">
@push('schema')
@php
    $productSchema = [
        '@context' => 'https://schema.org',
        '@type' => 'Product',
        'name' => $product->name,
        'description' => strip_tags($product->summary ?: $product->description_html ?? ''),
        'url' => route('machinery.show', $product->slug),
        'category' => $product->categories->pluck('name')->implode(', '),
        'itemCondition' => $product->condition === 'new' ? 'https://schema.org/NewCondition' : 'https://schema.org/UsedCondition',
        'offers' => [
            '@type' => 'Offer',
            'priceCurrency' => 'GBP',
            'availability' => 'https://schema.org/InStock',
            'url' => route('machinery.show', $product->slug),
            'seller' => [
                '@type' => 'Organization',
                'name' => 'Henry Porter Machinery',
            ],
        ],
    ];
    if ($product->coverImage()) {
        $productSchema['image'] = $product->coverImage()->url();
    }
    if ($product->brand) {
        $productSchema['brand'] = [
            '@type' => 'Brand',
            'name' => $product->brand->name,
        ];
    }
    $breadcrumbSchema = [
        '@context' => 'https://schema.org',
        '@type' => 'BreadcrumbList',
        'itemListElement' => [
            [
                '@type' => 'ListItem',
                'position' => 1,
                'name' => 'Home',
                'item' => route('home'),
            ],
            [
                '@type' => 'ListItem',
                'position' => 2,
                'name' => 'Machinery',
                'item' => route('machinery.index'),
            ],
            [
                '@type' => 'ListItem',
                'position' => 3,
                'name' => $product->name,
                'item' => route('machinery.show', $product->slug),
            ],
        ],
    ];
@endphp
<script type="application/ld+json">
{!! json_encode($productSchema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) !!}
</script>
<script type="application/ld+json">
{!! json_encode($breadcrumbSchema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) !!}
</script>
@endpush
<x-site.container class="py-10"><nav aria-label="Breadcrumb" class="mb-7 flex flex-wrap gap-2 text-xs text-muted"><a href="{{ route('home') }}">Home</a><span>/</span><a href="{{ route('machinery.index') }}">Machinery</a><span>/</span><span>{{ $product->name }}</span></nav>
<div class="grid items-start gap-10 lg:grid-cols-2"><div data-gallery>@php($images=$product->media->sortBy('pivot.sort_order')->filter(fn($m)=>str_starts_with($m->mime_type,'image/'))->values())
@if($images->isNotEmpty())<a href="{{ $images[0]->url() }}" data-gallery-open class="product-main-image block overflow-hidden rounded-card border border-line bg-white"><img data-gallery-main src="{{ $images[0]->url() }}" alt="{{ $images[0]->alt_text ?: $product->name }}" class="size-full object-contain"></a><p class="mt-3 text-xs text-muted">Select an image to enlarge.</p><div class="mt-4 grid grid-cols-4 gap-3">@foreach($images as $image)<a href="{{ $image->url() }}" data-gallery-thumb data-alt="{{ $image->alt_text ?: $product->name }}" aria-label="View image {{ $loop->iteration }}" class="product-thumb overflow-hidden rounded-xl border border-line bg-white"><img src="{{ $image->url() }}" alt="{{ $image->alt_text ?: $product->name }}" class="size-full object-contain" loading="lazy"></a>@endforeach</div>
<dialog data-gallery-dialog class="gallery-dialog"><div class="flex justify-between gap-3 p-4"><button type="button" data-gallery-prev aria-label="Previous image">&larr; Previous</button><button type="button" data-gallery-close aria-label="Close image gallery">Close &times;</button><button type="button" data-gallery-next aria-label="Next image">Next &rarr;</button></div><img data-gallery-large src="{{ $images[0]->url() }}" alt="{{ $product->name }}"></dialog>
@else<div class="product-main-image"><x-site.image-placeholder :label="$product->name" /></div>@endif</div>
<div class="lg:sticky lg:top-24"><span class="section-eyebrow">{{ ucfirst($product->condition) }} machinery</span><h1 class="font-display text-4xl font-extrabold tracking-tight sm:text-5xl">{{ $product->name }}</h1>@if($product->brand?->is_active)<a class="mt-5 inline-flex font-display text-sm font-bold uppercase tracking-widest text-brand" href="{{ route('brands.show',$product->brand->slug) }}">{{ $product->brand->name }}</a>@endif<div class="catalogue-prose mt-5 text-muted">{!! $product->summary !!}</div><div class="mt-6 flex flex-wrap gap-2">@foreach($product->categories->where('is_active',true) as $category)<a class="rounded-full border border-line px-3 py-2 text-xs transition hover:border-brand hover:text-brand" href="{{ route('categories.show',$category->slug) }}">{{ $category->name }}</a>@endforeach</div><div class="mt-8 rounded-card bg-ink p-7 text-white"><h2 class="text-xl font-bold">Is this the right machine for you?</h2><p class="mt-3 text-sm leading-relaxed text-white/70">Speak to our team about specifications, demonstrations and your requirements.</p><div class="mt-6 flex flex-wrap gap-3"><x-site.button href="#enquiry">Enquire about this machine</x-site.button><x-site.button href="tel:+442892622611" variant="ghost">Call our team</x-site.button></div></div></div></div>
<section class="mt-14 rounded-card border border-line bg-white p-6 sm:p-10"><h2 class="font-display text-2xl font-bold">Details &amp; specifications</h2><div class="catalogue-prose mt-6">{!! $product->description_html !!}</div></section>
@if($product->videos->isNotEmpty())<section class="mt-12"><h2 class="font-display text-2xl font-bold">See it in action</h2><div class="mt-6 grid gap-6 lg:grid-cols-2">@foreach($product->videos as $video)@if($video->provider === 'local')<div class="video-placeholder rounded-card relative overflow-hidden bg-ink p-8 text-white flex flex-col justify-between min-h-[240px]" data-local-video="{{ asset('storage/' . $video->video_id) }}">@if($product->coverImage())<img src="{{ $product->coverImage()->url() }}" alt="{{ $product->name }}" class="absolute inset-0 size-full object-cover opacity-30 pointer-events-none">@endif<div class="relative z-10"><span class="inline-block rounded-full bg-brand/90 px-3 py-1 text-xs font-semibold uppercase tracking-wider text-white">Demonstration</span><p class="mt-3 text-sm font-medium text-white/95">Watch the {{ $product->name }} demonstration video.</p></div><div class="relative z-10 mt-6"><button type="button" class="catalogue-submit inline-flex items-center gap-2" data-local-video-play><span>Play video &rarr;</span></button></div><noscript><video controls class="mt-4 w-full rounded-card"><source src="{{ asset('storage/' . $video->video_id) }}" type="video/mp4">Your browser does not support HTML5 video.</video></noscript></div>@else<div class="video-placeholder rounded-card bg-ink p-8 text-white" data-youtube="{{ $video->video_id }}"><p class="text-sm text-white/70">YouTube loads only when you choose to play.</p><button type="button" class="catalogue-submit mt-5" data-video-play>Play video &rarr;</button><noscript><a href="https://www.youtube.com/watch?v={{ $video->video_id }}" rel="noopener" target="_blank">Watch on YouTube</a></noscript></div>@endif @endforeach</div></section>@endif
@if($product->testimonials->isNotEmpty())<section class="mt-12"><h2 class="font-display text-2xl font-bold">Customer feedback</h2><div class="mt-6 grid gap-5 lg:grid-cols-2">@foreach($product->testimonials as $testimonial)<x-site.testimonial-card :item="['html'=>$testimonial->content_html]" />@endforeach</div></section>@endif
<section class="mt-14" id="enquiry"><x-site.enquiry-form :product="$product" /></section>
@if($related->isNotEmpty())<section class="mt-14"><x-site.heading eyebrow="Explore more" title="Related machinery" /><div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">@foreach($related as $machine)<x-site.machinery-card :item="$catalogue->productCard($machine)" />@endforeach</div></section>@endif
</x-site.container></x-layouts.site>
