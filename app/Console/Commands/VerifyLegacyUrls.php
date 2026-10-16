<?php

namespace App\Console\Commands;

use App\Http\Controllers\CatalogueController;
use App\Models\LegacyUrlMapping;
use App\Services\Catalogue\LegacyUrls;
use Illuminate\Console\Command;
use Illuminate\Contracts\Http\Kernel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

class VerifyLegacyUrls extends Command
{
    protected $signature = 'catalogue:verify-legacy-urls {--apply : Save and activate verified mappings}';

    protected $description = 'Check the versioned sitemap and imported URL mappings against public Laravel destinations';

    public function handle(LegacyUrls $urls): int
    {
        $inventory = json_decode(File::get(resource_path('data/legacy-sitemap.json')), true, flags: JSON_THROW_ON_ERROR);
        $canonical = app(CatalogueController::class)->sitemapUrls();
        $failures = [];
        $report = [];
        $mappings = LegacyUrlMapping::all()->keyBy('source_hash');
        foreach (array_unique(array_merge($inventory['paths'], ['/filtering/', '/filtering', '/contact-form', '/index.php'])) as $path) {
            if (in_array($path, $canonical, true)) {
                $report[] = ['source' => $path, 'target' => $path, 'status' => 'canonical'];

                continue;
            }
            $existing = $mappings->get($urls->hash($path));
            $target = $existing ? ['type' => $existing->target_type, 'id' => $existing->target_id, 'filters' => $existing->filters ?? []] : $urls->infer($path);
            if (! $target) {
                $failures[] = $path;

                continue;
            }
            if (! $existing) {
                $mappings->put($urls->hash($path), new LegacyUrlMapping(['source_hash' => $urls->hash($path), 'source_path' => $path, 'target_type' => $target['type'], 'target_id' => $target['id'], 'filters' => $target['filters'], 'evidence' => 'live-sitemap']));
            }
        }
        $checked = [];
        config(['session.driver' => 'array']);
        app('session')->forgetDrivers();
        foreach ($mappings as $map) {
            $destination = $urls->destination($map->target_type, $map->target_id, $map->filters);
            $path = parse_url($destination ?? '', PHP_URL_PATH);
            $okay = $destination !== null && in_array($path, $canonical, true);
            if ($okay && ! isset($checked[$destination])) {
                $response = app(Kernel::class)->handle(Request::create(rtrim(config('app.url'), '/').$destination, 'GET'));
                $okay = $response->getStatusCode() === 200;
                $checked[$destination] = $okay;
            } elseif ($okay) {
                $okay = $checked[$destination];
            }
            $report[] = ['source' => $map->source_path.($map->source_query ? '?'.$map->source_query : ''), 'target' => $destination, 'status' => $okay ? 'verified' : 'unresolved'];
            if (! $okay) {
                $failures[] = $map->source_path;

                continue;
            }
            if ($this->option('apply')) {
                $map->verification_status = 'verified';
                $map->save();
            }
        }
        if ($this->option('apply')) {
            foreach ($report as &$entry) {
                if ($entry['status'] === 'unresolved' || $entry['target'] === null) {
                    continue;
                }
                $response = app(Kernel::class)->handle(Request::create(rtrim(config('app.url'), '/').$entry['source'], 'GET'));
                $location = $response->headers->get('Location');
                $actual = $location ? (parse_url($location, PHP_URL_PATH) ?: '/').(($query = parse_url($location, PHP_URL_QUERY)) ? '?'.$query : '') : null;
                $entry['http_status'] = $response->getStatusCode();
                $correct = $entry['source'] === $entry['target'] ? $response->getStatusCode() === 200 : ($response->getStatusCode() === 301 && $actual === $entry['target']);
                if (! $correct) {
                    $entry['status'] = 'unresolved';
                    $failures[] = $entry['source'];
                }
            }
            unset($entry);
        }
        $result = ['sitemap_paths' => count($inventory['paths']), 'checked_mappings' => count($mappings), 'failures' => array_values(array_unique($failures)), 'urls' => $report];
        if ($this->option('apply')) {
            File::ensureDirectoryExists(storage_path('app/private/legacy-urls'));
            File::put(storage_path('app/private/legacy-urls/report.json'), json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR));
        }
        $this->info(count($mappings).' mappings checked; '.count($result['failures']).' unresolved. '.($this->option('apply') ? 'Verification report saved privately.' : 'Dry run; no mappings changed.'));
        foreach ($result['failures'] as $failure) {
            $this->error($failure);
        }

        return $failures ? self::FAILURE : self::SUCCESS;
    }
}
