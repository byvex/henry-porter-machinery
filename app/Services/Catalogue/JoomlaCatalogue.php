<?php

namespace App\Services\Catalogue;

use Illuminate\Support\Str;
use RuntimeException;

class JoomlaCatalogue
{
    public const BRANDS = [3 => ['pottinger', 'Pöttinger'], 4 => ['bobman', 'Bobman'], 5 => ['hustler', 'Hustler'], 6 => ['multione', 'MultiOne'], 7 => ['strautmann', 'Strautmann'], 8 => ['fleming', 'Fleming'], 23 => ['sherpa', 'Sherpa'], -1 => ['malone', 'Malone'], -2 => ['reck', 'Reck']];

    public const CATEGORIES = [
        9 => ['grassland', 'Grassland', null], 10 => ['tillage', 'Tillage', null],
        12 => ['tedders', 'Tedders', 'grassland'], 13 => ['silage-wagons', 'Silage Wagons', 'grassland'],
        14 => ['tillers', 'Tillers', 'tillage'], 15 => ['rakes', 'Rakes', 'grassland'],
        16 => ['mowers', 'Mowers', 'grassland'], 17 => ['balers', 'Balers', 'grassland'],
        18 => ['cubicle-cleaners', 'Cubicle Cleaners', null], 19 => ['feeders', 'Feeders', null],
        11 => ['other-machinery', 'Other Machinery', null],
        -1 => ['compact-loaders', 'Compact Loaders', null], -2 => ['log-splitters', 'Log Splitters', null],
        -3 => ['post-drivers', 'Post Drivers', null], -4 => ['slurry-mixers', 'Slurry Mixers', null],
    ];

    public function __construct(private JoomlaSqlReader $reader, private CatalogueMedia $media, private CatalogueHtml $html) {}

