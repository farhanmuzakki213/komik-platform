<?php

use App\Livewire\CreatorDashboard;
use Illuminate\Support\Facades\Route;
use App\Livewire\ComicManager;

Route::view('/', 'welcome');

Route::middleware(['auth', 'verified'])->group(function () {
    // FAKTA: Arahkan langsung ke Livewire Class
    Route::get('/dashboard', CreatorDashboard::class)->name('dashboard');

    Route::get('/comics', ComicManager::class)->name('comics.index');
    // Rute bawaan Breeze untuk profile
    Route::view('profile', 'profile')->name('profile');
});

require __DIR__.'/auth.php';
