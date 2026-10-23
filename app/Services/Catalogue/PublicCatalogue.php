<?php

namespace App\Services\Catalogue;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductTestimonial;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

class PublicCatalogue
{
    /** @return list<string> */
    public function descendants(Category $category): array
    {
        $categories = Category::where('is_active', true)->get(['id', 'parent_id']);
        $ids = [$category->id];
        do {
            $before = count($ids);
            foreach ($categories as $child) {
                if (in_array($child->parent_id, $ids, true) && ! in_array($child->id, $ids, true)) {
                    $ids[] = $child->id;
                }
            }
        } while (count($ids) > $before);

        return $ids;
    }

    /** @return Builder<Product> */
    public function inCategory(Category $category): Builder
    {
        $ids = $this->descendants($category);

        return Product::catalogue()->whereHas('categories', fn ($q) => $q->whereIn('categories.id', $ids));
    }

    /** @return array<string,mixed> */
    public function productCard(Product $p): array
    {
        $image = $p->relationLoaded('media') ? $p->media->sortBy('pivot.sort_order')->first(fn ($m) => str_starts_with($m->mime_type, 'image/')) : $p->coverImage();

        return ['title' => $p->name, 'brand' => $p->brand->name ?? '', 'description' => Str::limit(trim(html_entity_decode(strip_tags($p->summary ?: $p->description_html ?? ''), ENT_QUOTES, 'UTF-8')), 155), 'condition' => ucfirst($p->condition ?? 'new'), 'category' => implode(' ', $p->categories->pluck('slug')->all()), 'imageUrl' => $image?->url(), 'url' => route('machinery.show', $p->slug)];
    }

    /** @return array<string,mixed> */
    public function categoryCard(Category $category, int $index = 0): array
    {
        $query = $this->inCategory($category);
        $cover = (clone $query)->with('media')->orderBy('sort_order')->get()->map(fn ($p) => $p->media->sortBy('pivot.sort_order')->first(fn ($m) => str_starts_with($m->mime_type, 'image/')))->first(fn ($m) => $m !== null);

        return ['title' => $category->name, 'description' => Str::limit(strip_tags($category->description ?? ''), 130), 'image' => 'Explore '.$category->name, 'imageUrl' => $category->image?->url() ?? $cover?->url(), 'eyebrow' => $query->count().' machines', 'size' => match ($index) {
            0 => 'wide',1 => 'tall',default => 'small'
        }, 'filter' => $category->slug, 'url' => route('categories.show', $category->slug)];
    }

    /** @return array<string,mixed> */
    public function brandCard(Brand $brand): array
    {
        $cover = $brand->products()->catalogue()->with('media')->orderBy('sort_order')->get()->map(fn ($p) => $p->media->sortBy('pivot.sort_order')->first(fn ($m) => str_starts_with($m->mime_type, 'image/')))->first(fn ($m) => $m !== null);

        return ['name' => $brand->name, 'slug' => $brand->slug, 'url' => route('brands.show', $brand->slug), 'logo' => $brand->logo?->url(), 'image' => $brand->image?->url() ?? $cover?->url(), 'count' => $brand->products()->catalogue()->count()];
    }

    /** @return array<string,mixed> */
    public function homepage(): array
    {
        $data = require resource_path('data/home.php');
        $brands = Brand::where('is_active', true)->where('show_on_homepage', true)->with(['logo', 'image'])->orderBy('homepage_order')->orderBy('name')->get();
        $categories = Category::where('is_active', true)->where('show_on_homepage', true)->with('image')->orderBy('homepage_order')->orderBy('name')->get();
        $featured = Product::catalogue()->with(['brand', 'media', 'categories'])->where('is_featured', true)->orderBy('sort_order')->orderByDesc('created_at')->limit(6)->get();
        if ($featured->isEmpty()) {
            $featured = Product::catalogue()->with(['brand', 'media', 'categories'])->latest()->limit(6)->get();
        }
        $data['brands'] = $brands->map(fn ($b) => $this->brandCard($b))->all();
        $data['categories'] = $categories->map(fn ($c, $i) => $this->categoryCard($c, $i))->all();
        $data['products'] = $featured->map(fn ($p) => $this->productCard($p))->all();
        $data['usedMachines'] = Product::catalogue()->where('condition', 'used')->with(['brand', 'media', 'categories'])->latest()->limit(3)->get()->map(fn ($p) => $this->productCard($p))->all();
        $filters = $featured->flatMap(fn ($p) => $p->categories)->where('is_active', true)->unique('id')->sortBy('sort_order');
        $data['productFilters'] = $filters->map(fn ($c) => ['slug' => $c->slug, 'name' => $c->name])->values()->all();
        $data['statistics'] = [['value' => 2006, 'suffix' => '', 'label' => 'Established'], ['value' => Brand::where('is_active', true)->count(), 'suffix' => '', 'label' => 'Brands in Our Range'], ['value' => Product::catalogue()->count(), 'suffix' => '', 'label' => 'Machines to Explore'], ['value' => Product::catalogue()->where('condition', 'used')->count(), 'suffix' => '', 'label' => 'Used Machines']];
        $data['trustItems'][3] = ['icon' => 'agriculture', 'title' => 'Serving Farmers Since 2006'];
        $data['faqs'][1]['answer'] = 'Our range includes '.Brand::where('is_active', true)->orderBy('sort_order')->pluck('name')->implode(', ').'. Contact us for advice on the right machine.';
        $data['testimonials'] = ProductTestimonial::whereHas('product', fn ($q) => $q->catalogue())->get()->unique('content_html')->take(3)->map(fn ($t) => ['html' => $t->content_html])->values()->all();
        $data['news'] = array_slice(app(SiteContent::class)->news(), 0, 2);

        return $data;
    }
}