    /**
     * @return array<string, mixed>
     */
    public function prepare(string $source): array
    {
        $root = realpath($source);
        if ($root === false || ! is_dir($root.'/files') || ! is_file($root.'/db/db.sql')) {
            throw new RuntimeException('Expected a backup folder containing db/db.sql and files/.');
        }
        $dump = $this->reader->read($root.'/db/db.sql');
        $tables = $dump['tables'];
        $get = fn ($name) => $tables['virtuemart_'.$name] ?? [];
        $products = $this->indexed($get('products'), 'virtuemart_product_id');
        $texts = $this->indexed($get('products_en_gb'), 'virtuemart_product_id');
        $categories = $this->indexed($get('categories'), 'virtuemart_category_id');
        $categoryTexts = $this->indexed($get('categories_en_gb'), 'virtuemart_category_id');
        if ($products === []) {
            throw new RuntimeException('The source catalogue is empty.');
        }
        foreach ($products as $id => $product) {
            if (! isset($texts[$id]) || trim($texts[$id]['product_name'] ?? '') === '') {
                throw new RuntimeException('Missing product translation/name for source ID '.$id);
            }
            if ((int) ($product['product_parent_id'] ?? 0) !== 0) {
                throw new RuntimeException('Unexpected product variants require an explicit mapping.');
            }
        }
        foreach ($categories as $id => $category) {
            if (! isset($categoryTexts[$id]) || ! $this->categoryTarget((int) $id)) {
                throw new RuntimeException('Unmapped source category: '.$id);
            }
        }
        $plan = ['source_checksum' => $dump['checksum'], 'source_prefix' => $dump['prefix'], 'assets' => [], 'brands' => [], 'categories' => [], 'products' => [], 'videos' => [], 'testimonials' => [], 'urls' => [], 'warnings' => [], 'unresolved_references' => [], 'audit' => array_filter($tables, fn ($key) => str_starts_with($key, 'virtuemart_'), ARRAY_FILTER_USE_KEY)];
        $sourceMedia = [];
        $pathAssets = [];
        $addAsset = function (string $url, ?array $row = null) use (&$plan, &$pathAssets, &$sourceMedia, $root): string {
            $asset = $this->media->inspect($root.'/files', $url);
            if ($row === null && isset($pathAssets[$asset['source_path']])) {
                return $pathAssets[$asset['source_path']];
            }
            $id = $row['virtuemart_media_id'] ?? null;
            $key = $id === null ? 'embedded:'.hash('sha256', $asset['source_path']) : 'media:'.$id;
            $plan['assets'][$key] = $asset + [
                'source_key' => 'joomla:'.$key, 'legacy_media_id' => $id === null ? null : (int) $id,
                'title' => $row['file_title'] ?? basename($asset['source_path']),
                'alt_text' => ($row['file_meta'] ?? '') ?: (($row['file_description'] ?? '') ?: ($row['file_title'] ?? '')),
                'source_published' => ($row['published'] ?? '1') === '1',
            ];
            $pathAssets[$asset['source_path']] ??= $key;
            if ($id !== null) {
                $sourceMedia[(int) $id] = $key;
            }

            return $key;
        };
        foreach ($get('medias') as $row) {
            if (isset($sourceMedia[(int) $row['virtuemart_media_id']])) {
                throw new RuntimeException('Duplicate source media ID.');
            }
            $addAsset($row['file_url'], $row);
        }
        $resolve = function (string $url, bool $image) use ($addAsset, &$plan): ?string {
            $url = html_entity_decode(trim($url), ENT_QUOTES | ENT_HTML5, 'UTF-8');
            $parts = parse_url($url);
            if ($parts === false) {
                return null;
            }
            $scheme = strtolower($parts['scheme'] ?? '');
            if ($scheme !== '' && ! in_array($scheme, ['http', 'https', 'mailto', 'tel'], true)) {
                return null;
            }
            if (in_array($scheme, ['mailto', 'tel'], true)) {
                return $image ? null : $url;
            }
            if (isset($parts['host']) && ! $this->media->ownHost($parts['host'])) {
                if ($image) {
                    $plan['warnings'][] = 'External embedded image retained: '.$url;
                }

                return $url;
            }
            if ($url === '' || str_starts_with($url, '#')) {
                return $image ? null : $url;
            }
            if ($image || preg_match('/\.(?:jpe?g|png|gif|webp|avif|pdf)(?:$|\?)/i', $url)) {
                $key = $addAsset($url);

                $base = (string) config('filesystems.disks.public.url');
                if (config('filesystems.disks.public.driver') === 'local') {
                    // Stored HTML must survive a move from localhost to the production domain.
                    $base = parse_url($base, PHP_URL_PATH) ?: '/storage';
                }

                return rtrim($base, '/').'/'.$plan['assets'][$key]['storage_path'];
            }

            return $scheme === '' ? 'https://henryportermachinery.co.uk/'.ltrim($url, '/') : $url;
        };
        $categoryImages = [];
        foreach ($get('category_medias') as $row) {
            $this->requireReference($categories, $row['virtuemart_category_id'], 'category media category');
            $this->requireReference($sourceMedia, $row['virtuemart_media_id'], 'category media');
            $categoryImages[(int) $row['virtuemart_category_id']] ??= $sourceMedia[(int) $row['virtuemart_media_id']];
        }
        foreach (self::BRANDS as $sourceId => [$slug, $name]) {
            $plan['brands'][$slug] = ['source_key' => 'joomla:brand:'.$slug, 'name' => $name, 'slug' => $slug, 'description' => null, 'logo_key' => $categoryImages[$sourceId] ?? null, 'sort_order' => count($plan['brands']), 'is_active' => true];
        }
        foreach (self::CATEGORIES as $sourceId => [$slug, $name, $parent]) {
            $plan['categories'][$slug] = ['source_key' => 'joomla:category:'.$slug, 'name' => $name, 'slug' => $slug, 'parent_slug' => $parent, 'description' => $this->html->clean($categoryTexts[$sourceId]['category_description'] ?? '', $resolve), 'image_key' => $categoryImages[$sourceId] ?? null, 'sort_order' => count($plan['categories']), 'is_active' => ($categories[$sourceId]['published'] ?? '1') === '1'];
        }
        $assignments = [];
        foreach ($get('product_categories') as $row) {
            $this->requireReference($products, $row['virtuemart_product_id'], 'product category product');
            $this->requireReference($categories, $row['virtuemart_category_id'], 'product category');
            $assignments[(int) $row['virtuemart_product_id']][(int) $row['virtuemart_category_id']] = max(0, (int) ($row['ordering'] ?? 0));
        }
        $galleries = [];
        foreach ($get('product_medias') as $row) {
            if ((int) $row['virtuemart_product_id'] === 0) {
                $this->requireReference($sourceMedia, $row['virtuemart_media_id'], 'orphan gallery media');
                $plan['unresolved_references'][] = ['type' => 'orphan_gallery', 'source_link_id' => (int) $row['id'], 'legacy_product_id' => 0, 'legacy_media_id' => (int) $row['virtuemart_media_id'], 'action' => 'association_excluded_media_preserved'];

                continue;
            }
            $this->requireReference($products, $row['virtuemart_product_id'], 'gallery product');
            $this->requireReference($sourceMedia, $row['virtuemart_media_id'], 'gallery media');
            $galleries[(int) $row['virtuemart_product_id']][] = ['media_key' => $sourceMedia[(int) $row['virtuemart_media_id']], 'legacy_product_media_id' => (int) $row['id'], 'sort_order' => max(0, (int) $row['ordering'])];
        }
        ksort($products, SORT_NUMERIC);
        $slugs = [];
        foreach ($products as $id => $product) {
            $id = (int) $id;
            $text = $texts[$id];
            $type = in_array($id, [7, 8, 9, 10, 11, 12, 52, 56], true) ? 'template' : ($id === 60 ? 'content' : 'machinery');
            $name = trim($text['product_name']);
            $slug = Str::slug($name) ?: 'product-'.$id;
            if (isset($slugs[$slug])) {
                $slug .= '-'.$id;
            }
            if (isset($slugs[$slug])) {
                throw new RuntimeException('Product slug collision requires review: '.$id);
            }
            $slugs[$slug] = true;
            $oldCategories = $assignments[$id] ?? [];
            $brand = null;
            $equipment = [];
            foreach ($oldCategories as $categoryId => $order) {
                if (isset(self::BRANDS[$categoryId])) {
                    if ($brand !== null && $brand !== self::BRANDS[$categoryId][0]) {
                        throw new RuntimeException('Product has conflicting brands: '.$id);
                    }
                    $brand = self::BRANDS[$categoryId][0];
                }
                if (isset(self::CATEGORIES[$categoryId])) {
                    $equipment[self::CATEGORIES[$categoryId][0]] = $order;
                }
            }
            $brand = match (true) {
                in_array($id, [57, 58], true) => 'malone', $id === 59 => 'reck',
                in_array($id, [84, 85, 86, 87, 88], true) => 'pottinger', default => $brand,
            };
            $extra = match (true) {
                $id === 43 => ['feeders'], $id === 51 || in_array($id, [76, 77, 78, 79, 80, 81], true) => ['compact-loaders'],
                $id === 57 => ['log-splitters'], $id === 58 => ['post-drivers'], $id === 59 => ['slurry-mixers'],
                $id === 50 => ['other-machinery'], in_array($id, [84, 85, 86], true) => ['silage-wagons', 'grassland'],
                $id === 87 => ['rakes', 'grassland'], $id === 88 => ['mowers', 'grassland'], default => [],
            };
            foreach ($extra as $category) {
                $equipment[$category] ??= 0;
            }
            if ($type === 'machinery' && ($brand === null || $equipment === [])) {
                throw new RuntimeException('Machinery has no explicit brand/equipment mapping: '.$id);
            }
            $flags = $type === 'machinery' ? [] : ['legacy_'.$type];
            if (in_array($id, [78, 80], true)) {
                $flags[] = 'duplicate_name_review';
            }
            $plan['products'][$id] = [
                'legacy_product_id' => $id, 'name' => $name, 'slug' => $slug,
                'summary' => $this->html->clean($text['product_s_desc'] ?? '', $resolve),
                'description_html' => $this->html->clean($text['product_desc'] ?? '', $resolve),
                'brand_slug' => $brand, 'condition' => $type === 'machinery' ? (isset($oldCategories[2]) ? 'used' : 'new') : null,
                'status' => $type === 'machinery' && $product['published'] === '1' ? 'published' : 'draft',
                'record_type' => $type, 'is_featured' => ($product['product_special'] ?? '0') === '1',
                'sort_order' => max(0, (int) ($product['pordering'] ?? 0)), 'review_flags' => $flags,
                'source_metadata' => ['slug' => $text['slug'], 'published' => $product['published'], 'categories' => array_keys($oldCategories), 'sku' => $product['product_sku'] ?? null, 'meta_title' => $text['customtitle'] ?? '', 'meta_description' => $text['metadesc'] ?? '', 'meta_keywords' => $text['metakey'] ?? '', 'robots' => $product['metarobot'] ?? ''],
                'created_at' => $this->date($product['created_on'] ?? null), 'updated_at' => $this->date($product['modified_on'] ?? null),
                'category_slugs' => $equipment, 'gallery' => $galleries[$id] ?? [],
            ];
        }
        $definitions = $this->indexed($get('customs'), 'virtuemart_custom_id');
        foreach ($get('product_customfields') as $field) {
            $id = (int) $field['virtuemart_product_id'];
            $this->requireReference($products, $id, 'custom field product');
            $this->requireReference($definitions, $field['virtuemart_custom_id'], 'custom field definition');
            // VirtueMart filters the definition's published flag, not the instance's fpublished=0.
            if (($definitions[$field['virtuemart_custom_id']]['published'] ?? '0') !== '1') {
                $plan['warnings'][] = 'Unpublished custom definition for field '.$field['virtuemart_customfield_id'];

                continue;
            }
            if ((int) ($field['disabler'] ?? 0) !== 0 || (int) ($field['override'] ?? 0) !== 0) {
                throw new RuntimeException('Custom field inheritance requires review.');
            }
            $base = ['legacy_product_id' => $id, 'legacy_customfield_id' => (int) $field['virtuemart_customfield_id'], 'sort_order' => max(0, (int) $field['ordering'])];
            if ((int) $field['virtuemart_custom_id'] === 3) {
                if (preg_match('~https://(?:www\.)?youtube\.com/embed/([a-zA-Z0-9_-]{11})(?=[\s"\x27?/<]|$)~', $field['customfield_value'], $match)) {
                    $plan['videos'][] = $base + ['provider' => 'youtube', 'video_id' => $match[1]];
                } else {
                    $plan['warnings'][] = 'Invalid video retained in audit: source custom field '.$field['virtuemart_customfield_id'];
                }
            } elseif ((int) $field['virtuemart_custom_id'] === 4) {
                $plan['testimonials'][] = $base + ['content_html' => $this->html->clean($field['customfield_value'], $resolve)];
            } else {
                throw new RuntimeException('Unmapped custom field definition.');
            }
        }
        if ($plan['unresolved_references'] !== []) {
            $plan['warnings'][] = count($plan['unresolved_references']).' gallery links with source product ID 0 excluded; their images and original associations are preserved in the audit.';
        }
        $plan['urls'] = $this->urls($tables, $plan);
        $plan['counts'] = [
            'products' => count($plan['products']), 'published_machinery' => count(array_filter($plan['products'], fn ($p) => $p['status'] === 'published' && $p['record_type'] === 'machinery')),
            'brands' => count($plan['brands']), 'categories' => count($plan['categories']),
            'registered_media' => count($get('medias')), 'media_assets' => count($plan['assets']),
            'source_product_media' => count($get('product_medias')), 'product_media' => array_sum(array_map('count', $galleries)), 'videos' => count($plan['videos']), 'testimonials' => count($plan['testimonials']), 'legacy_urls' => count($plan['urls']),
        ];

        return $plan;
    }

