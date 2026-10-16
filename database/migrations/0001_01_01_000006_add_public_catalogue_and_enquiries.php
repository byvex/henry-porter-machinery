<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        foreach (['brands', 'categories'] as $table) {
            Schema::table($table, function (Blueprint $t): void {
                $t->boolean('show_on_homepage')->default(false);
                $t->unsignedInteger('homepage_order')->default(0);
            });
        }
        foreach (['pottinger', 'bobman', 'hustler', 'multione', 'strautmann', 'fleming', 'sherpa'] as $i => $slug) {
            DB::table('brands')->where('slug', $slug)->update(['show_on_homepage' => true, 'homepage_order' => $i]);
        }
        foreach (['compact-loaders', 'grassland', 'tillage', 'feeders', 'cubicle-cleaners', 'other-machinery'] as $i => $slug) {
            DB::table('categories')->where('slug', $slug)->update(['show_on_homepage' => true, 'homepage_order' => $i]);
        }
        Schema::create('enquiries', function (Blueprint $t): void {
            $t->uuid('id')->primary();
            $t->string('submission_key', 64)->unique();
            $t->string('name');
            $t->string('email');
            $t->string('phone', 50)->nullable();
            $t->text('message');
            $t->foreignUuid('product_id')->nullable()->constrained()->nullOnDelete();
            $t->string('product_name')->nullable();
            $t->text('product_url')->nullable();
            $t->string('status', 12)->default('new')->index();
            $t->string('notification_status', 20)->default('disabled');
            $t->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('enquiries');
        foreach (['brands', 'categories'] as $table) {
            Schema::table($table, fn (Blueprint $t) => $t->dropColumn(['show_on_homepage', 'homepage_order']));
        }
    }
};
