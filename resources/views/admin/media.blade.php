<x-layouts::app title="Media Library">
<div class="admin-shell space-y-6" x-data="adminMediaLibrary()">
    {{-- Header --}}
    <div class="flex flex-wrap items-end justify-between gap-4">
        <div>
            <p class="admin-eyebrow">Catalogue</p>
            <h1 class="mt-2 text-3xl font-semibold tracking-tight">Media Library</h1>
            <p class="mt-2 text-sm text-zinc-500 dark:text-zinc-400">
                Browse, search, inspect, and manage all catalogue imagery and files across products, brands, and categories.
                <span class="font-medium text-zinc-700 dark:text-zinc-300" x-text="'(' + total.toLocaleString() + ' total items)'"></span>
            </p>
        </div>
        <div class="flex flex-wrap items-center gap-3">
            <button
                type="button"
                class="admin-secondary inline-flex items-center gap-1.5"
                @click="pruneOrphans()"
            >
                <flux:icon.trash class="size-4" />
                Prune Orphaned Media
            </button>

            <label class="admin-primary inline-flex cursor-pointer items-center gap-1.5">
                <flux:icon.arrow-up-tray class="size-4" />
                <span>Upload to Library</span>
                <input
                    type="file"
                    multiple
                    accept="image/jpeg,image/png,image/webp,image/gif,image/avif"
                    class="sr-only"
                    @change="uploadFiles($event)"
                >
            </label>
        </div>
    </div>

    {{-- Feedback alert banner --}}
    <div
        x-cloak
        x-show="feedback"
        x-transition
        class="rounded-xl border p-4 text-sm"
        :class="feedbackType === 'error' ? 'border-red-300 bg-red-50 text-red-900 dark:border-red-900 dark:bg-red-950/40 dark:text-red-200' : 'border-emerald-300 bg-emerald-50 text-emerald-900 dark:border-emerald-800 dark:bg-emerald-950/40 dark:text-emerald-200'"
        x-text="feedback"
    ></div>

    {{-- Uploading status indicator --}}
    <div
        x-cloak
        x-show="uploading"
        class="rounded-xl border border-zinc-200 bg-zinc-50 p-4 text-sm text-zinc-700 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-300"
    >
        Uploading images to media library&hellip;
    </div>

    {{-- Filter Bar --}}
    <div class="admin-panel space-y-4">
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-5">
            <div>
                <x-admin.field label="Search">
                    <input
                        class="admin-input"
                        type="search"
                        x-model="search"
                        @input="onSearchInput()"
                        placeholder="Search title, alt, or path…"
                    >
                </x-admin.field>
            </div>
            <div>
                <x-admin.field label="Source">
                    <select class="admin-input" x-model="source" @change="onFilterChange()">
                        <option value="">All sources</option>
                        <option value="whatsapp_local">WhatsApp / Local imports</option>
                        <option value="admin_upload">Admin uploads</option>
                        <option value="remote_web">Remote web URLs</option>
                        <option value="legacy_joomla">Legacy Joomla</option>
                    </select>
                </x-admin.field>
            </div>
            <div>
                <x-admin.field label="Attachment">
                    <select class="admin-input" x-model="attachment" @change="onFilterChange()">
                        <option value="">All attachment states</option>
                        <option value="attached">Attached anywhere</option>
                        <option value="products">Attached to products</option>
                        <option value="brands_categories">Brands / Categories</option>
                        <option value="unattached">Unattached / Orphaned</option>
                    </select>
                </x-admin.field>
            </div>
            <div>
                <x-admin.field label="Brand">
                    <select class="admin-input" x-model="brand" @change="onFilterChange()">
                        <option value="">All brands</option>
                        @foreach($brands as $b)
                            <option value="{{ $b->id }}">{{ $b->name }}</option>
                        @endforeach
                    </select>
                </x-admin.field>
            </div>
            <div>
                <x-admin.field label="Product">
                    <select class="admin-input" x-model="product" @change="onFilterChange()">
                        <option value="">All products</option>
                        @foreach($products as $p)
                            <option value="{{ $p->id }}">{{ $p->name }}</option>
                        @endforeach
                    </select>
                </x-admin.field>
            </div>
        </div>
    </div>

    {{-- Bulk Action Toolbar --}}
    <div
        x-cloak
        x-show="selected.length > 0"
        x-transition
        class="flex flex-wrap items-center justify-between gap-3 rounded-xl border border-red-200 bg-red-50 p-3 text-sm text-red-900 dark:border-red-900 dark:bg-red-950/40 dark:text-red-200"
    >
        <div class="flex items-center gap-3">
            <span class="font-semibold" x-text="selected.length + ' items selected'"></span>
            <button
                type="button"
                class="text-xs font-medium underline hover:text-red-700 dark:hover:text-red-100"
                @click="selectAllOnPage()"
            >
                Select all on this page
            </button>
            <button
                type="button"
                class="text-xs font-medium underline hover:text-red-700 dark:hover:text-red-100"
                @click="clearSelection()"
            >
                Clear selection
            </button>
        </div>
        <div class="flex items-center gap-2">
            <button
                type="button"
                class="admin-secondary bg-white text-xs hover:bg-zinc-100 dark:bg-zinc-800"
                @click="bulkDetach()"
            >
                Detach from products
            </button>
            <button
                type="button"
                class="admin-primary bg-red-600 text-xs hover:bg-red-700"
                @click="bulkDelete()"
            >
                Detach & Delete Permanently
            </button>
        </div>
    </div>

    {{-- Grid Panel --}}
    <div class="admin-panel relative min-h-[350px]">
        {{-- Loading overlay --}}
        <div
            x-cloak
            x-show="loading"
            class="admin-loader-overlay"
        >
            <div class="admin-loader-pill">
                <svg class="size-4.5 animate-spin text-red-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                </svg>
                <span>Loading media…</span>
            </div>
        </div>

        {{-- Grid --}}
        <div class="grid grid-cols-2 gap-4 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6">
            <template x-for="item in items" :key="item.id">
                <div
                    class="group relative flex flex-col overflow-hidden rounded-xl border transition-all bg-white dark:bg-zinc-900"
                    :class="selected.includes(item.id) ? 'border-red-500 ring-2 ring-red-500/30 dark:border-red-500' : 'border-zinc-200 hover:border-zinc-400 dark:border-zinc-700 dark:hover:border-zinc-500'"
                >
                    {{-- Checkbox with proper Alpine two-way binding --}}
                    <div class="absolute left-2.5 top-2.5 z-10">
                        <input
                            type="checkbox"
                            :value="item.id"
                            x-model="selected"
                            class="size-4.5 rounded border-zinc-300 text-red-600 focus:ring-red-500 cursor-pointer shadow-sm"
                            :aria-label="'Select ' + item.title"
                        >
                    </div>

                    {{-- Badges --}}
                    <div class="absolute right-2 top-2 z-10 flex flex-col items-end gap-1">
                        <template x-if="item.is_cover">
                            <span class="rounded bg-red-600 px-1.5 py-0.5 text-[10px] font-bold uppercase tracking-wider text-white shadow-sm">
                                Cover
                            </span>
                        </template>
                        <template x-if="!item.is_cover && item.products_count > 0">
                            <span class="rounded bg-zinc-900/80 px-1.5 py-0.5 text-[10px] font-medium text-white backdrop-blur-sm shadow-sm dark:bg-zinc-100/90 dark:text-zinc-900" x-text="item.products_count + ' prod' + (item.products_count > 1 ? 's' : '')">
                            </span>
                        </template>
                        <template x-if="item.brands_as_image_count > 0 || item.brands_as_logo_count > 0">
                            <span class="rounded bg-blue-600 px-1.5 py-0.5 text-[10px] font-medium text-white shadow-sm">
                                Brand
                            </span>
                        </template>
                        <template x-if="item.categories_as_image_count > 0">
                            <span class="rounded bg-indigo-600 px-1.5 py-0.5 text-[10px] font-medium text-white shadow-sm">
                                Category
                            </span>
                        </template>
                        <template x-if="item.products_count === 0 && item.brands_as_image_count === 0 && item.brands_as_logo_count === 0 && item.categories_as_image_count === 0">
                            <span class="rounded bg-amber-500/90 px-1.5 py-0.5 text-[10px] font-medium text-white shadow-sm">
                                Unattached
                            </span>
                        </template>
                        <template x-if="item.is_whatsapp">
                            <span class="rounded bg-emerald-600 px-1.5 py-0.5 text-[9px] font-semibold uppercase text-white shadow-sm">
                                WhatsApp
                            </span>
                        </template>
                    </div>

                    {{-- Thumbnail Button --}}
                    <button
                        type="button"
                        class="flex aspect-square w-full items-center justify-center bg-zinc-50 p-2 text-left focus:outline-none dark:bg-zinc-950"
                        @click="inspect(item)"
                    >
                        <template x-if="item.mime_type.startsWith('image/')">
                            <img
                                :src="item.url"
                                :alt="item.alt_text"
                                class="max-h-full max-w-full rounded object-contain transition-transform group-hover:scale-105"
                                loading="lazy"
                            >
                        </template>
                        <template x-if="!item.mime_type.startsWith('image/')">
                            <div class="flex flex-col items-center justify-center text-zinc-400">
                                <flux:icon.document class="size-8" />
                                <span class="mt-1 text-[11px] font-medium" x-text="item.mime_type"></span>
                            </div>
                        </template>
                    </button>

                    {{-- Metadata info footer --}}
                    <div
                        class="cursor-pointer border-t border-zinc-100 p-2.5 dark:border-zinc-800"
                        @click="inspect(item)"
                    >
                        <p class="truncate text-xs font-medium text-zinc-900 dark:text-zinc-100" :title="item.title" x-text="item.title"></p>
                        <p class="mt-0.5 truncate text-[11px] text-zinc-500 dark:text-zinc-400">
                            <template x-if="item.products.length > 0">
                                <span x-text="item.products[0].name"></span>
                            </template>
                            <template x-if="item.products.length === 0 && item.brands_as_logo.length > 0">
                                <span x-text="item.brands_as_logo[0].name + ' (Logo)'"></span>
                            </template>
                            <template x-if="item.products.length === 0 && item.brands_as_logo.length === 0 && item.brands_as_image.length > 0">
                                <span x-text="item.brands_as_image[0].name + ' (Photo)'"></span>
                            </template>
                            <template x-if="item.products.length === 0 && item.brands_as_logo.length === 0 && item.brands_as_image.length === 0 && item.categories_as_image.length > 0">
                                <span x-text="item.categories_as_image[0].name"></span>
                            </template>
                            <template x-if="item.products.length === 0 && item.brands_as_logo.length === 0 && item.brands_as_image.length === 0 && item.categories_as_image.length === 0">
                                <span class="text-amber-600 dark:text-amber-400">Not attached</span>
                            </template>
                        </p>
                    </div>
                </div>
            </template>
        </div>

        {{-- Empty state --}}
        <div
            x-cloak
            x-show="!loading && items.length === 0"
            class="py-16 text-center text-zinc-500 dark:text-zinc-400"
        >
            <flux:icon.photo class="mx-auto size-12 opacity-40" />
            <p class="mt-3 text-base font-medium">No media found</p>
            <p class="mt-1 text-sm text-zinc-400">Try adjusting your search terms or filters.</p>
        </div>

        {{-- Numbered Pagination Bar --}}
        <div
            x-cloak
            x-show="total > 0"
            class="mt-6 flex flex-wrap items-center justify-between gap-4 border-t border-zinc-200 pt-4 dark:border-zinc-800 text-xs text-zinc-500 dark:text-zinc-400"
        >
            <div>
                Showing <span class="font-medium text-zinc-900 dark:text-zinc-100" x-text="from"></span>
                to <span class="font-medium text-zinc-900 dark:text-zinc-100" x-text="to"></span>
                of <span class="font-medium text-zinc-900 dark:text-zinc-100" x-text="total"></span> items
            </div>

            <div class="flex items-center gap-1.5">
                {{-- Previous button --}}
                <button
                    type="button"
                    class="admin-secondary px-2.5 py-1 text-xs"
                    :disabled="page <= 1"
                    @click="changePage(page - 1)"
                >
                    &larr; Prev
                </button>

                {{-- Page numbers --}}
                <template x-for="p in paginationPages" :key="p">
                    <div>
                        <template x-if="p === '...'">
                            <span class="px-2 py-1 text-zinc-400">&hellip;</span>
                        </template>
                        <template x-if="p !== '...'">
                            <button
                                type="button"
                                class="rounded-md px-2.5 py-1 text-xs font-semibold transition-colors"
                                :class="p === page ? 'bg-red-600 text-white' : 'hover:bg-zinc-100 dark:hover:bg-zinc-800 text-zinc-700 dark:text-zinc-300'"
                                @click="changePage(p)"
                                x-text="p"
                            ></button>
                        </template>
                    </div>
                </template>

                {{-- Next button --}}
                <button
                    type="button"
                    class="admin-secondary px-2.5 py-1 text-xs"
                    :disabled="page >= lastPage"
                    @click="changePage(page + 1)"
                >
                    Next &rarr;
                </button>
            </div>
        </div>
    </div>

    {{-- Detail Inspector Modal --}}
    <template x-if="inspecting">
        <div
            class="fixed inset-0 z-50 flex items-center justify-center overflow-y-auto bg-black/60 p-4 backdrop-blur-xs"
            @keydown.escape.window="closeInspector()"
            role="dialog"
            aria-modal="true"
        >
            <div
                class="relative max-h-[92vh] w-full max-w-5xl overflow-hidden rounded-2xl bg-white shadow-2xl dark:bg-zinc-900 flex flex-col"
                @click.stop
            >
                {{-- Modal Header --}}
                <div class="flex items-center justify-between border-b border-zinc-200 px-6 py-4 dark:border-zinc-800">
                    <div>
                        <h2 class="text-lg font-semibold text-zinc-900 dark:text-zinc-100">
                            Attachment Details
                        </h2>
                        <p class="text-xs text-zinc-500 dark:text-zinc-400">
                            ID: <code class="font-mono text-[11px]" x-text="inspecting.id"></code>
                        </p>
                    </div>
                    <button
                        type="button"
                        class="rounded-lg p-1.5 text-zinc-400 hover:bg-zinc-100 hover:text-zinc-700 dark:hover:bg-zinc-800 dark:hover:text-zinc-200"
                        @click="closeInspector()"
                        aria-label="Close details"
                    >
                        <flux:icon.x-mark class="size-5" />
                    </button>
                </div>

                {{-- Modal Body --}}
                <div class="overflow-y-auto p-6">
                    <div class="grid gap-6 lg:grid-cols-12">
                        {{-- Left Column: Large Preview & File Info --}}
                        <div class="space-y-4 lg:col-span-7">
                            <div class="flex max-h-[460px] items-center justify-center overflow-hidden rounded-xl border border-zinc-200 bg-zinc-100 p-3 dark:border-zinc-800 dark:bg-zinc-950">
                                <template x-if="inspecting.mime_type.startsWith('image/')">
                                    <img
                                        :src="inspecting.url"
                                        :alt="inspecting.alt_text"
                                        class="max-h-[430px] w-auto max-w-full rounded object-contain"
                                    >
                                </template>
                                <template x-if="!inspecting.mime_type.startsWith('image/')">
                                    <div class="py-12 text-center text-zinc-400">
                                        <flux:icon.document class="mx-auto size-16" />
                                        <p class="mt-2 text-sm" x-text="inspecting.mime_type"></p>
                                    </div>
                                </template>
                            </div>

                            <div class="flex items-center justify-between text-xs text-zinc-500">
                                <a
                                    :href="inspecting.url"
                                    target="_blank"
                                    rel="noopener"
                                    class="admin-link inline-flex items-center gap-1"
                                >
                                    View full size <flux:icon.arrow-top-right-on-square class="size-3.5" />
                                </a>
                                <span x-text="'Size: ' + inspecting.file_size + ' · ' + inspecting.mime_type"></span>
                            </div>

                            <div class="rounded-xl border border-zinc-200 bg-zinc-50 p-3 text-xs dark:border-zinc-800 dark:bg-zinc-950 space-y-1.5 font-mono text-zinc-600 dark:text-zinc-400 break-all">
                                <div><span class="font-sans font-semibold text-zinc-700 dark:text-zinc-300">Storage path:</span> <span x-text="inspecting.storage_path"></span></div>
                                <template x-if="inspecting.source_path">
                                    <div><span class="font-sans font-semibold text-zinc-700 dark:text-zinc-300">Source:</span> <span x-text="inspecting.source_path"></span></div>
                                </template>
                                <template x-if="inspecting.checksum">
                                    <div><span class="font-sans font-semibold text-zinc-700 dark:text-zinc-300">SHA256:</span> <span x-text="inspecting.checksum"></span></div>
                                </template>
                                <div><span class="font-sans font-semibold text-zinc-700 dark:text-zinc-300">Created:</span> <span x-text="inspecting.created_at"></span></div>
                            </div>
                        </div>

                        {{-- Right Column: Editable Metadata & Usage --}}
                        <div class="space-y-6 lg:col-span-5">
                            {{-- Edit Metadata Form --}}
                            <div class="space-y-4 rounded-xl border border-zinc-200 p-4 dark:border-zinc-800">
                                <h3 class="text-sm font-semibold text-zinc-900 dark:text-zinc-100">Image Metadata</h3>
                                <x-admin.field label="Title">
                                    <input class="admin-input" x-model="inspectTitle" placeholder="Image title or headline">
                                </x-admin.field>
                                <x-admin.field label="Alt Text" hint="Descriptive text for accessibility and SEO.">
                                    <input class="admin-input" x-model="inspectAltText" placeholder="Describe the image content">
                                </x-admin.field>
                                <button
                                    type="button"
                                    class="admin-secondary w-full justify-center text-xs"
                                    :disabled="savingMetadata"
                                    @click="saveMetadata()"
                                >
                                    <span x-text="savingMetadata ? 'Saving…' : 'Save metadata'"></span>
                                </button>
                            </div>

                            {{-- "Used In" Section --}}
                            <div class="space-y-3 rounded-xl border border-zinc-200 p-4 dark:border-zinc-800">
                                <h3 class="text-sm font-semibold text-zinc-900 dark:text-zinc-100">Where this image is used</h3>

                                <template x-if="inspecting.products.length === 0 && inspecting.brands_as_logo.length === 0 && inspecting.brands_as_image.length === 0 && inspecting.categories_as_image.length === 0">
                                    <div class="rounded-lg bg-amber-50 p-3 text-xs text-amber-800 dark:bg-amber-950/40 dark:text-amber-300">
                                        This image is not currently attached to any product, brand, or category.
                                    </div>
                                </template>

                                <div class="space-y-2 max-h-56 overflow-y-auto">
                                    {{-- Products --}}
                                    <template x-for="p in inspecting.products" :key="p.id">
                                        <div class="flex items-center justify-between rounded-lg border border-zinc-100 bg-zinc-50 p-2 text-xs dark:border-zinc-800 dark:bg-zinc-950">
                                            <div class="min-w-0 pr-2">
                                                <a :href="p.edit_url" class="admin-link block truncate font-medium" x-text="p.name"></a>
                                                <span class="text-[11px] text-zinc-500" x-text="p.is_cover ? 'Cover image' : 'Gallery position #' + (p.sort_order + 1)"></span>
                                            </div>
                                            <button
                                                type="button"
                                                class="shrink-0 text-[11px] font-semibold text-red-600 hover:underline"
                                                @click="detachFromProduct(inspecting.id, p.id, p.name)"
                                            >
                                                Detach
                                            </button>
                                        </div>
                                    </template>

                                    {{-- Brands Logo --}}
                                    <template x-for="b in inspecting.brands_as_logo" :key="'logo-' + b.id">
                                        <div class="flex items-center justify-between rounded-lg border border-zinc-100 bg-zinc-50 p-2 text-xs dark:border-zinc-800 dark:bg-zinc-950">
                                            <a :href="b.edit_url" class="admin-link truncate font-medium" x-text="b.name"></a>
                                            <span class="rounded bg-blue-100 px-1.5 py-0.5 text-[10px] font-semibold text-blue-800 dark:bg-blue-900/40 dark:text-blue-300">Brand Logo</span>
                                        </div>
                                    </template>

                                    {{-- Brands Image --}}
                                    <template x-for="b in inspecting.brands_as_image" :key="'img-' + b.id">
                                        <div class="flex items-center justify-between rounded-lg border border-zinc-100 bg-zinc-50 p-2 text-xs dark:border-zinc-800 dark:bg-zinc-950">
                                            <a :href="b.edit_url" class="admin-link truncate font-medium" x-text="b.name"></a>
                                            <span class="rounded bg-blue-100 px-1.5 py-0.5 text-[10px] font-semibold text-blue-800 dark:bg-blue-900/40 dark:text-blue-300">Brand Card Photo</span>
                                        </div>
                                    </template>

                                    {{-- Categories Image --}}
                                    <template x-for="c in inspecting.categories_as_image" :key="'cat-' + c.id">
                                        <div class="flex items-center justify-between rounded-lg border border-zinc-100 bg-zinc-50 p-2 text-xs dark:border-zinc-800 dark:bg-zinc-950">
                                            <a :href="c.edit_url" class="admin-link truncate font-medium" x-text="c.name"></a>
                                            <span class="rounded bg-indigo-100 px-1.5 py-0.5 text-[10px] font-semibold text-indigo-800 dark:bg-indigo-900/40 dark:text-indigo-300">Category Image</span>
                                        </div>
                                    </template>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Modal Footer --}}
                <div class="flex items-center justify-between border-t border-zinc-200 bg-zinc-50 px-6 py-3 dark:border-zinc-800 dark:bg-zinc-950">
                    <button
                        type="button"
                        class="text-xs font-semibold text-red-600 hover:text-red-700"
                        @click="deleteSingle(inspecting.id)"
                    >
                        Detach & Delete Permanently
                    </button>
                    <button
                        type="button"
                        class="admin-secondary text-xs"
                        @click="closeInspector()"
                    >
                        Close
                    </button>
                </div>
            </div>
        </div>
    </template>
</div>
</x-layouts::app>
