<?php

namespace Tests\Feature;

use App\Jobs\NotifyEnquiry;
use App\Livewire\Admin\EnquiryInbox;
use App\Mail\EnquiryReceived;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Enquiry;
use App\Models\LegacyUrlMapping;
use App\Models\Product;
use App\Models\User;
use App\Services\Catalogue\CatalogueAdmin;
use App\Services\Catalogue\LegacyUrls;
use App\Services\Catalogue\PublicCatalogue;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Livewire\Livewire;
use Tests\TestCase;

class PublicCatalogueTest extends TestCase
{
    use RefreshDatabase;

    private function machine(array $fields = []): Product
    {
        return Product::create(array_replace(['name' => 'Test mower', 'slug' => 'mower-'.Str::lower(Str::random(8)), 'record_type' => 'machinery', 'status' => 'published', 'condition' => 'new', 'summary' => 'Reliable mower', 'description_html' => '<table><tr><th>Width</th><td>3m</td></tr></table>'], $fields));
    }

    private function mapping(string $path, string $type, ?string $id = null, ?array $filters = null, ?array $query = null): LegacyUrlMapping
    {
        return LegacyUrlMapping::create(['source_hash' => app(LegacyUrls::class)->hash($path, $query ?? []), 'source_path' => $path, 'source_query' => $query ? http_build_query($query) : null, 'target_type' => $type, 'target_id' => $id, 'filters' => $filters, 'verification_status' => 'verified', 'evidence' => 'test']);
    }

    public function test_listings_show_only_published_machinery_and_paginate(): void
    {
        for ($i = 0; $i < 14; $i++) {
            $this->machine(['name' => 'Visible machine '.$i]);
        }
        $draft = $this->machine(['name' => 'Private draft', 'status' => 'draft']);
        $this->machine(['name' => 'Hidden template', 'record_type' => 'template']);
        $this->machine(['name' => 'Hidden content', 'record_type' => 'content']);
        $this->machine(['name' => 'Deleted machine'])->delete();
        $this->get('/machinery')->assertOk()->assertSee('14 machines found')->assertDontSee('Private draft')->assertDontSee('Hidden template')->assertDontSee('Deleted machine');
        $this->get('/machinery?page=2')->assertOk();
        $this->get('/machinery/'.$draft->slug)->assertNotFound();
    }

    public function test_category_descendants_distinct_counts_brand_condition_and_search(): void
    {
        $parent = Category::create(['name' => 'Grassland', 'slug' => 'grassland']);
        $child = Category::create(['name' => 'Mowers', 'slug' => 'mowers', 'parent_id' => $parent->id]);
        $brand = Brand::create(['name' => 'Brand One', 'slug' => 'brand-one']);
        $new = $this->machine(['name' => 'Alpha mower', 'brand_id' => $brand->id]);
        $new->categories()->attach([$parent->id, $child->id]);
        $used = $this->machine(['name' => 'Used mower', 'condition' => 'used']);
        $used->categories()->attach($child->id);
        $this->get('/categories/grassland')->assertOk()->assertSee('2 machines found');
        $this->get('/categories/grassland?brands[]=brand-one&q=Alpha')->assertOk()->assertSee('1 machine found')->assertDontSee('Used mower');
        $this->get('/used-machinery')->assertOk()->assertSee('Used mower')->assertDontSee('Alpha mower');
        $this->get('/machinery?q=not-a-match')->assertOk()->assertSee('No machinery matches these filters.');
        $empty = Category::create(['name' => 'Empty', 'slug' => 'empty']);
        $this->get('/categories/empty')->assertOk()->assertSee('Contact our team');
        $parent->update(['is_active' => false]);
        $this->get('/categories/grassland')->assertNotFound();
        $brand->update(['is_active' => false]);
        $this->get('/brands/brand-one')->assertNotFound();
    }

