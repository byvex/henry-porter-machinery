/**
 * Browser-ready Alpine.js components for Admin Media Library and Modal Picker.
 * Plain JavaScript; edit and refresh without rebuilding.
 */

function getCsrfToken() {
    return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
}

function adminMediaLibrary(config = {}) {
    return {
        items: [],
        loading: false,
        search: '',
        source: '',
        attachment: '',
        brand: '',
        product: '',
        page: 1,
        lastPage: 1,
        total: 0,
        from: 0,
        to: 0,
        perPage: 36,
        selected: [],
        inspecting: null,
        inspectTitle: '',
        inspectAltText: '',
        savingMetadata: false,
        feedback: null,
        feedbackType: 'success',
        uploading: false,
        searchTimeout: null,

        init() {
            // Read initial query params from window.location
            const params = new URLSearchParams(window.location.search);
            this.search = params.get('search') || '';
            this.source = params.get('source') || '';
            this.attachment = params.get('attachment') || '';
            this.brand = params.get('brand') || '';
            this.product = params.get('product') || '';
            const pageParam = parseInt(params.get('page'), 10);
            this.page = !isNaN(pageParam) && pageParam > 0 ? pageParam : 1;

            this.fetchItems(this.page);
        },

        onSearchInput() {
            clearTimeout(this.searchTimeout);
            this.searchTimeout = setTimeout(() => {
                this.page = 1;
                this.fetchItems(1);
            }, 300);
        },

        onFilterChange() {
            this.page = 1;
            this.fetchItems(1);
        },

        async fetchItems(page = 1) {
            this.loading = true;
            this.page = page;

            const params = new URLSearchParams();
            if (this.search) params.set('search', this.search);
            if (this.source) params.set('source', this.source);
            if (this.attachment) params.set('attachment', this.attachment);
            if (this.brand) params.set('brand', this.brand);
            if (this.product) params.set('product', this.product);
            params.set('page', page);
            params.set('per_page', this.perPage);

            // Sync with browser URL
            const newUrl = `${window.location.pathname}?${params.toString()}`;
            window.history.replaceState({}, '', newUrl);

            try {
                const response = await fetch(`/admin/media/items?${params.toString()}`, {
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                });

                if (!response.ok) {
                    throw new Error('Failed to load media items');
                }

                const data = await response.json();
                this.items = data.data || [];
                this.page = data.current_page || 1;
                this.lastPage = data.last_page || 1;
                this.total = data.total || 0;
                this.from = data.from || 0;
                this.to = data.to || 0;
            } catch (err) {
                this.showFeedback(err.message || 'Error fetching media items', 'error');
            } finally {
                this.loading = false;
            }
        },

        toggleSelect(id) {
            const index = this.selected.indexOf(id);
            if (index > -1) {
                this.selected.splice(index, 1);
            } else {
                this.selected.push(id);
            }
        },

        selectAllOnPage() {
            const pageIds = this.items.map(item => item.id);
            const merged = new Set([...this.selected, ...pageIds]);
            this.selected = Array.from(merged);
        },

        clearSelection() {
            this.selected = [];
        },

        inspect(item) {
            this.inspecting = item;
            this.inspectTitle = item.title || '';
            this.inspectAltText = item.alt_text || '';
        },

        closeInspector() {
            this.inspecting = null;
            this.inspectTitle = '';
            this.inspectAltText = '';
        },

        async saveMetadata() {
            if (!this.inspecting) return;
            this.savingMetadata = true;

            try {
                const response = await fetch(`/admin/media/${this.inspecting.id}`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': getCsrfToken(),
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    body: JSON.stringify({
                        title: this.inspectTitle,
                        alt_text: this.inspectAltText,
                    }),
                });

                const result = await response.json();
                if (!response.ok || !result.success) {
                    throw new Error(result.message || 'Failed to save metadata');
                }

                this.inspecting.title = this.inspectTitle;
                this.inspecting.alt_text = this.inspectAltText;

                // Update item in list
                const found = this.items.find(i => i.id === this.inspecting.id);
                if (found) {
                    found.title = this.inspectTitle;
                    found.alt_text = this.inspectAltText;
                }

                this.showFeedback('Image metadata saved.', 'success');
            } catch (err) {
                this.showFeedback(err.message, 'error');
            } finally {
                this.savingMetadata = false;
            }
        },

        async detachFromProduct(assetId, productId, productName) {
            if (!confirm(`Detach this image from ${productName}?`)) return;

            try {
                const response = await fetch(`/admin/media/${assetId}/detach`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': getCsrfToken(),
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    body: JSON.stringify({ product_id: productId }),
                });

                const result = await response.json();
                if (!response.ok || !result.success) {
                    throw new Error(result.message || 'Failed to detach image');
                }

                if (this.inspecting && this.inspecting.id === assetId) {
                    this.inspecting.products = this.inspecting.products.filter(p => p.id !== productId);
                    this.inspecting.products_count = this.inspecting.products.length;
                }

                const item = this.items.find(i => i.id === assetId);
                if (item) {
                    item.products = item.products.filter(p => p.id !== productId);
                    item.products_count = item.products.length;
                }

                this.showFeedback('Image detached from product.', 'success');
            } catch (err) {
                this.showFeedback(err.message, 'error');
            }
        },

        async deleteSingle(assetId) {
            if (!confirm('Permanently delete this image from database and storage? If attached, it will be detached first.')) return;

            try {
                const response = await fetch(`/admin/media/${assetId}`, {
                    method: 'DELETE',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': getCsrfToken(),
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                });

                const result = await response.json();
                if (!response.ok || !result.success) {
                    throw new Error(result.message || 'Could not delete image');
                }

                this.closeInspector();
                this.selected = this.selected.filter(id => id !== assetId);
                this.showFeedback(result.message, 'success');
                this.fetchItems(this.page);
            } catch (err) {
                this.showFeedback(err.message, 'error');
            }
        },

        async bulkDetach() {
            if (!this.selected.length) return;
            if (!confirm(`Detach ${this.selected.length} selected images from any attached products?`)) return;

            try {
                const response = await fetch('/admin/media/bulk-detach', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': getCsrfToken(),
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    body: JSON.stringify({ ids: this.selected }),
                });

                const result = await response.json();
                this.clearSelection();
                this.showFeedback(result.message, 'success');
                this.fetchItems(this.page);
            } catch (err) {
                this.showFeedback(err.message, 'error');
            }
        },

        async bulkDelete() {
            if (!this.selected.length) return;
            if (!confirm(`Permanently delete ${this.selected.length} selected images from database and storage? If attached, they will be detached first.`)) return;

            try {
                const response = await fetch('/admin/media/bulk-delete', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': getCsrfToken(),
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    body: JSON.stringify({ ids: this.selected }),
                });

                const result = await response.json();
                this.clearSelection();
                this.showFeedback(result.message, 'success');
                this.fetchItems(this.page);
            } catch (err) {
                this.showFeedback(err.message, 'error');
            }
        },

        async uploadFiles(event) {
            const files = event.target.files;
            if (!files || !files.length) return;

            this.uploading = true;
            const formData = new FormData();
            for (let i = 0; i < files.length; i++) {
                formData.append('files[]', files[i]);
            }

            try {
                const response = await fetch('/admin/media/upload', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': getCsrfToken(),
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    body: formData,
                });

                const result = await response.json();
                if (!response.ok || !result.success) {
                    throw new Error(result.message || 'Upload failed');
                }

                event.target.value = '';
                this.showFeedback(result.message, 'success');
                this.fetchItems(1);
            } catch (err) {
                this.showFeedback(err.message, 'error');
            } finally {
                this.uploading = false;
            }
        },

        async pruneOrphans() {
            if (!confirm('Scan for unreferenced media assets and untracked files on disk, and delete them?')) return;

            try {
                const response = await fetch('/admin/media/prune', {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': getCsrfToken(),
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                });

                const result = await response.json();
                this.showFeedback(result.output || 'Orphaned media pruned successfully.', 'success');
                this.fetchItems(1);
            } catch (err) {
                this.showFeedback(err.message, 'error');
            }
        },

        changePage(p) {
            if (p < 1 || p > this.lastPage || p === this.page) return;
            this.fetchItems(p);
        },

        showFeedback(msg, type = 'success') {
            this.feedback = msg;
            this.feedbackType = type;
            setTimeout(() => {
                if (this.feedback === msg) {
                    this.feedback = null;
                }
            }, 6000);
        },

        get paginationPages() {
            const current = this.page;
            const last = this.lastPage;
            const delta = 2;
            const range = [];

            for (let i = Math.max(2, current - delta); i <= Math.min(last - 1, current + delta); i++) {
                range.push(i);
            }

            if (current - delta > 2) {
                range.unshift('...');
            }
            if (current + delta < last - 1) {
                range.push('...');
            }

            range.unshift(1);
            if (last > 1) {
                range.push(last);
            }

            return range;
        }
    };
}

