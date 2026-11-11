<?php

namespace App\Console\Commands;

use App\Models\Brand;
use App\Models\Category;
use App\Models\LegacyUrlMapping;
use App\Models\MediaAsset;
use App\Models\Product;
use App\Models\ProductTestimonial;
use App\Services\Catalogue\CatalogueAdmin;
use App\Services\Catalogue\LegacyUrls;
use App\Services\Catalogue\MediaFolders;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;
use Throwable;

class UpdateBobmanMultiOneCatalogue extends Command
{
    protected $signature = 'catalogue:update-bobman-multione {--apply : Download/stage assets and write changes to the database and public storage} {--skip-url-verify : Skip running the full sitemap URL verification}';

    protected $description = 'Update the Bobman and MultiOne catalogue lines with current models, galvanised imagery, electric specs, PDF datasheets, and redirects';

    public function handle(CatalogueAdmin $admin, MediaFolders $folders, LegacyUrls $legacyUrls): int
    {
        /** @var array<string, mixed> $manifest */
        $manifest = require resource_path('data/bobman-multione-update.php');
        $apply = (bool) $this->option('apply');

        $brands = Brand::whereIn('slug', array_keys($manifest['brands']))->get()->keyBy('slug');
        foreach (array_keys($manifest['brands']) as $brandSlug) {
            if (! $brands->has($brandSlug)) {
                throw new RuntimeException('Missing required brand: '.$brandSlug);
            }
        }

        $requiredCategories = collect($manifest['products'])->flatMap(fn (array $p) => $p['categories'])->unique()->values()->all();
        $categories = Category::whereIn('slug', $requiredCategories)->get()->keyBy('slug');
        foreach ($requiredCategories as $categorySlug) {
            if (! $categories->has($categorySlug)) {
                throw new RuntimeException('Missing required category: '.$categorySlug);
            }
        }

        $rows = [];
        foreach ($manifest['retired_products'] as $retired) {
            $existing = Product::withTrashed()->where('slug', $retired['slug'])->first();
            $rows[] = [$retired['slug'], $retired['brand_slug'], 'retire -> draft (301 to /brands/'.$retired['brand_slug'].')', $existing ? 'found' : 'missing'];
        }

        foreach ($manifest['products'] as $entry) {
            foreach ($entry['videos'] as $video) {
                if (! $admin->youtube($video)) {
                    throw new RuntimeException('Invalid YouTube video ID for '.$entry['slug'].': '.$video);
                }
            }
            $admin->clean($entry['summary']);
            $admin->clean($entry['description_html']);
            $existing = Product::withTrashed()->where('slug', $entry['slug'])->first();
            $rows[] = [
                $entry['slug'],
                $entry['brand_slug'],
                ($existing ? 'update' : 'create').' published ('.count($entry['gallery']).' imgs, '.count($entry['pdfs'] ?? []).' pdfs, '.count($entry['videos']).' videos)',
                $existing ? 'existing' : 'new',
            ];
        }

        $this->table(['Slug', 'Brand', 'Action', 'Record'], $rows);

        if (! $apply) {
            $this->info('Dry run validated '.count($manifest['products']).' published products and '.count($manifest['retired_products']).' retired products. Pass --apply to write changes.');

            return self::SUCCESS;
        }

        $multioneLegacy = Product::withTrashed()->where('slug', 'multione-compact-loaders')->first();
        $multioneTestimonials = $multioneLegacy
            ? $multioneLegacy->testimonials()->orderBy('sort_order')->pluck('content_html')->all()
            : [];
        if ($multioneTestimonials === []) {
            $fallbackTestimonial = ProductTestimonial::whereHas('product', fn ($q) => $q->where('slug', 'like', 'multione-%'))->value('content_html');
            if (is_string($fallbackTestimonial) && $fallbackTestimonial !== '') {
                $multioneTestimonials = [$fallbackTestimonial];
            }
        }

        $writtenPaths = [];
        try {
            DB::transaction(function () use ($manifest, $brands, $categories, $admin, $folders, $legacyUrls, $multioneTestimonials, &$writtenPaths): void {
                $now = now();

                foreach ($manifest['brands'] as $brandSlug => $brandData) {
                    /** @var Brand $brand */
                    $brand = $brands->get($brandSlug);
                    $brand->update([
                        'description' => $admin->clean($brandData['description']),
                        'admin_modified_at' => $now,
                    ]);
                }

                foreach ($manifest['retired_products'] as $retired) {
                    $product = Product::withTrashed()->where('slug', $retired['slug'])->first();
                    if (! $product) {
                        continue;
                    }
                    $product->update([
                        'status' => 'draft',
                        'is_featured' => false,
                        'admin_modified_at' => $now,
                    ]);
                    $path = '/machinery/'.$product->slug;
                    LegacyUrlMapping::updateOrCreate(
                        ['source_hash' => $legacyUrls->hash($path)],
                        [
                            'source_path' => $path,
                            'source_query' => null,
                            'target_type' => 'product',
                            'target_id' => $product->id,
                            'filters' => null,
                            'evidence' => 'slug-history',
                            'verification_status' => 'verified',
                        ]
                    );
                }

                foreach ($manifest['products'] as $entry) {
                    /** @var Brand $brand */
                    $brand = $brands->get($entry['brand_slug']);
                    $product = Product::withTrashed()->where('slug', $entry['slug'])->first() ?? new Product;
                    if (! $product->exists) {
                        $product->id = (string) Str::uuid7();
                    }
                    if ($product->trashed()) {
                        $product->restore();
                    }

                    $folder = $folders->folder($product);
                    $descriptionHtml = $entry['description_html'];

                    foreach ($entry['pdfs'] ?? [] as $placeholder => $pdfSpec) {
                        $pdfAsset = $this->ensureAsset(
                            sourceKey: 'update:pdf:'.$entry['slug'].':'.$pdfSpec['filename'],
                            url: $pdfSpec['url'],
                            folder: $folder,
                            filename: $pdfSpec['filename'],
                            title: $pdfSpec['title'],
                            altText: $pdfSpec['title'],
                            expectedMimePrefix: 'application/pdf',
                            folders: $folders,
                            writtenPaths: $writtenPaths,
                        );
                        $relativeUrl = '/storage/'.ltrim($pdfAsset->storage_path, '/');
                        $descriptionHtml = str_replace($placeholder, $relativeUrl, $descriptionHtml);
                    }

                    $product->fill([
                        'name' => $entry['name'],
                        'slug' => $entry['slug'],
                        'summary' => $admin->clean($entry['summary']),
                        'description_html' => $admin->clean($descriptionHtml),
                        'brand_id' => $brand->id,
                        'condition' => $entry['condition'],
                        'status' => $entry['status'],
                        'record_type' => 'machinery',
                        'sort_order' => $entry['sort_order'],
                        'admin_modified_at' => $now,
                    ]);
                    $product->save();

                    LegacyUrlMapping::where('source_path', '/machinery/'.$product->slug)->delete();

                    $categorySync = [];
                    foreach ($entry['categories'] as $order => $catSlug) {
                        /** @var Category $cat */
                        $cat = $categories->get($catSlug);
                        $categorySync[$cat->id] = ['sort_order' => $order, 'source_imported' => false];
                    }
                    $product->categories()->sync($categorySync);

                    $mediaSync = [];
                    foreach ($entry['gallery'] as $index => $imgSpec) {
                        if (isset($imgSpec['existing_source_path'])) {
                            $asset = MediaAsset::where('source_path', $imgSpec['existing_source_path'])->first();
                            if (! $asset) {
                                throw new RuntimeException('Missing existing media asset: '.$imgSpec['existing_source_path']);
                            }
                            $asset->update([
                                'alt_text' => $imgSpec['alt_text'],
                                'admin_modified_at' => $now,
                            ]);
                        } else {
                            $asset = $this->ensureAsset(
                                sourceKey: 'update:img:'.$entry['slug'].':'.$imgSpec['filename'],
                                url: $imgSpec['url'],
                                folder: $folder,
                                filename: $imgSpec['filename'],
                                title: pathinfo($imgSpec['filename'], PATHINFO_FILENAME),
                                altText: $imgSpec['alt_text'],
                                expectedMimePrefix: 'image/',
                                folders: $folders,
                                writtenPaths: $writtenPaths,
                            );
                        }
                        $mediaSync[$asset->id] = ['sort_order' => $index];
                    }
                    $product->media()->sync($mediaSync);

                    $retainedVideoIds = [];
                    foreach ($entry['videos'] as $index => $videoId) {
                        $normalized = $admin->youtube($videoId);
                        if (! $normalized) {
                            throw new RuntimeException('Invalid YouTube video ID: '.$videoId);
                        }
                        $videoModel = $product->videos()->where('video_id', $normalized)->first() ?? $product->videos()->make();
                        $videoModel->fill([
                            'provider' => 'youtube',
                            'video_id' => $normalized,
                            'sort_order' => $index,
                        ]);
                        $videoModel->save();
                        $retainedVideoIds[] = $videoModel->id;
                    }
                    $product->videos()->whereNotIn('id', $retainedVideoIds)->delete();

                    if (! empty($entry['copy_multione_testimonial']) && $multioneTestimonials !== []) {
                        $retainedTestimonials = [];
                        foreach ($multioneTestimonials as $index => $html) {
                            $cleanHtml = $admin->clean($html);
                            $testimonialModel = $product->testimonials()->where('content_html', $cleanHtml)->first() ?? $product->testimonials()->make();
                            $testimonialModel->fill([
                                'content_html' => $cleanHtml,
                                'sort_order' => $index,
                            ]);
                            $testimonialModel->save();
                            $retainedTestimonials[] = $testimonialModel->id;
                        }
                        $product->testimonials()->whereNotIn('id', $retainedTestimonials)->delete();
                    }
                }
            });
        } catch (Throwable $error) {
            foreach ($writtenPaths as $path) {
                Storage::disk('public')->delete($path);
            }
            throw $error;
        }

        if (! $this->option('skip-url-verify')) {
            $this->call('catalogue:verify-legacy-urls', ['--apply' => true]);
        }
        $this->info('Bobman and MultiOne catalogue records, media assets, and legacy redirects updated.');

        return self::SUCCESS;
    }

