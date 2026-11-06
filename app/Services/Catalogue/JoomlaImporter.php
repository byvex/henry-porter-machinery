<?php

namespace App\Services\Catalogue;

use App\Models\Brand;
use App\Models\Category;
use App\Models\LegacyUrlMapping;
use App\Models\MediaAsset;
use App\Models\Product;
use App\Models\ProductTestimonial;
use App\Models\ProductVideo;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use RuntimeException;
use Throwable;

class JoomlaImporter
{
    public function __construct(private JoomlaCatalogue $catalogue, private CatalogueMedia $media) {}

    /**
     * @return array<string, mixed>
     */
    public function run(string $source, bool $apply = false, bool $overwriteEdited = false): array
    {
        $plan = app(JoomlaMediaPlan::class)->prepare($this->catalogue->prepare($source), $overwriteEdited);
        $report = [
            'skipped_edited' => $plan['skipped_edited'],
            'mode' => $apply ? 'apply' : 'dry-run', 'status' => 'validated',
            'source_checksum' => $plan['source_checksum'], 'source_prefix' => $plan['source_prefix'],
            'counts' => $plan['counts'], 'warnings' => array_values(array_unique($plan['warnings'])),
            'review_records' => array_values(array_map(fn ($p) => ['legacy_product_id' => $p['legacy_product_id'], 'flags' => $p['review_flags']], array_filter($plan['products'], fn ($p) => $p['review_flags'] !== []))),
            'media_checksums' => array_map(fn ($a) => ['source_path' => $a['source_path'], 'checksum' => $a['checksum']], array_values($plan['assets'])),
            'unresolved_references' => $plan['unresolved_references'],
            'pending_legacy_urls' => array_map(fn ($u) => ['path' => $u['source_path'], 'query' => $u['source_query'], 'evidence' => $u['evidence']], $plan['urls']),
        ];
        if (! $apply) {
            return $report;
        }
        $directory = storage_path('app/private/catalogue-imports/'.Str::uuid());
        File::ensureDirectoryExists($directory);
        $report['report_path'] = $directory.'/report.json';
        $ledger = [];
        try {
            $this->json($directory.'/audit.json', ['source_checksum' => $plan['source_checksum'], 'tables' => $plan['audit']]);
            $staged = $this->media->stage(array_filter($plan['assets'], fn ($a) => ! ($a['_skip'] ?? false)), $directory.'/stage');
            DB::transaction(function () use ($plan, $staged, $directory, &$ledger, &$report): void {
                $this->persist($plan);
                $this->media->publish($staged, $directory.'/previous-media', $ledger);
                // Write the success report before commit; an I/O failure still rolls back the import.
                $report['status'] = 'imported';
                $this->json($report['report_path'], $report);
            });
            $report['status'] = 'imported';
        } catch (Throwable $error) {
            $report['status'] = 'failed';
            $report['error'] = $error->getMessage();
            try {
                $this->media->rollback($ledger);
            } catch (Throwable $rollbackError) {
                $report['media_recovery_error'] = $rollbackError->getMessage();
            }
            $this->json($report['report_path'], $report);
            throw new RuntimeException('Catalogue import failed; report: '.$report['report_path'].' — '.$error->getMessage(), previous: $error);
        } finally {
            // Only generated per-run staging is removed. Backups and reports are retained privately.
            File::deleteDirectory($directory.'/stage');
        }

        return $report;
    }

