<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Facades\Storage;

class MediaAsset extends Model
{
    use HasUuids;

    protected $guarded = [];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return ['source_published' => 'boolean'];
    }

    /** @return BelongsToMany<Product, $this, ProductMedia> */
    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'product_media')->using(ProductMedia::class)->withPivot(['sort_order', 'legacy_product_media_id']);
    }

    public function url(): string
    {
        return Storage::disk('public')->url($this->storage_path);
    }
}
