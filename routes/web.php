<?php

use App\Livewire\Admin\CatalogueEditor;
use App\Livewire\Admin\CatalogueIndex;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Support\Facades\Route;

Route::view('/', 'home', require resource_path('data/home.php'))->name('home');
Route::view('/about', 'about', require resource_path('data/about.php'))->name('about');
Route::redirect('/about.html', '/about', 301);

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
    Route::get('/{resource}', CatalogueIndex::class)->whereIn('resource', ['products', 'categories', 'brands'])->name('catalogue.index');
    Route::get('/{resource}/create', CatalogueEditor::class)->whereIn('resource', ['products', 'categories', 'brands'])->name('catalogue.create');
    Route::get('/{resource}/{id}/edit', CatalogueEditor::class)->whereIn('resource', ['products', 'categories', 'brands'])->whereUuid('id')->name('catalogue.edit');
});
