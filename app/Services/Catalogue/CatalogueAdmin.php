<?php

namespace App\Services\Catalogue;

use App\Models\Brand;
use App\Models\Category;
use App\Models\LegacyUrlMapping;
use App\Models\MediaAsset;
use App\Models\Product;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Throwable;

class CatalogueAdmin
{
    /** @return class-string<Product>|class-string<Category>|class-string<Brand> */
    public function model(string $resource): string
    {
        return match ($resource) {
            'products' => Product::class, 'categories' => Category::class, 'brands' => Brand::class,
            default => abort(404),
        };
    }

    public function clean(string $html): string
    {
        return app(CatalogueHtml::class)->clean($html, function (string $url, bool $image): ?string {
            $url = html_entity_decode(trim($url), ENT_QUOTES | ENT_HTML5, 'UTF-8');
            if (preg_match('/[\x00-\x20]/', $url)) {
                return null;
            }
            if (str_starts_with($url, '/') && ! str_starts_with($url, '//')) {
                return $url;
            }
            $scheme = strtolower(parse_url($url, PHP_URL_SCHEME) ?: '');

            return in_array($scheme, $image ? ['https', 'http'] : ['https', 'http', 'mailto', 'tel'], true) ? $url : null;
        });
    }

    public function youtube(string $value): ?string
    {
        $value = trim($value);
        if (preg_match('/^[\w-]{11}$/D', $value)) {
            return $value;
        }
        $parts = parse_url($value);
        if (! $parts || ! in_array(strtolower($parts['scheme'] ?? ''), ['https', 'http'], true)) {
            return null;
        }
        $host = strtolower($parts['host'] ?? '');
        $id = null;
        if ($host === 'youtu.be') {
            $id = trim($parts['path'] ?? '', '/');
        }
        if (in_array($host, ['youtube.com', 'www.youtube.com', 'm.youtube.com', 'www.youtube-nocookie.com'], true)) {
            parse_str($parts['query'] ?? '', $query);
            $id = $query['v'] ?? null;
            if (preg_match('~^/(?:embed|shorts)/([\w-]{11})(?:/|$)~', $parts['path'] ?? '', $match)) {
                $id = $match[1];
            }
        }

        return is_string($id) && preg_match('/^[\w-]{11}$/D', $id) ? $id : null;
    }

