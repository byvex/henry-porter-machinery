<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('media_assets', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('source_key')->nullable()->unique();
            $table->timestamp('admin_modified_at')->nullable();
            $table->unsignedBigInteger('legacy_media_id')->nullable()->unique();
            $table->text('source_path')->nullable();
            $table->text('storage_path');
            $table->string('mime_type');
            $table->text('title')->nullable();
            $table->text('alt_text')->nullable();
            $table->char('checksum', 64);
            $table->boolean('source_published')->default(true);
            $table->timestamps();
        });
        Schema::create('brands', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('source_key')->nullable()->unique();
            $table->timestamp('admin_modified_at')->nullable();
            $table->string('name');
            $table->string('slug')->unique();
            $table->date('media_folder_date')->nullable();
            $table->softDeletes();
            $table->text('description')->nullable();
            $table->foreignUuid('logo_media_id')->nullable()->constrained('media_assets')->nullOnDelete();
            $table->foreignUuid('image_media_id')->nullable()->constrained('media_assets')->nullOnDelete();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
        Schema::create('categories', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('source_key')->nullable()->unique();
            $table->timestamp('admin_modified_at')->nullable();
            $table->string('name');
            $table->string('slug')->unique();
            $table->date('media_folder_date')->nullable();
            $table->softDeletes();
            $table->foreignUuid('parent_id')->nullable()->constrained('categories')->restrictOnDelete();
            $table->text('description')->nullable();
            $table->foreignUuid('image_media_id')->nullable()->constrained('media_assets')->nullOnDelete();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
        Schema::create('products', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->unsignedBigInteger('legacy_product_id')->nullable()->unique();
            $table->timestamp('admin_modified_at')->nullable();
            $table->string('name');
            $table->string('slug')->unique();
            $table->date('media_folder_date')->nullable();
            $table->softDeletes();
            $table->text('summary')->nullable();
            $table->longText('description_html')->nullable();
            $table->foreignUuid('brand_id')->nullable()->constrained()->nullOnDelete();
            $table->string('condition', 12)->nullable();
            $table->string('status', 12)->default('draft');
            $table->string('record_type', 12)->default('machinery');
            $table->boolean('is_featured')->default(false);
            $table->unsignedInteger('sort_order')->default(0);
            $table->json('review_flags')->nullable();
            $table->json('source_metadata')->nullable();
            $table->timestamps();
            $table->index(['record_type', 'status', 'condition']);
        });
        Schema::create('category_product', function (Blueprint $table) {
            $table->foreignUuid('product_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('category_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('source_imported')->default(false);
            $table->primary(['product_id', 'category_id']);
        });
        Schema::create('product_media', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('product_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('media_asset_id')->constrained()->cascadeOnDelete();
            $table->unsignedBigInteger('legacy_product_media_id')->nullable()->unique();
            $table->unsignedInteger('sort_order')->default(0);
            $table->unique(['product_id', 'media_asset_id']);
        });
        foreach (['product_videos', 'product_testimonials'] as $name) {
            Schema::create($name, function (Blueprint $table) use ($name) {
                $table->uuid('id')->primary();
                $table->foreignUuid('product_id')->constrained()->cascadeOnDelete();
                $table->unsignedBigInteger('legacy_customfield_id')->nullable()->unique();
                if ($name === 'product_videos') {
                    $table->string('provider', 24);
                    $table->string('video_id', 128);
                } else {
                    $table->longText('content_html');
                }
                $table->unsignedInteger('sort_order')->default(0);
                $table->timestamps();
            });
        }
        Schema::create('legacy_url_mappings', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->char('source_hash', 64)->unique();
            $table->text('source_path');
            $table->text('source_query')->nullable();
            $table->string('target_type', 24);
            $table->uuid('target_id')->nullable();
            $table->json('filters')->nullable();
            $table->string('verification_status', 24)->default('pending');
            $table->string('evidence', 64);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        foreach (['legacy_url_mappings', 'product_testimonials', 'product_videos', 'product_media', 'category_product', 'products', 'categories', 'brands', 'media_assets'] as $table) {
            Schema::dropIfExists($table);
        }
    }
};
