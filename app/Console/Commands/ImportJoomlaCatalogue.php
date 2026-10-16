<?php

namespace App\Console\Commands;

use App\Services\Catalogue\JoomlaImporter;
use Illuminate\Console\Command;
use Throwable;

class ImportJoomlaCatalogue extends Command
{
    protected $signature = 'catalogue:import-joomla {source : Folder containing db/db.sql and files/} {--apply : Write the validated import to the database and public storage} {--overwrite-edited : Restore source values for admin-edited or deleted records} {--json : Print the full report as JSON}';

    protected $description = 'Validate or import the legacy VirtueMart catalogue without executing Joomla SQL or PHP';

    public function handle(JoomlaImporter $importer): int
    {
        try {
            $source = $this->argument('source');
            if (! is_string($source)) {
                $this->error('The source must be a backup folder path.');

                return self::FAILURE;
            }
            $report = $importer->run($source, (bool) $this->option('apply'), (bool) $this->option('overwrite-edited'));
        } catch (Throwable $error) {
            $this->error($error->getMessage());

            return self::FAILURE;
        }
        if ($this->option('json')) {
            $this->line(json_encode($report, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR));
        } else {
            $this->info($this->option('apply') ? 'Catalogue imported successfully.' : 'Dry run validated. No database or media files were changed.');
            $this->table(['Record type', 'Count'], array_map(fn ($key, $count) => [$key, $count], array_keys($report['counts']), $report['counts']));
            foreach ($report['warnings'] as $warning) {
                $this->warn($warning);
            }
            if (isset($report['report_path'])) {
                $this->line('Private report: '.$report['report_path']);
            }
        }

        return self::SUCCESS;
    }
}