    public function test_homepage_uses_selected_records_real_products_and_fallbacks(): void
    {
        $brand = Brand::create(['name' => 'Homepage Brand', 'slug' => 'homepage-brand', 'show_on_homepage' => true]);
        Brand::create(['name' => 'Unselected Brand', 'slug' => 'not-selected']);
        $category = Category::create(['name' => 'Homepage Category', 'slug' => 'homepage-category', 'show_on_homepage' => true]);
        $product = $this->machine(['name' => 'Real featured mower', 'is_featured' => true, 'brand_id' => $brand->id]);
        $product->categories()->attach($category->id);
        $data = app(PublicCatalogue::class)->homepage();
        $this->assertCount(1, $data['brands']);
        $this->assertSame('Real featured mower', $data['products'][0]['title']);
        $this->get('/')->assertOk()->assertSee('Homepage Category')->assertSee('Real featured mower')->assertDontSee('500+')->assertDontSee('BOMFORD');
        $product->update(['is_featured' => false]);
        $this->assertSame($product->name, app(PublicCatalogue::class)->homepage()['products'][0]['title']);
        $category->update(['show_on_homepage' => false]);
        $this->assertSame([], app(PublicCatalogue::class)->homepage()['categories']);
    }

    public function test_details_preserve_tables_and_delay_youtube_and_unknown_urls_are_404(): void
    {
        $product = $this->machine();
        $product->videos()->create(['provider' => 'youtube', 'video_id' => 'OdSOqXLrK00', 'sort_order' => 0]);
        $this->get('/machinery/'.$product->slug)->assertOk()->assertSee('<table>', false)->assertSee('data-video-play', false)->assertDontSee('<iframe', false)->assertSee('Enquire about');
        $this->get('/made-up-page')->assertNotFound()->assertSee('Let');
        $this->get('/sitemap.xml')->assertOk()->assertSee('/machinery/'.$product->slug)->assertDontSee('/admin');
        foreach (['/brands', '/categories', '/parts', '/contact', '/news', '/cookie-policy', '/sitemap'] as $path) {
            $this->get($path)->assertOk();
        }
        $this->get('/machinery?q=mower')->assertHeader('X-Robots-Tag', 'noindex, follow');
    }

    public function test_legacy_paths_query_variants_unavailable_targets_and_safe_redirects(): void
    {
        $brand = Brand::create(['name' => 'Brand', 'slug' => 'brand', 'source_key' => 'joomla:brand:pottinger']);
        $product = $this->machine(['brand_id' => $brand->id, 'legacy_product_id' => 16]);
        $this->mapping('/vm/mower/old-detail.html', 'product', $product->id);
        $this->get('/vm/mower/old-detail.html?utm_source=test')->assertRedirect('/machinery/'.$product->slug)->assertStatus(301);
        $this->get('/index.php?option=com_virtuemart&view=productdetails&virtuemart_product_id=16&Itemid=42')->assertRedirect('/machinery/'.$product->slug)->assertStatus(301);
        $product->update(['status' => 'draft']);
        $this->get('/vm/mower/old-detail.html')->assertRedirect('/brands/brand');
        $this->mapping('/filtering/', 'listing');
        $this->get('/filtering/?q=mower&virtuemart_category_id[]=3')->assertRedirect('/machinery?q=mower&brands%5B0%5D=brand');
        $this->mapping('/unsafe.html', 'page', null, ['page' => 'https://evil.example']);
        $this->get('/unsafe.html')->assertNotFound();
    }

