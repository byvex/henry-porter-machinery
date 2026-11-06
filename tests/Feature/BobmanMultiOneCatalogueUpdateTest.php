<?php

namespace Tests\Feature;

use App\Models\Brand;
use App\Models\Category;
use App\Models\MediaAsset;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class BobmanMultiOneCatalogueUpdateTest extends TestCase
{
    use RefreshDatabase;

    private function seedBaseCatalogue(): void
    {
        $bobman = Brand::create([
            'name' => 'Bobman',
            'slug' => 'bobman',
            'is_active' => true,
            'show_on_homepage' => true,
            'sort_order' => 1,
        ]);

        $multione = Brand::create([
            'name' => 'MultiOne',
            'slug' => 'multione',
            'is_active' => true,
            'show_on_homepage' => true,
            'sort_order' => 2,
        ]);

        $cubicleCleaners = Category::create([
            'name' => 'Cubicle Cleaners',
            'slug' => 'cubicle-cleaners',
            'is_active' => true,
            'sort_order' => 1,
        ]);

        Category::create([
            'name' => 'Feeders',
            'slug' => 'feeders',
            'is_active' => true,
            'sort_order' => 2,
        ]);

        $compactLoaders = Category::create([
            'name' => 'Compact Loaders',
            'slug' => 'compact-loaders',
            'is_active' => true,
            'sort_order' => 3,
        ]);

        foreach ([
            'images/virtuemart/product/Bobman Super Galv.jpg' => 'products/2019/10-28/super/galv.jpg',
            'images/virtuemart/product/Bobman SL .jpg' => 'products/2019/07-30/sl/sl-1.jpg',
            'images/virtuemart/product/Bobman SL 2.jpg' => 'products/2019/07-30/sl/sl-2.jpg',
        ] as $sourcePath => $storagePath) {
            $sampleFile = UploadedFile::fake()->image('sample.jpg', 400, 300);
            $bytes = $sampleFile->getContent();
            Storage::disk('public')->put($storagePath, $bytes);
            MediaAsset::create([
                'source_key' => 'test:'.md5($sourcePath),
                'source_path' => $sourcePath,
                'storage_path' => $storagePath,
                'mime_type' => 'image/jpeg',
                'checksum' => hash('sha256', $bytes),
                'title' => basename($sourcePath),
                'alt_text' => basename($sourcePath),
                'source_published' => true,
            ]);
        }

        foreach (['bobman-s' => 'Bobman S', 'bobman-super' => 'Bobman Super', 'bobman-fl' => 'Bobman FL', 'bobman-sl' => 'Bobman SL'] as $slug => $name) {
            $product = Product::create([
                'name' => $name,
                'slug' => $slug,
                'brand_id' => $bobman->id,
                'condition' => 'new',
                'status' => 'published',
                'record_type' => 'machinery',
                'sort_order' => 0,
            ]);
            $product->categories()->attach($cubicleCleaners->id, ['sort_order' => 0, 'source_imported' => true]);
            if ($slug === 'bobman-fl') {
                $product->testimonials()->create([
                    'content_html' => '<p>I bought the Bobman FL with the disinfectant box.</p><h6>- Alfred Hartley, Moneymore.</h6>',
                    'sort_order' => 0,
                ]);
            }
        }

        $multioneLegacy = Product::create([
            'name' => 'MultiOne Compact Loaders',
            'slug' => 'multione-compact-loaders',
            'brand_id' => $multione->id,
            'condition' => 'new',
            'status' => 'published',
            'record_type' => 'machinery',
            'sort_order' => 0,
        ]);
        $multioneLegacy->categories()->attach($compactLoaders->id, ['sort_order' => 0, 'source_imported' => true]);
        $multioneLegacy->testimonials()->create([
            'content_html' => '<p>We raise ironwork on roads ahead of surfacing crews and recently invested in a MultiOne loader.</p><h6>- Robert Rushe, Robert Rushe Ltd, Moira.</h6>',
            'sort_order' => 0,
        ]);
    }

    public function test_updates_bobman_and_multione_catalogue_and_redirects_retired_models(): void
    {
        Storage::fake('public');
        $this->seedBaseCatalogue();

        // Dry run makes no changes
        $this->artisan('catalogue:update-bobman-multione')
            ->assertExitCode(0);

        $this->assertSame('published', Product::where('slug', 'bobman-s')->value('status'));
        $this->assertNull(Product::where('slug', 'bobman-promax')->first());

        $jpgFile = UploadedFile::fake()->image('photo.jpg', 600, 400);
        $pngFile = UploadedFile::fake()->image('photo.png', 600, 400);
        $jpgBytes = $jpgFile->getContent();
        $pngBytes = $pngFile->getContent();
        $pdfBytes = "%PDF-1.4\n1 0 obj\n<< /Type /Catalog >>\nendobj\n%%EOF\n";

        Http::fake(function ($request) use ($jpgBytes, $pngBytes, $pdfBytes) {
            $url = $request->url();
            if (str_ends_with($url, '.pdf')) {
                return Http::response($pdfBytes, 200, ['Content-Type' => 'application/pdf']);
            }
            if (str_ends_with($url, '.png')) {
                return Http::response($pngBytes, 200, ['Content-Type' => 'image/png']);
            }

            return Http::response($jpgBytes, 200, ['Content-Type' => 'image/jpeg']);
        });

        $this->artisan('catalogue:update-bobman-multione', ['--apply' => true, '--skip-url-verify' => true])
            ->assertExitCode(0);

        // Retired products are draft, protected with admin_modified_at, and redirect 301 to their brand page
        $bobmanS = Product::where('slug', 'bobman-s')->firstOrFail();
        $this->assertSame('draft', $bobmanS->status);
        $this->assertNotNull($bobmanS->admin_modified_at);
        $this->get('/machinery/bobman-s')->assertRedirect('/brands/bobman')->assertStatus(301);

        $multioneOld = Product::where('slug', 'multione-compact-loaders')->firstOrFail();
        $this->assertSame('draft', $multioneOld->status);
        $this->assertNotNull($multioneOld->admin_modified_at);
        $this->get('/machinery/multione-compact-loaders')->assertRedirect('/brands/multione')->assertStatus(301);

        // Bobman brand page displays the 5 active models and excludes Bobman S
        $bobmanBrandResponse = $this->get('/brands/bobman');
        $bobmanBrandResponse->assertOk()
            ->assertSee('Bobman Super')
            ->assertSee('Bobman Front Load (FL)')
            ->assertSee('Bobman Self Load (SL)')
            ->assertSee('Bobman Promax')
            ->assertSee('Bobman Madmax')
            ->assertDontSee('>Bobman S<', false);

        // Bobman Super & Front Load include Electric sections, galvanised details, machine video, and company video
        $this->get('/machinery/bobman-super')
            ->assertOk()
            ->assertSee('Bobman Super Electric')
            ->assertSee('galvanised', false)
            ->assertSee('iFnVe5SQfX4')
            ->assertSee('YeU-ikT1Qxo');

        $this->get('/machinery/bobman-fl')
            ->assertOk()
            ->assertSee('Bobman Front Load Electric')
            ->assertSee('galvanised', false)
            ->assertSee('j93QpNT8wVU')
            ->assertSee('YeU-ikT1Qxo')
            ->assertSee('Alfred Hartley');

        // Promax & Madmax are assigned to both cubicle-cleaners and feeders
        $promax = Product::where('slug', 'bobman-promax')->firstOrFail();
        $this->assertEqualsCanonicalizing(['cubicle-cleaners', 'feeders'], $promax->categories->pluck('slug')->all());

        // MultiOne brand page displays all 8 active series and excludes obsolete 4 Series / 4.2K
        $multioneBrandResponse = $this->get('/brands/multione');
        $multioneBrandResponse->assertOk()
            ->assertSee('MultiOne 1 Series')
            ->assertSee('MultiOne 2 Series')
            ->assertSee('MultiOne 5 Series')
            ->assertSee('MultiOne 6 Series')
            ->assertSee('MultiOne 7 Series')
            ->assertSee('MultiOne 8 Series')
            ->assertSee('MultiOne 11 Series')
            ->assertSee('MultiOne EZ Series (Emission Zero)')
            ->assertDontSee('4 Series')
            ->assertDontSee('4.2K');

        // MultiOne Series pages include spec tables, local PDF datasheets, video, attachments link, and customer testimonial
        $series5 = Product::where('slug', 'multione-5-series')->firstOrFail();
        $this->assertNotNull($series5->admin_modified_at);
        $this->assertStringContainsString('/storage/products/', $series5->description_html);
        $this->assertStringNotContainsString('__PDF_', $series5->description_html);

        $this->get('/machinery/multione-5-series')
            ->assertOk()
            ->assertSee('5.2K iD')
            ->assertSee('5.3K iD')
            ->assertSee('Download MultiOne 5.2K iD Technical Data Sheet (PDF)')
            ->assertSee('https://www.multione.com/attachments/', false)
            ->assertSee('xHkTdof8cQw')
            ->assertSee('Robert Rushe');
    }
}
