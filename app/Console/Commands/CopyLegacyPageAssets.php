<?php

namespace App\Console\Commands;

use App\Services\Catalogue\CatalogueMedia;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use RuntimeException;
use Throwable;

class CopyLegacyPageAssets extends Command
{
    protected $signature = 'site:copy-legacy-assets {source : Backup folder containing files/} {--apply : Copy the verified files to public storage}';

    protected $description = 'Verify or copy only the referenced legacy news and Parts assets';

    public function handle(CatalogueMedia $media): int
    {
        $source = $this->argument('source');
        if (! is_string($source)) {
            throw new RuntimeException('A backup path is required.');
        }
        $manifest = json_decode(File::get(resource_path('data/legacy-page-assets.json')), true, flags: JSON_THROW_ON_ERROR);
        $assets = [];
        foreach ($manifest as $entry) {
            $asset = $media->inspect($source.'/files', $entry['source_path']);
            if ($asset['checksum'] !== $entry['checksum']) {
                throw new RuntimeException('Source asset changed: '.$entry['source_path']);
            }
            if (! is_string($entry['storage_path']) || str_contains($entry['storage_path'], '..') || ! preg_match('~^(news/|pages/parts/)~', $entry['storage_path'])) {
                throw new RuntimeException('Unsafe asset destination.');
            }
            $asset['storage_path'] = $entry['storage_path'];
            $assets[$entry['storage_path']] = $asset;
        }
        if ($this->option('apply')) {
            $directory = storage_path('app/private/page-assets/'.Str::uuid7());
            $ledger = [];
            try {
                $staged = $media->stage($assets, $directory.'/stage');
                $media->publish($staged, $directory.'/previous', $ledger);
            } catch (Throwable $error) {
                $media->rollback($ledger);
                throw $error;
            } finally {
                File::deleteDirectory($directory.'/stage');
            }
        }
        $this->info(count($assets).($this->option('apply') ? ' page assets copied and checksummed.' : ' page assets validated. Dry run; nothing written.'));

        return self::SUCCESS;
    }
}
