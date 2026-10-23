import { Editor } from '@tiptap/core';
import Image from '@tiptap/extension-image';
import Link from '@tiptap/extension-link';
import Subscript from '@tiptap/extension-subscript';
import Superscript from '@tiptap/extension-superscript';
import Table from '@tiptap/extension-table';
import TableCell from '@tiptap/extension-table-cell';
import TableHeader from '@tiptap/extension-table-header';
import TableRow from '@tiptap/extension-table-row';
import Underline from '@tiptap/extension-underline';
import StarterKit from '@tiptap/starter-kit';

const CatalogueLink = Link.extend({
    addAttributes() {
        return {
            ...this.parent?.(),
            title: {
                default: null,
                parseHTML: (element) => element.getAttribute('title'),
                renderHTML: (attributes) => (attributes.title ? { title: attributes.title } : {}),
            },
        };
    },
});

function createAdminRichText(modelName) {
    let editor = null;

    return {
        mode: 'visual',
        html: '',
        tick: 0,
        canUndo: false,
        canRedo: false,
        needsPreviewSync: false,
        sourceDirty: false,
        popover: null,
        linkForm: { href: '', title: '' },
        imageForm: { src: '', alt: '' },

        init() {
            const initial = this.$wire?.get(modelName) ?? this.$refs.source?.value ?? '';
            this.html = typeof initial === 'string' ? initial : '';
            if (this.$refs.source && this.$refs.source.value !== this.html) {
                this.$refs.source.value = this.html;
            }

            editor = new Editor({
                element: this.$refs.editor,
                extensions: [
                    StarterKit.configure({
                        heading: { levels: [2, 3, 4, 5, 6] },
                        code: false,
                        codeBlock: false,
                        strike: false,
                    }),
                    Underline,
                    Subscript,
                    Superscript,
                    CatalogueLink.configure({
                        openOnClick: false,
                        autolink: false,
                    }),
                    Image.configure({
                        inline: false,
                        allowBase64: false,
                    }),
                    Table.configure({
                        resizable: false,
                    }),
                    TableRow,
                    TableHeader,
                    TableCell,
                ],
                content: this.html,
                onTransaction: () => {
                    this.tick++;
                    this.canUndo = editor ? editor.can().undo() : false;
                    this.canRedo = editor ? editor.can().redo() : false;
                },
                onUpdate: ({ editor: instance }) => {
                    const nextHtml = instance.isEmpty ? '' : instance.getHTML();
                    this.html = nextHtml;
                    if (this.$refs.source) {
                        this.$refs.source.value = nextHtml;
                    }
                    this.$wire?.set(modelName, nextHtml, false);
                    this.needsPreviewSync = true;
                },
            });
        },

        destroy() {
            if (editor) {
                editor.destroy();
                editor = null;
            }
        },

        isActive(name, attributes = {}) {
            void this.tick;
            return editor ? editor.isActive(name, attributes) : false;
        },

        setMode(nextMode) {
            if (this.mode === nextMode) {
                return;
            }
            this.closePopover();
            if (nextMode === 'html') {
                if (this.$refs.source) {
                    this.$refs.source.value = this.html;
                }
                this.flushPreview();
            } else if (nextMode === 'visual' && editor && this.sourceDirty) {
                editor.commands.setContent(this.html || '', false);
                this.sourceDirty = false;
                this.tick++;
            }
            this.mode = nextMode;
        },

        onSourceInput(value) {
            this.html = value;
            this.sourceDirty = true;
        },

        flushPreview() {
            if (!this.needsPreviewSync) {
                return;
            }
            this.needsPreviewSync = false;
            this.$wire?.set(modelName, this.html, true);
        },

        command(action, payload = {}) {
            if (!editor) {
                return;
            }
            this.closePopover();
            const chain = editor.chain().focus();
            switch (action) {
                case 'paragraph':
                    chain.setParagraph().run();
                    break;
                case 'heading':
                    chain.toggleHeading({ level: payload.level }).run();
                    break;
                case 'bold':
                    chain.toggleBold().run();
                    break;
                case 'italic':
                    chain.toggleItalic().run();
                    break;
                case 'underline':
                    chain.toggleUnderline().run();
                    break;
                case 'superscript':
                    chain.toggleSuperscript().run();
                    break;
                case 'subscript':
                    chain.toggleSubscript().run();
                    break;
                case 'clear':
                    chain.unsetAllMarks().clearNodes().run();
                    break;
                case 'bulletList':
                    chain.toggleBulletList().run();
                    break;
                case 'orderedList':
                    chain.toggleOrderedList().run();
                    break;
                case 'blockquote':
                    chain.toggleBlockquote().run();
                    break;
                case 'hr':
                    chain.setHorizontalRule().run();
                    break;
                case 'insertTable':
                    chain.insertTable({ rows: 3, cols: 2, withHeaderRow: true }).run();
                    break;
                case 'addRowBefore':
                    chain.addRowBefore().run();
                    break;
                case 'addRowAfter':
                    chain.addRowAfter().run();
                    break;
                case 'deleteRow':
                    chain.deleteRow().run();
                    break;
                case 'addColumnBefore':
                    chain.addColumnBefore().run();
                    break;
                case 'addColumnAfter':
                    chain.addColumnAfter().run();
                    break;
                case 'deleteColumn':
                    chain.deleteColumn().run();
                    break;
                case 'toggleHeaderRow':
                    chain.toggleHeaderRow().run();
                    break;
                case 'toggleHeaderCell':
                    chain.toggleHeaderCell().run();
                    break;
                case 'mergeOrSplit':
                    chain.mergeOrSplit().run();
                    break;
                case 'deleteTable':
                    chain.deleteTable().run();
                    break;
                case 'undo':
                    chain.undo().run();
                    break;
                case 'redo':
                    chain.redo().run();
                    break;
            }
        },

        openLinkPopover() {
            if (!editor) {
                return;
            }
            if (this.popover === 'link') {
                this.closePopover();
                return;
            }
            const attrs = editor.getAttributes('link');
            this.linkForm = {
                href: attrs.href || '',
                title: attrs.title || '',
            };
            this.popover = 'link';
            this.$nextTick(() => this.$refs.linkHref?.focus());
        },

        applyLink() {
            if (!editor) {
                return;
            }
            const href = (this.linkForm.href || '').trim();
            const title = (this.linkForm.title || '').trim();
            if (!href) {
                editor.chain().focus().extendMarkRange('link').unsetLink().run();
            } else {
                editor
                    .chain()
                    .focus()
                    .extendMarkRange('link')
                    .setLink({ href, ...(title ? { title } : { title: null }) })
                    .run();
            }
            this.closePopover();
        },

        removeLink() {
            if (!editor) {
                return;
            }
            editor.chain().focus().extendMarkRange('link').unsetLink().run();
            this.closePopover();
        },

        openImagePopover() {
            if (!editor) {
                return;
            }
            if (this.popover === 'image') {
                this.closePopover();
                return;
            }
            const attrs = editor.getAttributes('image');
            this.imageForm = {
                src: attrs.src || '',
                alt: attrs.alt || '',
            };
            this.popover = 'image';
            this.$nextTick(() => this.$refs.imageSrc?.focus());
        },

        selectGalleryImage(src, alt) {
            this.imageForm.src = src || '';
            if (!this.imageForm.alt && alt) {
                this.imageForm.alt = alt;
            }
        },

        applyImage() {
            if (!editor) {
                return;
            }
            const src = (this.imageForm.src || '').trim();
            const alt = (this.imageForm.alt || '').trim();
            if (src) {
                editor
                    .chain()
                    .focus()
                    .setImage({ src, ...(alt ? { alt } : {}) })
                    .run();
            }
            this.closePopover();
        },

        closePopover() {
            this.popover = null;
        },
    };
}

window.createAdminRichText = createAdminRichText;

document.addEventListener('alpine:init', () => {
    window.Alpine.data('adminRichText', createAdminRichText);
});

if (window.Alpine) {
    window.Alpine.data('adminRichText', createAdminRichText);
}
