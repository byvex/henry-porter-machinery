<?php

namespace App\Livewire\Admin;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Services\Catalogue\CatalogueAdmin;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\WithFileUploads;

#[Layout('layouts.app')]
class CatalogueEditor extends Component
{
    use WithFileUploads;

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
        $this->form = array_replace(['name' => '', 'slug' => '', 'sort_order' => 0, 'summary' => '', 'description_html' => '', 'description' => '', 'brand_id' => null, 'category_ids' => [], 'condition' => 'new', 'status' => 'draft', 'is_featured' => false, 'is_active' => true, 'parent_id' => null], $record ? $record->only(['name', 'slug', 'sort_order', 'summary', 'description_html', 'description', 'brand_id', 'condition', 'status', 'is_featured', 'is_active', 'parent_id']) : []);
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
        $record = app(CatalogueAdmin::class)->save($this->resource, $this->id, $this->form, $this->gallery, $this->videos, $this->testimonials, ['images' => $this->images, 'image' => $this->image, 'logo' => $this->logo, 'remove_image' => $this->removeImage, 'remove_logo' => $this->removeLogo]);
        session()->flash('success', 'Changes saved.');
        $this->redirectRoute('admin.catalogue.edit', ['resource' => $this->resource, 'id' => $record->id], navigate: true);
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

        return view('livewire.admin.catalogue-editor', ['record' => $this->id ? $class::findOrFail($this->id) : null, 'brands' => Brand::orderBy('name')->get(), 'categories' => Category::orderBy('name')->get(), 'preview' => app(CatalogueAdmin::class)->clean($this->form[$this->resource === 'products' ? 'description_html' : 'description'] ?? '')])->title(($this->id ? 'Edit ' : 'Create ').($this->resource === 'categories' ? 'category' : rtrim($this->resource, 's')));
    }
}
