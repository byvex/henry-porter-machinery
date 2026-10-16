<?php

namespace Tests\Feature;

use App\Models\Brand;
use App\Models\Category;
use App\Models\LegacyUrlMapping;
use App\Models\MediaAsset;
use App\Models\Product;
use App\Models\ProductVideo;
use App\Services\Catalogue\CatalogueMedia;
use App\Services\Catalogue\JoomlaImporter;
use App\Services\Catalogue\JoomlaSqlReader;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;
use Tests\TestCase;

class JoomlaCatalogueImportTest extends TestCase
{
    use RefreshDatabase;

    private string $sandbox;

    private string $originalStorage;

    private array $source;

    protected function setUp(): void
    {
        parent::setUp();
        $this->originalStorage = storage_path();
        $this->sandbox = storage_path('framework/testing/catalogue-'.Str::uuid());
        File::ensureDirectoryExists($this->sandbox.'/backup/files/images');
        File::ensureDirectoryExists($this->sandbox.'/backup/db');
        $this->app->useStoragePath($this->sandbox.'/runtime');
        Storage::fake('public');
        foreach (['first', 'second', 'embedded'] as $name) {
            $this->image($name, 30);
        }
        $this->source = $this->fixture();
        $this->dump();
    }

    protected function tearDown(): void
    {
        $this->app->useStoragePath($this->originalStorage);
        File::deleteDirectory($this->sandbox);
        parent::tearDown();
    }

    public function test_dry_run_has_no_database_or_media_or_report_writes(): void
    {
        $beforeFiles = File::allFiles($this->sandbox);
        $beforeDump = hash_file('sha256', $this->sandbox.'/backup/db/db.sql');
        $writes = [];
        DB::listen(function ($query) use (&$writes) {
            if (preg_match('/^\s*(insert|update|delete|create|alter|drop)/i', $query->sql)) {
                $writes[] = $query->sql;
            }
        });
        $report = app(JoomlaImporter::class)->run($this->sandbox.'/backup');
        $this->assertSame('dry-run', $report['mode']);
        $this->assertSame(8, $report['counts']['products']);
        $this->assertSame(3, $report['counts']['published_machinery']);
        $this->assertSame(3, $report['counts']['media_assets']);
        $this->assertSame([], $writes);
        $this->assertCount(count($beforeFiles), File::allFiles($this->sandbox));
        $this->assertSame([], Storage::disk('public')->allFiles());
        $this->assertSame($beforeDump, hash_file('sha256', $this->sandbox.'/backup/db/db.sql'));
        $this->assertDatabaseCount('products', 0);
    }

