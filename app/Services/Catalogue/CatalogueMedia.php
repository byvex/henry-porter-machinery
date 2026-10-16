<?php

namespace App\Services\Catalogue;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

class CatalogueMedia
{
    /**
     * @return array{source_path: string, absolute_path: string, storage_path: string, mime_type: string, checksum: string}
     */
    public function inspect(string $root, string $url): array
    {
        $url = html_entity_decode(trim($url), ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $parts = parse_url($url);
        if ($parts === false || isset($parts['scheme']) && ! in_array(strtolower($parts['scheme']), ['http', 'https'], true)) {
            throw new RuntimeException('Unsupported local media URL.');
        }
        if (isset($parts['host']) && ! $this->ownHost($parts['host'])) {
            throw new RuntimeException('External media must not be downloaded by the importer.');
        }
        $relative = ltrim(str_replace('\\', '/', rawurldecode($parts['path'] ?? '')), '/');
        while (str_starts_with($relative, './')) {
            $relative = substr($relative, 2);
        }
        if ($relative === '' || str_contains($relative, "\0") || str_contains($relative, ':') || in_array('..', explode('/', $relative), true)) {
            throw new RuntimeException('Unsafe source media path.');
        }
        $base = realpath($root);
        $source = realpath($root.DIRECTORY_SEPARATOR.$relative);
        if ($base === false || $source === false || ! is_file($source)) {
            throw new RuntimeException('Missing source media: '.$relative);
        }
        $prefix = rtrim(str_replace('\\', '/', $base), '/').'/';
        if (! str_starts_with(strtolower(str_replace('\\', '/', $source)), strtolower($prefix))) {
            throw new RuntimeException('Source media escapes the backup files directory.');
        }
        $mime = mime_content_type($source);
        $extension = match ($mime) {
            'image/jpeg' => 'jpg', 'image/png' => 'png', 'image/gif' => 'gif',
            'image/webp' => 'webp', 'image/avif' => 'avif', 'application/pdf' => 'pdf',
            default => throw new RuntimeException('Unsupported media MIME type: '.$relative),
        };

        $checksum = hash_file('sha256', $source);
        if ($checksum === false) {
            throw new RuntimeException('Could not checksum source media.');
        }

        return [
            'source_path' => $relative,
            'absolute_path' => $source,
            'storage_path' => 'catalogue/joomla/'.hash('sha256', $relative).'.'.$extension,
            'mime_type' => $mime,
            'checksum' => $checksum,
        ];
    }

    public function ownHost(string $host): bool
    {
        return in_array(strtolower($host), ['henryportermachinery.co.uk', 'www.henryportermachinery.co.uk'], true);
    }

    /**
     * @param  array<string, array{source_path: string, absolute_path: string, storage_path: string, checksum: string}>  $assets
     * @return array<string, array{path: string, checksum: string}>
     */
    public function stage(array $assets, string $directory): array
    {
        File::ensureDirectoryExists($directory);
        $staged = [];
        foreach ($assets as $asset) {
            $key = $asset['storage_path'];
            if (isset($staged[$key])) {
                continue;
            }
            $path = $directory.'/'.hash('sha256', $key);
            if (! copy($asset['absolute_path'], $path) || hash_file('sha256', $path) !== $asset['checksum']) {
                throw new RuntimeException('Media staging verification failed: '.$asset['source_path']);
            }
            $staged[$key] = ['path' => $path, 'checksum' => $asset['checksum']];
        }

        return $staged;
    }

    /** The ledger is populated before each write so even a partially failed write can be reversed. */
    /**
     * @param  array<string, array{path: string, checksum: string}>  $staged
     * @param  array<string, string|null>  $ledger
     */
    public function publish(array $staged, string $backupDirectory, array &$ledger): void
    {
        File::ensureDirectoryExists($backupDirectory);
        $disk = Storage::disk('public');
        foreach ($staged as $destination => $file) {
            $exists = $disk->exists($destination);
            if ($exists && hash('sha256', $disk->get($destination)) === $file['checksum']) {
                continue;
            }
            $backup = null;
            if ($exists) {
                $backup = $backupDirectory.'/'.hash('sha256', $destination);
                File::put($backup, $disk->get($destination));
            }
            $ledger[$destination] = $backup;
            $stream = fopen($file['path'], 'rb');
            try {
                if ($stream === false || ! $disk->put($destination, $stream)) {
                    throw new RuntimeException('Failed to publish catalogue media: '.$destination);
                }
            } finally {
                if (is_resource($stream)) {
                    fclose($stream);
                }
            }
            if (hash('sha256', $disk->get($destination)) !== $file['checksum']) {
                throw new RuntimeException('Published media checksum mismatch: '.$destination);
            }
        }
    }

    /**
     * @param  array<string, string|null>  $ledger
     */
    public function rollback(array $ledger): void
    {
        $disk = Storage::disk('public');
        foreach (array_reverse($ledger, true) as $destination => $backup) {
            if ($backup === null) {
                if (! $disk->delete($destination)) {
                    throw new RuntimeException('Could not remove a partially imported media file.');
                }
            } elseif (! $disk->put($destination, File::get($backup))) {
                throw new RuntimeException('Could not restore previous catalogue media.');
            }
        }
    }
}