    /**
     * @param  array<string, mixed>  $plan
     */
    private function persist(array $plan): void
    {
        $media = [];
        foreach ($plan['assets'] as $key => $asset) {
            if ($asset['_skip'] ?? false) {
                $media[$key] = $asset['id'];

                continue;
            }
            $fields = $asset;
            unset($fields['absolute_path'], $fields['_skip']);
            $fields['admin_modified_at'] = null;
            $model = MediaAsset::updateOrCreate(['source_key' => $asset['source_key']], $fields);
            $media[$key] = $model->id;
        }
        $brands = [];
        foreach ($plan['brands'] as $key => $brand) {
            if ($brand['_skip']) {
                $brands[$key] = $brand['id'];

                continue;
            }
            $fields = $brand;
            if (! Brand::withTrashed()->where('source_key', $brand['source_key'])->exists()) {
                $homeOrder = array_search($brand['slug'], ['pottinger', 'bobman', 'hustler', 'multione', 'strautmann', 'fleming'], true);
                $fields['show_on_homepage'] = $homeOrder !== false;
                $fields['homepage_order'] = $homeOrder === false ? 0 : $homeOrder;
            }
            unset($fields['logo_key'], $fields['_skip']);
            $fields['deleted_at'] = null;
            $fields['admin_modified_at'] = null;
            $fields['logo_media_id'] = $brand['logo_key'] === null ? null : $media[$brand['logo_key']];
            $brands[$key] = Brand::withTrashed()->updateOrCreate(['source_key' => $brand['source_key']], $fields)->id;
        }
        $categories = [];
        foreach ($plan['categories'] as $key => $category) {
            if ($category['_skip']) {
                $categories[$key] = $category['id'];

                continue;
            }
            $fields = $category;
            if (! Category::withTrashed()->where('source_key', $category['source_key'])->exists()) {
                $homeOrder = array_search($category['slug'], ['compact-loaders', 'grassland', 'tillage', 'feeders', 'cubicle-cleaners', 'other-machinery'], true);
                $fields['show_on_homepage'] = $homeOrder !== false;
                $fields['homepage_order'] = $homeOrder === false ? 0 : $homeOrder;
            }
            unset($fields['parent_slug'], $fields['image_key'], $fields['_skip']);
            $fields['deleted_at'] = null;
            $fields['admin_modified_at'] = null;
            $fields['image_media_id'] = $category['image_key'] === null ? null : $media[$category['image_key']];
            $categories[$key] = Category::withTrashed()->updateOrCreate(['source_key' => $category['source_key']], $fields)->id;
        }
        foreach ($plan['categories'] as $key => $category) {
            if ($category['_skip']) {
                continue;
            }
            Category::whereKey($categories[$key])->update(['parent_id' => $category['parent_slug'] === null ? null : $categories[$category['parent_slug']]]);
        }
        $products = [];
        foreach ($plan['products'] as $id => $product) {
            if ($product['_skip']) {
                $products[$id] = $product['id'];

                continue;
            }
            $fields = $product;
            unset($fields['brand_slug'], $fields['category_slugs'], $fields['gallery'], $fields['_skip']);
            $fields['deleted_at'] = null;
            $fields['admin_modified_at'] = null;
            $fields['brand_id'] = $product['brand_slug'] === null ? null : $brands[$product['brand_slug']];
            $model = Product::withTrashed()->firstOrNew(['legacy_product_id' => $id]);
            $model->timestamps = false;
            $model->fill($fields)->save();
            $products[$id] = $model->id;
            $assignments = [];
            foreach ($product['category_slugs'] as $slug => $order) {
                $assignments[$categories[$slug]] = ['sort_order' => $order, 'source_imported' => true];
            }
            // Reconcile imported relationships, preserving manually attached relationships.
            $obsolete = $model->categories()->wherePivot('source_imported', true)->whereNotIn('categories.id', array_keys($assignments))->pluck('categories.id');
            $model->categories()->detach($obsolete);
            $model->categories()->syncWithoutDetaching($assignments);
            $sourceLinks = [];
            foreach ($product['gallery'] as $link) {
                $sourceLinks[] = $link['legacy_product_media_id'];
                DB::table('product_media')->updateOrInsert(['legacy_product_media_id' => $link['legacy_product_media_id']], [
                    'id' => DB::table('product_media')->where('legacy_product_media_id', $link['legacy_product_media_id'])->value('id') ?? (string) Str::uuid7(),
                    'product_id' => $model->id, 'media_asset_id' => $media[$link['media_key']], 'sort_order' => $link['sort_order'],
                ]);
            }
            DB::table('product_media')->where('product_id', $model->id)->whereNotNull('legacy_product_media_id')->whereNotIn('legacy_product_media_id', $sourceLinks)->delete();
        }
        foreach ([['videos', ProductVideo::class], ['testimonials', ProductTestimonial::class]] as [$type, $class]) {
            $retained = [];
            foreach ($plan[$type] as $row) {
                if ($plan['products'][$row['legacy_product_id']]['_skip']) {
                    continue;
                }
                $fields = $row;
                unset($fields['legacy_product_id']);
                $fields['product_id'] = $products[$row['legacy_product_id']];
                $class::updateOrCreate(['legacy_customfield_id' => $row['legacy_customfield_id']], $fields);
                $retained[] = $row['legacy_customfield_id'];
            }
            $class::whereIn('product_id', array_map(fn ($p) => $p['id'], array_filter($plan['products'], fn ($p) => ! $p['_skip'])))->whereNotNull('legacy_customfield_id')->whereNotIn('legacy_customfield_id', $retained)->delete();
        }
        foreach ($plan['urls'] as $url) {
            $target = $url['target'];
            $targetId = match ($target['type']) {
                'product', 'content' => $products[$target['key']] ?? null,
                'brand' => $brands[$target['key']] ?? null,
                'category' => $categories[$target['key']] ?? null,
                default => null,
            };
            if ($target['type'] !== 'listing' && $targetId === null) {
                throw new RuntimeException('Unresolved legacy URL target.');
            }
            $existingMap = LegacyUrlMapping::where('source_hash', $url['source_hash'])->first();
            $verified = $existingMap && $existingMap->target_type === $target['type'] && $existingMap->target_id === $targetId && $existingMap->filters === $target['filters'] ? $existingMap->verification_status : 'pending';
            LegacyUrlMapping::updateOrCreate(['source_hash' => $url['source_hash']], [
                'source_path' => $url['source_path'], 'source_query' => $url['source_query'],
                'target_type' => $target['type'], 'target_id' => $targetId, 'filters' => $target['filters'],
                'verification_status' => $verified, 'evidence' => $url['evidence'],
            ]);
        }
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function json(string $path, array $data): void
    {
        $json = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);
        if (File::put($path, $json) === false) {
            throw new RuntimeException('Could not write the private import report.');
        }
    }
}
