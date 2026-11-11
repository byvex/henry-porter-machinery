<div class="admin-shell space-y-6">
    <div class="flex flex-wrap items-end justify-between gap-4">
        <div><p class="admin-eyebrow">Catalogue</p><h1 class="mt-2 text-3xl font-semibold tracking-tight">{{ ucfirst($resource) }}</h1><p class="mt-2 text-sm text-zinc-500 dark:text-zinc-400">{{ $resource === 'products' ? 'Machinery, galleries and publication in one place.' : 'Keep your catalogue organised and easy to explore.' }}</p></div>
        <div class="flex items-center gap-2">
            @if($resource === 'products')
                <a class="admin-secondary inline-flex items-center gap-1.5" href="{{ route('admin.import-export') }}" wire:navigate>Import / Export</a>
            @endif
            <a class="admin-primary" href="{{ route('admin.catalogue.create', $resource) }}" wire:navigate>Add {{ $resource === 'categories' ? 'category' : rtrim($resource, 's') }} <span aria-hidden="true">+</span></a>
        </div>
    </div>
    <x-admin.feedback />
    <div class="admin-panel space-y-4">
        <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
            <x-admin.field label="Search"><input class="admin-input" type="search" wire:model.live.debounce.300ms="search" placeholder="Search by name"></x-admin.field>
            <x-admin.field label="Status"><select class="admin-input" wire:model.live="status"><option value="">All statuses</option>@if($resource === 'products')<option value="published">Published</option><option value="draft">Draft</option>@else<option value="active">Active</option><option value="inactive">Inactive</option>@endif</select></x-admin.field>
            @if($resource === 'products')
            <x-admin.field label="Brand"><select class="admin-input" wire:model.live="brand"><option value="">All brands</option>@foreach($brands as $item)<option value="{{ $item->id }}">{{ $item->name }}</option>@endforeach</select></x-admin.field>
            <x-admin.field label="Category"><select class="admin-input" wire:model.live="category"><option value="">All categories</option>@foreach($categories as $item)<option value="{{ $item->id }}">{{ $item->name }}</option>@endforeach</select></x-admin.field>
            <x-admin.field label="Condition"><select class="admin-input" wire:model.live="condition"><option value="">New and used</option><option value="new">New</option><option value="used">Used</option></select></x-admin.field>
            <x-admin.field label="Record type"><select class="admin-input" wire:model.live="type"><option value="">All record types</option><option value="machinery">Machinery</option><option value="template">Hidden templates</option><option value="content">Hidden content</option></select></x-admin.field>
            @endif
        </div>
        @if($resource === 'products')<label class="flex items-center gap-2 text-sm"><input type="checkbox" wire:model.live="trash"> Show deleted products</label>@endif
    </div>
    <div class="admin-panel admin-table-panel overflow-hidden">
        <div class="overflow-x-auto"><table class="w-full text-left text-sm">
            <thead class="bg-zinc-50 text-xs uppercase tracking-wider text-zinc-500 dark:bg-zinc-950 dark:text-zinc-400"><tr><th class="p-4">{{ ucfirst($resource === 'categories' ? 'category' : rtrim($resource, 's')) }}</th><th class="p-4">Details</th><th class="p-4">Status</th><th class="p-4 text-right">Actions</th></tr></thead>
            <tbody class="divide-y divide-zinc-200 dark:divide-zinc-700">
            @forelse($records as $item)
                @php($thumbnail = $resource === 'products' ? $item->media->sortBy('pivot.sort_order')->first() : ($resource === 'brands' ? ($item->logo ?? $item->image) : $item->image))
                <tr wire:key="record-{{ $item->id }}" class="transition-colors hover:bg-zinc-50 dark:hover:bg-zinc-800">
                    <td class="p-4"><div class="flex items-center gap-3">@if($thumbnail && str_starts_with($thumbnail->mime_type, 'image/'))<img class="h-14 w-20 rounded-lg bg-white object-contain" src="{{ $thumbnail->url() }}" alt="">@else<span class="flex h-14 w-20 items-center justify-center rounded-lg bg-zinc-100 text-xs text-zinc-500 dark:bg-zinc-800">No image</span>@endif<div class="min-w-40"><p class="font-medium">{{ $item->name }}</p><p class="mt-1 text-xs text-zinc-500">{{ $item->slug }}</p></div></div></td>
                    <td class="p-4 text-zinc-500 dark:text-zinc-400">@if($resource === 'products')<p>{{ $item->brand?->name ?? 'No brand' }} &middot; {{ ucfirst($item->condition ?? '—') }}</p><p class="mt-1 text-xs">{{ ucfirst($item->record_type) }} @if($item->legacy_product_id)&middot; Joomla #{{ $item->legacy_product_id }}@endif</p>@if($item->review_flags)<span class="mt-1 inline-block text-xs text-amber-600">Review required</span>@endif @elseif($resource === 'categories'){{ $item->parent?->name ?? 'Top-level category' }}@else Order {{ $item->sort_order }}@endif</td>
                    <td class="p-4"><span class="admin-badge">{{ $resource === 'products' ? ($item->trashed() ? 'Deleted' : ($item->record_type !== 'machinery' ? 'Hidden' : ucfirst($item->status))) : ($item->is_active ? 'Active' : 'Inactive') }}</span></td>
                    <td class="p-4"><div class="flex justify-end gap-3">@if($resource === 'products' && $item->trashed())<button type="button" class="admin-link" wire:click="restore('{{ $item->id }}')">Restore</button>@else<a class="admin-link" href="{{ route('admin.catalogue.edit', [$resource, $item->id]) }}" wire:navigate>Edit</a><button type="button" class="text-zinc-500 hover:text-red-600" wire:click="delete('{{ $item->id }}')" wire:confirm="Delete this record? Referenced brands and categories cannot be deleted.">Delete</button>@endif</div></td>
                </tr>
            @empty<tr><td colspan="4" class="p-12 text-center text-zinc-500">No {{ $resource }} match your filters.</td></tr>@endforelse
            </tbody>
        </table></div>
    </div>
    {{ $records->links() }}
</div>
