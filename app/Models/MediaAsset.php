<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;
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

    /** @return HasMany<Brand, $this> */
    public function brandsAsImage(): HasMany
    {
        return $this->hasMany(Brand::class, 'image_media_id')->withTrashed();
    }

    /** @return HasMany<Brand, $this> */
    public function brandsAsLogo(): HasMany
    {
        return $this->hasMany(Brand::class, 'logo_media_id')->withTrashed();
    }

    /** @return HasMany<Category, $this> */
    public function categoriesAsImage(): HasMany
    {
        return $this->hasMany(Category::class, 'image_media_id')->withTrashed();
    }

    /**
     * @param  Builder<static>  $query
     * @return Builder<static>
     */
    public function scopeFilterSource(Builder $query, ?string $source): Builder
    {
        return match ($source) {
            'whatsapp_local' => $query->where(function ($q) {
                $q->where('storage_path', 'like', '%whatsapp%')
                    ->orWhere('source_path', 'like', 'local:%');
            }),
            'remote_web' => $query->where('source_path', 'like', 'http%'),
            'admin_upload' => $query->whereNull('source_path'),
            'legacy_joomla' => $query->where(function ($q) {
                $q->where('storage_path', 'like', 'catalogue/joomla/%')
                    ->orWhere('storage_path', 'like', 'legacy/%')
                    ->orWhere('source_path', 'like', 'images/virtuemart/%');
            }),
            default => $query,
        };
    }

    /**
     * @param  Builder<static>  $query
     * @return Builder<static>
     */
    public function scopeFilterAttachment(Builder $query, ?string $status): Builder
    {
        return match ($status) {
            'attached' => $query->where(function ($q) {
                $q->whereHas('products')
                    ->orWhereHas('brandsAsImage')
                    ->orWhereHas('brandsAsLogo')
                    ->orWhereHas('categoriesAsImage');
            }),
            'unattached' => $query->whereDoesntHave('products')
                ->whereDoesntHave('brandsAsImage')
                ->whereDoesntHave('brandsAsLogo')
                ->whereDoesntHave('categoriesAsImage'),
            'products' => $query->whereHas('products'),
            'brands_categories' => $query->where(function ($q) {
                $q->whereHas('brandsAsImage')
                    ->orWhereHas('brandsAsLogo')
                    ->orWhereHas('categoriesAsImage');
            }),
            default => $query,
        };
    }

    /**
     * @param  Builder<static>  $query
     * @return Builder<static>
     */
    public function scopeFilterSearch(Builder $query, ?string $search): Builder
    {
        if (! filled($search)) {
            return $query;
        }

        $term = '%'.trim($search).'%';

        return $query->where(function ($q) use ($term) {
            $q->where('title', 'like', $term)
                ->orWhere('alt_text', 'like', $term)
                ->orWhere('storage_path', 'like', $term)
                ->orWhere('source_path', 'like', $term);
        });
    }

    public function isProtectedLegacy(): bool
    {
        return str_starts_with($this->storage_path, 'catalogue/joomla/');
    }

    public function isReferenced(): bool
    {
        if (DB::table('product_media')->where('media_asset_id', $this->id)->exists()) {
            return true;
        }

        if (Brand::withTrashed()->where(function ($query) {
            $query->where('image_media_id', $this->id)->orWhere('logo_media_id', $this->id);
        })->exists()) {
            return true;
        }

        if (Category::withTrashed()->where('image_media_id', $this->id)->exists()) {
            return true;
        }

        $path = $this->storage_path;
        if (Product::withTrashed()->where(function ($query) use ($path) {
            $query->where('description_html', 'like', '%'.$path.'%')->orWhere('summary', 'like', '%'.$path.'%');
        })->exists()) {
            return true;
        }

        if (Category::withTrashed()->where('description', 'like', '%'.$path.'%')->exists()) {
            return true;
        }

        if (Brand::withTrashed()->where('description', 'like', '%'.$path.'%')->exists()) {
            return true;
        }

        if (DB::table('product_testimonials')->where('content_html', 'like', '%'.$path.'%')->exists()) {
            return true;
        }

        return false;
    }

    public function fileSize(): ?int
    {
        return Storage::disk('public')->exists($this->storage_path)
            ? Storage::disk('public')->size($this->storage_path)
            : null;
    }

    public function formattedFileSize(): string
    {
        $bytes = $this->fileSize();
        if ($bytes === null) {
            return 'Unknown';
        }
        $units = ['B', 'KB', 'MB', 'GB'];
        $power = $bytes > 0 ? (int) floor(log($bytes, 1024)) : 0;

        return number_format($bytes / pow(1024, $power), 1).' '.($units[$power] ?? 'B');
    }

    public function url(): string
    {
        return Storage::disk('public')->url($this->storage_path);
    }
}
