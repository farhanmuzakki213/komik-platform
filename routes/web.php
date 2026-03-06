<?php

use App\Livewire\AdminComicManager;
use App\Livewire\AdminDashboard;
use App\Livewire\CreatorDashboard;
use App\Livewire\ComicManager;
use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome')->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    // FAKTA: Arahkan langsung ke Livewire Class
    Route::get('/dashboard', function() {
        if (auth()->user()->hasRole('admin')) {
            return redirect()->route('admin.dashboard');
        }
        return redirect()->route('author.dashboard');
    })->name('dashboard');

    Route::middleware(['role:admin'])->group(function () {
        Route::get('/admin/dashboard', AdminDashboard::class)->name('admin.dashboard');
        Route::get('/admin/management', AdminComicManager::class)->name('admin.management');
    });

    Route::middleware(['role:penulis'])->group(function () {
        Route::get('/author/dashboard', CreatorDashboard::class)->name('author.dashboard');
        Route::get('/author/comics', ComicManager::class)->name('comics.index');
    });
    Route::view('profile', 'profile')->name('profile');
});

require __DIR__.'/auth.php';