    /**
     * @return array{type: string, key: string|int|null, filters: array<string, string>|null}|null
     */
    public function categoryTarget(int $id): ?array
    {
        if (isset(self::BRANDS[$id])) {
            return ['type' => 'brand', 'key' => self::BRANDS[$id][0], 'filters' => null];
        }
        if (isset(self::CATEGORIES[$id])) {
            return ['type' => 'category', 'key' => self::CATEGORIES[$id][0], 'filters' => null];
        }

        return match ($id) {
            0 => ['type' => 'listing', 'key' => null, 'filters' => []],
            1, 20 => ['type' => 'listing', 'key' => null, 'filters' => ['condition' => 'new']],
            2 => ['type' => 'listing', 'key' => null, 'filters' => ['condition' => 'used']],
            21 => ['type' => 'brand', 'key' => 'pottinger', 'filters' => null],
            22 => ['type' => 'content', 'key' => 60, 'filters' => null],
            default => null,
        };
    }

    /**
     * @param  array<string, list<array<string, string|null>>>  $tables
     * @param  array<string, mixed>  $plan
     * @return list<array<string, mixed>>
     */
    private function urls(array $tables, array $plan): array
    {
        $urls = [];
        $add = function (string $url, array $target, string $evidence, bool $replace = false) use (&$urls): void {
            $parts = parse_url(html_entity_decode($url, ENT_QUOTES | ENT_HTML5, 'UTF-8'));
            if ($parts === false || isset($parts['host']) && ! $this->media->ownHost($parts['host'])) {
                return;
            }
            $path = '/'.ltrim($parts['path'] ?? '', '/');
            parse_str($parts['query'] ?? '', $query);
            ksort($query);
            $query = http_build_query($query);
            $hash = hash('sha256', $path.'?'.$query);
            if (! isset($urls[$hash]) || $replace) {
                $urls[$hash] = ['source_hash' => $hash, 'source_path' => $path, 'source_query' => $query ?: null, 'target' => $target, 'evidence' => $evidence];
            }
        };
        $menus = [];
        foreach ($tables['menu'] as $menu) {
            if (($menu['client_id'] ?? '0') !== '0' || ($menu['published'] ?? '0') !== '1') {
                continue;
            }
            $target = $this->queryTarget($menu['link'] ?? '', $plan);
            if ($target === null) {
                continue;
            }
            $path = trim($menu['path'] ?? '', '/');
            if ($path === '') {
                continue;
            }
            $add('/'.$path.'.html', $target, 'public-menu', true);
            $add('/'.ltrim($menu['link'], '/').'&Itemid='.$menu['id'], $target, 'public-menu-query', true);
            $menus[] = ['path' => $path, 'target' => $target];
        }
        foreach ($plan['products'] as $id => $product) {
            $target = ['type' => $product['record_type'] === 'content' ? 'content' : 'product', 'key' => $id, 'filters' => null];
            $add('/index.php?option=com_virtuemart&view=productdetails&virtuemart_product_id='.$id, $target, 'source-product-id');
            foreach ($menus as $menu) {
                $menuTarget = $menu['target'];
                $matches = match ($menuTarget['type']) {
                    'brand' => $product['brand_slug'] === $menuTarget['key'],
                    'category' => array_key_exists($menuTarget['key'], $product['category_slugs']),
                    'listing' => empty($menuTarget['filters']) || ($menuTarget['filters']['condition'] ?? null) === $product['condition'],
                    default => false,
                };
                if (! $matches || $product['record_type'] !== 'machinery') {
                    continue;
                }
                $old = $product['source_metadata']['slug'];
                $add('/'.$menu['path'].'/'.$old.'-detail.html', $target, 'inferred-alias');
            }
        }
        foreach (array_keys($this->indexed($tables['virtuemart_categories'], 'virtuemart_category_id')) as $id) {
            $target = $this->categoryTarget((int) $id);
            $add('/index.php?option=com_virtuemart&view=category&virtuemart_category_id='.$id, $target, 'source-category-id');
        }
        foreach (['finder_links', 'redirect_links'] as $table) {
            foreach ($tables[$table] as $row) {
                $url = $row['url'] ?? $row['old_url'] ?? '';
                $target = $this->queryTarget($url, $plan);
                if ($target !== null) {
                    $add($url, $target, 'stored-'.$table, true);
                }
            }
        }

        return array_values($urls);
    }

