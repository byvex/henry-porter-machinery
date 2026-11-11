<div class="admin-shell space-y-6">
    <div class="flex flex-wrap items-end justify-between gap-4">
        <div>
            <p class="admin-eyebrow">Catalogue</p>
            <h1 class="mt-2 text-3xl font-semibold tracking-tight">Bulk Import & Export</h1>
            <p class="mt-2 text-sm text-zinc-500 dark:text-zinc-400">
                Update products, specifications, and image galleries in bulk using Excel (.xlsx) or CSV spreadsheets.
            </p>
        </div>
        <div class="flex gap-2">
            <a class="admin-secondary inline-flex items-center gap-1.5" href="{{ route('admin.catalogue.index', 'products') }}" wire:navigate>
                &larr; Back to Products
            </a>
        </div>
    </div>

    <x-admin.feedback />

    <!-- Tab navigation -->
    <div class="border-b border-zinc-200 dark:border-zinc-800 flex gap-4 text-sm font-medium">
        <button type="button"
                wire:click="$set('activeTab', 'import')"
                class="pb-3 border-b-2 transition-colors {{ $activeTab === 'import' ? 'border-red-600 text-red-600 font-semibold' : 'border-transparent text-zinc-500 hover:text-zinc-700 dark:text-zinc-400 dark:hover:text-zinc-200' }}">
            Import / Bulk Update
        </button>
        <button type="button"
                wire:click="$set('activeTab', 'export')"
                class="pb-3 border-b-2 transition-colors {{ $activeTab === 'export' ? 'border-red-600 text-red-600 font-semibold' : 'border-transparent text-zinc-500 hover:text-zinc-700 dark:text-zinc-400 dark:hover:text-zinc-200' }}">
            Export & Templates
        </button>
    </div>

    @if ($activeTab === 'import')
        <!-- Import Tab -->
        <div class="space-y-6">
            @if ($importCompleted)
                <div class="rounded-xl border border-emerald-500/30 bg-emerald-50/50 p-6 dark:bg-emerald-950/20">
                    <div class="flex items-start gap-4">
                        <div class="rounded-full bg-emerald-100 p-2 text-emerald-600 dark:bg-emerald-900/50">
                            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                        </div>
                        <div class="flex-1">
                            <h3 class="text-lg font-semibold text-emerald-900 dark:text-emerald-100">Import Completed Successfully</h3>
                            <div class="mt-2 text-sm text-emerald-800 dark:text-emerald-200 space-y-1">
                                <p><strong>{{ $importResult['created'] ?? 0 }}</strong> products created</p>
                                <p><strong>{{ $importResult['updated'] ?? 0 }}</strong> products updated</p>
                                @if (!empty($importResult['failed']))
                                    <p class="text-amber-600 font-semibold"><strong>{{ $importResult['failed'] }}</strong> rows skipped or failed</p>
                                @endif
                            </div>

                            @if (!empty($importResult['errors']))
                                <div class="mt-4 rounded-lg bg-amber-50 p-3 text-xs text-amber-900 dark:bg-amber-950/40 dark:text-amber-200">
                                    <p class="font-semibold">Issues during import:</p>
                                    <ul class="mt-1 list-disc pl-4 space-y-0.5">
                                        @foreach ($importResult['errors'] as $err)
                                            <li>{{ $err }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif

                            <div class="mt-4 flex gap-3">
                                <a href="{{ route('admin.catalogue.index', 'products') }}" class="admin-primary text-xs" wire:navigate>
                                    View Products Catalogue
                                </a>
                                <button type="button" wire:click="resetValidationState" class="admin-secondary text-xs">
                                    Import Another Sheet
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            <div class="admin-panel space-y-6">
                <div class="flex items-center justify-between">
                    <div>
                        <h2 class="text-lg font-semibold">1. Choose Spreadsheet File</h2>
                        <p class="text-sm text-zinc-500">Upload an Excel (.xlsx) or CSV file with product details and image URLs.</p>
                    </div>
                    <button type="button" wire:click="downloadTemplate" class="text-xs text-red-600 hover:underline">
                        Download sample template (.xlsx)
                    </button>
                </div>

                <div class="space-y-4">
                    <x-admin.field label="Spreadsheet File (.xlsx, .csv)" error="uploadFile">
                        <input type="file"
                               wire:model="uploadFile"
                               accept=".xlsx,.csv,text/csv,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet"
                               class="admin-input block w-full text-sm text-zinc-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-xs file:font-semibold file:bg-zinc-100 hover:file:bg-zinc-200 dark:file:bg-zinc-800 dark:hover:file:bg-zinc-700">
                    </x-admin.field>

                    <div wire:loading wire:target="uploadFile" class="text-xs text-zinc-500">
                        Uploading file for inspection...
                    </div>

                    <div class="rounded-lg border border-zinc-200 p-4 dark:border-zinc-800 space-y-3">
                        <label class="flex items-start gap-3 cursor-pointer">
                            <input type="checkbox" wire:model.live="replaceGallery" class="mt-1 rounded border-zinc-300 text-red-600">
                            <div>
                                <span class="text-sm font-medium">Replace existing galleries</span>
                                <p class="text-xs text-zinc-500">
                                    When checked, any product matched in the sheet will have its existing gallery replaced with the images in the sheet.
                                    When unchecked (recommended default), sheet images are appended without removing existing photos.
                                </p>
                            </div>
                        </label>
                    </div>

                    <div class="flex gap-3">
                        <button type="button"
                                wire:click="validateSpreadsheet"
                                wire:loading.attr="disabled"
                                class="admin-primary">
                            <span wire:loading.remove wire:target="validateSpreadsheet">Validate & Preview Sheet</span>
                            <span wire:loading wire:target="validateSpreadsheet">Inspecting file...</span>
                        </button>
                        @if ($hasParsed)
                            <button type="button" wire:click="resetValidationState" class="admin-secondary">
                                Reset
                            </button>
                        @endif
                    </div>
                </div>
            </div>

            @if ($hasParsed)
                <!-- Validation Summary & Preview -->
                <div class="admin-panel space-y-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <h2 class="text-lg font-semibold">2. Validation Summary & Preview</h2>
                            <p class="text-sm text-zinc-500">Review matched records and detected images before writing to the database.</p>
                        </div>
                    </div>

                    <!-- Counters -->
                    <div class="grid grid-cols-3 gap-4">
                        <div class="rounded-lg bg-zinc-50 p-3 dark:bg-zinc-900">
                            <p class="text-xs text-zinc-500">Total Rows</p>
                            <p class="text-xl font-bold">{{ $validationSummary['total'] ?? 0 }}</p>
                        </div>
                        <div class="rounded-lg bg-emerald-50 p-3 dark:bg-emerald-950/30">
                            <p class="text-xs text-emerald-600 dark:text-emerald-400">Updating Existing</p>
                            <p class="text-xl font-bold text-emerald-700 dark:text-emerald-300">{{ $validationSummary['to_update'] ?? 0 }}</p>
                        </div>
                        <div class="rounded-lg bg-blue-50 p-3 dark:bg-blue-950/30">
                            <p class="text-xs text-blue-600 dark:text-blue-400">New Products</p>
                            <p class="text-xl font-bold text-blue-700 dark:text-blue-300">{{ $validationSummary['to_create'] ?? 0 }}</p>
                        </div>
                    </div>

                    @if (!empty($validationWarnings))
                        <div class="rounded-lg border border-amber-300 bg-amber-50 p-4 dark:border-amber-700 dark:bg-amber-950/30">
                            <p class="text-sm font-semibold text-amber-800 dark:text-amber-200">Warnings ({{ count($validationWarnings) }}):</p>
                            <ul class="mt-2 list-disc pl-5 text-xs text-amber-700 dark:text-amber-300 space-y-1">
                                @foreach (array_slice($validationWarnings, 0, 10) as $warn)
                                    <li>{{ $warn }}</li>
                                @endforeach
                                @if (count($validationWarnings) > 10)
                                    <li>...and {{ count($validationWarnings) - 10 }} more warnings</li>
                                @endif
                            </ul>
                        </div>
                    @endif

                    @if (!empty($validationErrors))
                        <div class="rounded-lg border border-red-300 bg-red-50 p-4 dark:border-red-700 dark:bg-red-950/30">
                            <p class="text-sm font-semibold text-red-800 dark:text-red-200">Errors ({{ count($validationErrors) }}):</p>
                            <ul class="mt-2 list-disc pl-5 text-xs text-red-700 dark:text-red-300 space-y-1">
                                @foreach ($validationErrors as $err)
                                    <li>{{ $err }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <!-- Preview Table -->
                    <div class="overflow-x-auto rounded-lg border border-zinc-200 dark:border-zinc-800">
                        <table class="w-full text-left text-xs">
                            <thead class="bg-zinc-50 uppercase text-zinc-500 dark:bg-zinc-900 dark:text-zinc-400">
                                <tr>
                                    <th class="p-3">Action</th>
                                    <th class="p-3">Product Name</th>
                                    <th class="p-3">Slug</th>
                                    <th class="p-3">Brand</th>
                                    <th class="p-3">Categories</th>
                                    <th class="p-3 text-center">Cover</th>
                                    <th class="p-3 text-center">Gallery</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-zinc-200 dark:divide-zinc-800">
                                @foreach ($validationRows as $row)
                                    <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-900/50">
                                        <td class="p-3">
                                            @if (($row['action'] ?? '') === 'update')
                                                <span class="inline-flex items-center rounded-md bg-emerald-50 px-2 py-1 text-xs font-medium text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-300">
                                                    Update
                                                </span>
                                            @else
                                                <span class="inline-flex items-center rounded-md bg-blue-50 px-2 py-1 text-xs font-medium text-blue-700 dark:bg-blue-950/40 dark:text-blue-300">
                                                    Create
                                                </span>
                                            @endif
                                        </td>
                                        <td class="p-3 font-medium">{{ $row['name'] }}</td>
                                        <td class="p-3 text-zinc-500">{{ $row['slug'] }}</td>
                                        <td class="p-3">{{ $row['brand'] ?? '—' }}</td>
                                        <td class="p-3">{{ $row['categories'] ?? '—' }}</td>
                                        <td class="p-3 text-center">
                                            @if (!empty($row['cover_image_url']))
                                                <span class="text-emerald-600 font-semibold">&check;</span>
                                            @else
                                                <span class="text-zinc-400">&mdash;</span>
                                            @endif
                                        </td>
                                        <td class="p-3 text-center">
                                            @php($galleryCount = count(array_filter(preg_split('/[\r\n;,]+/', (string) ($row['gallery_image_urls'] ?? '')))))
                                            <span class="font-medium {{ $galleryCount > 0 ? 'text-zinc-900 dark:text-zinc-100' : 'text-zinc-400' }}">
                                                {{ $galleryCount }}
                                            </span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <!-- Confirm Action -->
                    <div class="flex items-center justify-between pt-4 border-t border-zinc-200 dark:border-zinc-800">
                        <div class="text-xs text-zinc-500">
                            @if ($replaceGallery)
                                <span class="text-amber-600 font-medium">&bull; Gallery replace is ON: existing photos will be detached and replaced.</span>
                            @else
                                <span>&bull; Gallery append is ON: existing photos are preserved; new photos added.</span>
                            @endif
                        </div>
                        <button type="button"
                                wire:click="executeImport"
                                wire:loading.attr="disabled"
                                class="admin-primary">
                            <span wire:loading.remove wire:target="executeImport">Confirm & Run Import Now</span>
                            <span wire:loading wire:target="executeImport">Processing import & downloading images...</span>
                        </button>
                    </div>
                </div>
            @endif
        </div>
    @else
        <!-- Export Tab -->
        <div class="space-y-6">
            <div class="admin-panel space-y-6">
                <div>
                    <h2 class="text-lg font-semibold">Export Catalogue Products</h2>
                    <p class="text-sm text-zinc-500">
                        Download your existing catalogue products as an Excel spreadsheet or CSV file. The file includes product IDs, slugs, names, specs, cover URLs, and gallery URLs.
                    </p>
                </div>

                <div class="grid gap-4 sm:grid-cols-2">
                    <x-admin.field label="Filter by Brand (Optional)">
                        <select class="admin-input" wire:model.live="exportBrandId">
                            <option value="">All Brands ({{ $totalProducts }} products)</option>
                            @foreach ($brands as $b)
                                <option value="{{ $b->id }}">{{ $b->name }}</option>
                            @endforeach
                        </select>
                    </x-admin.field>
                </div>

                <div class="flex flex-wrap gap-3 pt-2">
                    <button type="button" wire:click="exportExcel" class="admin-primary inline-flex items-center gap-2">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                        </svg>
                        Download Excel (.xlsx)
                    </button>
                    <button type="button" wire:click="exportCsv" class="admin-secondary inline-flex items-center gap-2">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                        </svg>
                        Download CSV (.csv)
                    </button>
                </div>
            </div>

            <div class="admin-panel space-y-4">
                <div>
                    <h2 class="text-lg font-semibold">Blank Product Template</h2>
                    <p class="text-sm text-zinc-500">
                        Get a clean, formatted template with columns for ID, slug, name, brand, category, condition, price, cover image, gallery images, specifications, and descriptions.
                    </p>
                </div>
                <div>
                    <button type="button" wire:click="downloadTemplate" class="admin-secondary inline-flex items-center gap-2">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        Download Blank Template (.xlsx)
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