    /** @param array<string, mixed> $form
     * @param  array<int, array<string, mixed>>  $gallery
     * @param  array<int, array<string, mixed>>  $videos
     * @param  array<int, array<string, mixed>>  $testimonials
     * @param  array{images?: array<int, UploadedFile>, image?: UploadedFile|null, logo?: UploadedFile|null, remove_image?: bool, remove_logo?: bool}  $uploads
     */
    public function save(string $resource, ?string $id, array $form, array $gallery, array $videos, array $testimonials, array $uploads): Product|Category|Brand
    {
        Gate::authorize('manage-catalogue');
        $class = $this->model($resource);
        $record = $id ? $class::findOrFail($id) : new $class;
        if (! $record->exists) {
            $record->id = (string) Str::uuid7();
        }
        $form['slug'] = trim((string) ($form['slug'] ?? '')) ?: Str::slug($form['name'] ?? '');
        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/', Rule::unique($resource, 'slug')->ignore($record->id)],
            'sort_order' => ['required', 'integer', 'min:0', 'max:1000000'],
        ];
        if ($resource === 'products') {
            $rules += [
                'summary' => ['nullable', 'string', 'max:30000'], 'description_html' => ['nullable', 'string', 'max:500000'],
                'brand_id' => ['nullable', 'uuid', Rule::exists('brands', 'id')->whereNull('deleted_at')],
                'category_ids' => ['array'], 'category_ids.*' => ['uuid', 'distinct', Rule::exists('categories', 'id')->whereNull('deleted_at')],
                'condition' => ['required', Rule::in(['new', 'used'])], 'status' => ['required', Rule::in(['draft', 'published'])], 'is_featured' => ['boolean'],
            ];
        } else {
            $rules += ['show_on_homepage' => ['boolean'], 'homepage_order' => ['integer', 'min:0', 'max:1000000'], 'image_alt_text' => ['nullable', 'string', 'max:1000'], 'logo_alt_text' => ['nullable', 'string', 'max:1000'], 'description' => ['nullable', 'string', 'max:100000'], 'is_active' => ['boolean']];
            if ($resource === 'categories') {
                $rules['parent_id'] = ['nullable', 'uuid', Rule::exists('categories', 'id')->whereNull('deleted_at')];
            }
        }
        $data = Validator::make($form, $rules)->validate();
        $prefix = $resource === 'products' ? 'machinery' : $resource;
        if (LegacyUrlMapping::where('source_path', '/'.$prefix.'/'.$data['slug'])->where('target_id', '!=', $record->id)->exists()) {
            throw ValidationException::withMessages(['slug' => 'This slug is reserved by an existing public redirect.']);
        }
        if ($resource === 'categories') {
            $parent = $data['parent_id'] ?? null;
            $seen = [$record->id];
            while ($parent) {
                if (in_array($parent, $seen, true)) {
                    throw ValidationException::withMessages(['parent_id' => 'A category cannot be its own ancestor.']);
                }
                $seen[] = $parent;
                $parent = Category::whereKey($parent)->value('parent_id');
            }
        }
        Validator::make(['gallery' => $gallery, 'videos' => $videos, 'testimonials' => $testimonials], [
            'gallery' => ['array', 'max:200'], 'gallery.*.id' => ['required', 'uuid', 'distinct'], 'gallery.*.alt_text' => ['nullable', 'string', 'max:1000'],
            'videos' => ['array', 'max:50'], 'videos.*.id' => ['nullable', 'uuid', 'distinct'], 'videos.*.value' => ['required', 'string', 'max:2048'],
            'testimonials' => ['array', 'max:100'], 'testimonials.*.id' => ['nullable', 'uuid', 'distinct'], 'testimonials.*.content_html' => ['required', 'string', 'max:30000'],
        ])->validate();
        foreach ($videos as $index => $video) {
            if (! $this->youtube($video['value'])) {
                throw ValidationException::withMessages(['videos.'.$index.'.value' => 'Enter a valid YouTube URL or video ID.']);
            }
        }
        foreach (array_intersect_key($uploads, array_flip(['images', 'image', 'logo'])) as $key => $upload) {
            foreach (is_array($upload) ? $upload : [$upload] as $file) {
                if ($file) {
                    Validator::make([$key => $file], [$key => ['file', 'image', 'mimes:jpg,jpeg,png,webp,gif,avif', 'mimetypes:image/jpeg,image/png,image/webp,image/gif,image/avif', 'max:10240']])->validate();
                }
            }
        }
        if (count($uploads['images'] ?? []) > 20) {
            throw ValidationException::withMessages(['images' => 'Upload at most 20 images at a time.']);
        }
        $written = [];
        try {
            DB::transaction(function () use ($resource, $record, $data, $gallery, $videos, $testimonials, $uploads, &$written): void {
                $fields = $data;
                unset($fields['category_ids'], $fields['image_alt_text'], $fields['logo_alt_text']);
                foreach (['summary', 'description_html', 'description'] as $field) {
                    if (isset($fields[$field])) {
                        $fields[$field] = $this->clean($fields[$field]);
                    }
                }
                if ($resource === 'products') {
                    $fields['record_type'] = $record->record_type ?? 'machinery';
                }
                $record->fill($fields);
                $record->admin_modified_at = now();
                $folder = app(MediaFolders::class)->folder($record);
                $oldSlug = $record->getRawOriginal('slug');
                $record->save();
                if (is_string($oldSlug)) {
                    app(LegacyUrls::class)->rememberSlug($record, $oldSlug);
                }
                if ($record instanceof Product) {
                    $record->categories()->sync(array_fill_keys($data['category_ids'] ?? [], ['source_imported' => false, 'sort_order' => 0]));
                    $allowed = $record->media()->pluck('media_assets.id')->all();
                    $links = [];
                    foreach ($gallery as $index => $image) {
                        if (! in_array($image['id'], $allowed, true)) {
                            throw ValidationException::withMessages(['gallery' => 'This image is not attached to this product.']);
                        }
                        $asset = MediaAsset::whereKey($image['id'])->firstOrFail();
                        $asset->update(['alt_text' => $image['alt_text'] ?? '', 'admin_modified_at' => now()]);
                        $links[$asset->id] = ['sort_order' => $index];
                    }
                    foreach ($uploads['images'] ?? [] as $file) {
                        $asset = $this->upload($file, $folder, $written);
                        $links[$asset->id] = ['sort_order' => count($links)];
                    }
                    $record->media()->sync($links);
                    foreach (['videos' => $videos, 'testimonials' => $testimonials] as $relation => $rows) {
                        $retained = [];
                        foreach ($rows as $index => $row) {
                            $child = ! empty($row['id']) ? $record->$relation()->whereKey($row['id'])->firstOrFail() : $record->$relation()->make();
                            $child->fill($relation === 'videos' ? ['provider' => 'youtube', 'video_id' => $this->youtube($row['value'])] : ['content_html' => $this->clean($row['content_html'])]);
                            $child->sort_order = $index;
                            $child->save();
                            $retained[] = $child->id;
                        }
                        $record->$relation()->whereNotIn('id', $retained)->delete();
                    }
                } else {
                    foreach (['image' => 'image_media_id', 'logo' => 'logo_media_id'] as $key => $column) {
                        if ($resource !== 'brands' && $key === 'logo') {
                            continue;
                        }
                        if ($uploads[$key] ?? null) {
                            $record->setAttribute($column, $this->upload($uploads[$key], $folder, $written)->id);
                        } elseif ($uploads['remove_'.$key] ?? false) {
                            $record->setAttribute($column, null);
                        }
                        if ($record->getAttribute($column) && array_key_exists($key.'_alt_text', $data)) {
                            MediaAsset::whereKey($record->getAttribute($column))->update(['alt_text' => $data[$key.'_alt_text'], 'admin_modified_at' => now()]);
                        }
                    }
                    $record->save();
                }
            });
        } catch (Throwable $error) {
            foreach ($written as $path) {
                Storage::disk('public')->delete($path);
            }
            throw $error;
        }

