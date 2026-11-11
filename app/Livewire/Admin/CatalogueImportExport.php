<?php

namespace App\Livewire\Admin;

use App\Models\Brand;
use App\Models\Product;
use App\Services\Catalogue\CatalogueImportExport as ImportExportService;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

#[Layout('layouts.app')]
class CatalogueImportExport extends Component
{
    use WithFileUploads;

    public string $activeTab = 'import';

    // Export options
    public string $exportBrandId = '';

    // Import options
    public $uploadFile = null;

    public bool $replaceGallery = false;

    // Validation state
    public bool $hasParsed = false;

    public array $validationSummary = [];

    public array $validationRows = [];

    public array $validationWarnings = [];

    public array $validationErrors = [];

    // Import completion state
    public bool $importCompleted = false;

    public array $importResult = [];

    public function downloadTemplate(ImportExportService $service): BinaryFileResponse
    {
        return $service->template('xlsx');
    }

    public function exportExcel(ImportExportService $service): BinaryFileResponse
    {
        $filters = $this->exportBrandId ? ['brand_id' => $this->exportBrandId] : [];

        return $service->export('xlsx', $filters);
    }

    public function exportCsv(ImportExportService $service): BinaryFileResponse
    {
        $filters = $this->exportBrandId ? ['brand_id' => $this->exportBrandId] : [];

        return $service->export('csv', $filters);
    }

    public function updatedUploadFile(): void
    {
        $this->resetValidationState();
    }

    public function resetValidationState(): void
    {
        $this->hasParsed = false;
        $this->validationSummary = [];
        $this->validationRows = [];
        $this->validationWarnings = [];
        $this->validationErrors = [];
        $this->importCompleted = false;
        $this->importResult = [];
    }

    public function validateSpreadsheet(ImportExportService $service): void
    {
        if (! $this->uploadFile) {
            $this->addError('uploadFile', 'Please select a spreadsheet file.');

            return;
        }

        $path = method_exists($this->uploadFile, 'getRealPath') ? $this->uploadFile->getRealPath() : (string) $this->uploadFile;

        try {
            $rows = $service->parse($path);
        } catch (\Throwable $e) {
            $this->addError('uploadFile', 'Failed to read spreadsheet: '.$e->getMessage());

            return;
        }

        if (empty($rows)) {
            $this->addError('uploadFile', 'No product rows found in the uploaded file.');

            return;
        }

        $validation = $service->validateRows($rows);

        $this->hasParsed = true;
        $this->validationSummary = $validation['summary'];
        $this->validationRows = $validation['rows'];
        $this->validationWarnings = $validation['warnings'];
        $this->validationErrors = $validation['errors'];
    }

    public function executeImport(ImportExportService $service): void
    {
        if (! $this->hasParsed || empty($this->validationRows)) {
            $this->addError('uploadFile', 'Please upload and validate a file first.');

            return;
        }

        if (! empty($this->validationErrors)) {
            $this->addError('uploadFile', 'Cannot proceed while there are validation errors.');

            return;
        }

        $result = $service->execute($this->validationRows, $this->replaceGallery, true);

        $this->importCompleted = true;
        $this->importResult = $result;
        $this->hasParsed = false;
        $this->validationRows = [];
        $this->uploadFile = null;
    }

    public function render()
    {
        return view('livewire.admin.catalogue-import-export', [
            'brands' => Brand::orderBy('name')->get(),
            'totalProducts' => Product::count(),
        ]);
    }
}
