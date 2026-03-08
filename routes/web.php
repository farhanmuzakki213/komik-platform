<?php

use App\Livewire\AdminChapterTable;
use App\Livewire\AdminComicManager;
use App\Livewire\AdminDashboard;
use App\Livewire\CreatorDashboard;
use App\Livewire\ComicManager;
use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;

/* HEADER MENU */
Volt::route('/', 'frontend.home')->name('home');
Volt::route('/originals', 'frontend.originals')->name('originals');
Volt::route('/categories', 'frontend.categories')->name('categories');
Volt::route('/rankings', 'frontend.rankings')->name('rankings');

Volt::route('/comic/{comic}', 'frontend.comics.show')->name('comics.show');
Volt::route('/comic/{comic}/episode/{chapter}', 'frontend.comics.read')->name('comics.read');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', function() {
        if (auth()->user()->hasRole('admin')) {
            return redirect()->route('admin.dashboard');
        }
        return redirect()->route('author.dashboard');
    })->name('dashboard');

    Route::middleware(['role:admin'])->group(function () {
        Route::get('/admin/dashboard', AdminDashboard::class)->name('admin.dashboard');

        Route::get('/admin/management', AdminComicManager::class)->name('admin.management');
        Route::get('/admin/management/episodes', AdminChapterTable::class)->name('admin.management.episodes');
    });

    Route::middleware(['role:penulis'])->group(function () {
        Route::get('/author/dashboard', CreatorDashboard::class)->name('author.dashboard');

        Volt::route('/author/comics/create', 'pages.author.serial.comic-create')->name('author.comics.create');
        Volt::route('/author/comics/{comic}/edit', 'pages.author.serial.comic-edit')->name('author.comics.edit');

        Volt::route('/author/comics/{comic}/episodes/create', 'pages.author.episode.episode-create')->name('author.episodes.create');
        Volt::route('/author/comics/{comic}/episodes', 'pages.author.episode.episode-manager')->name('author.episodes.index');
        Volt::route('/author/episodes/{chapter}/edit', 'pages.author.episode.episode-edit')->name('author.episodes.edit');

        Route::get('/author/comics', ComicManager::class)->name('comics.index');
    });
    Route::view('profile', 'profile')->name('profile');
});

require __DIR__.'/auth.php';