    public function test_import_preserves_content_relationships_and_is_repeatable(): void
    {
        $manualBrand = Brand::create(['name' => 'Manual Brand', 'slug' => 'manual-brand']);
        $manualCategory = Category::create(['name' => 'Manual category', 'slug' => 'manual-category']);
        $manualProduct = Product::create(['name' => 'Manual product', 'slug' => 'manual-product', 'brand_id' => $manualBrand->id]);
        Storage::disk('public')->put('unrelated.txt', 'keep this file');
        $report = app(JoomlaImporter::class)->run($this->sandbox.'/backup', true);
        $this->assertSame('imported', $report['status']);
        $this->assertDatabaseCount('products', 9);
        $this->assertDatabaseCount('brands', 10);
        $this->assertDatabaseCount('categories', 16);
        $this->assertDatabaseCount('media_assets', 3);
        $this->assertDatabaseCount('product_videos', 1);
        $this->assertDatabaseCount('product_testimonials', 2);
        $this->assertSame(3, Product::catalogue()->count());
        $product = Product::where('legacy_product_id', 16)->firstOrFail();
        $this->assertSame('Disc Harrows – Pöttinger', $product->name);
        $this->assertSame('pottinger', $product->brand->slug);
        $this->assertContains('tillage', $product->categories->pluck('slug')->all());
        $this->assertSame(2, $product->coverImage()->legacy_media_id);
        $this->assertStringContainsString('<table>', $product->description_html);
        $this->assertStringContainsString('O\'Brien, 3.5m', $product->description_html);
        $this->assertStringContainsString('src="/storage/products/', $product->description_html);
        $this->assertStringNotContainsString('http://localhost', $product->description_html);
        $this->assertStringContainsString('https://www.poettinger.at/', $product->description_html);
        foreach (['<script', 'onclick', 'javascript:', 'style=', 'class=', '<iframe'] as $unsafe) {
            $this->assertStringNotContainsString($unsafe, $product->description_html);
        }
        $this->assertSame('new', $product->condition);
        $this->assertNull($product->created_at);
        $this->assertSame('template-pottinger-7', $product->source_metadata['slug']);
        foreach ([7, 52, 56, 60, 88] as $hidden) {
            $this->assertFalse(Product::catalogue()->where('legacy_product_id', $hidden)->exists());
        }
        $this->assertSame('used', Product::where('legacy_product_id', 88)->firstOrFail()->condition);
        $this->assertNotSame(Product::where('legacy_product_id', 78)->value('slug'), Product::where('legacy_product_id', 80)->value('slug'));
        $this->assertContains('duplicate_name_review', Product::where('legacy_product_id', 78)->firstOrFail()->review_flags);
        $this->assertSame('OdSOqXLrK00', ProductVideo::firstOrFail()->video_id);
        $this->assertSame(0, LegacyUrlMapping::where('verification_status', '!=', 'pending')->count());
        $menu = LegacyUrlMapping::where('source_path', '/new-machines/pottinger.html')->whereNull('source_query')->firstOrFail();
        $this->assertSame('product', $menu->target_type);
        $this->assertSame($product->id, $menu->target_id);
        $this->assertFileExists($report['report_path']);
        $audit = json_decode(File::get(dirname($report['report_path']).'/audit.json'), true, flags: JSON_THROW_ON_ERROR);
        $this->assertArrayNotHasKey('users', $audit['tables']);
        $this->assertSame('0', $audit['tables']['virtuemart_product_customfields'][0]['published']);
        $product->categories()->attach($manualCategory->id);
        $ids = Product::orderBy('id')->pluck('id')->all();
        $counts = [DB::table('category_product')->count(), DB::table('product_media')->count(), LegacyUrlMapping::count()];
        $files = Storage::disk('public')->allFiles();
        app(JoomlaImporter::class)->run($this->sandbox.'/backup', true);
        $this->assertSame($ids, Product::orderBy('id')->pluck('id')->all());
        $this->assertSame($counts, [DB::table('category_product')->count(), DB::table('product_media')->count(), LegacyUrlMapping::count()]);
        $this->assertSame($files, Storage::disk('public')->allFiles());
        $this->assertTrue($product->fresh()->categories->contains($manualCategory));
        $this->assertSame('Manual product', $manualProduct->fresh()->name);
        $this->assertSame('keep this file', Storage::disk('public')->get('unrelated.txt'));
    }

    public function test_reimport_preserves_homepage_selections_and_verified_url_mappings(): void
    {
        app(JoomlaImporter::class)->run($this->sandbox.'/backup', true);
        $brand = Brand::where('slug', 'pottinger')->firstOrFail();
        $category = Category::where('slug', 'grassland')->firstOrFail();
        $brand->update(['show_on_homepage' => false, 'homepage_order' => 99]);
        $category->update(['show_on_homepage' => false, 'homepage_order' => 98]);
        $mapping = LegacyUrlMapping::firstOrFail();
        $mapping->update(['verification_status' => 'verified']);
        app(JoomlaImporter::class)->run($this->sandbox.'/backup', true);
        $this->assertFalse($brand->fresh()->show_on_homepage);
        $this->assertSame(99, $brand->fresh()->homepage_order);
        $this->assertFalse($category->fresh()->show_on_homepage);
        $this->assertSame(98, $category->fresh()->homepage_order);
        $this->assertSame('verified', $mapping->fresh()->verification_status);
    }

    public function test_admin_edits_and_deletions_survive_reimport_unless_explicitly_overwritten(): void
    {
        app(JoomlaImporter::class)->run($this->sandbox.'/backup', true);
        $product = Product::where('legacy_product_id', 16)->firstOrFail();
        $path = $product->coverImage()->storage_path;
        $uuid = $product->id;
        $originalName = $product->name;
        $product->update(['name' => 'Admin changed name', 'admin_modified_at' => now()]);
        $product->videos()->delete();
        $this->image('second', 234);
        $report = app(JoomlaImporter::class)->run($this->sandbox.'/backup', true);
        $this->assertContains('products:16', $report['skipped_edited']);
        $this->assertSame('Admin changed name', $product->fresh()->name);
        $this->assertSame(0, $product->videos()->count());
        $product->delete();
        app(JoomlaImporter::class)->run($this->sandbox.'/backup', true);
        $this->assertTrue(Product::withTrashed()->findOrFail($uuid)->trashed());
        app(JoomlaImporter::class)->run($this->sandbox.'/backup', true, true);
        $this->assertSame($originalName, $product->fresh()->name);
        $this->assertFalse($product->fresh()->trashed());
        $this->assertSame($path, $product->fresh()->coverImage()->storage_path);
    }

