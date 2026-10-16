<?php

namespace App\Services\Catalogue;

use App\Models\Brand;
use App\Models\Category;
use App\Models\MediaAsset;
use App\Models\Product;
use Illuminate\Support\Str;

class JoomlaMediaPlan
{
    /** @param array<string, mixed> $plan
     * @return array<string, mixed>
     */
    public function prepare(array $plan, bool $overwrite): array
    {
        $fallback = now()->format('Y-m-d');
        $dates = [];
        foreach ($plan['audit']['virtuemart_categories'] ?? [] as $row) {
            $dates[(int) $row['virtuemart_category_id']] = $row['created_on'] ?? null;
        }
        $models = ['products' => Product::class, 'categories' => Category::class, 'brands' => Brand::class];
        $plan['skipped_edited'] = [];
        foreach ($models as $type => $class) {
            $sourceIds = $type === 'brands' ? JoomlaCatalogue::BRANDS : JoomlaCatalogue::CATEGORIES;
            foreach ($plan[$type] as $key => &$row) {
                $existing = $type === 'products'
                    ? $class::withTrashed()->where('legacy_product_id', $row['legacy_product_id'])->first()
                    : $class::withTrashed()->where('source_key', $row['source_key'])->first();
                $row['id'] = $existing ? $existing->getKey() : (string) Str::uuid7();
                $sourceDate = $row['created_at'] ?? null;
                if ($type !== 'products') {
                    foreach ($sourceIds as $sourceId => $mapping) {
                        if ($mapping[0] === $row['slug']) {
                            $sourceDate = $dates[$sourceId] ?? null;
                            break;
                        }
                    }
                    $row['created_at'] = $sourceDate && ! str_starts_with($sourceDate, '0000-') ? $sourceDate : ($existing ? ($existing->getAttribute('created_at') ?? now()) : now());
                }
                $valid = is_string($sourceDate) && preg_match('/^\d{4}-\d{2}-\d{2}/', $sourceDate) && ! str_starts_with($sourceDate, '0000-');
                $row['media_folder_date'] = ($existing ? $existing->getRawOriginal('media_folder_date') : null) ?? ($valid ? substr($sourceDate, 0, 10) : $fallback);
                if (! $valid) {
                    $plan['warnings'][] = 'Media folder date fallback: '.$type.' '.$key.' uses '.$row['media_folder_date'];
                }
                $row['_skip'] = $existing && ($existing->admin_modified_at || $existing->trashed()) && ! $overwrite;
                if ($row['_skip']) {
                    $plan['skipped_edited'][] = $type.':'.$key;
                }
            }
            unset($row);
        }
        // A single source asset has one stable home, even when reused by several records.
        $owners = [];
        $sorted = $plan['products'];
        ksort($sorted, SORT_NUMERIC);
        foreach ($sorted as $row) {
            foreach ($row['gallery'] as $link) {
                $owners[$link['media_key']] ??= ['products', $row];
            }
        }
        foreach (['categories', 'brands'] as $type) {
            foreach ($plan[$type] as $row) {
                $key = $row[$type === 'brands' ? 'logo_key' : 'image_key'];
                if ($key !== null) {
                    $owners[$key] ??= [$type, $row];
                }
            }
        }
        // Embedded-only files belong to the first product/category which references them.
        foreach (['products', 'categories'] as $type) {
            $rows = $plan[$type];
            if ($type === 'products') {
                ksort($rows, SORT_NUMERIC);
            }
            foreach ($rows as $row) {
                $html = ($row['summary'] ?? '').($row['description_html'] ?? '').($row['description'] ?? '');
                foreach ($plan['assets'] as $key => $asset) {
                    if (str_contains($html, $asset['storage_path'])) {
                        $owners[$key] ??= [$type, $row];
                    }
                }
            }
        }
        $protectedMedia = [];
        $protectedHtml = '';
        if (! $overwrite) {
            foreach (['products', 'categories', 'brands'] as $type) {
                $class = $models[$type];
                foreach ($plan[$type] as $row) {
                    if (! $row['_skip']) {
                        continue;
                    }
                    $record = $class::withTrashed()->whereKey($row['id'])->firstOrFail();
                    if ($record instanceof Product) {
                        foreach ($record->media()->pluck('media_assets.id') as $id) {
                            $protectedMedia[$id] = true;
                        }
                        $protectedHtml .= ($record->description_html ?? '').($record->summary ?? '');
                    } else {
                        foreach (['image_media_id', 'logo_media_id'] as $field) {
                            if ($record->getAttribute($field)) {
                                $protectedMedia[$record->getAttribute($field)] = true;
                            }
                        }
                        $protectedHtml .= $record->description ?? '';
                    }
                }
            }
        }
        $replace = [];
        foreach ($plan['assets'] as $key => &$asset) {
            $existing = MediaAsset::where('source_key', $asset['source_key'])->first();
            $asset['id'] = $existing ? $existing->getKey() : (string) Str::uuid7();
            $old = $asset['storage_path'];
            if ($existing) {
                $asset['storage_path'] = $existing->storage_path;
                if (! $overwrite && ($existing->admin_modified_at || isset($protectedMedia[$existing->id]) || str_contains($protectedHtml, $existing->storage_path))) {
                    foreach (['title', 'alt_text', 'checksum', 'mime_type'] as $field) {
                        $asset[$field] = $existing->$field;
                    }
                    $asset['_skip'] = true;
                }
            } else {
                $folder = isset($owners[$key])
                    ? $owners[$key][0].'/'.str_replace('-', '/', substr($owners[$key][1]['media_folder_date'], 0, 7)).'-'.substr($owners[$key][1]['media_folder_date'], 8, 2).'/'.$owners[$key][1]['id']
                    : 'legacy/'.now()->format('Y/m-d');
                $asset['storage_path'] = $folder.'/'.app(MediaFolders::class)->filename($asset['source_path'], pathinfo($old, PATHINFO_EXTENSION));
            }
            $replace[$old] = $asset['storage_path'];
        }
        unset($asset);
        foreach (['products', 'categories'] as $type) {
            foreach ($plan[$type] as &$row) {
                foreach (['summary', 'description_html', 'description'] as $field) {
                    if (isset($row[$field])) {
                        $row[$field] = strtr($row[$field], $replace);
                    }
                }
            }
            unset($row);
        }
        foreach ($plan['testimonials'] as &$row) {
            $row['content_html'] = strtr($row['content_html'], $replace);
        }
        unset($row);

        return $plan;
    }
}
