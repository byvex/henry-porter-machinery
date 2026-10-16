<?php

namespace App\Console\Commands;

use App\Models\Brand;
use App\Models\Category;
use App\Models\MediaAsset;
use App\Models\Product;
use App\Models\ProductTestimonial;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

class PruneLegacyCatalogueMedia extends Command
{
    protected $signature = 'catalogue:prune-legacy-media {manifest : Private JSON manifest recorded before rebuilding} {--apply : Delete verified, unreferenced old flat files}';

    protected $description = 'Verify and optionally remove only obsolete files listed in a legacy catalogue media manifest';

    public function handle(): int
    {
        $path = $this->argument('manifest');
        if (! is_string($path)) {
            throw new RuntimeException('A manifest path is required.');
        }
        $manifest = json_decode(File::get($path), true, flags: JSON_THROW_ON_ERROR);
        if (! is_array($manifest) || ! isset($manifest['files']) || ! is_array($manifest['files'])) {
            throw new RuntimeException('Invalid media manifest.');
        }
        $disk = Storage::disk('public');
        $paths = [];
        foreach ($manifest['files'] as $entry) {
            if (! is_array($entry) || ! is_string($entry['path'] ?? null) || ! is_string($entry['checksum'] ?? null)) {
                throw new RuntimeException('Invalid manifest entry.');
            }
            $path = $entry['path'];
            if (! preg_match('~^catalogue/joomla/[a-f0-9]{64}\.(jpg|png|gif|webp|avif|pdf)$~D', $path)) {
                throw new RuntimeException('Manifest contains a path outside the old flat catalogue directory.');
            }
            if (! $disk->exists($path)) {
                continue;
            }
            if (hash('sha256', $disk->get($path)) !== $entry['checksum']) {
                throw new RuntimeException('Old file changed since manifest creation: '.$path);
            }
            if (MediaAsset::where('storage_path', $path)->exists() || Product::withTrashed()->where('description_html', 'like', '%'.$path.'%')->orWhere('summary', 'like', '%'.$path.'%')->exists() || Category::withTrashed()->where('description', 'like', '%'.$path.'%')->exists() || Brand::withTrashed()->where('description', 'like', '%'.$path.'%')->exists() || ProductTestimonial::where('content_html', 'like', '%'.$path.'%')->exists()) {
                throw new RuntimeException('Old file is still referenced: '.$path);
            }
            $paths[] = $path;
        }
        if ($this->option('apply')) {
            foreach ($paths as $path) {
                // Every path was allowlisted, checksummed and checked for references above.
                if (! $disk->delete($path)) {
                    throw new RuntimeException('Could not remove obsolete file: '.$path);
                }
            }
        }
        $this->info(count($paths).($this->option('apply') ? ' obsolete files removed.' : ' obsolete files verified. Dry run; nothing deleted.'));

        return self::SUCCESS;
    }
}
