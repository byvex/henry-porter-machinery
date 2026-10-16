<x-layouts::app title="Catalogue overview">
<div class="admin-shell space-y-8">
    <div><p class="admin-eyebrow">Henry Porter Machinery</p><h1 class="mt-2 text-3xl font-semibold tracking-tight">Your catalogue, organised.</h1><p class="mt-3 text-zinc-500 dark:text-zinc-400">Manage machinery, equipment categories and the brands you represent.</p></div>
    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        @foreach(['Products' => $products, 'Published machinery' => $published, 'Brands' => $brands, 'Categories' => $categories] as $label => $count)
        <div class="admin-panel"><p class="text-sm text-zinc-500 dark:text-zinc-400">{{ $label }}</p><p class="mt-3 text-4xl font-semibold">{{ $count }}</p></div>
        @endforeach
    </div>
    <div class="grid gap-5 lg:grid-cols-3">
        @foreach(['products' => ['Machinery', 'Build galleries, organise details and control publication.'], 'categories' => ['Equipment categories', 'Create a useful hierarchy with images and descriptions.'], 'brands' => ['Your brands', 'Keep logos, card photography and brand information together.']] as $resource => [$label, $copy])
        <x-admin.panel :title="$label" :description="$copy"><a class="admin-primary inline-flex" href="{{ route('admin.catalogue.index', $resource) }}" wire:navigate>Manage {{ $resource }} <span aria-hidden="true">&rarr;</span></a></x-admin.panel>
        @endforeach
    </div>
    <p class="text-sm text-zinc-500 dark:text-zinc-400">Homepage and About content remain independent. Public catalogue pages will be connected in a later phase.</p>
</div>
</x-layouts::app>
