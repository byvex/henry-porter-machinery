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
use Livewire\WithPagination;

#[Layout('layouts.app')]
class CatalogueIndex extends Component
{
    use WithPagination;

    #[Locked]
    public string $resource;

    public string $search = '';

    public string $status = '';

    public string $condition = '';

    public string $brand = '';

    public string $category = '';

    public string $type = '';

    public bool $trash = false;

    public function boot(): void
    {
        Gate::authorize('manage-catalogue');
    }

    public function mount(string $resource): void
    {
        app(CatalogueAdmin::class)->model($resource);
        $this->resource = $resource;
    }

    public function updated(string $property): void
    {
        $this->resetPage();
    }

    public function delete(string $id): void
    {
        Gate::authorize('manage-catalogue');
        app(CatalogueAdmin::class)->delete($this->resource, $id);
        session()->flash('success', 'Record deleted.');
        $this->resetPage();
    }

    public function restore(string $id): void
    {
        Gate::authorize('manage-catalogue');
        abort_unless($this->resource === 'products', 404);
        $product = Product::onlyTrashed()->findOrFail($id);
        $product->admin_modified_at = now();
        $product->restore();
        session()->flash('success', 'Product restored.');
    }

    public function render(): View
    {
        $class = app(CatalogueAdmin::class)->model($this->resource);
        $query = $class::query()->where('name', 'like', '%'.$this->search.'%');
        if ($this->resource === 'products') {
            $query->with(['brand', 'media'])->withCount('categories');
            if ($this->trash) {
                $query->onlyTrashed();
            }
            foreach (['status' => $this->status, 'condition' => $this->condition, 'brand_id' => $this->brand, 'record_type' => $this->type] as $field => $value) {
                if ($value !== '') {
                    $query->where($field, $value);
                }
            }
            if ($this->category !== '') {
                $query->whereHas('categories', fn ($q) => $q->where('categories.id', $this->category));
            }
        } else {
            $query->with($this->resource === 'brands' ? ['logo', 'image'] : ['image', 'parent']);
            if ($this->status !== '') {
                $query->where('is_active', $this->status === 'active');
            }
        }

        return view('livewire.admin.catalogue-index', ['records' => $query->orderBy('sort_order')->orderBy('name')->paginate(15), 'brands' => Brand::orderBy('name')->get(), 'categories' => Category::orderBy('name')->get()])->title(ucfirst($this->resource));
    }
}
