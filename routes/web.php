<?php

use App\Http\Controllers\CatalogueController;
use App\Http\Controllers\EnquiryController;
use App\Livewire\Admin\CatalogueEditor;
use App\Livewire\Admin\CatalogueImportExport;
use App\Livewire\Admin\CatalogueIndex;
use App\Livewire\Admin\EnquiryInbox;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Services\Catalogue\PublicCatalogue;
use App\Services\Catalogue\SiteContent;
use Illuminate\Support\Facades\Route;

Route::get('/', [CatalogueController::class, 'home'])->name('home');
Route::view('/about', 'about', require resource_path('data/about.php'))->name('about');
Route::redirect('/about.html', '/about', 301);

$catalogue = CatalogueController::class;
Route::get('/machinery', [$catalogue, 'index'])->name('machinery.index');
Route::get('/new-machinery', [$catalogue, 'new'])->name('machinery.new');
Route::get('/used-machinery', [$catalogue, 'used'])->name('machinery.used');
Route::get('/machinery/{slug}', [$catalogue, 'show'])->name('machinery.show');
Route::get('/categories', fn () => app($catalogue)->directory('categories', app(PublicCatalogue::class)))->name('categories.index');
Route::get('/categories/{slug}', [$catalogue, 'category'])->name('categories.show');
Route::get('/brands', fn () => app($catalogue)->directory('brands', app(PublicCatalogue::class)))->name('brands.index');
Route::get('/brands/{slug}', [$catalogue, 'brand'])->name('brands.show');
foreach (['parts', 'service', 'contact', 'cookie-policy'] as $page) {
    Route::get('/'.$page, fn () => app($catalogue)->page($page, app(SiteContent::class)))->name($page);
}
Route::get('/news', [$catalogue, 'news'])->name('news.index');
Route::get('/news/{slug}', [$catalogue, 'article'])->name('news.show');
Route::get('/sitemap', [$catalogue, 'sitemap'])->name('sitemap');
Route::get('/sitemap.xml', [$catalogue, 'xml'])->name('sitemap.xml');
Route::get('/index.php', fn () => redirect('/', 301));
Route::post('/enquiries', EnquiryController::class.'@store')->name('enquiries.store');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard', fn () => auth()->user()->role === 'admin' ? redirect()->route('admin.dashboard') : view('dashboard'))->name('dashboard');
});

require __DIR__.'/settings.php';

Route::middleware(['auth', 'verified', 'can:manage-catalogue'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', function () {
        return view('admin.dashboard', [
            'products' => Product::count(),
            'published' => Product::catalogue()->count(),
            'brands' => Brand::count(),
            'categories' => Category::count(),
        ]);
    })->name('dashboard');
    Route::get('/enquiries', EnquiryInbox::class)->name('enquiries.index');
    Route::get('/enquiries/{id}', EnquiryInbox::class)->whereUuid('id')->name('enquiries.show');
    Route::get('/import-export', CatalogueImportExport::class)->name('import-export');
    Route::get('/{resource}', CatalogueIndex::class)->whereIn('resource', ['products', 'categories', 'brands'])->name('catalogue.index');
    Route::get('/{resource}/create', CatalogueEditor::class)->whereIn('resource', ['products', 'categories', 'brands'])->name('catalogue.create');
    Route::get('/{resource}/{id}/edit', CatalogueEditor::class)->whereIn('resource', ['products', 'categories', 'brands'])->whereUuid('id')->name('catalogue.edit');
});
