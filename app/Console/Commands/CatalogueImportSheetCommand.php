<?php

namespace App\Console\Commands;

use App\Services\Catalogue\CatalogueImportExport;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class CatalogueImportSheetCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'catalogue:import-sheet
                            {file : Path to the spreadsheet (.xlsx or .csv)}
                            {--replace-gallery : Replace existing product gallery with sheet images instead of appending}
                            {--skip-images : Skip downloading images}
                            {--dry-run : Validate and simulate the import without writing changes}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import or update catalogue products from an Excel (.xlsx) or CSV file with remote/local images';

    /**
     * Execute the console command.
     */
    public function handle(CatalogueImportExport $service): int
    {
        $filePath = (string) $this->argument('file');

        if (! File::exists($filePath)) {
            $this->error("File not found: {$filePath}");

            return self::FAILURE;
        }

        $dryRun = (bool) $this->option('dry-run');
        $replaceGallery = (bool) $this->option('replace-gallery');
        $skipImages = (bool) $this->option('skip-images');

        $this->info("Parsing spreadsheet: {$filePath}");

        try {
            $rows = $service->parse($filePath);
        } catch (\Throwable $e) {
            $this->error("Failed to parse sheet: {$e->getMessage()}");

            return self::FAILURE;
        }

        $this->line('Parsed '.count($rows).' row(s).');

        $validation = $service->validateRows($rows);

        $this->newLine();
        $this->info('--- Validation Summary ---');
        $this->line('Total rows:        '.($validation['summary']['total'] ?? 0));
        $this->line('Existing matched:  '.($validation['summary']['to_update'] ?? 0));
        $this->line('New products:      '.($validation['summary']['to_create'] ?? 0));

        if (! empty($validation['warnings'])) {
            $this->newLine();
            $this->warn('Warnings ('.count($validation['warnings']).'):');
            foreach (array_slice($validation['warnings'], 0, 10) as $warning) {
                $this->warn(" - {$warning}");
            }
            if (count($validation['warnings']) > 10) {
                $this->warn(' ... and '.(count($validation['warnings']) - 10).' more warnings.');
            }
        }

        if (! empty($validation['errors'])) {
            $this->newLine();
            $this->error('Errors ('.count($validation['errors']).'):');
            foreach ($validation['errors'] as $error) {
                $this->error(" - {$error}");
            }

            return self::FAILURE;
        }

        if ($dryRun) {
            $this->newLine();
            $this->info('[DRY RUN] Validation successful. No changes were made to the database or storage.');

            return self::SUCCESS;
        }

        $this->newLine();
        $this->info('Executing import...'.($replaceGallery ? ' (REPLACING existing galleries)' : ' (APPENDING to galleries)'));

        $result = $service->execute($validation['rows'], $replaceGallery, ! $skipImages);

        $this->newLine();
        $this->info('--- Import Complete ---');
        $this->line("Products created:  {$result['created']}");
        $this->line("Products updated:  {$result['updated']}");
        $this->line("Failed rows:       {$result['failed']}");

        if (! empty($result['errors'])) {
            $this->newLine();
            $this->error('Execution errors ('.count($result['errors']).'):');
            foreach ($result['errors'] as $err) {
                $this->error(" - {$err}");
            }

            return self::FAILURE;
        }

        return self::SUCCESS;
    }
}
