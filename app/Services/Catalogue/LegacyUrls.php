<?php

namespace App\Services\Catalogue;

use App\Models\Brand;
use App\Models\Category;
use App\Models\LegacyUrlMapping;
use App\Models\Product;

class LegacyUrls
{
    /** @param array<string,mixed> $query */
    public function hash(string $path, array $query = []): string
    {
        ksort($query);

        return hash('sha256', $path.'?'.http_build_query($query));
    }

    /** @param array<string,mixed>|null $filters */
    public function destination(string $type, ?string $id, ?array $filters = null): ?string
    {
        $filters ??= [];
        $url = null;
        if (in_array($type, ['product', 'content'], true)) {
            $p = $id ? Product::withTrashed()->find($id) : null;
            if ($p?->record_type === 'content') {
                return '/parts';
            }
            if ($p && ! $p->trashed() && $p->record_type === 'machinery' && $p->status === 'published') {
                return '/machinery/'.$p->slug;
            }
            if ($p?->condition === 'used') {
                return '/used-machinery';
            }
            if ($p?->brand?->is_active) {
                return '/brands/'.$p->brand->slug;
            }
            if ($p) {
                $category = $p->categories()->where('is_active', true)->first();
                if ($category) {
                    return '/categories/'.$category->slug;
                }
            }

            return '/machinery';
        }
        if ($type === 'brand') {
            $b = $id ? Brand::where('is_active', true)->find($id) : null;
            $url = $b ? '/brands/'.$b->slug : '/machinery';
        }
        if ($type === 'category') {
            $c = $id ? Category::where('is_active', true)->find($id) : null;
            $url = $c ? '/categories/'.$c->slug : '/machinery';
        }
        if ($type === 'listing') {
            $url = '/machinery';
        }
        if ($type === 'page') {
            return in_array($filters['page'] ?? '', ['/', '/about', '/parts', '/contact', '/news', '/cookie-policy', '/sitemap'], true) ? $filters['page'] : null;
        }
        if ($type === 'news') {
            foreach (app(SiteContent::class)->news() as $n) {
                if (($filters['slug'] ?? '') === $n['slug']) {
                    return '/news/'.$n['slug'];
                }
            }

            return '/news';
        }
        if ($url === null) {
            return null;
        }
        if ($url === '/machinery' && in_array($filters['condition'] ?? '', ['new', 'used'], true)) {
            $url = $filters['condition'] === 'new' ? '/new-machinery' : '/used-machinery';
            unset($filters['condition']);
        }
        $params = array_intersect_key($filters, array_flip(['q', 'condition', 'brands', 'categories', 'sort', 'page']));

        return $url.($params ? '?'.http_build_query($params, '', '&', PHP_QUERY_RFC3986) : '');
    }

    /** @return array{type:string,id:?string,filters:array<string,mixed>}|null */
    public function categoryTarget(int $id): ?array
    {
        $t = app(JoomlaCatalogue::class)->categoryTarget($id);
        if (! $t) {
            return null;
        }
        $entity = match ($t['type']) {
            'brand' => Brand::withTrashed()->where('source_key', 'joomla:brand:'.$t['key'])->first(), 'category' => Category::withTrashed()->where('source_key', 'joomla:category:'.$t['key'])->first(), 'content' => Product::withTrashed()->where('legacy_product_id', $t['key'])->first(), default => null
        };

        return ['type' => $t['type'], 'id' => $entity?->id, 'filters' => $t['filters'] ?? []];
    }

    /** @return array{type:string,id:?string,filters:array<string,mixed>}|null */
    public function infer(string $path): ?array
    {
        $simple = ['/about.html' => '/about', '/parts.html' => '/parts', '/new-machines/all-new-machines/parts.html' => '/parts', '/contact-form.html' => '/contact', '/contact-form' => '/contact', '/cookie-policy.html' => '/cookie-policy', '/news-events.html' => '/news', '/news-events/itemlist/category/1-news-events.html' => '/news', '/index.php' => '/', '/2-uncategorised/19-new-machines.html' => '/new-machinery', '/2-uncategorised/20-used-machines.html' => '/used-machinery', '/2-uncategorised/21-contact.html' => '/contact'];
        if (isset($simple[$path])) {
            if (in_array($simple[$path], ['/new-machinery', '/used-machinery'], true)) {
                return ['type' => 'listing', 'id' => null, 'filters' => ['condition' => $simple[$path] === '/new-machinery' ? 'new' : 'used']];
            }

            return ['type' => 'page', 'id' => null, 'filters' => ['page' => $simple[$path]]];
        }
        foreach (app(SiteContent::class)->news() as $n) {
            if ($path === '/news-events/item/'.$n['legacy_id'].'-'.$n['slug'].'.html') {
                return ['type' => 'news', 'id' => null, 'filters' => ['slug' => $n['slug']]];
            }
        }
        if (preg_match('~^/(?:new-machines|vm)/.+/([^/]+)-detail\.html$~', $path, $m)) {
            $p = Product::withTrashed()->where('source_metadata->slug', rawurldecode($m[1]))->first();
            if ($p) {
                return ['type' => $p->record_type === 'content' ? 'content' : 'product', 'id' => $p->id, 'filters' => []];
            }
        }
        $aliases = ['all-new-machines' => 20, 'all-pottinger' => 21, 'pottinger' => 3, 'bobman' => 4, 'hustler' => 5, 'multione' => 6, 'strautmann' => 7, 'fleming' => 8, 'sherpa-miniloaders' => 23, 'sherpa-mini-loaders' => 23, 'cubicle-cleaners' => 18, 'feeders' => 19, 'grassland' => 9, 'tillage' => 10, 'more' => 11, 'impress-baler' => 17, 'mower' => 16, 'rakes' => 15, 'silage-wagon' => 13, 'tedder' => 12, 'tiller' => 14, 'used-machinery' => 2];
        if (preg_match('~^/(?:filtering/([^/]+)/?|vm/([^/]+)\.html|new-machines/(?:pottinger/)?([^/]+)\.html|2-uncategorised/\d+-([^/]+)\.html)$~', $path, $m)) {
            $alias = collect(array_slice($m, 1))->first(fn ($s) => $s !== '');
            if (isset($aliases[$alias])) {
                $target = $this->categoryTarget($aliases[$alias]);
                if ($target && str_contains($path, '/pottinger/') && in_array($alias, ['grassland', 'tillage'], true)) {
                    $target['filters']['brands'] = ['pottinger'];
                } if ($target && str_starts_with($path, '/new-machines/')) {
                    $target['filters']['condition'] = 'new';
                }

                return $target;
            }
        }

        return match ($path) {
            '/new-machines.html' => ['type' => 'listing', 'id' => null, 'filters' => ['condition' => 'new']], '/used-machines.html' => ['type' => 'listing', 'id' => null, 'filters' => ['condition' => 'used']], '/vm.html','/filtering/','/filtering' => ['type' => 'listing', 'id' => null, 'filters' => []], default => null
        };
    }