    public function test_admin_slug_changes_create_redirects_and_reserve_old_aliases(): void
    {
        $user = User::factory()->create();
        $user->role = 'admin';
        $user->save();
        $this->actingAs($user);
        $product = $this->machine(['slug' => 'old-slug']);
        app(CatalogueAdmin::class)->save('products', $product->id, ['name' => 'Updated machine', 'slug' => 'new-slug', 'sort_order' => 0, 'condition' => 'new', 'status' => 'published', 'category_ids' => [], 'brand_id' => null], [], [], [], []);
        $this->get('/machinery/old-slug')->assertRedirect('/machinery/new-slug')->assertStatus(301);
        $this->expectException(ValidationException::class);
        app(CatalogueAdmin::class)->save('products', null, ['name' => 'Another', 'slug' => 'old-slug', 'sort_order' => 0, 'condition' => 'new', 'status' => 'draft'], [], [], [], []);
    }

    private function enquiryData(?Product $product = null): array
    {
        $token = (string) Str::uuid7();
        $this->withSession(['enquiry_tokens' => [$token]]);

        return ['name' => 'A Farmer', 'email' => 'farmer@example.test', 'phone' => '01234', 'message' => 'Please tell me more about this machine.', 'submission_token' => $token, 'product_id' => $product?->id];
    }

    public function test_enquiry_is_persisted_without_smtp_and_duplicates_are_idempotent(): void
    {
        Queue::fake();
        config(['enquiries.notifications_enabled' => false]);
        $product = $this->machine();
        $data = $this->enquiryData($product);
        $this->post('/enquiries', $data)->assertRedirect(route('machinery.show', $product->slug).'#enquiry')->assertSessionHas('enquiry_success');
        $this->post('/enquiries', $data)->assertSessionHas('enquiry_success');
        $this->assertDatabaseCount('enquiries', 1);
        $enquiry = Enquiry::firstOrFail();
        $this->assertSame('7', $enquiry->id[14]);
        $this->assertSame($product->name, $enquiry->product_name);
        $this->assertSame('disabled', $enquiry->notification_status);
        Queue::assertNothingPushed();
        $this->get('/admin/enquiries')->assertRedirect('/login');
    }

    public function test_enquiry_validation_honeypot_and_unpublished_product_checks(): void
    {
        $this->post('/enquiries', $this->enquiryData() + ['website' => 'spam'])->assertSessionHasErrors('message');
        $this->assertDatabaseCount('enquiries', 0);
        $product = $this->machine(['status' => 'draft']);
        $this->post('/enquiries', $this->enquiryData($product))->assertSessionHasErrors('product_id');
        $data = $this->enquiryData();
        $data['email'] = 'invalid';
        $this->post('/enquiries', $data)->assertSessionHasErrors('email');
    }

    public function test_rate_limit_and_unissued_tokens_cannot_create_extra_enquiries(): void
    {
        $data = $this->enquiryData();
        $data['submission_token'] = (string) Str::uuid7();
        $this->post('/enquiries', $data)->assertSessionHasErrors('message');
        for ($i = 0; $i < 5; $i++) {
            $this->post('/enquiries', $this->enquiryData())->assertSessionHas('enquiry_success');
        }
        $this->post('/enquiries', $this->enquiryData())->assertSessionHasErrors('message');
        $this->assertDatabaseCount('enquiries', 5);
    }

    public function test_notifications_are_queued_and_admin_can_manage_inbox(): void
    {
        Queue::fake();
        Mail::fake();
        config(['enquiries.notifications_enabled' => true]);
        $this->post('/enquiries', $this->enquiryData())->assertSessionHas('enquiry_success');
        $enquiry = Enquiry::firstOrFail();
        Queue::assertPushed(NotifyEnquiry::class);
        (new NotifyEnquiry($enquiry->id))->handle();
        Mail::assertSent(EnquiryReceived::class);
        $this->assertSame('sent', $enquiry->fresh()->notification_status);
        $user = User::factory()->create();
        $this->actingAs($user)->get('/admin/enquiries')->assertForbidden();
        $user->role = 'admin';
        $user->save();
        Livewire::test(EnquiryInbox::class, ['id' => $enquiry->id])->set('status', 'read')->call('save')->assertHasNoErrors();
        $this->assertSame('read', $enquiry->fresh()->status);
    }
}
