<div class="admin-shell space-y-6" x-data="adminMediaPicker()">
    <div class="flex flex-wrap items-center justify-between gap-4"><div><a class="admin-link text-sm" href="{{ route('admin.catalogue.index', $resource) }}" wire:navigate>&larr; {{ ucfirst($resource) }}</a><h1 class="mt-3 text-3xl font-semibold tracking-tight">{{ $id ? 'Edit' : 'Create' }} {{ $resource === 'categories' ? 'category' : rtrim($resource, 's') }}</h1></div>@if($record?->legacy_product_id)<span class="admin-badge">Joomla #{{ $record->legacy_product_id }} &middot; {{ ucfirst($record->record_type) }}</span>@elseif($record?->source_key)<span class="admin-badge">Imported record</span>@endif</div>
    <x-admin.feedback />
    @if($resource === 'products' && $record?->review_flags)<div class="rounded-xl border border-amber-300 bg-amber-50 p-4 text-sm text-amber-900"><p class="font-semibold">Migration review</p>@foreach($record->review_flags as $flag)<p>{{ str_replace('_', ' ', $flag) }}</p>@endforeach</div>@endif
    <form wire:submit="save" class="space-y-6">
        <div class="grid items-start gap-6 xl:grid-cols-3">
            <div class="space-y-6 xl:col-span-2">
                <x-admin.panel title="The essentials">
                    <div class="space-y-5">
                        <x-admin.field label="Name"><input required maxlength="255" class="admin-input" wire:model="form.name"></x-admin.field>
                        <x-admin.field label="URL slug" hint="Leave blank to generate from the name. Existing slugs remain stable when renaming."><input class="admin-input" wire:model="form.slug"></x-admin.field>
                        @if($resource === 'products')<x-admin.field label="Short description"><textarea class="admin-input" rows="3" wire:model="form.summary"></textarea></x-admin.field>@endif
                        <x-admin.rich-text label="Description" hint="Use the visual editor or switch to HTML source. Paragraphs, lists, links, images and specification tables are supported; unsafe markup is removed when saving." :model="'form.'.($resource === 'products' ? 'description_html' : 'description')" :gallery="$resource === 'products' ? $gallery : []" />
                        <details class="rounded-lg border border-zinc-200 p-4 dark:border-zinc-700"><summary class="cursor-pointer text-sm font-medium">Preview description</summary><div class="admin-preview mt-4">{!! $preview !!}</div></details>
                    </div>
                </x-admin.panel>
                @if($resource === 'products')
                <x-admin.panel title="Gallery" description="The first image is the cover. Reorder or remove images, then save your changes.">
                    <div class="grid gap-4 sm:grid-cols-2">
                    @foreach($gallery as $index => $asset)
                        <div wire:key="gallery-{{ $asset['id'] }}" class="rounded-xl border border-zinc-200 p-3 dark:border-zinc-700">
                            @if(str_starts_with($asset['mime_type'], 'image/'))<img class="aspect-video w-full rounded-lg bg-white object-contain" src="{{ $asset['url'] }}" alt="{{ $asset['alt_text'] }}">@else<a class="admin-link" href="{{ $asset['url'] }}" target="_blank" rel="noopener">View attached document</a>@endif
                            <div class="my-3 flex items-center justify-between"><span class="text-xs font-semibold">{{ $index === 0 ? 'Cover image' : 'Image '.($index + 1) }}</span><div class="flex gap-3"><button type="button" class="admin-link disabled:opacity-30" wire:click="moveImage({{ $index }}, -1)" @disabled($index === 0) aria-label="Move image earlier">&uarr;</button><button type="button" class="admin-link disabled:opacity-30" wire:click="moveImage({{ $index }}, 1)" @disabled($loop->last) aria-label="Move image later">&darr;</button><button type="button" class="text-xs text-red-600" wire:click="removeImageAt({{ $index }})">Remove</button></div></div>
                            @if($index > 0)<button type="button" class="admin-link mb-3 text-xs" wire:click="makeCover({{ $index }})">Set as cover</button>@endif
                            <x-admin.field label="Alt text"><input class="admin-input" wire:model="gallery.{{ $index }}.alt_text"></x-admin.field>
                        </div>
                    @endforeach
                    </div>
                    @if(!$gallery)<p class="mb-4 text-sm text-zinc-500">No gallery images yet.</p>@endif
                    <div class="mt-5 flex flex-wrap items-center gap-3">
                        <button type="button" class="admin-secondary inline-flex items-center gap-1.5" @click="open('gallery', @json(array_column($gallery, 'id')))">
                            <flux:icon.photo class="size-4" />
                            Choose from Media Library
                        </button>
                    </div>
                    <x-admin.field class="mt-4" label="Or upload new images" hint="JPEG, PNG, WebP, GIF or AVIF. Up to 10 MB each; 20 per upload."><input class="admin-input" type="file" multiple accept="image/jpeg,image/png,image/webp,image/gif,image/avif" wire:model="images"></x-admin.field>
                    <p class="mt-2 text-sm text-zinc-500" wire:loading wire:target="images">Uploading images…</p>
                    @if($images)<p class="mt-2 text-sm">{{ count($images) }} new image(s) ready to save.</p>@endif
                </x-admin.panel>
                <x-admin.panel title="YouTube videos" description="Enter a YouTube URL or video ID. Use the arrows to control display order.">
                    <div class="space-y-4">@foreach($videos as $index => $video)<div class="flex items-end gap-3" wire:key="video-{{ $index }}"><x-admin.field class="grow" :label="'Video '.($index + 1)"><input class="admin-input" wire:model="videos.{{ $index }}.value" placeholder="https://www.youtube.com/watch?v=…"></x-admin.field><button type="button" class="admin-secondary" aria-label="Move video earlier" wire:click="moveRow('videos', {{ $index }}, -1)">&uarr;</button><button type="button" class="admin-secondary" aria-label="Move video later" wire:click="moveRow('videos', {{ $index }}, 1)">&darr;</button><button type="button" class="admin-secondary" wire:click="removeVideo({{ $index }})">Remove</button></div>@endforeach</div>
                    <button class="admin-secondary mt-4" type="button" wire:click="addVideo">Add video</button>
                </x-admin.panel>
                <x-admin.panel title="Testimonials" description="Keep each testimonial separate, including repeated source testimonials.">
                    <div class="space-y-4">@foreach($testimonials as $index => $testimonial)<div wire:key="testimonial-{{ $index }}"><x-admin.field :label="'Testimonial '.($index + 1)"><textarea class="admin-input" rows="4" wire:model="testimonials.{{ $index }}.content_html"></textarea></x-admin.field><button type="button" class="admin-secondary mt-2" aria-label="Move testimonial earlier" wire:click="moveRow('testimonials', {{ $index }}, -1)">&uarr;</button><button type="button" class="admin-secondary mt-2" aria-label="Move testimonial later" wire:click="moveRow('testimonials', {{ $index }}, 1)">&darr;</button><button type="button" class="admin-link mt-2 text-sm" wire:click="removeTestimonial({{ $index }})">Remove testimonial</button></div>@endforeach</div>
                    <button class="admin-secondary mt-4" type="button" wire:click="addTestimonial">Add testimonial</button>
                </x-admin.panel>
                @else
                <x-admin.panel :title="$resource === 'brands' ? 'Brand imagery' : 'Category image'" description="Images keep their original proportions. Replacing or removing an image does not delete shared files.">
                    @foreach($resource === 'brands' ? ['logo' => 'Logo', 'image' => 'Card photo'] : ['image' => 'Category image'] as $field => $label)
                    @php
                        $previewUrl = ($field === 'logo' && $selectedLogoUrl)
                            ? $selectedLogoUrl
                            : (($field === 'image' && $selectedImageUrl)
                                ? $selectedImageUrl
                                : ($record?->$field ? $record->$field->url() : null));
                    @endphp
                    <div class="mb-6 space-y-3">
                        @if($previewUrl)
                            <div class="relative">
                                <img class="h-36 w-full rounded-lg bg-white object-contain" src="{{ $previewUrl }}" alt="{{ $label }}">
                                @if(($field === 'logo' && $selectedLogoUrl) || ($field === 'image' && $selectedImageUrl))
                                    <span class="absolute top-2 right-2 rounded bg-emerald-600 px-2 py-0.5 text-[10px] font-semibold text-white">Selected from Library</span>
                                @endif
                            </div>
                        @endif
                        <div class="flex flex-wrap items-center gap-2">
                            <button type="button" class="admin-secondary text-xs inline-flex items-center gap-1.5" @click="open('{{ $field }}')">
                                <flux:icon.photo class="size-4" />
                                Choose {{ strtolower($label) }} from Library
                            </button>
                        </div>
                        <x-admin.field :label="'Or upload new '.$label" hint="JPEG, PNG, WebP, GIF or AVIF; up to 10 MB."><input type="file" class="admin-input" accept="image/jpeg,image/png,image/webp,image/gif,image/avif" wire:model="{{ $field }}"></x-admin.field>
                        <x-admin.field :label="$label.' alt text'"><input class="admin-input" wire:model="form.{{ $field }}_alt_text"></x-admin.field>
                        <label class="flex items-center gap-2 text-sm"><input type="checkbox" wire:model="{{ $field === 'logo' ? 'removeLogo' : 'removeImage' }}"> Remove current {{ strtolower($label) }}</label>
                    </div>
                    @endforeach
                    <p class="text-sm text-zinc-500" wire:loading wire:target="image,logo">Uploading image…</p>
                </x-admin.panel>
                @endif
            </div>
            <div class="space-y-6">
                <x-admin.panel title="Organisation">
                    <div class="space-y-5">
                    @if($resource === 'products')
                        <x-admin.field label="Brand"><select class="admin-input" wire:model="form.brand_id"><option value="">No brand</option>@foreach($brands as $brand)<option value="{{ $brand->id }}">{{ $brand->name }}{{ !$brand->is_active ? ' (inactive)' : '' }}</option>@endforeach</select></x-admin.field>
                        <fieldset><legend class="mb-2 text-sm font-medium">Categories</legend><div class="max-h-72 space-y-3 overflow-y-auto rounded-lg border border-zinc-200 p-3 dark:border-zinc-700">@foreach($categories as $category)<label class="flex items-center gap-2 text-sm"><input type="checkbox" value="{{ $category->id }}" wire:model="form.category_ids">{{ $category->name }}</label>@endforeach</div></fieldset>
                        <x-admin.field label="Condition"><select class="admin-input" wire:model="form.condition"><option value="new">New</option><option value="used">Used</option></select></x-admin.field>
                        <x-admin.field label="Publication"><select class="admin-input" wire:model="form.status"><option value="draft">Draft</option><option value="published">Published</option></select></x-admin.field>
                        @if($record && $record->record_type !== 'machinery')<p class="text-sm text-amber-600">{{ ucfirst($record->record_type) }} records remain hidden from the public catalogue.</p>@endif
                        <label class="flex items-center gap-2 text-sm"><input type="checkbox" wire:model="form.is_featured">Featured machinery</label>
                    @else
                        @if($resource === 'categories')<x-admin.field label="Parent category"><select class="admin-input" wire:model="form.parent_id"><option value="">Top-level category</option>@foreach($categories as $category)@if($category->id !== $id)<option value="{{ $category->id }}">{{ $category->name }}</option>@endif @endforeach</select></x-admin.field>@endif
                        <label class="flex items-center gap-2 text-sm"><input type="checkbox" wire:model="form.is_active">Active</label><label class="flex items-center gap-2 text-sm"><input type="checkbox" wire:model="form.show_on_homepage">Show on homepage</label><x-admin.field label="Homepage order"><input class="admin-input" type="number" min="0" wire:model="form.homepage_order"></x-admin.field>
                    @endif
                        <x-admin.field label="Display order" hint="Lower numbers appear first."><input class="admin-input" type="number" min="0" max="1000000" wire:model="form.sort_order"></x-admin.field>
                    </div>
                </x-admin.panel>
                <div class="admin-panel space-y-3"><button class="admin-primary w-full justify-center" type="submit" wire:loading.attr="disabled" wire:target="save,images,image,logo"><span wire:loading.remove wire:target="save">Save changes</span><span wire:loading wire:target="save">Saving…</span></button><a class="admin-secondary flex justify-center" href="{{ route('admin.catalogue.index', $resource) }}" wire:navigate>Cancel</a><p class="text-xs leading-relaxed text-zinc-500">Saved edits are protected from ordinary Joomla reimports.</p></div>
            </div>
        </div>
    </form>

    {{-- Media Picker Modal (Alpine.js + JSON) --}}
    <div
        x-cloak
        x-show="isOpen"
        class="fixed inset-0 z-50 flex items-center justify-center overflow-y-auto bg-black/60 p-4 backdrop-blur-xs"
        @keydown.escape.window="close()"
        role="dialog"
        aria-modal="true"
    >
        <div
            class="relative max-h-[90vh] w-full max-w-4xl overflow-hidden rounded-2xl bg-white shadow-2xl dark:bg-zinc-900 flex flex-col"
            @click.stop
        >
            <div class="flex items-center justify-between border-b border-zinc-200 px-6 py-4 dark:border-zinc-800">
                <div>
                    <h2 class="text-lg font-semibold text-zinc-900 dark:text-zinc-100">
                        Choose from Media Library
                    </h2>
                    <p class="text-xs text-zinc-500 dark:text-zinc-400">
                        <span x-show="target === 'gallery'">Select images to add to the product gallery.</span>
                        <span x-show="target !== 'gallery'">Select an image from the library.</span>
                    </p>
                </div>
                <button
                    type="button"
                    class="rounded-lg p-1.5 text-zinc-400 hover:bg-zinc-100 hover:text-zinc-700 dark:hover:bg-zinc-800 dark:hover:text-zinc-200"
                    @click="close()"
                    aria-label="Close modal"
                >
                    <flux:icon.x-mark class="size-5" />
                </button>
            </div>

            {{-- Filter bar --}}
            <div class="border-b border-zinc-200 bg-zinc-50 p-4 dark:border-zinc-800 dark:bg-zinc-950">
                <div class="grid gap-3 sm:grid-cols-2">
                    <input
                        type="search"
                        class="admin-input text-xs"
                        placeholder="Search by filename or title…"
                        x-model="search"
                        @input="onSearchInput()"
                    >
                    <select class="admin-input text-xs" x-model="source" @change="fetchItems(1)">
                        <option value="">All sources</option>
                        <option value="whatsapp_local">WhatsApp / Local</option>
                        <option value="admin_upload">Admin uploads</option>
                        <option value="remote_web">Remote web URLs</option>
                        <option value="legacy_joomla">Legacy Joomla</option>
                    </select>
                </div>
            </div>

            {{-- Asset Grid --}}
            <div class="relative overflow-y-auto p-4 max-h-[55vh] min-h-[220px]">
                {{-- Loading spinner --}}
                <div
                    x-cloak
                    x-show="loading"
                    class="admin-loader-overlay"
                >
                    <div class="admin-loader-pill text-xs">
                        <svg class="size-4 animate-spin text-red-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                        </svg>
                        <span>Loading…</span>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3 sm:grid-cols-4 md:grid-cols-6">
                    <template x-for="item in items" :key="item.id">
                        <button
                            type="button"
                            :disabled="existingGalleryIds.includes(item.id)"
                            @click="target === 'gallery' ? toggleSelect(item.id) : selectSingle(item)"
                            class="group relative flex aspect-square flex-col overflow-hidden rounded-lg border p-1 text-left transition-all bg-white dark:bg-zinc-900"
                            :class="existingGalleryIds.includes(item.id)
                                ? 'opacity-40 border-zinc-200 cursor-not-allowed bg-zinc-100 dark:border-zinc-800 dark:bg-zinc-950'
                                : ((target === 'gallery' ? selectedIds.includes(item.id) : (singleSelected && singleSelected.id === item.id))
                                    ? 'border-red-600 ring-2 ring-red-600/40 bg-red-50/20'
                                    : 'border-zinc-200 hover:border-zinc-400 dark:border-zinc-700')"
                        >
                            <template x-if="item.mime_type.startsWith('image/')">
                                <img :src="item.url" :alt="item.alt_text" class="h-full w-full rounded object-contain" loading="lazy">
                            </template>
                            <template x-if="!item.mime_type.startsWith('image/')">
                                <div class="flex h-full w-full items-center justify-center text-zinc-400">
                                    <flux:icon.document class="size-6" />
                                </div>
                            </template>

                            <template x-if="existingGalleryIds.includes(item.id)">
                                <span class="absolute inset-x-1 bottom-1 rounded bg-zinc-800/90 py-0.5 text-center text-[9px] font-semibold text-white">
                                    Already attached
                                </span>
                            </template>
                            <template x-if="!existingGalleryIds.includes(item.id) && (target === 'gallery' ? selectedIds.includes(item.id) : (singleSelected && singleSelected.id === item.id))">
                                <div class="absolute right-1.5 top-1.5 rounded-full bg-red-600 p-0.5 text-white shadow">
                                    <flux:icon.check class="size-3.5" />
                                </div>
                            </template>
                        </button>
                    </template>
                </div>

                {{-- Empty state --}}
                <div
                    x-cloak
                    x-show="!loading && items.length === 0"
                    class="py-12 text-center text-xs text-zinc-500"
                >
                    No images found matching your search.
                </div>

                {{-- Pagination --}}
                <div
                    x-cloak
                    x-show="lastPage > 1"
                    class="mt-4 flex items-center justify-between border-t border-zinc-100 pt-3 dark:border-zinc-800 text-xs text-zinc-500"
                >
                    <span>Page <span x-text="page"></span> of <span x-text="lastPage"></span> (<span x-text="total"></span> total)</span>
                    <div class="flex items-center gap-1">
                        <button
                            type="button"
                            class="admin-secondary px-2 py-0.5 text-xs"
                            :disabled="page <= 1"
                            @click="changePage(page - 1)"
                        >
                            &larr; Prev
                        </button>
                        <button
                            type="button"
                            class="admin-secondary px-2 py-0.5 text-xs"
                            :disabled="page >= lastPage"
                            @click="changePage(page + 1)"
                        >
                            Next &rarr;
                        </button>
                    </div>
                </div>
            </div>

            {{-- Modal Footer --}}
            <div class="flex items-center justify-between border-t border-zinc-200 bg-zinc-50 px-6 py-3 dark:border-zinc-800 dark:bg-zinc-950">
                <span class="text-xs text-zinc-500">
                    <span x-show="target === 'gallery'" x-text="selectedIds.length + ' image(s) selected'"></span>
                    <span x-show="target !== 'gallery'" x-text="singleSelected ? '1 image selected' : 'No image selected'"></span>
                </span>
                <div class="flex items-center gap-2">
                    <button type="button" class="admin-secondary text-xs" @click="close()">
                        Cancel
                    </button>
                    <button
                        type="button"
                        class="admin-primary text-xs"
                        @click="attachToLivewire()"
                        :disabled="target === 'gallery' ? !selectedIds.length : !singleSelected"
                    >
                        <span x-show="target === 'gallery'" x-text="'Add selected images (' + selectedIds.length + ')'"></span>
                        <span x-show="target !== 'gallery'">Use selected image</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