function adminMediaPicker() {
    return {
        isOpen: false,
        target: 'gallery', // 'gallery', 'image', 'logo'
        search: '',
        source: '',
        items: [],
        page: 1,
        lastPage: 1,
        total: 0,
        loading: false,
        selectedIds: [],
        singleSelected: null,
        existingGalleryIds: [],
        searchTimeout: null,

        open(target, existingIds = []) {
            this.target = target;
            this.existingGalleryIds = existingIds;
            this.selectedIds = [];
            this.singleSelected = null;
            this.search = '';
            this.source = '';
            this.page = 1;
            this.isOpen = true;
            this.fetchItems(1);
        },

        close() {
            this.isOpen = false;
        },

        onSearchInput() {
            clearTimeout(this.searchTimeout);
            this.searchTimeout = setTimeout(() => {
                this.fetchItems(1);
            }, 300);
        },

        async fetchItems(page = 1) {
            this.loading = true;
            this.page = page;

            const params = new URLSearchParams();
            if (this.search) params.set('search', this.search);
            if (this.source) params.set('source', this.source);
            params.set('page', page);
            params.set('per_page', 24);

            try {
                const response = await fetch(`/admin/media/items?${params.toString()}`, {
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                });

                const data = await response.json();
                this.items = data.data || [];
                this.page = data.current_page || 1;
                this.lastPage = data.last_page || 1;
                this.total = data.total || 0;
            } catch (err) {
                console.error('Error fetching picker media:', err);
            } finally {
                this.loading = false;
            }
        },

        toggleSelect(id) {
            if (this.existingGalleryIds.includes(id)) return;
            const idx = this.selectedIds.indexOf(id);
            if (idx > -1) {
                this.selectedIds.splice(idx, 1);
            } else {
                this.selectedIds.push(id);
            }
        },

        selectSingle(item) {
            this.singleSelected = item;
        },

        attachToLivewire() {
            if (this.target === 'gallery') {
                const chosenItems = this.items.filter(i => this.selectedIds.includes(i.id));
                if (chosenItems.length && window.Livewire) {
                    const payload = chosenItems.map(i => ({
                        id: i.id,
                        alt_text: i.alt_text || '',
                        url: i.url,
                        mime_type: i.mime_type,
                    }));
                    Livewire.dispatch('media-picker-attached', { target: 'gallery', items: payload });
                }
            } else if (this.target === 'image' || this.target === 'logo') {
                if (this.singleSelected && window.Livewire) {
                    Livewire.dispatch('media-picker-attached', {
                        target: this.target,
                        item: {
                            id: this.singleSelected.id,
                            alt_text: this.singleSelected.alt_text || '',
                            url: this.singleSelected.url,
                        }
                    });
                }
            }

            this.close();
        },

        changePage(p) {
            if (p < 1 || p > this.lastPage || p === this.page) return;
            this.fetchItems(p);
        }
    };
}

window.adminMediaLibrary = adminMediaLibrary;
window.adminMediaPicker = adminMediaPicker;

document.addEventListener('alpine:init', () => {
    window.Alpine.data('adminMediaLibrary', adminMediaLibrary);
    window.Alpine.data('adminMediaPicker', adminMediaPicker);
});
