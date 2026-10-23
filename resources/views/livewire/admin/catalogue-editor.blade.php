<div class="admin-shell space-y-6">
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
                    <x-admin.field class="mt-5" label="Add images" hint="JPEG, PNG, WebP, GIF or AVIF. Up to 10 MB each; 20 per upload."><input class="admin-input" type="file" multiple accept="image/jpeg,image/png,image/webp,image/gif,image/avif" wire:model="images"></x-admin.field>
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
                    <div class="mb-6 space-y-3">
                        @if($record?->$field)<img class="h-36 w-full rounded-lg bg-white object-contain" src="{{ $record->$field->url() }}" alt="{{ $label }}">@endif
                        <x-admin.field :label="$label" hint="JPEG, PNG, WebP, GIF or AVIF; up to 10 MB."><input type="file" class="admin-input" accept="image/jpeg,image/png,image/webp,image/gif,image/avif" wire:model="{{ $field }}"></x-admin.field>
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
</div>