    public function test_failure_rolls_back_database_and_restores_overwritten_media_then_allows_rerun(): void
    {
        app(JoomlaImporter::class)->run($this->sandbox.'/backup', true);
        $asset = MediaAsset::where('legacy_media_id', 1)->firstOrFail();
        $before = Storage::disk('public')->get($asset->storage_path);
        $name = Product::where('legacy_product_id', 16)->value('name');
        $this->image('first', 230);
        $this->source['virtuemart_products_en_gb'][1]['product_name'] = 'Changed on source';
        $this->dump();
        $this->app->instance(CatalogueMedia::class, new class extends CatalogueMedia
        {
            public function publish(array $staged, string $backupDirectory, array &$ledger): void
            {
                parent::publish(array_slice($staged, 0, 1, true), $backupDirectory, $ledger);
                throw new RuntimeException('Injected publish failure');
            }
        });
        try {
            app(JoomlaImporter::class)->run($this->sandbox.'/backup', true);
            $this->fail('Expected import failure.');
        } catch (RuntimeException $error) {
            $this->assertStringContainsString('Injected publish failure', $error->getMessage());
        }
        $this->assertSame($before, Storage::disk('public')->get($asset->storage_path));
        $this->assertSame($name, Product::where('legacy_product_id', 16)->value('name'));
        $this->assertSame($asset->checksum, $asset->fresh()->checksum);
        $this->assertDatabaseCount('products', 8);
        $reports = File::glob(storage_path('app/private/catalogue-imports/*/report.json'));
        $failed = array_filter($reports, fn ($path) => json_decode(File::get($path), true)['status'] === 'failed');
        $this->assertCount(1, $failed);
        $this->app->forgetInstance(CatalogueMedia::class);
        app(JoomlaImporter::class)->run($this->sandbox.'/backup', true);
        $this->assertNotSame($before, Storage::disk('public')->get($asset->storage_path));
        $this->assertSame('Changed on source', Product::where('legacy_product_id', 16)->value('name'));
    }

    public function test_failed_first_import_removes_new_media_and_rolls_back_all_rows(): void
    {
        Storage::disk('public')->put('unrelated.txt', 'keep');
        $this->app->instance(CatalogueMedia::class, new class extends CatalogueMedia
        {
            public function publish(array $staged, string $backupDirectory, array &$ledger): void
            {
                parent::publish(array_slice($staged, 0, 1, true), $backupDirectory, $ledger);
                throw new RuntimeException('Injected first-import failure');
            }
        });
        try {
            app(JoomlaImporter::class)->run($this->sandbox.'/backup', true);
            $this->fail('Expected import failure.');
        } catch (RuntimeException $error) {
            $this->assertStringContainsString('Injected first-import failure', $error->getMessage());
        }
        foreach (['products', 'brands', 'categories', 'media_assets', 'legacy_url_mappings'] as $table) {
            $this->assertDatabaseCount($table, 0);
        }
        $this->assertSame(['unrelated.txt'], Storage::disk('public')->allFiles());
    }

    public function test_missing_files_and_traversal_and_broken_relationships_are_rejected_before_writes(): void
    {
        foreach (['missing-file', 'traversal', 'bad-reference'] as $case) {
            $original = $this->source;
            if ($case === 'missing-file') {
                $this->source['virtuemart_medias'][0]['file_url'] = 'images/missing.png';
            } elseif ($case === 'traversal') {
                $this->source['virtuemart_medias'][0]['file_url'] = '../outside.png';
            } else {
                $this->source['virtuemart_product_medias'][0]['virtuemart_media_id'] = '999';
            }
            $this->dump();
            try {
                app(JoomlaImporter::class)->run($this->sandbox.'/backup', true);
                $this->fail('Expected validation failure for '.$case);
            } catch (RuntimeException $error) {
                $this->assertNotSame('', $error->getMessage());
            }
            $this->assertDatabaseCount('products', 0);
            $this->assertSame([], Storage::disk('public')->allFiles());
            $this->source = $original;
        }
    }

