@props(['label', 'hint' => null, 'model', 'gallery' => []])
@php($images = array_values(array_filter($gallery, fn (array $asset): bool => str_starts_with((string) ($asset['mime_type'] ?? ''), 'image/'))))
<div {{ $attributes->class(['block space-y-2']) }} data-admin-rich-text x-data="adminRichText(@js($model))">
    <div class="flex flex-wrap items-center justify-between gap-2">
        <label class="block text-sm font-medium" :for="$id('rich-text-source')">{{ $label }}</label>
        <div class="admin-wysiwyg-tabs" role="tablist" aria-label="{{ $label }} editor mode">
            <button type="button" role="tab" class="admin-wysiwyg-tab" :class="{ 'is-active': mode === 'visual' }" :aria-selected="mode === 'visual' ? 'true' : 'false'" @click="setMode('visual')">Visual</button>
            <button type="button" role="tab" class="admin-wysiwyg-tab" :class="{ 'is-active': mode === 'html' }" :aria-selected="mode === 'html' ? 'true' : 'false'" @click="setMode('html')">HTML</button>
        </div>
    </div>

    <div class="admin-wysiwyg" x-show="mode === 'visual'" x-cloak>
        <div class="admin-wysiwyg-toolbar" role="toolbar" aria-label="{{ $label }} formatting">
            <div class="admin-wysiwyg-group">
                <button type="button" class="admin-wysiwyg-btn" :class="{ 'is-active': isActive('paragraph') }" @click="command('paragraph')" title="Paragraph" aria-label="Paragraph">P</button>
                <button type="button" class="admin-wysiwyg-btn" :class="{ 'is-active': isActive('heading', { level: 2 }) }" @click="command('heading', { level: 2 })" title="Heading 2" aria-label="Heading 2">H2</button>
                <button type="button" class="admin-wysiwyg-btn" :class="{ 'is-active': isActive('heading', { level: 3 }) }" @click="command('heading', { level: 3 })" title="Heading 3" aria-label="Heading 3">H3</button>
                <button type="button" class="admin-wysiwyg-btn" :class="{ 'is-active': isActive('heading', { level: 4 }) }" @click="command('heading', { level: 4 })" title="Heading 4" aria-label="Heading 4">H4</button>
            </div>

            <div class="admin-wysiwyg-group">
                <button type="button" class="admin-wysiwyg-btn font-bold" :class="{ 'is-active': isActive('bold') }" @click="command('bold')" title="Bold" aria-label="Bold">B</button>
                <button type="button" class="admin-wysiwyg-btn italic" :class="{ 'is-active': isActive('italic') }" @click="command('italic')" title="Italic" aria-label="Italic">I</button>
                <button type="button" class="admin-wysiwyg-btn underline" :class="{ 'is-active': isActive('underline') }" @click="command('underline')" title="Underline" aria-label="Underline">U</button>
                <button type="button" class="admin-wysiwyg-btn" :class="{ 'is-active': isActive('superscript') }" @click="command('superscript')" title="Superscript" aria-label="Superscript">X<sup>2</sup></button>
                <button type="button" class="admin-wysiwyg-btn" :class="{ 'is-active': isActive('subscript') }" @click="command('subscript')" title="Subscript" aria-label="Subscript">X<sub>2</sub></button>
                <button type="button" class="admin-wysiwyg-btn" @click="command('clear')" title="Clear formatting" aria-label="Clear formatting">Tx</button>
            </div>

            <div class="admin-wysiwyg-group">
                <button type="button" class="admin-wysiwyg-btn" :class="{ 'is-active': isActive('bulletList') }" @click="command('bulletList')" title="Bullet list" aria-label="Bullet list">&bull; List</button>
                <button type="button" class="admin-wysiwyg-btn" :class="{ 'is-active': isActive('orderedList') }" @click="command('orderedList')" title="Numbered list" aria-label="Numbered list">1. List</button>
                <button type="button" class="admin-wysiwyg-btn" :class="{ 'is-active': isActive('blockquote') }" @click="command('blockquote')" title="Blockquote" aria-label="Blockquote">&ldquo; Quote</button>
                <button type="button" class="admin-wysiwyg-btn" @click="command('hr')" title="Horizontal divider" aria-label="Horizontal divider">&mdash; Rule</button>
            </div>

            <div class="admin-wysiwyg-group">
                <button type="button" class="admin-wysiwyg-btn" :class="{ 'is-active': isActive('link') || popover === 'link' }" @click="openLinkPopover()" title="Insert or edit link" aria-label="Insert or edit link">Link</button>
                <button type="button" class="admin-wysiwyg-btn" x-show="isActive('link')" @click="removeLink()" title="Remove link" aria-label="Remove link">Unlink</button>
                <button type="button" class="admin-wysiwyg-btn" :class="{ 'is-active': popover === 'image' }" @click="openImagePopover()" title="Insert image" aria-label="Insert image">Image</button>
                <button type="button" class="admin-wysiwyg-btn" :class="{ 'is-active': isActive('table') }" @click="command('insertTable')" title="Insert specification table" aria-label="Insert specification table">+ Table</button>
            </div>

            <div class="admin-wysiwyg-group ml-auto">
                <button type="button" class="admin-wysiwyg-btn" @click="command('undo')" :disabled="!canUndo" title="Undo" aria-label="Undo">&#8630;</button>
                <button type="button" class="admin-wysiwyg-btn" @click="command('redo')" :disabled="!canRedo" title="Redo" aria-label="Redo">&#8631;</button>
            </div>
        </div>

        <div class="admin-wysiwyg-subtoolbar" x-show="isActive('table')" x-cloak role="toolbar" aria-label="Table controls">
            <span class="text-xs font-semibold text-zinc-500 dark:text-zinc-400">Table:</span>
            <button type="button" class="admin-wysiwyg-btn" @click="command('addRowBefore')">+ Row above</button>
            <button type="button" class="admin-wysiwyg-btn" @click="command('addRowAfter')">+ Row below</button>
            <button type="button" class="admin-wysiwyg-btn" @click="command('deleteRow')">Delete row</button>
            <button type="button" class="admin-wysiwyg-btn" @click="command('addColumnBefore')">+ Col left</button>
            <button type="button" class="admin-wysiwyg-btn" @click="command('addColumnAfter')">+ Col right</button>
            <button type="button" class="admin-wysiwyg-btn" @click="command('deleteColumn')">Delete col</button>
            <button type="button" class="admin-wysiwyg-btn" @click="command('toggleHeaderRow')">Header row</button>
            <button type="button" class="admin-wysiwyg-btn" @click="command('toggleHeaderCell')">Header cell</button>
            <button type="button" class="admin-wysiwyg-btn" @click="command('mergeOrSplit')">Merge / Split</button>
            <button type="button" class="admin-wysiwyg-btn text-red-600 dark:text-rose-400" @click="command('deleteTable')">Delete table</button>
        </div>

        <div class="admin-wysiwyg-popover" x-show="popover === 'link'" x-cloak @keydown.escape.stop.prevent="closePopover()">
            <div class="grid gap-3 sm:grid-cols-2">
                <div>
                    <label class="mb-1 block text-xs font-medium">Link URL</label>
                    <input type="text" class="admin-input" x-ref="linkHref" x-model="linkForm.href" placeholder="https://… or /machinery/…" @keydown.enter.prevent="applyLink()">
                </div>
                <div>
                    <label class="mb-1 block text-xs font-medium">Title (optional)</label>
                    <input type="text" class="admin-input" x-model="linkForm.title" placeholder="Accessible link title" @keydown.enter.prevent="applyLink()">
                </div>
            </div>
            <div class="mt-3 flex flex-wrap items-center gap-2">
                <button type="button" class="admin-primary py-1.5 text-xs" @click="applyLink()">Apply link</button>
                <button type="button" class="admin-secondary py-1.5 text-xs" x-show="isActive('link')" @click="removeLink()">Remove link</button>
                <button type="button" class="admin-secondary py-1.5 text-xs" @click="closePopover()">Cancel</button>
            </div>
        </div>

        <div class="admin-wysiwyg-popover" x-show="popover === 'image'" x-cloak @keydown.escape.stop.prevent="closePopover()">
            <div class="grid gap-3 sm:grid-cols-2">
                <div>
                    <label class="mb-1 block text-xs font-medium">Image URL or storage path</label>
                    <input type="text" class="admin-input" x-ref="imageSrc" x-model="imageForm.src" placeholder="/storage/products/… or https://…" @keydown.enter.prevent="applyImage()">
                </div>
                <div>
                    <label class="mb-1 block text-xs font-medium">Alt text</label>
                    <input type="text" class="admin-input" x-model="imageForm.alt" placeholder="Describe the image" @keydown.enter.prevent="applyImage()">
                </div>
            </div>
            @if($images)
                <div class="mt-3">
                    <p class="mb-2 text-xs font-medium text-zinc-500 dark:text-zinc-400">Or pick an existing gallery image:</p>
                    <div class="flex flex-wrap gap-2">
                        @foreach($images as $index => $asset)
                            <button type="button" class="admin-wysiwyg-gallery-pick" @click="selectGalleryImage(@js($asset['url']), @js($asset['alt_text'] ?? ''))" title="Use gallery image {{ $index + 1 }}">
                                <img src="{{ $asset['url'] }}" alt="{{ $asset['alt_text'] ?: 'Gallery image '.($index + 1) }}" class="h-10 w-14 rounded object-contain bg-white">
                            </button>
                        @endforeach
                    </div>
                </div>
            @endif
            <div class="mt-3 flex flex-wrap items-center gap-2">
                <button type="button" class="admin-primary py-1.5 text-xs" @click="applyImage()">Insert image</button>
                <button type="button" class="admin-secondary py-1.5 text-xs" @click="closePopover()">Cancel</button>
            </div>
        </div>

        <div wire:ignore>
            <div x-ref="editor" class="admin-wysiwyg-content admin-preview" @focusout="flushPreview()"></div>
        </div>
    </div>

    <div x-show="mode === 'html'">
        <textarea
            :id="$id('rich-text-source')"
            x-ref="source"
            class="admin-input font-mono text-sm"
            rows="12"
            wire:model.blur="{{ $model }}"
            @input="onSourceInput($event.target.value)"
        ></textarea>
    </div>

    @if($hint)<span class="block text-xs text-zinc-500 dark:text-zinc-400">{{ $hint }}</span>@endif
</div>
