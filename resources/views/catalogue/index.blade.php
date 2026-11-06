<x-layouts.site :title="$title.' | Henry Porter Machinery'" :description="$description">
@php
    $activeFilterCount = 0;
    if (!empty($filters['q'])) $activeFilterCount++;
    if (!empty($condition) && !request()->routeIs('machinery.new', 'machinery.used')) $activeFilterCount++;
    if (!empty($filters['brands'])) $activeFilterCount += count($filters['brands']);
    if (!empty($filters['categories'])) $activeFilterCount += count($filters['categories']);
    if (!empty($filters['sort']) && $filters['sort'] !== 'recommended') $activeFilterCount++;
@endphp
<x-site.page-hero :title="$title" eyebrow="Machinery" :description="$description" :image="$image" />
<x-site.container class="catalogue-layout relative grid items-start gap-8 py-12 lg:grid-cols-4">
{{-- Mobile Filter Drawer Backdrop --}}
<div class="catalogue-drawer-backdrop lg:hidden" data-catalogue-drawer-backdrop aria-hidden="true"></div>

{{-- Filters (Permanent sidebar on desktop, slide-over drawer on mobile) --}}
<aside id="catalogue-filter-drawer" class="catalogue-drawer min-w-0 lg:col-span-1" data-catalogue-filter-drawer aria-label="Machinery search and filters">
<form method="GET" action="{{ url()->current() }}" class="catalogue-drawer-form">
<div class="catalogue-drawer-header">
<div>
<h2 class="font-display text-lg font-bold">Find your machinery</h2>
@if($activeFilterCount > 0)
<p class="mt-0.5 text-xs text-muted">{{ $activeFilterCount }} {{ Str::plural('filter', $activeFilterCount) }} active</p>
@endif
</div>
<button type="button" class="catalogue-drawer-close lg:hidden" data-catalogue-drawer-close aria-label="Close filters">
<x-icon.close class="size-5" />
</button>
</div>
<div class="catalogue-drawer-body space-y-5">
<label class="block text-sm font-semibold">Search<input class="catalogue-input mt-2" name="q" value="{{ $filters['q'] ?? '' }}" maxlength="100" placeholder="Machine or model"></label>
@if(!request()->routeIs('machinery.new','machinery.used'))<label class="block text-sm font-semibold">Condition<select class="catalogue-input mt-2" name="condition"><option value="">New and used</option><option value="new" @selected($condition==='new')>New</option><option value="used" @selected($condition==='used')>Used</option></select></label>@endif
@if(!($entity instanceof \App\Models\Brand))<fieldset><legend class="mb-3 text-sm font-semibold">Brands</legend><div class="max-h-52 space-y-2 overflow-y-auto">@foreach($brands as $brand)<label class="flex items-center gap-2 text-sm"><input type="checkbox" name="brands[]" value="{{ $brand->slug }}" @checked(in_array($brand->slug,$filters['brands'] ?? []))>{{ $brand->name }}</label>@endforeach</div></fieldset>@endif
<fieldset><legend class="mb-3 text-sm font-semibold">Categories</legend><div class="max-h-64 space-y-2 overflow-y-auto">@foreach($categories as $category)<label class="flex items-center gap-2 text-sm"><input type="checkbox" name="categories[]" value="{{ $category->slug }}" @checked(in_array($category->slug,$filters['categories'] ?? []))>{{ $category->name }}</label>@endforeach</div></fieldset>
<label class="block text-sm font-semibold">Sort by<select class="catalogue-input mt-2" name="sort">@foreach(['recommended'=>'Recommended','newest'=>'Newest first','name'=>'Name A–Z'] as $value=>$label)<option value="{{ $value }}" @selected(($filters['sort'] ?? 'recommended')===$value)>{{ $label }}</option>@endforeach</select></label>
</div>
<div class="catalogue-drawer-footer">
<button class="catalogue-submit w-full" type="submit">Apply filters @if($activeFilterCount > 0)<span class="ml-1 text-xs font-normal opacity-90">({{ $activeFilterCount }})</span>@endif</button>
@if($activeFilterCount > 0)
<a class="block text-center text-sm font-medium text-brand hover:underline" href="{{ url()->current() }}">Reset all filters</a>
@else
<a class="block text-center text-sm text-muted underline" href="{{ url()->current() }}">Reset filters</a>
@endif
</div>
</form>
</aside>

{{-- Machinery listing --}}
<div class="min-w-0 lg:col-span-3">
<div class="mb-6 flex flex-wrap items-center justify-between gap-4">
<div class="flex items-center gap-3">
<p class="text-sm text-muted">{{ $products->total() }} {{ Str::plural('machine',$products->total()) }} found</p>
<button type="button" class="catalogue-inline-filter-btn inline-flex items-center gap-1.5 rounded-full border border-line bg-white px-3.5 py-1.5 text-xs font-semibold text-charcoal shadow-xs transition hover:border-brand hover:text-brand lg:hidden" data-catalogue-filter-trigger aria-controls="catalogue-filter-drawer" aria-expanded="false">
<x-icon.tune class="size-3.5 text-brand" />
<span>Filters</span>
@if($activeFilterCount > 0)
<span class="inline-flex size-4 items-center justify-center rounded-full bg-brand text-[10px] font-bold text-white">{{ $activeFilterCount }}</span>
@endif
</button>
</div>
<a class="text-sm font-semibold text-brand" href="{{ route('categories.index') }}">Browse categories &rarr;</a>
</div>
<div class="grid gap-6 sm:grid-cols-2 xl:grid-cols-3">@forelse($products as $product)<x-site.machinery-card :item="$catalogue->productCard($product)" />@empty<div class="rounded-card border border-line bg-white p-10 sm:col-span-2"><h2 class="text-2xl font-bold">No machinery matches these filters.</h2><p class="mt-4 text-muted">Our team can help you find the right equipment, including options outside the current listings.</p><a class="mt-5 inline-flex text-brand underline" href="{{ route('contact') }}">Contact our team</a></div>@endforelse</div>
<div class="mt-9">{{ $products->links() }}</div>
</div>
</x-site.container>

{{-- Mobile Centered Floating Action Button (FAB) --}}
<button type="button" class="catalogue-filter-fab lg:hidden" data-catalogue-filter-trigger aria-controls="catalogue-filter-drawer" aria-expanded="false" aria-label="Open search and filters">
<x-icon.tune class="size-5 shrink-0" />
<span>Filters</span>
@if($activeFilterCount > 0)
<span class="catalogue-filter-fab-badge" aria-label="{{ $activeFilterCount }} active filters">{{ $activeFilterCount }}</span>
@endif
</button>
</x-layouts.site>