    public function test_sql_reader_handles_escaping_and_explicit_columns_and_ignores_other_sql(): void
    {
        $path = $this->sandbox.'/backup/db/db.sql';
        File::append($path, "\nINSERT INTO `test_virtuemart_products_en_gb` (`slug`, `virtuemart_product_id`, `product_name`, `product_desc`, `product_s_desc`) VALUES ('explicit', '999', 'O''Brien; Pöttinger', 'commas, parentheses () and \\'quotes\\'', NULL);\nDROP TABLE products;\nINSERT INTO `test_users` VALUES (deliberately invalid private data);\n");
        $result = app(JoomlaSqlReader::class)->read($path);
        $last = end($result['tables']['virtuemart_products_en_gb']);
        $this->assertSame("O'Brien; Pöttinger", $last['product_name']);
        $this->assertSame("commas, parentheses () and 'quotes'", $last['product_desc']);
        $this->assertNull($last['product_s_desc']);
        $this->assertArrayNotHasKey('users', $result['tables']);
        $this->assertDatabaseCount('products', 0);
    }

    public function test_zero_product_gallery_rows_are_audited_without_losing_the_media(): void
    {
        $this->source['virtuemart_product_medias'][] = ['id' => '99', 'virtuemart_product_id' => '0', 'virtuemart_media_id' => '1', 'ordering' => '1'];
        $this->dump();
        $report = app(JoomlaImporter::class)->run($this->sandbox.'/backup', true);
        $this->assertSame(3, $report['counts']['source_product_media']);
        $this->assertSame(2, $report['counts']['product_media']);
        $this->assertCount(1, $report['unresolved_references']);
        $this->assertSame(99, $report['unresolved_references'][0]['source_link_id']);
        $this->assertDatabaseCount('product_media', 2);
        $this->assertNotNull(MediaAsset::where('legacy_media_id', 1)->first());
    }

    public function test_existing_public_and_authentication_routes_work_with_imported_catalogue(): void
    {
        $this->withoutVite();
        app(JoomlaImporter::class)->run($this->sandbox.'/backup', true);
        $this->get('/')->assertOk()->assertSee('Premium Farm');
        $this->get('/about')->assertOk()->assertSee('Built on experience.');
        $this->get('/about.html')->assertRedirect('/about');
        $this->get('/login')->assertOk();
        $this->get('/dashboard')->assertRedirect(route('login'));
    }

    private function image(string $name, int $colour): void
    {
        $image = imagecreatetruecolor(2, 2);
        imagefill($image, 0, 0, imagecolorallocate($image, $colour, 80, 30));
        imagepng($image, $this->sandbox.'/backup/files/images/'.$name.'.png');
        imagedestroy($image);
    }

    private function dump(): void
    {
        $sql = "-- Isolated test catalogue. No source SQL is executed.\n";
        foreach ($this->source as $name => $rows) {
            $columns = array_keys($rows[0]);
            $sql .= 'CREATE TABLE `test_'.$name."` (\n".implode(",\n", array_map(fn ($c) => '  `'.$c.'` text', $columns))."\n) ENGINE=InnoDB DEFAULT CHARSET=utf8;\n";
            foreach ($rows as $row) {
                $values = array_map(fn ($v) => $v === null ? 'NULL' : "'".str_replace(['\\', "'", "\n", "\r", "\0"], ['\\\\', "\\'", '\\n', '\\r', '\\0'], (string) $v)."'", array_values($row));
                $sql .= 'INSERT INTO `test_'.$name.'` VALUES ('.implode(',', $values).");\n";
            }
        }
        File::put($this->sandbox.'/backup/db/db.sql', $sql);
    }

