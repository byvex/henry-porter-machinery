<?php

namespace App\Services\Catalogue;

use App\Models\Brand;
use App\Models\Category;
use App\Models\MediaAsset;
use App\Models\Product;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Csv;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Throwable;

class CatalogueImportExport
{
    public function __construct(
        protected MediaFolders $folders,
        protected CatalogueAdmin $admin,
    ) {}

    /**
     * Export products into an .xlsx or .csv download.
     *
     * @param  array{brand_id?: string|null, status?: string|null, condition?: string|null}  $filters
     */
    public function export(string $format = 'xlsx', array $filters = []): BinaryFileResponse
    {
        $query = Product::with(['brand', 'categories', 'media', 'videos'])->orderBy('sort_order')->orderBy('name');

        if (! empty($filters['brand_id'])) {
            $query->where('brand_id', $filters['brand_id']);
        }
        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }
        if (! empty($filters['condition'])) {
            $query->where('condition', $filters['condition']);
        }

        $products = $query->get();

        $spreadsheet = new Spreadsheet;
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Products');

        $headers = [
            'ID',
            'Name',
            'Slug',
            'Brand',
            'Categories',
            'Condition',
            'Status',
            'Record Type',
            'Featured',
            'Sort Order',
            'Summary',
            'Cover Image URL',
            'Gallery Image URLs',
            'Video URLs',
            'Description HTML',
        ];

        foreach ($headers as $colIndex => $header) {
            $cell = Coordinate::stringFromColumnIndex($colIndex + 1).'1';
            $sheet->setCellValue($cell, $header);
            $sheet->getStyle($cell)->getFont()->setBold(true);
        }

        $rowIndex = 2;
        foreach ($products as $product) {
            $sortedMedia = $product->media->sortBy('pivot.sort_order')->values();
            $coverMedia = $sortedMedia->first();
            $galleryMedia = $sortedMedia->slice(1);

            $coverUrl = $coverMedia ? ($coverMedia->source_path ?: url('/storage/'.ltrim($coverMedia->storage_path, '/'))) : '';
            $galleryUrls = $galleryMedia->map(fn (MediaAsset $m) => $m->source_path ?: url('/storage/'.ltrim($m->storage_path, '/')))->implode("\n");

            $videoUrls = $product->videos->sortBy('sort_order')->map(fn ($v) => $v->provider === 'local' ? 'local:'.$v->video_id : $v->video_id)->implode(', ');
            $categories = $product->categories->sortBy('pivot.sort_order')->pluck('name')->implode(', ');

            $sheet->setCellValue('A'.$rowIndex, $product->id);
            $sheet->setCellValue('B'.$rowIndex, $product->name);
            $sheet->setCellValue('C'.$rowIndex, $product->slug);
            $sheet->setCellValue('D'.$rowIndex, $product->brand?->name ?? '');
            $sheet->setCellValue('E'.$rowIndex, $categories);
            $sheet->setCellValue('F'.$rowIndex, $product->condition);
            $sheet->setCellValue('G'.$rowIndex, $product->status);
            $sheet->setCellValue('H'.$rowIndex, $product->record_type);
            $sheet->setCellValue('I'.$rowIndex, $product->is_featured ? 'yes' : 'no');
            $sheet->setCellValue('J'.$rowIndex, $product->sort_order);
            $sheet->setCellValue('K'.$rowIndex, $product->summary);
            $sheet->setCellValue('L'.$rowIndex, $coverUrl);
            $sheet->setCellValue('M'.$rowIndex, $galleryUrls);
            $sheet->setCellValue('N'.$rowIndex, $videoUrls);
            $sheet->setCellValue('O'.$rowIndex, $product->description_html);

            $rowIndex++;
        }

