<?php

namespace App\Livewire\Admin;

use App\Models\Brand;
use App\Models\Category;
use App\Models\MediaAsset;
use App\Models\Product;
use App\Services\Catalogue\CatalogueAdmin;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

#[Layout('layouts.app')]
class CatalogueEditor extends Component
{
    use WithFileUploads, WithPagination;

    #[Locked]
    public string $resource;

    #[Locked]
    public ?string $id = null;

    /** @var array<string, mixed> */
    public array $form = [];

    /** @var array<int, array<string, mixed>> */
    public array $gallery = [];

    /** @var array<int, array<string, mixed>> */
    public array $videos = [];

    /** @var array<int, array<string, mixed>> */
    public array $testimonials = [];

    /** @var array<int, TemporaryUploadedFile> */
    public array $images = [];

    public ?TemporaryUploadedFile $image = null;

    public ?TemporaryUploadedFile $logo = null;

    public bool $removeImage = false;

    public bool $removeLogo = false;

    public bool $mediaPickerOpen = false;

    public string $mediaPickerTarget = 'gallery';

    public string $mediaPickerSearch = '';

    public string $mediaPickerSource = '';

    /** @var array<string> */
    public array $mediaPickerSelected = [];

    public ?string $mediaPickerSingle = null;

    public ?string $selectedImageId = null;

    public ?string $selectedImageUrl = null;

    public ?string $selectedLogoId = null;

    public ?string $selectedLogoUrl = null;

    public function boot(): void
    {
        Gate::authorize('manage-catalogue');
    }

    public function mount(string $resource, ?string $id = null): void
    {
        $this->resource = $resource;
        $this->id = $id;
        $class = app(CatalogueAdmin::class)->model($resource);
        $record = $id ? $class::findOrFail($id) : null;
        $this->form = array_replace(['name' => '', 'slug' => '', 'sort_order' => 0, 'summary' => '', 'description_html' => '', 'description' => '', 'brand_id' => null, 'category_ids' => [], 'condition' => 'new', 'status' => 'draft', 'is_featured' => false, 'is_active' => true, 'show_on_homepage' => false, 'homepage_order' => 0, 'parent_id' => null], $record ? $record->only(['name', 'slug', 'sort_order', 'summary', 'description_html', 'description', 'brand_id', 'condition', 'status', 'is_featured', 'is_active', 'parent_id', 'show_on_homepage', 'homepage_order']) : []);
        if ($record && ! ($record instanceof Product)) {
            $this->form['image_alt_text'] = $record->image->alt_text ?? '';
            if ($record instanceof Brand) {
                $this->form['logo_alt_text'] = $record->logo->alt_text ?? '';
            }
        }
        if ($record instanceof Product) {
            $this->form['condition'] ??= 'new';
            $this->form['category_ids'] = $record->categories->pluck('id')->all();
            $this->gallery = $record->media()->orderByPivot('sort_order')->get()->map(fn ($m) => ['id' => $m->id, 'alt_text' => $m->alt_text ?? '', 'url' => $m->url(), 'mime_type' => $m->mime_type])->all();
            $this->videos = $record->videos->map(fn ($v) => ['id' => $v->id, 'value' => $v->video_id])->all();
            $this->testimonials = $record->testimonials->map(fn ($t) => ['id' => $t->id, 'content_html' => $t->content_html])->all();
        }
    }

    public function save(): void
    {
        Gate::authorize('manage-catalogue');
        $this->form['brand_id'] = ($this->form['brand_id'] ?? '') ?: null;
        $this->form['parent_id'] = ($this->form['parent_id'] ?? '') ?: null;
        $record = app(CatalogueAdmin::class)->save($this->resource, $this->id, $this->form, $this->gallery, $this->videos, $this->testimonials, [
            'images' => $this->images,
            'image' => $this->image,
            'logo' => $this->logo,
            'remove_image' => $this->removeImage,
            'remove_logo' => $this->removeLogo,
            'selected_image' => $this->selectedImageId,
            'selected_logo' => $this->selectedLogoId,
        ]);
        session()->flash('success', 'Changes saved.');
        $this->redirectRoute('admin.catalogue.edit', ['resource' => $this->resource, 'id' => $record->id], navigate: true);
    }

    public function updatedMediaPickerSearch(): void
    {
        $this->resetPage('pickerPage');
    }

    public function updatedMediaPickerSource(): void
    {
        $this->resetPage('pickerPage');
    }

    public function openMediaPicker(string $target = 'gallery'): void
    {
        $this->mediaPickerTarget = $target;
        $this->mediaPickerSelected = [];
        $this->mediaPickerSingle = null;
        $this->mediaPickerSearch = '';
        $this->mediaPickerSource = '';
        $this->resetPage('pickerPage');
        $this->mediaPickerOpen = true;
    }

    public function closeMediaPicker(): void
    {
        $this->mediaPickerOpen = false;
    }

    public function toggleMediaPickerAsset(string $assetId): void
    {
        if (in_array($assetId, $this->mediaPickerSelected, true)) {
            $this->mediaPickerSelected = array_values(array_diff($this->mediaPickerSelected, [$assetId]));
        } else {
            $this->mediaPickerSelected[] = $assetId;
        }
    }

    public function selectMediaPickerSingle(string $assetId): void
    {
        $this->mediaPickerSingle = $assetId;
    }

