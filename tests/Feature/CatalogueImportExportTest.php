<?php

namespace Tests\Feature;

use App\Livewire\Admin\CatalogueImportExport as ImportExportComponent;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use App\Services\Catalogue\CatalogueImportExport;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class CatalogueImportExportTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        $user = User::factory()->create(['role' => 'admin']);
        $this->actingAs($user);

        return $user;
    }

    public function test_import_export_route_requires_admin(): void
    {
        $this->get('/admin/import-export')->assertRedirect('/login');

        $user = User::factory()->create(['role' => 'user']);
        $this->actingAs($user)->get('/admin/import-export')->assertForbidden();

        $this->admin();
        $this->get('/admin/import-export')->assertOk()->assertSee('Bulk Import');
    }

    public function test_service_can_generate_blank_template(): void
    {
        $service = app(CatalogueImportExport::class);
        $response = $service->template('xlsx');
        $path = $response->getFile()->getPathname();

        $this->assertFileExists($path);
        $this->assertStringEndsWith('.xlsx', $path);

        $parsed = $service->parse($path);
        $this->assertNotEmpty($parsed);
        $this->assertSame('MultiOne 5 Series', $parsed[0]['name']);
        $this->assertSame('multione-5-series', $parsed[0]['slug']);

        @unlink($path);
    }

    public function test_service_can_export_products_to_csv_and_xlsx(): void
    {
        $brand = Brand::create(['name' => 'MultiOne', 'slug' => 'multione']);
        $category = Category::create(['name' => 'Loaders', 'slug' => 'loaders']);

        $product = Product::create([
            'name' => 'MultiOne 5.3K',
            'slug' => 'multione-5-3k',
            'brand_id' => $brand->id,
            'condition' => 'new',
            'status' => 'published',
            'summary' => 'Compact loader',
            'description_html' => '<p>High performance</p>',
        ]);
        $product->categories()->attach($category);

        $service = app(CatalogueImportExport::class);

        // Test CSV export
        $csvResponse = $service->export('csv');
        $csvPath = $csvResponse->getFile()->getPathname();
        $this->assertFileExists($csvPath);
        $csvContent = File::get($csvPath);
        $this->assertStringContainsString('MultiOne 5.3K', $csvContent);
        $this->assertStringContainsString('multione-5-3k', $csvContent);
        $this->assertStringContainsString('MultiOne', $csvContent);
        @unlink($csvPath);

        // Test XLSX export
        $xlsxResponse = $service->export('xlsx', ['brand_id' => $brand->id]);
        $xlsxPath = $xlsxResponse->getFile()->getPathname();
        $this->assertFileExists($xlsxPath);
        $parsedXlsx = $service->parse($xlsxPath);
        $this->assertCount(1, $parsedXlsx);
        $this->assertSame('MultiOne 5.3K', $parsedXlsx[0]['name']);
        @unlink($xlsxPath);
    }

    public function test_import_updates_existing_product_and_attaches_media(): void
    {
        Storage::fake('public');

        $brand = Brand::create(['name' => 'MultiOne', 'slug' => 'multione']);
        $category = Category::create(['name' => 'Articulated Loaders', 'slug' => 'articulated-loaders']);

        $product = Product::create([
            'name' => 'MultiOne 5 Series',
            'slug' => 'multione-5-series',
            'brand_id' => $brand->id,
            'condition' => 'new',
            'status' => 'published',
            'summary' => 'Original summary to preserve',
            'description_html' => '<p>Original description</p>',
        ]);

        // Create a temporary local image file to simulate downloading
        $tempDir = storage_path('app/testing-images');
        File::ensureDirectoryExists($tempDir);
        $sampleImagePath = $tempDir.'/test-loader.png';
        // 1x1 transparent PNG bytes
        File::put($sampleImagePath, base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNk+A8AAQUBAScY42YAAAAASUVORK5CYII='));

        $service = app(CatalogueImportExport::class);

        $rows = [
            [
                'id' => $product->id,
                'slug' => 'multione-5-series',
                'name' => 'MultiOne 5 Series (Updated)',
                'brand' => 'MultiOne',
                'categories' => 'Articulated Loaders',
                'cover_image_url' => 'local:'.$sampleImagePath,
                'gallery_image_urls' => 'local:'.$sampleImagePath,
            ],
        ];

        $validation = $service->validateRows($rows);
        $this->assertEmpty($validation['errors']);
        $this->assertSame(1, $validation['summary']['to_update']);

        $result = $service->execute($validation['rows'], true, true);

        $this->assertSame(0, $result['created']);
        $this->assertSame(1, $result['updated']);

        $product->refresh();
        $this->assertSame('MultiOne 5 Series (Updated)', $product->name);
        // Non-destructive: empty summary in sheet leaves existing summary intact
        $this->assertSame('Original summary to preserve', $product->summary);
        $this->assertSame('<p>Original description</p>', $product->description_html);

        // Media attached
        $this->assertGreaterThan(0, $product->media()->count());

        @unlink($sampleImagePath);
    }

    public function test_artisan_import_command_supports_dry_run_and_execution(): void
    {
        Storage::fake('public');

        $brand = Brand::create(['name' => 'Bobman', 'slug' => 'bobman']);

        // Create a CSV file
        $tempCsv = storage_path('app/temp-test-catalogue.csv');
        $csvData = "id,slug,name,brand,categories,condition,status,cover_image_url,gallery_image_urls\n";
        $csvData .= ",bobman-sl,Bobman SL Dispenser,Bobman,Bedding Machines,new,published,,\n";
        File::put($tempCsv, $csvData);

        // Dry-run command
        $this->artisan('catalogue:import-sheet', [
            'file' => $tempCsv,
            '--dry-run' => true,
        ])->assertSuccessful()->expectsOutputToContain('[DRY RUN]');

        $this->assertNull(Product::where('slug', 'bobman-sl')->first());

        // Actual execution
        $this->artisan('catalogue:import-sheet', [
            'file' => $tempCsv,
            '--skip-images' => true,
        ])->assertSuccessful()->expectsOutputToContain('--- Import Complete ---');

        $this->assertNotNull(Product::where('slug', 'bobman-sl')->first());

        @unlink($tempCsv);
    }

    public function test_livewire_component_validates_and_executes_upload(): void
    {
        Storage::fake('public');
        $this->admin();

        $csvData = "id,slug,name,brand,categories,condition,status,cover_image_url,gallery_image_urls\n";
        $csvData .= ",yardex-pro,Yardex Pro,Yardex,Spreaders,new,published,,\n";

        $uploaded = UploadedFile::fake()->createWithContent('livewire-test.csv', $csvData);

        $test = Livewire::test(ImportExportComponent::class)
            ->set('uploadFile', $uploaded)
            ->call('validateSpreadsheet')
            ->assertHasNoErrors()
            ->assertSet('hasParsed', true)
            ->assertCount('validationRows', 1)
            ->call('executeImport')
            ->assertSet('importCompleted', true);

        $this->assertNotNull(Product::where('slug', 'yardex-pro')->first());
    }
}
