<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Services\Catalogue\PublicCatalogue;
use App\Services\Catalogue\SiteContent;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class CatalogueController extends Controller
{
    public function home(PublicCatalogue $catalogue): View
    {
        return view('home', $catalogue->homepage());
    }

    public function index(Request $request, PublicCatalogue $catalogue): View
    {
        return $this->listing($request, $catalogue);
    }

    public function new(Request $request, PublicCatalogue $catalogue): View
    {
        return $this->listing($request, $catalogue, 'new');
    }

    public function used(Request $request, PublicCatalogue $catalogue): View
    {
        return $this->listing($request, $catalogue, 'used');
    }

    public function category(Request $request, string $slug, PublicCatalogue $catalogue): View
    {
        return $this->listing($request, $catalogue, null, Category::where('is_active', true)->where('slug', $slug)->firstOrFail());
    }

    public function brand(Request $request, string $slug, PublicCatalogue $catalogue): View
    {
        return $this->listing($request, $catalogue, null, Brand::where('is_active', true)->where('slug', $slug)->firstOrFail());
    }

    private function listing(Request $request, PublicCatalogue $catalogue, ?string $condition = null, Category|Brand|null $entity = null): View
    {
        $filters = $request->validate(['q' => ['nullable', 'string', 'max:100'], 'condition' => ['nullable', 'in:new,used'], 'brands' => ['sometimes', 'array', 'max:20'], 'brands.*' => ['string', 'max:255'], 'categories' => ['sometimes', 'array', 'max:30'], 'categories.*' => ['string', 'max:255'], 'sort' => ['nullable', 'in:recommended,newest,name'], 'page' => ['nullable', 'integer', 'min:1', 'max:10000']]);
        $query = Product::catalogue()->with(['brand', 'media', 'categories']);
        $condition ??= $filters['condition'] ?? null;
        if ($condition) {
            $query->where('condition', $condition);
        }
        if ($entity instanceof Category) {
            $ids = $catalogue->descendants($entity);
            $query->whereHas('categories', fn ($q) => $q->whereIn('categories.id', $ids));
        }
        if ($entity instanceof Brand) {
            $query->where('brand_id', $entity->id);
        }
        if (! empty($filters['q'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('name', 'like', '%'.$filters['q'].'%')->orWhere('summary', 'like', '%'.$filters['q'].'%');
            });
        }
        if (! empty($filters['brands'])) {
            $query->whereHas('brand', fn ($q) => $q->where('is_active', true)->whereIn('slug', $filters['brands']));
        }
        if (! empty($filters['categories'])) {
            $ids = [];
            foreach (Category::where('is_active', true)->whereIn('slug', $filters['categories'])->get() as $c) {
                $ids = array_merge($ids, $catalogue->descendants($c));
            }
            $query->whereHas('categories', fn ($q) => $q->whereIn('categories.id', array_unique($ids)));
        }
        match ($filters['sort'] ?? 'recommended') {
            'newest' => $query->orderByDesc('created_at'), 'name' => $query->orderBy('name'), default => $query->orderBy('sort_order')->orderBy('name')
        };
        $products = $query->orderBy('products.id')->paginate(12)->withQueryString();
        $title = $entity ? $entity->name : match ($condition) {
            'new' => 'New Machinery','used' => 'Used Machinery',default => 'Explore Our Machinery'
        };
        $description = $entity ? trim(strip_tags($entity->description ?? '')) : '';
        $description = $description ?: 'Explore dependable machinery for your farm, backed by practical advice and support from our Lisburn team.';
        $image = $entity instanceof Brand ? $catalogue->brandCard($entity)['image'] : ($entity instanceof Category ? $catalogue->categoryCard($entity)['imageUrl'] : null);

        return view('catalogue.index', compact('products', 'filters', 'title', 'description', 'entity', 'condition', 'image') + ['catalogue' => $catalogue, 'brands' => Brand::where('is_active', true)->orderBy('sort_order')->get(), 'categories' => Category::where('is_active', true)->orderBy('sort_order')->get()]);
    }

    public function show(string $slug, PublicCatalogue $catalogue): View
    {
        $product = Product::catalogue()->with(['brand', 'media', 'categories', 'videos', 'testimonials'])->where('slug', $slug)->firstOrFail();
        $related = Product::catalogue()->whereKeyNot($product->id)->where(function ($q) use ($product) {
            if ($product->brand_id) {
                $q->where('brand_id', $product->brand_id);
            } $q->orWhereHas('categories', fn ($c) => $c->whereIn('categories.id', $product->categories->pluck('id')));
        })->with(['brand', 'media', 'categories'])->orderBy('sort_order')->limit(3)->get();

        return view('catalogue.show', compact('product', 'related', 'catalogue'));
    }

    public function directory(string $directory, PublicCatalogue $catalogue): View
    {
        abort_unless(in_array($directory, ['brands', 'categories'], true), 404);
        $items = $directory === 'brands' ? Brand::where('is_active', true)->orderBy('sort_order')->get()->map(fn ($b) => $catalogue->brandCard($b))->all() : Category::where('is_active', true)->orderBy('sort_order')->get()->map(fn ($c, $i) => $catalogue->categoryCard($c, 2))->all();

        return view('catalogue.directory', compact('directory', 'items'));
    }

    public function page(string $page, SiteContent $content): View
    {
        abort_unless(in_array($page, ['parts', 'service', 'contact', 'cookie-policy'], true), 404);

        return view('site-page', ['page' => $page, 'data' => $content->data()['pages'][$page] ?? [], 'content' => $content]);
    }

    public function news(SiteContent $content): View
    {
        return view('news.index', ['news' => $content->news()]);
    }

    public function article(string $slug, SiteContent $content): View
    {
        $article = collect($content->news())->firstWhere('slug', $slug);
        abort_unless($article !== null, 404);

        return view('news.show', compact('article', 'content'));
    }

    /** @return list<string> */
    public function sitemapUrls(): array
    {
        $urls = ['/', '/about', '/machinery', '/new-machinery', '/used-machinery', '/categories', '/brands', '/parts', '/service', '/contact', '/news', '/cookie-policy', '/sitemap'];
        foreach (['machinery' => Product::catalogue()->pluck('slug'), 'categories' => Category::where('is_active', true)->pluck('slug'), 'brands' => Brand::where('is_active', true)->pluck('slug')] as $prefix => $slugs) {
            foreach ($slugs as $slug) {
                $urls[] = '/'.$prefix.'/'.$slug;
            }
        }
        foreach (app(SiteContent::class)->news() as $article) {
            $urls[] = '/news/'.$article['slug'];
        }

        return $urls;
    }

    public function sitemap(): View
    {
        return view('sitemap', ['paths' => $this->sitemapUrls()]);
    }

    public function xml(): Response
    {
        $xml = '<?xml version="1.0" encoding="UTF-8"?><urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';
        foreach ($this->sitemapUrls() as $path) {
            $xml .= '<url><loc>'.htmlspecialchars(rtrim(config('app.url'), '/').$path, ENT_XML1).'</loc></url>';
        }

        return response($xml.'</urlset>', 200, ['Content-Type' => 'application/xml']);
    }
}