    public function attachSelectedMedia(): void
    {
        if ($this->mediaPickerTarget === 'gallery') {
            if (! empty($this->mediaPickerSelected)) {
                $existingIds = array_column($this->gallery, 'id');
                $assets = MediaAsset::whereIn('id', $this->mediaPickerSelected)->get();
                foreach ($assets as $asset) {
                    if (in_array($asset->id, $existingIds, true)) {
                        continue;
                    }
                    $this->gallery[] = [
                        'id' => $asset->id,
                        'alt_text' => $asset->alt_text ?? '',
                        'url' => $asset->url(),
                        'mime_type' => $asset->mime_type,
                    ];
                }
            }
        } elseif ($this->mediaPickerTarget === 'image') {
            if ($this->mediaPickerSingle) {
                $asset = MediaAsset::whereKey($this->mediaPickerSingle)->first();
                if ($asset) {
                    $this->selectedImageId = $asset->id;
                    $this->selectedImageUrl = $asset->url();
                    $this->form['image_alt_text'] = $asset->alt_text ?? '';
                    $this->removeImage = false;
                    $this->image = null;
                }
            }
        } elseif ($this->mediaPickerTarget === 'logo') {
            if ($this->mediaPickerSingle) {
                $asset = MediaAsset::whereKey($this->mediaPickerSingle)->first();
                if ($asset) {
                    $this->selectedLogoId = $asset->id;
                    $this->selectedLogoUrl = $asset->url();
                    $this->form['logo_alt_text'] = $asset->alt_text ?? '';
                    $this->removeLogo = false;
                    $this->logo = null;
                }
            }
        }

        $this->closeMediaPicker();
    }

    /**
     * @param  list<array{id: string, alt_text: string, url: string, mime_type?: string}>  $items
     * @param  array{id: string, alt_text: string, url: string}|null  $item
     */
    #[On('media-picker-attached')]
    public function onMediaPickerAttached(string $target, array $items = [], ?array $item = null): void
    {
        if ($target === 'gallery' && ! empty($items)) {
            $existingIds = array_column($this->gallery, 'id');
            foreach ($items as $entry) {
                if (in_array($entry['id'], $existingIds, true)) {
                    continue;
                }
                $this->gallery[] = [
                    'id' => $entry['id'],
                    'alt_text' => $entry['alt_text'] ?? '',
                    'url' => $entry['url'],
                    'mime_type' => $entry['mime_type'] ?? 'image/jpeg',
                ];
            }
        } elseif ($target === 'image' && ! empty($item)) {
            $this->selectedImageId = $item['id'];
            $this->selectedImageUrl = $item['url'];
            $this->form['image_alt_text'] = $item['alt_text'] ?? '';
            $this->removeImage = false;
            $this->image = null;
        } elseif ($target === 'logo' && ! empty($item)) {
            $this->selectedLogoId = $item['id'];
            $this->selectedLogoUrl = $item['url'];
            $this->form['logo_alt_text'] = $item['alt_text'] ?? '';
            $this->removeLogo = false;
            $this->logo = null;
        }
    }

    public function moveImage(int $index, int $direction): void
    {
        $target = $index + $direction;
        if (! in_array($direction, [-1, 1], true) || ! isset($this->gallery[$index], $this->gallery[$target])) {
            return;
        }
        [$this->gallery[$index], $this->gallery[$target]] = [$this->gallery[$target], $this->gallery[$index]];
    }

    public function makeCover(int $index): void
    {
        if (! isset($this->gallery[$index])) {
            return;
        }
        $image = $this->gallery[$index];
        array_splice($this->gallery, $index, 1);
        array_unshift($this->gallery, $image);
    }

    public function moveRow(string $type, int $index, int $direction): void
    {
        if (! in_array($type, ['videos', 'testimonials'], true) || ! in_array($direction, [-1, 1], true)) {
            return;
        }
        $target = $index + $direction;
        if (! isset($this->{$type}[$index], $this->{$type}[$target])) {
            return;
        }
        [$this->{$type}[$index], $this->{$type}[$target]] = [$this->{$type}[$target], $this->{$type}[$index]];
    }

    public function removeImageAt(int $index): void
    {
        unset($this->gallery[$index]);
        $this->gallery = array_values($this->gallery);
    }

    public function addVideo(): void
    {
        $this->videos[] = ['id' => null, 'value' => ''];
    }

    public function removeVideo(int $index): void
    {
        unset($this->videos[$index]);
        $this->videos = array_values($this->videos);
    }

    public function addTestimonial(): void
    {
        $this->testimonials[] = ['id' => null, 'content_html' => ''];
    }

    public function removeTestimonial(int $index): void
    {
        unset($this->testimonials[$index]);
        $this->testimonials = array_values($this->testimonials);
    }

    public function render(): View
    {
        $class = app(CatalogueAdmin::class)->model($this->resource);

        $pickerAssets = null;
        if ($this->mediaPickerOpen) {
            $pickerAssets = MediaAsset::query()
                ->filterSearch($this->mediaPickerSearch)
                ->filterSource($this->mediaPickerSource)
                ->latest('created_at')
                ->paginate(24, pageName: 'pickerPage');
        }

        return view('livewire.admin.catalogue-editor', [
            'record' => $this->id ? $class::findOrFail($this->id) : null,
            'brands' => Brand::orderBy('name')->get(),
            'categories' => Category::orderBy('name')->get(),
            'preview' => app(CatalogueAdmin::class)->clean($this->form[$this->resource === 'products' ? 'description_html' : 'description'] ?? ''),
            'pickerAssets' => $pickerAssets,
        ])->title(($this->id ? 'Edit ' : 'Create ').($this->resource === 'categories' ? 'category' : rtrim($this->resource, 's')));
    }
}
