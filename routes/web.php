<?php

use Illuminate\Support\Facades\Route;

Route::view('/', 'home', require resource_path('data/home.php'))->name('home');
Route::view('/about', 'about', require resource_path('data/about.php'))->name('about');
Route::redirect('/about.html', '/about', 301);

Route::middleware(['auth', 'verified'])->group(function () {
    Route::view('dashboard', 'dashboard')->name('dashboard');
});

require __DIR__.'/settings.php';