    private function fixture(): array
    {
        $products = [];
        $texts = [];
        foreach ([7 => 'TEMPLATE - Pottinger', 16 => 'Disc Harrows – Pöttinger', 52 => 'TEMPLATE - Video', 56 => 'TEMPLATE - Testimonial', 60 => 'Parts', 78 => 'Sherpa 100 Diesel', 80 => 'Sherpa 100 Diesel', 88 => 'Used mower'] as $id => $name) {
            $products[] = ['virtuemart_product_id' => (string) $id, 'product_parent_id' => '0', 'product_sku' => '', 'product_in_stock' => '0', 'published' => in_array($id, [7, 88]) ? '0' : '1', 'product_special' => '0', 'pordering' => '0', 'created_on' => '0000-00-00 00:00:00', 'modified_on' => '2020-01-02 12:00:00'];
            $texts[] = ['virtuemart_product_id' => (string) $id, 'product_name' => $name, 'slug' => $id === 16 ? 'template-pottinger-7' : 'source-'.$id, 'product_s_desc' => '<p>Short summary</p>', 'product_desc' => '<h3 class="uk-heading" style="color:red" onclick="bad()">Specifications</h3><table><tr><td colspan="2">O\'Brien, 3.5m</td></tr></table><img src="images/embedded.png" onerror="bad()"><a href="https://www.poettinger.at/">Manufacturer</a><a href="javascript:bad()">Unsafe</a><script>bad()</script><iframe src="https://evil.example/"></iframe>'];
        }
        $categories = [];
        $categoryTexts = [];
        foreach ([2 => 'Used Machinery', 3 => 'Pottinger', 9 => 'Grassland', 10 => 'Tillage', 23 => 'Sherpa'] as $id => $name) {
            $categories[] = ['virtuemart_category_id' => (string) $id, 'published' => '1'];
            $categoryTexts[] = ['virtuemart_category_id' => (string) $id, 'category_name' => $name, 'category_description' => '', 'slug' => Str::slug($name)];
        }
        $links = [];
        foreach ([[7, 3], [16, 3], [16, 10], [78, 23], [80, 23], [88, 2]] as $i => [$product, $category]) {
            $links[] = ['id' => (string) ($i + 1), 'virtuemart_product_id' => (string) $product, 'virtuemart_category_id' => (string) $category, 'ordering' => '0'];
        }

        return [
            'virtuemart_products' => $products,
            'virtuemart_products_en_gb' => $texts,
            'virtuemart_categories' => $categories,
            'virtuemart_categories_en_gb' => $categoryTexts,
            'virtuemart_medias' => [
                ['virtuemart_media_id' => '1', 'file_url' => 'images/first.png', 'file_title' => 'First', 'file_meta' => 'First image', 'published' => '1'],
                ['virtuemart_media_id' => '2', 'file_url' => 'images/second.png', 'file_title' => 'Second', 'file_meta' => 'Second image', 'published' => '1'],
            ],
            'virtuemart_product_categories' => $links,
            'virtuemart_product_medias' => [
                ['id' => '1', 'virtuemart_product_id' => '16', 'virtuemart_media_id' => '1', 'ordering' => '2'],
                ['id' => '2', 'virtuemart_product_id' => '16', 'virtuemart_media_id' => '2', 'ordering' => '1'],
            ],
            'virtuemart_category_medias' => [['id' => '1', 'virtuemart_category_id' => '3', 'virtuemart_media_id' => '1', 'ordering' => '0']],
            'virtuemart_customs' => [['virtuemart_custom_id' => '3', 'published' => '1'], ['virtuemart_custom_id' => '4', 'published' => '1']],
            'virtuemart_product_customfields' => [
                ['virtuemart_customfield_id' => '1', 'virtuemart_product_id' => '16', 'virtuemart_custom_id' => '3', 'customfield_value' => '<iframe src="https://www.youtube.com/embed/OdSOqXLrK00 "></iframe>', 'published' => '0', 'ordering' => '0'],
                ['virtuemart_customfield_id' => '2', 'virtuemart_product_id' => '52', 'virtuemart_custom_id' => '3', 'customfield_value' => '<iframe src="#"></iframe>', 'published' => '0', 'ordering' => '0'],
                ['virtuemart_customfield_id' => '3', 'virtuemart_product_id' => '16', 'virtuemart_custom_id' => '4', 'customfield_value' => '<p style="color:red">Customer feedback</p><script>bad()</script>', 'published' => '0', 'ordering' => '1'],
                ['virtuemart_customfield_id' => '4', 'virtuemart_product_id' => '56', 'virtuemart_custom_id' => '4', 'customfield_value' => '<p>Template feedback</p>', 'published' => '0', 'ordering' => '0'],
            ],
            'menu' => [['id' => '1', 'client_id' => '0', 'published' => '1', 'path' => 'new-machines/pottinger', 'link' => 'index.php?option=com_virtuemart&view=productdetails&virtuemart_product_id=16']],
        ];
    }
}
