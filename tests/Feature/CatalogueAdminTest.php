<?php

namespace Tests\Feature;

use App\Livewire\Admin\CatalogueEditor;
use App\Livewire\Admin\CatalogueIndex;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Passkey;
use App\Models\Product;
use App\Models\User;
use App\Services\Catalogue\CatalogueAdmin;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Livewire\Livewire;
use Tests\TestCase;

class CatalogueAdminTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        $user = User::factory()->create();
        $user->role = 'admin';
        $user->save();
        $this->actingAs($user);

        return $user;
    }

    private function form(array $overrides = []): array
    {
        return array_replace(['name' => 'New machine', 'slug' => '', 'sort_order' => 0, 'summary' => '', 'description_html' => '<table><tr><td>3 metres</td></tr></table><script>alert(1)</script>', 'condition' => 'new', 'status' => 'published', 'brand_id' => null, 'category_ids' => [], 'is_featured' => true], $overrides);
    }

    public function test_admin_access_is_enforced_on_pages_and_livewire_mutations(): void
    {
        $this->get('/admin')->assertRedirect('/login');
        $user = User::factory()->create();
        $this->assertSame('user', $user->fresh()->role);
        $this->actingAs($user)->get('/admin/products')->assertForbidden();
        Livewire::test(CatalogueIndex::class, ['resource' => 'products'])->assertForbidden();
        $admin = $this->admin();
        $this->get('/admin')->assertOk();
        foreach (['products', 'brands', 'categories'] as $resource) {
            $this->get('/admin/'.$resource)->assertOk();
            $this->get('/admin/'.$resource.'/create')->assertOk();
        }
        $component = Livewire::test(CatalogueEditor::class, ['resource' => 'products']);
        $admin->role = 'user';
        $admin->save();
        $component->call('save')->assertForbidden();
    }

    public function test_registration_cannot_assign_an_admin_role_and_unverified_admin_cannot_enter(): void
    {
        $this->post('/register', ['name' => 'Visitor', 'email' => 'visitor@example.test', 'password' => 'Password123!', 'password_confirmation' => 'Password123!', 'role' => 'admin']);
        $this->assertSame('user', User::where('email', 'visitor@example.test')->firstOrFail()->role);
        $user = User::factory()->unverified()->create();
        $user->role = 'admin';
        $user->save();
        $this->actingAs($user)->get('/admin')->assertRedirect('/email/verify');
    }

    public function test_administrator_command_promotes_an_existing_account_without_changing_password(): void
    {
        $user = User::factory()->create();
        $hash = $user->password;
        $this->artisan('catalogue:admin', ['email' => $user->email])->assertSuccessful();
        $this->assertSame('admin', $user->fresh()->role);
        $this->assertSame($hash, $user->fresh()->password);
    }

    public function test_temporary_upload_endpoint_rejects_regular_verified_users(): void
    {
        $user = User::factory()->create();
        $url = URL::temporarySignedRoute('livewire.upload-file', now()->addMinute());
        $this->actingAs($user)->post($url, ['files' => [UploadedFile::fake()->image('photo.jpg')]])->assertForbidden();
    }

    public function test_uuidv7_and_passkey_relationships_work(): void
    {
        $user = $this->admin();
        $this->assertSame('7', $user->id[14]);
        $passkey = $user->passkeys()->create(['name' => 'Test key', 'credential_id' => 'credential', 'credential' => ['aaguid' => 'test']]);
        $this->assertInstanceOf(Passkey::class, $passkey);
        $this->assertSame('7', $passkey->id[14]);
        $this->assertSame($user->id, $passkey->user_id);
        $this->assertSame($user->id, $passkey->user->id);
    }

    public function test_product_crud_uploads_sanitisation_gallery_order_and_restore(): void
    {
        $this->admin();
        Storage::fake('public');
        $category = Category::create(['name' => 'Mowers', 'slug' => 'mowers']);
        $brand = Brand::create(['name' => 'Pöttinger', 'slug' => 'pottinger']);
        $product = app(CatalogueAdmin::class)->save('products', null, $this->form(['brand_id' => $brand->id, 'category_ids' => [$category->id]]), [], [['value' => 'https://youtu.be/OdSOqXLrK00']], [['content_html' => '<p>Excellent</p><img src=x onerror=alert(1)>']], ['images' => [UploadedFile::fake()->image('Mower photo.png'), UploadedFile::fake()->image('Second.png')]]);
        $this->assertSame('7', $product->id[14]);
        $this->assertSame(1, Product::catalogue()->count());
        $this->assertStringContainsString('<table>', $product->description_html);
        $this->assertStringNotContainsString('script', $product->description_html);
        $this->assertSame(1, $product->categories()->count());
        $this->assertSame(1, $product->videos()->count());
        $this->assertSame(1, $product->testimonials()->count());
        $images = $product->media()->orderByPivot('sort_order')->get();
        foreach ($images as $asset) {
            $this->assertStringStartsWith('products/'.now()->format('Y/m-d').'/'.$product->id.'/', $asset->storage_path);
            $this->assertLessThanOrEqual(100, strlen(basename($asset->storage_path)));
            Storage::disk('public')->assertExists($asset->storage_path);
        }
        $this->assertSame('7', DB::table('product_media')->first()->id[14]);
        $component = Livewire::test(CatalogueEditor::class, ['resource' => 'products', 'id' => $product->id]);
        $component->call('moveImage', 1, -1)->call('save')->assertHasNoErrors();
        $this->assertSame($images[1]->id, $product->fresh()->coverImage()->id);
        app(CatalogueAdmin::class)->delete('products', $product->id);
        $this->assertSame(0, Product::catalogue()->count());
        Livewire::test(CatalogueIndex::class, ['resource' => 'products'])->call('restore', $product->id)->assertHasNoErrors();
        $this->assertSame(1, Product::catalogue()->count());
        $this->assertCount(2, Storage::disk('public')->allFiles());
    }

    public function test_brand_and_category_images_and_parent_cycle_checks(): void
    {
        $this->admin();
        Storage::fake('public');
        $service = app(CatalogueAdmin::class);
        $brand = $service->save('brands', null, ['name' => 'Brand', 'slug' => '', 'sort_order' => 0, 'is_active' => true], [], [], [], ['logo' => UploadedFile::fake()->image('logo.png'), 'image' => UploadedFile::fake()->image('machine.jpg')]);
        $this->assertNotSame($brand->logo_media_id, $brand->image_media_id);
        $this->assertStringStartsWith('brands/', $brand->logo->storage_path);
        $parent = Category::create(['name' => 'Grassland', 'slug' => 'grassland']);
        $child = $service->save('categories', null, ['name' => 'Mowers', 'slug' => '', 'description' => '', 'sort_order' => 0, 'is_active' => true, 'parent_id' => $parent->id], [], [], [], ['image' => UploadedFile::fake()->image('mowers.png')]);
        $this->assertStringStartsWith('categories/', $child->image->storage_path);
        try {
            $service->save('categories', $parent->id, ['name' => 'Grassland', 'slug' => 'grassland', 'sort_order' => 0, 'parent_id' => $child->id], [], [], [], []);
            $this->fail('Cycle should fail.');
        } catch (ValidationException $error) {
            $this->assertArrayHasKey('parent_id', $error->errors());
        }
        try {
            $service->delete('categories', $parent->id);
            $this->fail('Referenced category should remain.');
        } catch (ValidationException $error) {
            $this->assertArrayHasKey('delete', $error->errors());
        }
        Product::create(['name' => 'Machine', 'slug' => 'machine', 'brand_id' => $brand->id]);
        try {
            $service->delete('brands', $brand->id);
            $this->fail('Referenced brand should remain.');
        } catch (ValidationException $error) {
            $this->assertArrayHasKey('delete', $error->errors());
        }
    }

    public function test_invalid_uploads_videos_and_gallery_references_leave_no_new_files(): void
    {
        $this->admin();
        Storage::fake('public');
        $service = app(CatalogueAdmin::class);
        foreach ([['images' => [UploadedFile::fake()->create('bad.svg', 1, 'image/svg+xml')]], ['images' => [UploadedFile::fake()->image('big.jpg')->size(11000)]]] as $uploads) {
            try {
                $service->save('products', null, $this->form(), [], [], [], $uploads);
                $this->fail('Upload should fail.');
            } catch (ValidationException $error) {
                $this->assertNotEmpty($error->errors());
            }
        }
        try {
            $service->save('products', null, $this->form(), [], [['value' => 'https://evil.example/embed/OdSOqXLrK00']], [], []);
            $this->fail('Video should fail.');
        } catch (ValidationException $error) {
            $this->assertArrayHasKey('videos.0.value', $error->errors());
        }
        try {
            $service->save('products', null, $this->form(), [['id' => (string) Str::uuid7(), 'alt_text' => '']], [], [], ['images' => [UploadedFile::fake()->image('ok.jpg')]]);
            $this->fail('Foreign gallery should fail.');
        } catch (ValidationException $error) {
            $this->assertArrayHasKey('gallery', $error->errors());
        }
        $this->assertSame([], Storage::disk('public')->allFiles());
        $this->assertDatabaseCount('products', 0);
    }

    public function test_flat_file_cleanup_is_manifest_limited_and_read_only_by_default(): void
    {
        Storage::fake('public');
        $path = 'catalogue/joomla/'.str_repeat('a', 64).'.jpg';
        Storage::disk('public')->put($path, 'old file');
        Storage::disk('public')->put('unrelated.jpg', 'keep');
        $manifest = tempnam(sys_get_temp_dir(), 'catalogue-manifest');
        try {
            file_put_contents($manifest, json_encode(['files' => [['path' => $path, 'checksum' => hash('sha256', 'old file')]]], JSON_THROW_ON_ERROR));
            $this->artisan('catalogue:prune-legacy-media', ['manifest' => $manifest])->assertSuccessful();
            Storage::disk('public')->assertExists($path);
            $this->artisan('catalogue:prune-legacy-media', ['manifest' => $manifest, '--apply' => true])->assertSuccessful();
            Storage::disk('public')->assertMissing($path);
            Storage::disk('public')->assertExists('unrelated.jpg');
        } finally {
            unlink($manifest);
        }
    }

    public function test_later_uploads_use_creation_date_and_failed_save_removes_new_file(): void
    {
        $this->admin();
        Storage::fake('public');
        $product = Product::create(['name' => 'Old machine', 'slug' => 'old-machine', 'created_at' => '2020-04-06 10:00:00']);
        $service = app(CatalogueAdmin::class);
        $service->save('products', $product->id, $this->form(['slug' => 'old-machine']), [], [], [], ['images' => [UploadedFile::fake()->image('first.jpg')]]);
        $path = $product->fresh()->coverImage()->storage_path;
        $this->assertStringStartsWith('products/2020/04-06/'.$product->id.'/', $path);
        try {
            // The new file is written before the forged nested ID fails inside the transaction.
            $service->save('products', $product->id, $this->form(['slug' => 'old-machine']), [], [['id' => (string) Str::uuid7(), 'value' => 'OdSOqXLrK00']], [], ['images' => [UploadedFile::fake()->image('rolled-back.jpg')]]);
            $this->fail('Foreign nested ID should fail.');
        } catch (ModelNotFoundException $error) {
        }
        $this->assertSame([$path], Storage::disk('public')->allFiles());
        $this->assertDatabaseCount('media_assets', 1);
    }
}
