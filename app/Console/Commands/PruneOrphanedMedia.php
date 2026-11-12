<?php

namespace App\Console\Commands;

use App\Models\Brand;
use App\Models\Category;
use App\Models\MediaAsset;
use App\Models\Product;
use App\Models\ProductTestimonial;
use Illuminate\Console\Command;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Support\Facades\Storage;

class PruneOrphanedMedia extends Command
{
    protected $signature = 'catalogue:prune-orphaned-media {--apply : Delete unreferenced media records and orphaned files}';

    protected $description = 'Find and optionally remove unreferenced media records and orphaned files from public storage';

    public function handle(): int
    {
        $apply = (bool) $this->option('apply');
        $disk = Storage::disk('public');

        $orphanedAssets = [];
        $assets = MediaAsset::where('storage_path', 'not like', 'catalogue/joomla/%')->get();
        foreach ($assets as $asset) {
            if (! $asset->isReferenced()) {
                $orphanedAssets[] = $asset;
            }
        }

        $untrackedFiles = [];
        foreach (['products', 'categories', 'brands'] as $dir) {
            if (! $disk->exists($dir)) {
                continue;
            }
            $files = $disk->allFiles($dir);
            foreach ($files as $file) {
                if (MediaAsset::where('storage_path', $file)->exists()) {
                    continue;
                }
                if ($this->isFileReferencedInHtml($file)) {
                    continue;
                }
                $untrackedFiles[] = $file;
            }
        }

        $totalAssets = count($orphanedAssets);
        $totalUntracked = count($untrackedFiles);

        if ($totalAssets === 0 && $totalUntracked === 0) {
            $this->info('No orphaned media assets or untracked files found.');

            return self::SUCCESS;
        }

        if ($totalAssets > 0) {
            $this->line('<comment>Unreferenced Media Assets:</comment>');
            foreach ($orphanedAssets as $asset) {
                $this->line(" - [{$asset->id}] {$asset->storage_path}");
            }
        }

        if ($totalUntracked > 0) {
            $this->line('<comment>Untracked Files in Storage:</comment>');
            foreach ($untrackedFiles as $file) {
                $this->line(" - {$file}");
            }
        }

        if ($apply) {
            foreach ($orphanedAssets as $asset) {
                $disk->delete($asset->storage_path);
                $asset->delete();
            }

            foreach ($untrackedFiles as $file) {
                $disk->delete($file);
            }

            $this->cleanupEmptyDirectories($disk, ['products', 'categories', 'brands']);

            $this->info("Purge complete: Removed {$totalAssets} orphaned media asset(s) and {$totalUntracked} untracked file(s).");
        } else {
            $this->info("Dry run complete: Found {$totalAssets} orphaned media asset(s) and {$totalUntracked} untracked file(s). Run with --apply to delete them.");
        }

        return self::SUCCESS;
    }

    private function isFileReferencedInHtml(string $file): bool
    {
        if (Product::withTrashed()->where(function ($query) use ($file) {
            $query->where('description_html', 'like', '%'.$file.'%')->orWhere('summary', 'like', '%'.$file.'%');
        })->exists()) {
            return true;
        }

        if (Category::withTrashed()->where('description', 'like', '%'.$file.'%')->exists()) {
            return true;
        }

        if (Brand::withTrashed()->where('description', 'like', '%'.$file.'%')->exists()) {
            return true;
        }

        if (ProductTestimonial::where('content_html', 'like', '%'.$file.'%')->exists()) {
            return true;
        }

        return false;
    }

    /**
     * @param  Filesystem  $disk
     * @param  list<string>  $directories
     */
    private function cleanupEmptyDirectories($disk, array $directories): void
    {
        foreach ($directories as $dir) {
            if (! $disk->exists($dir)) {
                continue;
            }
            $subdirs = $disk->allDirectories($dir);
            // Sort by depth descending so deepest child directories are inspected and removed first
            usort($subdirs, fn (string $a, string $b) => substr_count($b, '/') <=> substr_count($a, '/'));
            foreach ($subdirs as $subdir) {
                if (count($disk->allFiles($subdir)) === 0 && count($disk->directories($subdir)) === 0) {
                    $disk->deleteDirectory($subdir);
                }
            }
        }
    }
}