    /**
     * @param  list<string>  $writtenPaths
     */
    private function ensureAsset(
        string $sourceKey,
        string $url,
        string $folder,
        string $filename,
        string $title,
        string $altText,
        string $expectedMimePrefix,
        MediaFolders $folders,
        array &$writtenPaths,
    ): MediaAsset {
        $existing = MediaAsset::where('source_key', $sourceKey)->first();
        if ($existing && Storage::disk('public')->exists($existing->storage_path)) {
            $existing->update([
                'title' => $title,
                'alt_text' => $altText,
                'admin_modified_at' => now(),
            ]);

            return $existing;
        }

        if (str_starts_with($url, 'local:') || file_exists($url)) {
            $localFile = str_starts_with($url, 'local:') ? substr($url, 6) : $url;
            if (! file_exists($localFile)) {
                throw new RuntimeException('Missing local asset file: '.$localFile);
            }
            $bytes = (string) file_get_contents($localFile);
        } else {
            $response = Http::timeout(60)->retry(2, 500)->withHeaders([
                'User-Agent' => 'HenryPorterMachineryCatalogueUpdater/1.0',
            ])->get($url);

            if (! $response->successful()) {
                throw new RuntimeException('Failed to download asset from '.$url.' (HTTP '.$response->status().')');
            }

            $bytes = $response->body();
        }
        if ($bytes === '') {
            throw new RuntimeException('Downloaded empty asset from '.$url);
        }

        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $mime = (string) $finfo->buffer($bytes);
        if (! str_starts_with($mime, $expectedMimePrefix)) {
            throw new RuntimeException('Unexpected MIME type "'.$mime.'" for '.$url.' (expected '.$expectedMimePrefix.'*)');
        }

        $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION) ?: 'bin');
        $storagePath = $existing?->storage_path ?: ($folder.'/'.$folders->filename($filename, $extension));

        if (! Storage::disk('public')->put($storagePath, $bytes)) {
            throw new RuntimeException('Could not write asset to public disk: '.$storagePath);
        }
        $writtenPaths[] = $storagePath;

        return MediaAsset::updateOrCreate(
            ['source_key' => $sourceKey],
            [
                'storage_path' => $storagePath,
                'source_path' => $url,
                'mime_type' => $mime,
                'checksum' => hash('sha256', $bytes),
                'title' => $title,
                'alt_text' => $altText,
                'source_published' => true,
                'admin_modified_at' => now(),
            ]
        );
    }
}
