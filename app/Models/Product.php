<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/** @property-read Brand|null $brand */
class Product extends Model
{
    use HasUuids, SoftDeletes;

    protected $guarded = [];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return ['media_folder_date' => 'date', 'admin_modified_at' => 'datetime', 'is_featured' => 'boolean', 'review_flags' => 'array', 'source_metadata' => 'array'];
    }

    /**
     * @param  Builder<static>  $query
     * @return Builder<static>
     */
    public function scopeCatalogue(Builder $query): Builder
    {
        return $query->where('record_type', 'machinery')->where('status', 'published');
    }

    /** @return BelongsTo<Brand, $this> */
    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class);
    }

    /** @return BelongsToMany<Category, $this> */
    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class)->withPivot(['sort_order', 'source_imported']);
    }

    /** @return BelongsToMany<MediaAsset, $this, ProductMedia> */
    public function media(): BelongsToMany
    {
        return $this->belongsToMany(MediaAsset::class, 'product_media')->using(ProductMedia::class)->withPivot(['sort_order', 'legacy_product_media_id']);
    }

    public function coverImage(): ?MediaAsset
    {
        return $this->media()->where('mime_type', 'like', 'image/%')->orderByPivot('sort_order')->orderByPivot('legacy_product_media_id')->first();
    }

    /** @return HasMany<ProductVideo, $this> */
    public function videos(): HasMany
    {
        return $this->hasMany(ProductVideo::class)->orderBy('sort_order')->orderBy('legacy_customfield_id');
    }

    /** @return HasMany<ProductTestimonial, $this> */
    public function testimonials(): HasMany
    {
        return $this->hasMany(ProductTestimonial::class)->orderBy('sort_order')->orderBy('legacy_customfield_id');
    }
}
