<?php

namespace Tests\Feature;

use App\Livewire\Admin\CatalogueEditor;
use App\Models\Brand;
use App\Models\Category;
use App\Models\MediaAsset;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Livewire\Livewire;
use Tests\TestCase;

class AdminMediaLibraryTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        $user = User::factory()->create(['role' => 'admin', 'email_verified_at' => now()]);
        $this->actingAs($user);

        return $user;
    }

    public function test_access_control_on_media_library_page_and_api(): void
    {
        // Guest redirected to login
        $this->get(route('admin.media'))->assertRedirect('/login');
        $this->get(route('admin.media.items'))->assertRedirect('/login');

        // Regular non-admin user is forbidden (403)
        $user = User::factory()->create(['role' => 'user']);
        $this->actingAs($user)->get(route('admin.media'))->assertForbidden();
        $this->actingAs($user)->get(route('admin.media.items'))->assertForbidden();

        // Admin gets 200
        $this->admin();
        $this->get(route('admin.media'))->assertOk();
        $this->get(route('admin.media.items'))->assertOk()->assertJsonStructure(['data', 'current_page', 'last_page', 'total']);
    }

    public function test_items_json_endpoint_filters_and_searches(): void
    {
        $this->admin();
        Storage::fake('public');

        MediaAsset::create([
            'storage_path' => 'products/2026/11-10/test/whatsapp-image-1.jpeg',
            'source_path' => 'local:docs/feedbacks/images/whatsapp-image-1.jpeg',
            'mime_type' => 'image/jpeg',
            'title' => 'Bale Handler Screen',
            'checksum' => hash('sha256', 'wa-1'),
        ]);

        MediaAsset::create([
            'storage_path' => 'products/2026/11-10/test/official.jpeg',
            'source_path' => 'https://hustlerequipment.com/images/official.jpg',
            'mime_type' => 'image/jpeg',
            'title' => 'Hustler Official Photo',
            'checksum' => hash('sha256', 'remote-1'),
        ]);

        MediaAsset::create([
            'storage_path' => 'media/2026/11-12/test/upload.jpeg',
            'source_path' => null,
            'mime_type' => 'image/jpeg',
            'title' => 'Yard Overview',
            'checksum' => hash('sha256', 'upload-1'),
        ]);

        // Filter by source
        $waResponse = $this->getJson(route('admin.media.items', ['source' => 'whatsapp_local']))->assertOk();
        $this->assertCount(1, $waResponse->json('data'));
        $this->assertSame('Bale Handler Screen', $waResponse->json('data.0.title'));

        $remoteResponse = $this->getJson(route('admin.media.items', ['source' => 'remote_web']))->assertOk();
        $this->assertCount(1, $remoteResponse->json('data'));
        $this->assertSame('Hustler Official Photo', $remoteResponse->json('data.0.title'));

        $adminUploadResponse = $this->getJson(route('admin.media.items', ['source' => 'admin_upload']))->assertOk();
        $this->assertCount(1, $adminUploadResponse->json('data'));
        $this->assertSame('Yard Overview', $adminUploadResponse->json('data.0.title'));

        // Search filter
        $searchResponse = $this->getJson(route('admin.media.items', ['search' => 'Overview']))->assertOk();
        $this->assertCount(1, $searchResponse->json('data'));
        $this->assertSame('Yard Overview', $searchResponse->json('data.0.title'));
    }

    public function test_items_json_endpoint_attachment_and_product_filtering(): void
    {
        $this->admin();
        Storage::fake('public');

        $brand = Brand::create(['name' => 'Hustler', 'slug' => 'hustler', 'sort_order' => 0, 'is_active' => true]);
        $product = Product::create(['name' => 'LM100 Softhands', 'slug' => 'lm100-softhands', 'brand_id' => $brand->id, 'sort_order' => 0, 'status' => 'published', 'record_type' => 'machinery']);

        $attachedAsset = MediaAsset::create([
            'storage_path' => 'products/attached.jpg',
            'mime_type' => 'image/jpeg',
            'title' => 'Attached Image',
            'checksum' => hash('sha256', 'att'),
        ]);
        $product->media()->attach($attachedAsset->id, ['id' => (string) Str::uuid7(), 'sort_order' => 0]);

        $orphanAsset = MediaAsset::create([
            'storage_path' => 'products/orphan.jpg',
            'mime_type' => 'image/jpeg',
            'title' => 'Orphan Image',
            'checksum' => hash('sha256', 'orph'),
        ]);

        // Filter unattached
        $orphanRes = $this->getJson(route('admin.media.items', ['attachment' => 'unattached']))->assertOk();
        $this->assertCount(1, $orphanRes->json('data'));
        $this->assertSame($orphanAsset->id, $orphanRes->json('data.0.id'));

        // Filter products
        $prodRes = $this->getJson(route('admin.media.items', ['attachment' => 'products']))->assertOk();
        $this->assertCount(1, $prodRes->json('data'));
        $this->assertSame($attachedAsset->id, $prodRes->json('data.0.id'));

        // Filter specific product
        $specificRes = $this->getJson(route('admin.media.items', ['product' => $product->id]))->assertOk();
        $this->assertCount(1, $specificRes->json('data'));
        $this->assertSame($attachedAsset->id, $specificRes->json('data.0.id'));
    }

    public function test_update_metadata_via_json(): void
    {
        $this->admin();
        Storage::fake('public');

        $asset = MediaAsset::create([
            'storage_path' => 'products/test.jpg',
            'mime_type' => 'image/jpeg',
            'title' => 'Old Title',
            'alt_text' => 'Old Alt',
            'checksum' => hash('sha256', 'test'),
        ]);

        $this->postJson(route('admin.media.update', $asset->id), [
            'title' => 'New Title',
            'alt_text' => 'New Alt Text',
        ])->assertOk()->assertJson(['success' => true]);

        $this->assertSame('New Title', $asset->fresh()->title);
        $this->assertSame('New Alt Text', $asset->fresh()->alt_text);
        $this->assertNotNull($asset->fresh()->admin_modified_at);
    }

    public function test_detach_from_product_via_json(): void
    {
        $this->admin();
        Storage::fake('public');

        $product = Product::create(['name' => 'Bale Handler', 'slug' => 'bale-handler', 'sort_order' => 0, 'status' => 'published', 'record_type' => 'machinery']);

        $asset1 = MediaAsset::create(['storage_path' => 'products/1.jpg', 'mime_type' => 'image/jpeg', 'checksum' => hash('sha256', '1')]);
        $asset2 = MediaAsset::create(['storage_path' => 'products/2.jpg', 'mime_type' => 'image/jpeg', 'checksum' => hash('sha256', '2')]);
        $asset3 = MediaAsset::create(['storage_path' => 'products/3.jpg', 'mime_type' => 'image/jpeg', 'checksum' => hash('sha256', '3')]);

        $product->media()->attach($asset1->id, ['id' => (string) Str::uuid7(), 'sort_order' => 0]);
        $product->media()->attach($asset2->id, ['id' => (string) Str::uuid7(), 'sort_order' => 1]);
        $product->media()->attach($asset3->id, ['id' => (string) Str::uuid7(), 'sort_order' => 2]);

        // Detach asset 2
        $this->postJson(route('admin.media.detach', $asset2->id), [
            'product_id' => $product->id,
        ])->assertOk()->assertJson(['success' => true]);

        $this->assertFalse(DB::table('product_media')->where('media_asset_id', $asset2->id)->where('product_id', $product->id)->exists());

        // Check re-normalization
        $rows = DB::table('product_media')->where('product_id', $product->id)->orderBy('sort_order')->get();
        $this->assertCount(2, $rows);
        $this->assertSame(0, $rows[0]->sort_order);
        $this->assertSame($asset1->id, $rows[0]->media_asset_id);
        $this->assertSame(1, $rows[1]->sort_order);
        $this->assertSame($asset3->id, $rows[1]->media_asset_id);
    }

    public function test_delete_single_via_json(): void
    {
        $this->admin();
        Storage::fake('public');

        $product = Product::create(['name' => 'Bale Handler', 'slug' => 'bale-handler', 'sort_order' => 0, 'status' => 'published', 'record_type' => 'machinery']);

        $path = 'products/2026/11-10/laptop-screen.jpg';
        Storage::disk('public')->put($path, 'screen-content');
        $asset = MediaAsset::create([
            'storage_path' => $path,
            'mime_type' => 'image/jpeg',
            'title' => 'Laptop Screen',
            'checksum' => hash('sha256', 'screen-content'),
        ]);
        $product->media()->attach($asset->id, ['id' => (string) Str::uuid7(), 'sort_order' => 0]);

        // Delete via JSON DELETE
        $this->deleteJson(route('admin.media.delete', $asset->id))
            ->assertOk()
            ->assertJson(['success' => true]);

        $this->assertDatabaseMissing('product_media', ['media_asset_id' => $asset->id]);
        $this->assertDatabaseMissing('media_assets', ['id' => $asset->id]);
        Storage::disk('public')->assertMissing($path);
    }

    public function test_bulk_detach_and_bulk_delete_via_json(): void
    {
        $this->admin();
        Storage::fake('public');

        $product = Product::create(['name' => 'Tractor', 'slug' => 'tractor', 'sort_order' => 0, 'status' => 'published', 'record_type' => 'machinery']);

        $path1 = 'products/bulk1.jpg';
        $path2 = 'products/bulk2.jpg';
        Storage::disk('public')->put($path1, 'b1');
        Storage::disk('public')->put($path2, 'b2');

        $asset1 = MediaAsset::create(['storage_path' => $path1, 'mime_type' => 'image/jpeg', 'checksum' => hash('sha256', 'b1')]);
        $asset2 = MediaAsset::create(['storage_path' => $path2, 'mime_type' => 'image/jpeg', 'checksum' => hash('sha256', 'b2')]);

        $product->media()->attach($asset1->id, ['id' => (string) Str::uuid7(), 'sort_order' => 0]);
        $product->media()->attach($asset2->id, ['id' => (string) Str::uuid7(), 'sort_order' => 1]);

        // Protected legacy asset
        $legacyPath = 'catalogue/joomla/legacy.jpg';
        Storage::disk('public')->put($legacyPath, 'leg');
        $legacyAsset = MediaAsset::create(['storage_path' => $legacyPath, 'mime_type' => 'image/jpeg', 'checksum' => hash('sha256', 'leg')]);

        // Bulk delete via JSON
        $this->postJson(route('admin.media.bulk-delete'), [
            'ids' => [$asset1->id, $asset2->id, $legacyAsset->id],
        ])->assertOk()->assertJson(['success' => true, 'deleted' => 2, 'skipped' => 1]);

        Storage::disk('public')->assertMissing($path1);
        Storage::disk('public')->assertMissing($path2);
        $this->assertDatabaseMissing('media_assets', ['id' => $asset1->id]);
        $this->assertDatabaseMissing('media_assets', ['id' => $asset2->id]);

        // Legacy file and record must remain intact
        Storage::disk('public')->assertExists($legacyPath);
        $this->assertDatabaseHas('media_assets', ['id' => $legacyAsset->id]);
    }

    public function test_upload_to_library_via_json(): void
    {
        $this->admin();
        Storage::fake('public');

        $file = UploadedFile::fake()->image('standalone.png');

        $this->post(route('admin.media.upload'), [
            'files' => [$file],
        ], ['Accept' => 'application/json'])->assertOk()->assertJson(['success' => true, 'count' => 1]);

        $asset = MediaAsset::where('title', 'standalone')->first();
        $this->assertNotNull($asset);
        $this->assertStringStartsWith('media/', $asset->storage_path);
        Storage::disk('public')->assertExists($asset->storage_path);
    }

    public function test_product_editor_attaches_media_from_picker_event(): void
    {
        $this->admin();
        Storage::fake('public');

        $existingAsset1 = MediaAsset::create(['storage_path' => 'products/exist1.jpg', 'mime_type' => 'image/jpeg', 'checksum' => hash('sha256', 'e1')]);
        $existingAsset2 = MediaAsset::create(['storage_path' => 'products/exist2.jpg', 'mime_type' => 'image/jpeg', 'checksum' => hash('sha256', 'e2')]);

        $product = Product::create(['name' => 'Picker Product', 'slug' => 'picker-product', 'sort_order' => 0, 'status' => 'published', 'record_type' => 'machinery']);

        $component = Livewire::test(CatalogueEditor::class, ['resource' => 'products', 'id' => $product->id]);

        // Dispatch Alpine picker attach event to Livewire
        $component->dispatch('media-picker-attached', target: 'gallery', items: [
            ['id' => $existingAsset1->id, 'alt_text' => 'Existing 1', 'url' => $existingAsset1->url(), 'mime_type' => 'image/jpeg'],
            ['id' => $existingAsset2->id, 'alt_text' => 'Existing 2', 'url' => $existingAsset2->url(), 'mime_type' => 'image/jpeg'],
        ]);

        $component->call('save')->assertHasNoErrors();

        $this->assertCount(2, $product->fresh()->media);
        $this->assertTrue($product->fresh()->media->contains('id', $existingAsset1->id));
        $this->assertTrue($product->fresh()->media->contains('id', $existingAsset2->id));
    }

    public function test_brand_and_category_editor_attaches_media_from_picker_event(): void
    {
        $this->admin();
        Storage::fake('public');

        $logoAsset = MediaAsset::create(['storage_path' => 'brands/brand-logo.png', 'mime_type' => 'image/png', 'checksum' => hash('sha256', 'logo')]);
        $imageAsset = MediaAsset::create(['storage_path' => 'brands/brand-photo.jpg', 'mime_type' => 'image/jpeg', 'checksum' => hash('sha256', 'photo')]);

        $brand = Brand::create(['name' => 'Picker Brand', 'slug' => 'picker-brand', 'sort_order' => 0, 'is_active' => true]);

        // Dispatch Alpine picker attach event for brand logo and image
        Livewire::test(CatalogueEditor::class, ['resource' => 'brands', 'id' => $brand->id])
            ->dispatch('media-picker-attached', target: 'logo', item: ['id' => $logoAsset->id, 'alt_text' => 'Logo Alt', 'url' => $logoAsset->url()])
            ->dispatch('media-picker-attached', target: 'image', item: ['id' => $imageAsset->id, 'alt_text' => 'Photo Alt', 'url' => $imageAsset->url()])
            ->call('save')
            ->assertHasNoErrors();

        $freshBrand = $brand->fresh();
        $this->assertSame($logoAsset->id, $freshBrand->logo_media_id);
        $this->assertSame($imageAsset->id, $freshBrand->image_media_id);

        // Category image picker
        $category = Category::create(['name' => 'Picker Category', 'slug' => 'picker-category', 'sort_order' => 0, 'is_active' => true]);
        $catAsset = MediaAsset::create(['storage_path' => 'categories/cat.png', 'mime_type' => 'image/png', 'checksum' => hash('sha256', 'cat')]);

        Livewire::test(CatalogueEditor::class, ['resource' => 'categories', 'id' => $category->id])
            ->dispatch('media-picker-attached', target: 'image', item: ['id' => $catAsset->id, 'alt_text' => 'Cat Alt', 'url' => $catAsset->url()])
            ->call('save')
            ->assertHasNoErrors();

        $this->assertSame($catAsset->id, $category->fresh()->image_media_id);
    }
}