        return $record;
    }

    /** @param list<string> $written */
    private function upload(UploadedFile $file, string $folder, array &$written): MediaAsset
    {
        $name = app(MediaFolders::class)->filename($file->getClientOriginalName(), $file->extension());
        $path = $folder.'/'.$name;
        $written[] = $path;
        if (! $file->storeAs($folder, $name, 'public')) {
            throw new \RuntimeException('Image could not be stored.');
        }
        $checksum = hash_file('sha256', $file->getRealPath());

        return MediaAsset::create(['storage_path' => $path, 'mime_type' => $file->getMimeType(), 'checksum' => $checksum, 'title' => pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME), 'admin_modified_at' => now()]);
    }

    public function delete(string $resource, string $id): void
    {
        Gate::authorize('manage-catalogue');
        $class = $this->model($resource);
        $record = $class::findOrFail($id);
        if ($record instanceof Brand && Product::withTrashed()->where('brand_id', $id)->exists()) {
            throw ValidationException::withMessages(['delete' => 'This brand has products. Deactivate it instead.']);
        }
        if ($record instanceof Category && ($record->children()->withTrashed()->exists() || DB::table('category_product')->where('category_id', $id)->exists())) {
            throw ValidationException::withMessages(['delete' => 'This category has products or children. Deactivate it instead.']);
        }
        $record->update(['admin_modified_at' => now()]);
        $record->delete();
    }
}