        return $this->downloadSpreadsheet($spreadsheet, 'hpm-products-export-'.now()->format('Y-m-d'), $format);
    }

    /**
     * Download a blank template with headers and a sample row.
     */
    public function template(string $format = 'xlsx'): BinaryFileResponse
    {
        $spreadsheet = new Spreadsheet;
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Products Template');

        $headers = [
            'ID',
            'Name',
            'Slug',
            'Brand',
            'Categories',
            'Condition',
            'Status',
            'Record Type',
            'Featured',
            'Sort Order',
            'Summary',
            'Cover Image URL',
            'Gallery Image URLs',
            'Video URLs',
            'Description HTML',
        ];

        foreach ($headers as $colIndex => $header) {
            $cell = Coordinate::stringFromColumnIndex($colIndex + 1).'1';
            $sheet->setCellValue($cell, $header);
            $sheet->getStyle($cell)->getFont()->setBold(true);
        }

        // Sample row
        $sheet->setCellValue('A2', '');
        $sheet->setCellValue('B2', 'MultiOne 5 Series');
        $sheet->setCellValue('C2', 'multione-5-series');
        $sheet->setCellValue('D2', 'MultiOne');
        $sheet->setCellValue('E2', 'Compact Loaders');
        $sheet->setCellValue('F2', 'new');
        $sheet->setCellValue('G2', 'published');
        $sheet->setCellValue('H2', 'machinery');
        $sheet->setCellValue('I2', 'no');
        $sheet->setCellValue('J2', 30);
        $sheet->setCellValue('K2', 'Compact telescopic-boom powerhouse with 5.2K iD and 5.3K iD models.');
        $sheet->setCellValue('L2', 'https://www.multione.com/wp-content/uploads/2024/05/5.3KiD_trequarti_700x700.png');
        $sheet->setCellValue('M2', "https://www.multione.com/wp-content/uploads/2026/02/5.3kiD_ghiaia-1-2.png\nhttps://www.multione.com/wp-content/uploads/2026/01/5.3_K_Id_combo_bucket.png");
        $sheet->setCellValue('N2', 'xHkTdof8cQw');
        $sheet->setCellValue('O2', '<h2>MultiOne 5 Series</h2><p>Specifications...</p>');

        return $this->downloadSpreadsheet($spreadsheet, 'hpm-products-template', $format);
    }

    /**
     * Parse an uploaded file or file path into normalized rows.
     *
     * @return list<array<string, mixed>>
     */
    public function parse(UploadedFile|string $file): array
    {
        $extension = '';
        if ($file instanceof UploadedFile) {
            $extension = strtolower($file->getClientOriginalExtension());
            $path = $file->getRealPath();
        } else {
            $extension = strtolower(pathinfo($file, PATHINFO_EXTENSION));
            $path = $file;
        }

        if ($extension === 'csv') {
            $reader = new \PhpOffice\PhpSpreadsheet\Reader\Csv;
            $spreadsheet = $reader->load($path);
        } elseif ($extension === 'xlsx') {
            $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx;
            $spreadsheet = $reader->load($path);
        } else {
            $spreadsheet = IOFactory::load($path);
        }

        $sheet = $spreadsheet->getActiveSheet();
        $rawRows = $sheet->toArray(null, true, true, true);

        if (empty($rawRows)) {
            return [];
        }

        $headerRow = array_shift($rawRows);
        $headerMap = [];

        foreach ($headerRow as $colLetter => $headerText) {
            if ($headerText === null || trim((string) $headerText) === '') {
                continue;
            }
            $normalized = strtolower(preg_replace('/[^a-zA-Z0-9]/', '', (string) $headerText));
            $headerMap[$colLetter] = match ($normalized) {
                'id', 'uuid', 'productid' => 'id',
                'name', 'productname', 'title' => 'name',
                'slug', 'urlslug' => 'slug',
                'brand', 'brandname', 'make' => 'brand',
                'categories', 'category', 'categorynames' => 'categories',
                'condition' => 'condition',
                'status' => 'status',
                'recordtype', 'type' => 'record_type',
                'featured', 'isfeatured' => 'is_featured',
                'sortorder', 'order' => 'sort_order',
                'summary', 'shortdescription' => 'summary',
                'coverimageurl', 'coverimage', 'cover', 'image' => 'cover_image_url',
                'galleryimageurls', 'gallery', 'galleryimages', 'images' => 'gallery_image_urls',
                'videourls', 'videos', 'video', 'youtube' => 'video_urls',
                'descriptionhtml', 'description', 'details' => 'description_html',
                default => null,
            };
        }

        $parsed = [];
        foreach ($rawRows as $row) {
            $mapped = [];
            $hasData = false;

            foreach ($headerMap as $colLetter => $key) {
                if ($key === null) {
                    continue;
                }
                $val = isset($row[$colLetter]) ? trim((string) $row[$colLetter]) : '';
                if ($val !== '') {
                    $hasData = true;
                }
                $mapped[$key] = $val;
            }

            if (! $hasData) {
                continue;
            }

            // Ensure required array keys are present
            foreach (['id', 'name', 'slug', 'brand', 'categories', 'condition', 'status', 'record_type', 'is_featured', 'sort_order', 'summary', 'cover_image_url', 'gallery_image_urls', 'video_urls', 'description_html'] as $expectedKey) {
                $mapped[$expectedKey] = $mapped[$expectedKey] ?? '';
            }

            $parsed[] = $mapped;
        }

        return $parsed;
    }

    /**
     * Validate parsed rows and prepare summary preview.
     *
     * @param  list<array<string, mixed>>  $rows
     * @return array{summary: array<string, int>, rows: list<array<string, mixed>>, errors: list<string>, warnings: list<string>}
     */
    public function validateRows(array $rows): array
    {
        $brands = Brand::pluck('id', 'slug')->all();
        $brandNames = Brand::pluck('id', 'name')->mapWithKeys(fn ($id, $name) => [strtolower($name) => $id])->all();

        $categories = Category::pluck('id', 'slug')->all();
        $categoryNames = Category::pluck('id', 'name')->mapWithKeys(fn ($id, $name) => [strtolower($name) => $id])->all();

        $existingSlugs = Product::pluck('id', 'slug')->all();
        $existingIds = Product::pluck('id', 'id')->all();

        $toCreate = 0;
        $toUpdate = 0;
        $errors = [];
        $warnings = [];

        $annotatedRows = [];

        foreach ($rows as $index => $row) {
            $rowNum = $index + 2;
            $slug = trim($row['slug'] ?? '');
            $id = trim($row['id'] ?? '');
            $name = trim($row['name'] ?? '');

            if ($slug === '' && $name === '') {
                $errors[] = "Row #{$rowNum}: Must have at least a Name or Slug.";

                continue;
            }

            if ($slug === '') {
                $slug = Str::slug($name);
                $row['slug'] = $slug;
            }

            $matchedProduct = null;
            if ($id !== '' && isset($existingIds[$id])) {
                $matchedProduct = $id;
            } elseif (isset($existingSlugs[$slug])) {
                $matchedProduct = $existingSlugs[$slug];
            }

            $action = $matchedProduct ? 'update' : 'create';
            if ($action === 'update') {
                $toUpdate++;
            } else {
                $toCreate++;
            }

            // Image URL check
            $coverUrl = trim($row['cover_image_url'] ?? '');
            $galleryUrls = $this->splitUrls($row['gallery_image_urls'] ?? '');
            $totalImages = ($coverUrl !== '' ? 1 : 0) + count($galleryUrls);

            // Brand check
            $brandStr = trim($row['brand'] ?? '');
            if ($brandStr !== '') {
                $brandLower = strtolower($brandStr);
                $brandSlug = Str::slug($brandStr);
                if (! isset($brands[$brandSlug]) && ! isset($brandNames[$brandLower])) {
                    $warnings[] = "Row #{$rowNum} ('{$slug}'): Brand '{$brandStr}' does not match any existing brand.";
                }
            }

            $annotatedRows[] = array_merge($row, [
                'action' => $action,
                'matched_id' => $matchedProduct,
                'image_count' => $totalImages,
            ]);
        }

        return [
            'summary' => [
                'total' => count($annotatedRows),
                'to_create' => $toCreate,
                'to_update' => $toUpdate,
            ],
            'rows' => $annotatedRows,
            'errors' => $errors,
            'warnings' => $warnings,
        ];
    }

    /**
     * Non-destructive smart import execution.
     *
     * @param  list<array<string, mixed>>  $rows
     * @return array{imported: int, created: int, updated: int, failed: int, errors: list<string>}
     */
    public function execute(array $rows, bool $replaceGalleries = true, bool $downloadImages = true): array
    {
        $brands = Brand::all()->keyBy(fn ($b) => Str::slug($b->name));
        $categories = Category::all()->keyBy(fn ($c) => Str::slug($c->name));

        $created = 0;
        $updated = 0;
        $failed = 0;
        $errors = [];

        foreach ($rows as $index => $row) {
            $rowNum = $index + 2;
            $slug = trim($row['slug'] ?? '');
            $id = trim($row['id'] ?? '');
            $name = trim($row['name'] ?? '');

            if ($slug === '' && $name !== '') {
                $slug = Str::slug($name);
            }

            if ($slug === '') {
                $failed++;
                $errors[] = "Row #{$rowNum}: Skipped due to missing slug and name.";

                continue;
            }

            try {
                DB::transaction(function () use ($row, $slug, $id, $name, $brands, $categories, $replaceGalleries, $downloadImages, &$created, &$updated): void {
                    $product = null;
                    if ($id !== '') {
                        $product = Product::find($id);
                    }
                    if (! $product) {
                        $product = Product::where('slug', $slug)->first();
                    }

                    $isNew = ! $product;
                    if ($isNew) {
                        $product = new Product;
                        $product->id = $id ?: (string) Str::uuid7();
                        $product->slug = $slug;
                        $product->name = $name ?: Str::title(str_replace('-', ' ', $slug));
                        $product->condition = in_array(strtolower($row['condition'] ?? ''), ['new', 'used'], true) ? strtolower($row['condition']) : 'new';
                        $product->status = in_array(strtolower($row['status'] ?? ''), ['published', 'draft'], true) ? strtolower($row['status']) : 'published';
                        $product->record_type = 'machinery';
                        $product->sort_order = is_numeric($row['sort_order'] ?? null) ? (int) $row['sort_order'] : 0;
                    }

                    // Smart non-destructive field updates: only update non-empty fields
                    $oldSlug = $product->slug;
                    if ($name !== '') {
                        $product->name = $name;
                    }
                    if ($slug !== '' && ! $isNew) {
                        $product->slug = $slug;
                    }
                    if (! empty($row['summary'])) {
                        $product->summary = $this->admin->clean($row['summary']);
                    }
                    if (! empty($row['description_html'])) {
                        $product->description_html = $this->admin->clean($row['description_html']);
                    }
                    if (! empty($row['condition']) && in_array(strtolower($row['condition']), ['new', 'used'], true)) {
                        $product->condition = strtolower($row['condition']);
                    }
                    if (! empty($row['status']) && in_array(strtolower($row['status']), ['published', 'draft'], true)) {
                        $product->status = strtolower($row['status']);
                    }
                    if (! empty($row['record_type'])) {
                        $product->record_type = strtolower($row['record_type']);
                    }
                    if (isset($row['is_featured']) && $row['is_featured'] !== '') {
                        $product->is_featured = in_array(strtolower((string) $row['is_featured']), ['1', 'true', 'yes'], true);
                    }
                    if (isset($row['sort_order']) && is_numeric($row['sort_order'])) {
                        $product->sort_order = (int) $row['sort_order'];
                    }

                    // Brand resolution
                    if (! empty($row['brand'])) {
                        $brandKey = Str::slug($row['brand']);
                        $matchedBrand = $brands->get($brandKey) ?? Brand::whereRaw('LOWER(name) = ?', [strtolower($row['brand'])])->first();
                        if ($matchedBrand) {
                            $product->brand_id = $matchedBrand->id;
                        }
                    }

                    $product->admin_modified_at = now();
                    $product->save();

                    if (! $isNew && $oldSlug && $oldSlug !== $product->slug) {
                        app(LegacyUrls::class)->rememberSlug($product, $oldSlug);
                    }

                    // Category resolution
                    if (! empty($row['categories'])) {
                        $catSync = [];
                        $catParts = array_map('trim', explode(',', (string) $row['categories']));
                        $catOrder = 0;
                        foreach ($catParts as $catName) {
                            if ($catName === '') {
                                continue;
                            }
                            $catKey = Str::slug($catName);
                            $matchedCat = $categories->get($catKey) ?? Category::whereRaw('LOWER(name) = ?', [strtolower($catName)])->first();
                            if ($matchedCat) {
                                $catSync[$matchedCat->id] = ['sort_order' => $catOrder++, 'source_imported' => false];
                            }
                        }
                        if (! empty($catSync)) {
                            $product->categories()->sync($catSync);
                        }
                    }

                    // Videos resolution
                    if (! empty($row['video_urls'])) {
                        $videoParts = array_map('trim', explode(',', (string) $row['video_urls']));
                        $retainedVideos = [];
                        foreach ($videoParts as $vOrder => $vVal) {
                            $vid = $this->admin->youtube($vVal);
                            $provider = 'youtube';
                            if (! $vid && (str_starts_with($vVal, 'local:') || str_ends_with($vVal, '.mp4') || str_starts_with($vVal, 'videos/'))) {
                                $provider = 'local';
                                $vid = preg_replace('/^local:/', '', $vVal);
                            }
                            if ($vid) {
                                $vModel = $product->videos()->where('video_id', $vid)->first() ?? $product->videos()->make();
                                $vModel->fill(['provider' => $provider, 'video_id' => $vid, 'sort_order' => $vOrder]);
                                $vModel->save();
                                $retainedVideos[] = $vModel->id;
                            }
                        }
                        if ($retainedVideos !== []) {
                            $product->videos()->whereNotIn('id', $retainedVideos)->delete();
                        }
                    }

                    // Cover & Gallery Image resolution
                    $coverUrl = trim($row['cover_image_url'] ?? '');
                    $galleryUrls = $this->splitUrls($row['gallery_image_urls'] ?? '');

                    $allUrls = [];
                    if ($coverUrl !== '') {
                        $allUrls[] = $coverUrl;
                    }
                    foreach ($galleryUrls as $gUrl) {
                        if (! in_array($gUrl, $allUrls, true)) {
                            $allUrls[] = $gUrl;
                        }
                    }

                    if (! empty($allUrls) && $downloadImages) {
                        $folder = $this->folders->folder($product);
                        $mediaSync = [];
                        $targetOrder = 0;

                        if (! $replaceGalleries) {
                            // Keep existing gallery attachments
                            foreach ($product->media()->orderBy('pivot.sort_order')->get() as $existingMedia) {
                                $mediaSync[$existingMedia->id] = ['sort_order' => $targetOrder++];
                            }
                        }

                        foreach ($allUrls as $imgUrl) {
                            $asset = $this->ingestImage($imgUrl, $folder, $product->slug);
                            if ($asset && ! isset($mediaSync[$asset->id])) {
                                $mediaSync[$asset->id] = ['sort_order' => $targetOrder++];
                            }
                        }

                        $product->media()->sync($mediaSync);
                    }

                    if ($isNew) {
                        $created++;
                    } else {
                        $updated++;
                    }
                });
            } catch (Throwable $e) {
                $failed++;
                $errors[] = "Row #{$rowNum} ('{$slug}'): ".$e->getMessage();
            }
        }

        return [
            'imported' => $created + $updated,
            'created' => $created,
            'updated' => $updated,
            'failed' => $failed,
            'errors' => $errors,
        ];
    }

    /**
     * Download or read an image and register MediaAsset.
     */
    protected function ingestImage(string $urlOrPath, string $folder, string $productSlug): ?MediaAsset
    {
        $urlOrPath = trim($urlOrPath);
        if ($urlOrPath === '') {
            return null;
        }

        // If it's already a relative /storage/ URL, find existing asset
        if (str_starts_with($urlOrPath, '/storage/')) {
            $storagePath = ltrim(substr($urlOrPath, 9), '/');
            $existing = MediaAsset::where('storage_path', $storagePath)->first();
            if ($existing) {
                return $existing;
            }
        }

        $sourceKey = 'sheet:img:'.$productSlug.':'.hash('sha256', $urlOrPath);
        $existing = MediaAsset::where('source_key', $sourceKey)->first();
        if ($existing && Storage::disk('public')->exists($existing->storage_path)) {
            return $existing;
        }

        $bytes = '';
        if (str_starts_with($urlOrPath, 'http://') || str_starts_with($urlOrPath, 'https://')) {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $urlOrPath);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 15);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36');
            $bytes = (string) curl_exec($ch);
            $httpCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($httpCode !== 200 || $bytes === '') {
                return null;
            }
        } else {
            $localFile = str_starts_with($urlOrPath, 'local:') ? substr($urlOrPath, 6) : $urlOrPath;
            if (! file_exists($localFile)) {
                return null;
            }
            $bytes = (string) file_get_contents($localFile);
        }

        if ($bytes === '') {
            return null;
        }

        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $mime = (string) $finfo->buffer($bytes);
        if (! str_starts_with($mime, 'image/')) {
            return null;
        }

        $filenameFromUrl = urldecode((string) parse_url($urlOrPath, PHP_URL_PATH));
        $ext = strtolower(pathinfo($filenameFromUrl, PATHINFO_EXTENSION) ?: 'jpg');
        if (! in_array($ext, ['jpg', 'jpeg', 'png', 'webp', 'gif'], true)) {
            $ext = 'jpg';
        }

        $originalName = pathinfo($filenameFromUrl, PATHINFO_FILENAME) ?: 'image';
        $storageFilename = $this->folders->filename($originalName, $ext);
        $storagePath = $folder.'/'.$storageFilename;

        if (! Storage::disk('public')->put($storagePath, $bytes)) {
            return null;
        }

        return MediaAsset::updateOrCreate(
            ['source_key' => $sourceKey],
            [
                'storage_path' => $storagePath,
                'source_path' => $urlOrPath,
                'mime_type' => $mime,
                'checksum' => hash('sha256', $bytes),
                'title' => Str::title(str_replace(['-', '_'], ' ', $originalName)),
                'alt_text' => Str::title(str_replace(['-', '_'], ' ', $originalName)),
                'source_published' => true,
                'admin_modified_at' => now(),
            ]
        );
    }

    /**
     * @return list<string>
     */
    protected function splitUrls(string $value): array
    {
        $lines = preg_split('/[\r\n;,]+/', $value) ?: [];

        return array_values(array_filter(array_map('trim', $lines)));
    }

    protected function downloadSpreadsheet(Spreadsheet $spreadsheet, string $baseFilename, string $format): BinaryFileResponse
    {
        $extension = strtolower($format) === 'csv' ? 'csv' : 'xlsx';
        $tempPath = tempnam(sys_get_temp_dir(), 'hpm_sheet_').'.'.$extension;

        if ($extension === 'csv') {
            $writer = new Csv($spreadsheet);
            $writer->setUseBOM(true);
            $writer->save($tempPath);
            $contentType = 'text/csv; charset=UTF-8';
        } else {
            $writer = new Xlsx($spreadsheet);
            $writer->save($tempPath);
            $contentType = 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet';
        }

        return response()->download($tempPath, $baseFilename.'.'.$extension, [
            'Content-Type' => $contentType,
        ])->deleteFileAfterSend(true);
    }
}