    /**
     * @param  array<string, mixed>  $plan
     * @return array{type: string, key: string|int|null, filters: array<string, string>|null}|null
     */
    private function queryTarget(string $url, array $plan): ?array
    {
        parse_str(parse_url(html_entity_decode($url), PHP_URL_QUERY) ?: '', $query);
        if (($query['option'] ?? '') !== 'com_virtuemart') {
            return null;
        }
        if (($query['view'] ?? '') === 'productdetails' && isset($plan['products'][(int) ($query['virtuemart_product_id'] ?? 0)])) {
            $id = (int) $query['virtuemart_product_id'];

            return ['type' => $plan['products'][$id]['record_type'] === 'content' ? 'content' : 'product', 'key' => $id, 'filters' => null];
        }

        return ($query['view'] ?? '') === 'category' ? $this->categoryTarget((int) ($query['virtuemart_category_id'] ?? 0)) : null;
    }

    /**
     * @param  list<array<string, string|null>>  $rows
     * @return array<int|string, array<string, string|null>>
     */
    private function indexed(array $rows, string $column): array
    {
        $indexed = [];
        foreach ($rows as $row) {
            if (! isset($row[$column]) || isset($indexed[$row[$column]])) {
                throw new RuntimeException('Missing or duplicate catalogue source identifier: '.$column);
            }
            $indexed[$row[$column]] = $row;
        }

        return $indexed;
    }

    /**
     * @param  array<int|string, mixed>  $rows
     */
    private function requireReference(array $rows, int|string $id, string $label): void
    {
        if (! array_key_exists($id, $rows)) {
            throw new RuntimeException('Broken source relationship: '.$label.' '.$id);
        }
    }

    private function date(?string $value): ?string
    {
        if (! $value || str_starts_with($value, '0000-') || strtotime($value) === false) {
            return null;
        }

        return $value;
    }
}