    /** @param array<string,mixed> $query */
    private function verifiedMapping(string $path, array $query): ?LegacyUrlMapping
    {
        $alternate = $path === '/' ? '/' : (str_ends_with($path, '/') ? rtrim($path, '/') : $path.'/');
        foreach (array_unique([$path, $alternate]) as $candidate) {
            $map = LegacyUrlMapping::where('source_hash', $this->hash($candidate, $query))->where('verification_status', 'verified')->first();
            if ($map) {
                return $map;
            }
        }

        return null;
    }

    /** @param array<string,mixed> $query */
    public function resolve(string $path, array $query = []): ?string
    {
        $query = array_filter($query, fn ($k) => ! str_starts_with(strtolower($k), 'utm_') && ! in_array($k, ['gclid', 'fbclid'], true), ARRAY_FILTER_USE_KEY);
        $map = $this->verifiedMapping($path, $query);
        if (! $map && $query) {
            $without = $query;
            unset($without['Itemid']);
            $map = $this->verifiedMapping($path, $without);
        }
        $target = $map ? ['type' => $map->target_type, 'id' => $map->target_id, 'filters' => $map->filters ?? []] : null;
        if (! $target && (($query['option'] ?? '') === 'com_virtuemart')) {
            if (($query['view'] ?? '') === 'productdetails' && is_scalar($query['virtuemart_product_id'] ?? null)) {
                $p = Product::withTrashed()->where('legacy_product_id', $query['virtuemart_product_id'])->first();
                if ($p) {
                    $target = ['type' => 'product', 'id' => $p->id, 'filters' => []];
                }
            } elseif (($query['view'] ?? '') === 'category' && is_scalar($query['virtuemart_category_id'] ?? 0)) {
                $target = $this->categoryTarget((int) ($query['virtuemart_category_id'] ?? 0));
            }
        }
        if (! $target) {
            $map = $this->verifiedMapping($path, []);
            if ($map) {
                $target = ['type' => $map->target_type, 'id' => $map->target_id, 'filters' => $map->filters ?? []];
            }
        }
        if (! $target) {
            return null;
        }
        if (in_array($target['type'], ['listing', 'brand', 'category'], true)) {
            if (is_string($query['q'] ?? null)) {
                $target['filters']['q'] = mb_substr($query['q'], 0, 100);
            }
            $categories = $query['virtuemart_category_id'] ?? [];
            foreach (is_array($categories) ? $categories : [$categories] as $sourceId) {
                if (! is_scalar($sourceId)) {
                    continue;
                }
                $extra = $this->categoryTarget((int) $sourceId);
                if (! $extra) {
                    continue;
                }
                if ($target['type'] === $extra['type'] && $target['id'] === $extra['id']) {
                    continue;
                }
                if ($extra['type'] === 'brand') {
                    $slug = Brand::whereKey($extra['id'])->value('slug');
                    if ($slug) {
                        $target['filters']['brands'][] = $slug;
                    }
                }
                if ($extra['type'] === 'category') {
                    $slug = Category::whereKey($extra['id'])->value('slug');
                    if ($slug) {
                        $target['filters']['categories'][] = $slug;
                    }
                }
                if ($extra['type'] === 'listing') {
                    $target['filters'] = array_replace($target['filters'], $extra['filters']);
                }
            }
            if (($query['orderby'] ?? '') === 'product_name') {
                $target['filters']['sort'] = 'name';
            }
            if (($query['orderby'] ?? '') === 'created_on') {
                $target['filters']['sort'] = 'newest';
            }
            $offset = $query['limitstart'] ?? $query['start'] ?? 0;
            if (is_numeric($offset) && $offset > 0) {
                $target['filters']['page'] = min(10000, (int) floor((int) $offset / 12) + 1);
            }
        }

        return $this->destination($target['type'], $target['id'], $target['filters']);
    }

    public function rememberSlug(Product|Brand|Category $record, string $old): void
    {
        if ($old === '' || $old === $record->slug) {
            return;
        }
        $prefix = $record instanceof Product ? 'machinery' : $record->getTable();
        $path = '/'.$prefix.'/'.$old;
        LegacyUrlMapping::updateOrCreate(['source_hash' => $this->hash($path)], ['source_path' => $path, 'source_query' => null, 'target_type' => $record instanceof Product ? 'product' : ($record instanceof Brand ? 'brand' : 'category'), 'target_id' => $record->id, 'filters' => null, 'evidence' => 'slug-history', 'verification_status' => 'verified']);
    }
}
